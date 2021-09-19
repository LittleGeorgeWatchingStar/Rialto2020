<?php


namespace Rialto\Purchasing\Manufacturer\Cli;


use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Rialto\Purchasing\Manufacturer\Command\PushManufacturerFeatureCommand;
use Rialto\Purchasing\Manufacturer\Command\PushManufacturerFeatureHandler;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Command line interface for pushing module manufacturer features to Madison.
 */
final class BulkPushModuleManufacturersConsoleCommand extends Command
{
    const NAME = 'purchasing:push-module-manufacturers';

    /** @var StockItemRepository */
    private $repo;

    /** @var PushManufacturerFeatureHandler */
    private $handler;

    public function __construct(EntityManagerInterface $em,
                                PushManufacturerFeatureHandler $handler)
    {
        parent::__construct(self::NAME);
        $this->repo = $em->getRepository(StockItem::class);
        $this->handler = $handler;
    }

    protected function configure()
    {
        $this->setDescription('Push all module manufacturers to Madison.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $modules = $this->repo->findModules();
            $command = PushManufacturerFeatureCommand::fromModules($modules);
            $this->handler->handle($command);
            $io->success('Push successful.');
        } catch (Exception $exception) {
            $io->error("Error: {$exception->getMessage()}");
        }
    }
}
