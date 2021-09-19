<?php

namespace Rialto\Port\CommandBus;


use Psr\Log\LoggerInterface;
use Rialto\Logging\Cli\LoggingCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\SerializerInterface;

final class HandleCommandConsoleCommand extends LoggingCommand
{
    const NAME = 'command-bus:handle-command';

    /** @var CommandBus */
    private $commandBus;

    /** @var SerializerInterface */
    private $serializer;

    public function __construct(CommandBus $commandBus,
                                SerializerInterface $serializer,
                                LoggerInterface $logger)
    {
        parent::__construct(self::NAME, $logger);
        $this->commandBus = $commandBus;
        $this->serializer = $serializer;
    }

    protected function configure()
    {
        $this
            ->setDescription('Handle a serialized command.')
            ->addArgument('commandName', InputArgument::REQUIRED)
            ->addArgument('commandArguments', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('commandName');
        $args = $input->getArgument('commandArguments');

        /** @var Command $command */
        $command = $this->serializer->deserialize($args, $name, 'json');

        $this->commandBus->handle($command);

        return 0;
    }
}
