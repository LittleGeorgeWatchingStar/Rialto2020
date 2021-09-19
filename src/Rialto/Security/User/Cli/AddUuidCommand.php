<?php

namespace Rialto\Security\User\Cli;

use Rialto\Database\Orm\DbManager;
use Rialto\Security\User\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Adds an SSO UUID to an existing user.
 */
class AddUuidCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('sso:uuid')
            ->setDescription('Adds an SSO UUID to an existing user')
            ->addArgument('username', InputArgument::REQUIRED, 'The user whose UUID will be set')
            ->addArgument('uuid', InputArgument::REQUIRED, 'The SSO UUID');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $uuid = $input->getArgument('uuid');

        $dbm = $this->getContainer()->get(DbManager::class);
        /* @var $user User */
        $user = $dbm->find(User::class, $username);
        if (! $user) {
            $output->writeln("<error>No such user $username</error>");
            return;
        }
        $user->addUuid($uuid);
        $dbm->flush();

        $output->writeln("User $user has the following UUIDs:");
        foreach ($user->getUuids() as $uuid) {
            $output->writeln("  $uuid");
        }
    }
}
