<?php

namespace Rialto\Logging\Cli;


use MongoDB\Database;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class RecreateMongoLogsCommand extends Command
{
    const MAX_COLLECTION_SIZE = 100; // MB

    private static $collections = [
        'automation',
        'email',
        'production',
    ];

    /** @var Database */
    private $database;

    public function __construct(Database $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    protected function configure()
    {
        $this->setName('log:mongo:recreate')
            ->setDescription("DELETES and recreates the Mongo logs")
            ->addArgument('collection', InputArgument::OPTIONAL,
                'The name of the collection to recreate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $collections = $this->getCollectionsToRecreate($input, $output);
        if (!$collections) {
            return;
        }
        $helper = $this->getHelper('question');
        $prompt = sprintf("This will delete the entire contents of '%s'! Are you sure? ",
            join("', '", $collections));
        $question = new ConfirmationQuestion($prompt, false);

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln("Aborting...");
            return;
        }

        foreach ($collections as $name) {
            $collection = $this->database->selectCollection($name);
            $collection->drop();
            $output->writeln("Dropped '$name' collection");
            $this->database->createCollection($name, [
                'capped' => true,
                'size' => self::MAX_COLLECTION_SIZE * 1024 * 1024, // bytes
            ]);
            $output->writeln("Created '$name' collection");
        }
    }

    private function getCollectionsToRecreate(InputInterface $input,
                                              OutputInterface $output)
    {
        $collection = trim($input->getArgument('collection'));
        if ($collection) {
            if (in_array($collection, self::$collections)) {
                return [$collection];
            } else {
                $output->writeln("<error>Invalid collection $collection.</error>");
                return null;
            }
        } else {
            return self::$collections;
        }
    }

}
