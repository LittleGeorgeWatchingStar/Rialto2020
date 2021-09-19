<?php

namespace Rialto\Manufacturing\Customization\Cli;

use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Substitution;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 *
 */
class ValidateSubstitutionsCommand extends ContainerAwareCommand
{
    /** @var ValidatorInterface */
    private $validator;

    /** @var OutputInterface */
    private $output;

    protected function configure()
    {
        $this->setName('rialto:validate-substitutions')
            ->setDescription('Check substitution and customization records for validity')
            ->addOption('groups', null, InputOption::VALUE_OPTIONAL,
                'Validation groups to use (comma-separated)', 'Default');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->validator = $this->getContainer()
            ->get('validator');
        $groups = array_map('trim', explode(',', $input->getOption('groups')));
        $subSummary = $this->validateSubstitutions($groups);
        $cmzSummary = $this->validateCustomizations($groups);

        $output->writeln("\nSUMMARY:");
        $output->writeln($subSummary);
        $output->writeln($cmzSummary);
    }

    private function validateSubstitutions(array $groups)
    {
        $substitutions = $this->getContainer()
            ->get('doctrine')
            ->getRepository(Substitution::class)
            ->findAll();

        return $this->validate($substitutions, 'substitutions', $groups);
    }

    private function validate(array $entities, $type, array $groups)
    {
        $this->output->writeln("\n=== Validating $type ===\n");
        $numErrors = 0;
        foreach ( $entities as $entity ) {
            $errors = $this->validator->validate($entity, null, $groups);
            if ( count($errors) > 0 ) {
                $this->output->writeln("$entity is invalid");
                $this->output->writeln((string) $errors);
                $numErrors ++;
            }
        }

        $total = count($entities);
        return "$numErrors of $total $type had errors.";
    }

    private function validateCustomizations(array $groups)
    {
        $customizations = $this->getContainer()
            ->get('doctrine')
            ->getRepository(Customization::class)
            ->findAll();

        return $this->validate($customizations, 'customizations', $groups);
    }
}
