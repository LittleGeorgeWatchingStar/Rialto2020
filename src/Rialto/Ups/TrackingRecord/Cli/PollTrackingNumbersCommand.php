<?php


namespace Rialto\Ups\TrackingRecord\Cli;


use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Rialto\Port\CommandBus\CommandBus;
use Rialto\Ups\TrackingRecord\Command\UpdateTrackingRecordCommand;
use Rialto\Ups\TrackingRecord\TrackingRecord;
use Rialto\Ups\TrackingRecord\TrackingRecordRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Poll the UPS API for any updates to tracking numbers that are not delivered.
 */
final class PollTrackingNumbersCommand extends Command
{
    const NAME = 'ups-tracking-record:poll-tracking-numbers';

    /** @var TrackingRecordRepository */
    private $trackingRecordRepo;

    /** @var CommandBus */
    private $bus;

    public function __construct(EntityManagerInterface $em, CommandBus $bus)
    {
        parent::__construct(self::NAME);
        $this->trackingRecordRepo = $em->getRepository(TrackingRecord::class);
        $this->bus = $bus;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $openRecords = $this->trackingRecordRepo->getUndelivered();

        foreach ($openRecords as $record) {
            try {
                $command = new UpdateTrackingRecordCommand($record->getTrackingNumber());
                $this->bus->handle($command);
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
            }
            sleep(1);
        }

        return 0;
    }
}
