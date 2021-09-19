<?php


namespace Rialto\Stock\Item\Cli;


use Doctrine\ORM\EntityManagerInterface;
use Rialto\Geppetto\Cad\GetLibraryPackagesRequest;
use Rialto\Geppetto\GeppettoClient;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class BulkSetDefaultWorkOrderCommand extends Command
{
    const NAME = 'stock-item:bulk-set-default-work-order';

    /** @var EntityManagerInterface */
    private $em;

    /** @var GeppettoClient */
    private $geppetto;

    public function __construct(EntityManagerInterface $em,
                                GeppettoClient $geppetto)
    {
        parent::__construct(self::NAME);
        $this->em = $em;
        $this->geppetto = $geppetto;
    }

    protected function configure()
    {
        $this->addArgument('type', InputArgument::REQUIRED, 'Work Type');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');

        if (!in_array($type, [WorkType::SMT, WorkType::THROUGH_HOLE])) {
            $io->error("Type must be one of " . WorkType::SMT . " or " . WorkType::THROUGH_HOLE);
            return 1;
        }

        $repo = $this->em->getRepository(StockItem::class);

        $request = $this->getRequest($type);
        $packageNames = array_map(function ($package) {
            return $package['name'];
        }, $this->geppetto->getLibraryPackages($request));

        foreach ($packageNames as $package) {
            $items = $repo->findBy([
                'package' => $package,
                'defaultWorkType' => null,
            ]);
            $io->writeln("PACKAGE: $package - $type");
            foreach ($items as $item) {
                $io->writeln($item->getSku());
                $item->setDefaultWorkType($this->em->find(WorkType::class, $type));
            }
        }

        $this->em->flush();

        return 0;
    }

    private function getRequest(string $type): GetLibraryPackagesRequest
    {
        // TODO: Handle more libraries.
        $library = 'qualified_1';
        return new GetLibraryPackagesRequest($library,
            $type === WorkType::THROUGH_HOLE);
    }
}
