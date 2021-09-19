<?php

namespace Rialto\Stock\Item\Version\Cli;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeactivateInvalidVersionsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('stock:deactivate-invalid-versions')
            ->addOption('filter', null, InputOption::VALUE_OPTIONAL)
            ->addOption('commit', null, InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        /** @var $em EntityManagerInterface */
        $em = $this->getContainer()->get(EntityManagerInterface::class);

        /** @var $qb QueryBuilder */
        $qb = $em->getRepository(ItemVersion::class)
            ->createQueryBuilder('v')
            ->join('v.stockItem', 'item')
            ->andWhere("v.version = ''")
            ->andWhere('item instance of ' . ManufacturedStockItem::class);
        if (($filter = $input->getOption('filter'))) {
            $qb->andWhere('item.stockCode like :filter')
                ->setParameter('filter', "%$filter%");
        }

        /** @var $invalid ItemVersion[] */
        $invalid = $qb->getQuery()
            ->getResult();

        foreach ($invalid as $version) {
            $io->writeln(sprintf('%s is invalid', $version->getFullSku()));
            $version->deactivate();
        }

        if ($input->getOption('commit')) {
            $em->flush();
            $io->success(sprintf('Deactivated %d invalid versions.', count($invalid)));
        } else {
            $io->note("Option --commit not given; no changes made.");
        }
    }

}
