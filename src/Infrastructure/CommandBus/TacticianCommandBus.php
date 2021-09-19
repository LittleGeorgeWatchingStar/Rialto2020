<?php


namespace Infrastructure\CommandBus;


use Rialto\Port\CommandBus\Command;
use Rialto\Port\CommandBus\CommandBus;

/**
 * A CommandBus Adapter for Tactician
 */
final class TacticianCommandBus implements CommandBus
{
    /** @var \League\Tactician\CommandBus */
    private $tacticianBus;

    public function __construct(\League\Tactician\CommandBus $tacticianBus)
    {
        $this->tacticianBus = $tacticianBus;
    }

    /**
     * @return mixed
     */
    public function handle(Command $command)
    {
        return $this->tacticianBus->handle($command);
    }
}
