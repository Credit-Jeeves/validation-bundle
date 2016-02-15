<?php

namespace RentJeeves\CoreBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Tools\SchemaValidator;

/**
 * This command part of ValidateSchemaCommand from Doctrine
 * We use this command for validate mapping because our db not sync with our schema
 */
class ValidateDoctrineMappingCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
        ->setName('renttrack:validate-doctrine-mapping')
        ->setDescription('Validate the mapping files.')
        ->setHelp(<<<EOT
'Validate that the mapping files are correct.'
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getEntityManager();

        $validator = new SchemaValidator($em);
        $errors = $validator->validateMapping();

        $exit = 0;
        if ($errors) {
            foreach ($errors as $className => $errorMessages) {
                $output->writeln("<error>[Mapping]  FAIL - The entity-class '" . $className . "' mapping is invalid:</error>");

                foreach ($errorMessages as $errorMessage) {
                    $output->writeln('* ' . $errorMessage);
                }

                $output->writeln('');
            }

            $exit = 1;
        } else {
            $output->writeln('<info>[Mapping]  OK - The mapping files are correct.</info>');
        }

        return $exit;
    }
}
