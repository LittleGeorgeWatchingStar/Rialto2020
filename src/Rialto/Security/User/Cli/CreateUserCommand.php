<?php

namespace Rialto\Security\User\Cli;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Security\Role\Role;
use Rialto\Security\User\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Create a new admin user.
 */
class CreateUserCommand extends Command
{
    /** @var ObjectManager */
    private $om;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(ObjectManager $om, ValidatorInterface $validator)
    {
        parent::__construct();
        $this->om = $om;
        $this->validator = $validator;
    }

    protected function configure()
    {
        $this
            ->setName('user:create')
            ->setDescription('Create a new admin user')
            ->addArgument('username', InputArgument::REQUIRED, 'The username')
            ->addArgument('uuid', InputArgument::REQUIRED, 'The SSO UUID');;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $username = $input->getArgument('username');
        $uuid = $input->getArgument('uuid');
        $user = new User($username);
        $user->setName($username);
        $user->addUuid($uuid);
        $user->addRole($this->getRole(Role::ADMIN));

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $this->handleErrors($errors);
            return 1;
        }

        $this->om->persist($user);
        $this->om->flush();
        $this->io->success("User \"$user\" has been created successfully!");
        return 0;
    }

    private function handleErrors(ConstraintViolationListInterface $errors)
    {
        foreach ($errors as $error) {
            $this->io->error($error->getMessage());
        }
    }

    /**
     * @param string $name
     * @return Role|object
     */
    private function getRole($name)
    {
        return $this->om->getRepository(Role::class)
            ->findOneBy(['name' => $name]);
    }
}
