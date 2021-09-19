<?php

namespace Rialto\Security\User\Cli;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Security\Role\Role;
use Rialto\Security\User\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Promotes the given user to admin privileges.
 */
class PromoteUserCommand extends Command
{
    /**
     * @var ObjectManager
     */
    private $om;

    public function __construct(ObjectManager $om)
    {
        parent::__construct();
        $this->om = $om;
    }

    protected function configure()
    {
        $this
            ->setName('user:promote')
            ->setDescription('Promote a user to admin privileges')
            ->addArgument('username', InputArgument::REQUIRED, 'The username');;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        /* @var $user User */
        $user = $this->om->find(User::class, $username);
        if (!$user) {
            throw new \Exception("No such user '$username'");
        }
        $user->addRole($this->getRole(Role::ADMIN));
        $this->om->flush();
        $output->writeln("$username is now an admin.");
    }

    /**
     * @param string $name
     * @return Role|object
     */
    private function getRole($name)
    {
        $repo = $this->om->getRepository(Role::class);
        return $repo->findOneBy(['name' => $name]);
    }
}
