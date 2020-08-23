<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Command;

use Linkorb\MultiRepo\Handler\MultiRepositoryHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class RepositoriesFixCommand extends Command
{
    use HandleInitializationAwareTrait;

    protected static $defaultName = 'linkorb:multi-repo:fix';

    private MultiRepositoryHandler $multiRepositoryHandler;

    public function __construct(MultiRepositoryHandler $multiRepositoryHandler)
    {
        $this->multiRepositoryHandler = $multiRepositoryHandler;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'fixer',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Apply only specified fixer'
            )
            ->addOption(
                'repo',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Run command on specified repositories only'
            )
            ->addOption(
                'label',
                'l',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Only repos with matching label'
            )
            ->setDescription('Setting up repositories specified in repos.yaml, applying fixers for them');

        $this->configureBase();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (is_int($code = $this->handleInitialization($input, $output))) {
            return $code;
        }

        $i = 0;

        try {
            foreach ($this->multiRepositoryHandler->iterateHandle() as $repoOutput) {
                $output->writeln(
                    sprintf(
                        '<fg=green>%04d Repository %s fixed successfully</>',
                        ++$i,
                        $repoOutput->getName()
                    )
                );
            }
        } catch (Throwable $exception) {
            return $this->handleException(
                $output,
                $exception,
                $input->hasOption('debug'),
                sprintf(
                    '<error>%04d Repository failed to fix with message: %s</error>',
                    ++$i,
                    $exception->getMessage()
                ));
        }

        return 0;
    }

    protected function initializeOptions(InputInterface $input): void
    {
        if ($input->getOption('repo') !== []) {
            $this->multiRepositoryHandler->replaceRepositories($input->getOption('repo'));
        }

        if ($input->getOption('fixer') !== []) {
            $this->multiRepositoryHandler->replaceFixers($input->getOption('fixer'));
        }

        if ($input->getOption('label') !== []) {
            $this->multiRepositoryHandler->setIntendedLabels($input->getOption('label'));
        }
    }
}
