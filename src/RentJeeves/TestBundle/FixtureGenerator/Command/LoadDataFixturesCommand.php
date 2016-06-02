<?php
namespace RentJeeves\TestBundle\FixtureGenerator\Command;

use RentJeeves\CoreBundle\Command\BaseCommand;

use RentJeeves\TestBundle\FixtureGenerator\Services\FixtureFinder;
use RentJeeves\TestBundle\FixtureGenerator\Services\FixtureLoadManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class LoadDataFixturesCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('renttrack:fixtures:load')
            ->setDescription('Load test fixtures with Nelmio/Alice.')
            ->addOption(
                'path',
                null,
                InputOption::VALUE_REQUIRED,
                'Load all fixtures from --path". Example: --path=@RjDataBundle/Resources/AliceFixtures/'
            )
            ->addOption(
                'files',
                'f',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'List of files to load from "@RjDataBundle/Resources/AliceFixtures/". Example: --files="test.yml"'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $input->getOption('files');
        $path = $input->getOption('path');

        if (true === empty($path)) {
            throw new InvalidOptionsException('Please, set --path for loading');
        }

        if (false === empty($files) && false === is_array($files)) {
            throw new InvalidOptionsException('Invalid param: --files should be array or empty');
        }

        $fixtures = $this->getFixtureFinder()->getFixtures($path, $files);

        $output->writeln(sprintf('<info>Start loading fixtures...</info>'));

        $objects = $this->getFixtureLoadManager()->load(
            $fixtures,
            function ($message) use ($output) {
                $output->writeln(sprintf('<comment>></comment> <info>%s</info>', $message));
            }
        );

        $output->writeLn(sprintf('<info>Loading is complete. Total count: %s</info>', count($objects)));
    }

    /**
     * @return FixtureLoadManager
     */
    protected function getFixtureLoadManager()
    {
        return $this->getContainer()->get('renttrack.fixture_load_manager');
    }

    /**
     * @return FixtureFinder
     */
    protected function getFixtureFinder()
    {
        return $this->getContainer()->get('renttrack.fixture_finder');
    }
}
