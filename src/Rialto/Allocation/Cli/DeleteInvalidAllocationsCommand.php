<?php

namespace Rialto\Allocation\Cli;


use Rialto\Allocation\Allocation\Orm\StockAllocationRepository;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Database\Orm\DbManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Deletes empty and invalid allocations.
 */
class DeleteInvalidAllocationsCommand extends Command
{
    const NAME = 'allocation:delete-invalid';

    /**
     * @var DbManager
     */
    private $dbm;

    /**
     * @var StockAllocationRepository
     */
    private $repo;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function __construct(DbManager $dbm, ValidatorInterface $validator)
    {
        parent::__construct(self::NAME);
        $this->dbm = $dbm;
        $this->repo = $dbm->getRepository(StockAllocation::class);
        $this->validator = $validator;
    }

    protected function configure()
    {
        $this->setAliases(['rialto:allocation:delete-invalid'])
            ->setDescription('Deletes empty and invalid allocations');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->dbm->beginTransaction();
        try {
            $emptyCount = $this->repo->deleteEmptyAllocations();
            $output->writeln(sprintf("Deleted %s empty allocations.",
                number_format($emptyCount)));

            /** @var StockAllocation[] $allocations */
            $allocations = $this->repo->findAll();
            $allocCount = count($allocations);

            $numDeleted = 0;
            $groups = ['Default', 'thorough'];
            foreach ($allocations as $alloc) {
                $output->write(sprintf("Allocation %8s: ", $alloc->getId()));
                $errors = $this->validator->validate($alloc, null, $groups);
                if (count($errors) > 0) {
                    $output->writeln(sprintf("<error>invalid</error>: %s",
                        $this->formatErrors($errors)));
                    $alloc->setUpdated();
                    $this->dbm->remove($alloc);
                    $numDeleted++;
                } else {
                    $output->writeln("valid");
                }
            }
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        $output->writeln(sprintf("Deleted %s invalid allocations of %s total.",
            number_format($numDeleted),
            number_format($allocCount)));
    }

    private function formatErrors(ConstraintViolationListInterface $errors)
    {
        $output = [];
        foreach ($errors as $error) {
            $output[] = $error->getMessage();
        }
        return join('; ', $output);
    }

}
