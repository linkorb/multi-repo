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
                'debug',
                null,
                InputOption::VALUE_OPTIONAL,
                'Show debug information about errors'
            )
            ->setDescription('Setting up repositories specified in repos.yaml, applying fixers for them');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            if ($input->getOption('repo') !== []) {
                $this->multiRepositoryHandler->replaceRepositories($input->getOption('repo'));
            }

            if ($input->getOption('fixer') !== []) {
                $this->multiRepositoryHandler->replaceFixers($input->getOption('fixer'));
            }
        } catch (Throwable $exception) {
            return $this->handleException(
                $output,
                $exception,
                $input->hasOption('debug'),
                sprintf('Got error during initialization: %s', $exception->getMessage())
            );
        }

        $total = $this->multiRepositoryHandler->getRepositoriesCount();
        $i = 0;

        try {
            foreach ($this->multiRepositoryHandler->iterateHandle() as $repoOutput) {
                $output->writeln(
                    sprintf(
                        '<fg=green>%04d / %04d Repository %s fixed successfully</>',
                        ++$i,
                        $total,
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
                    '<error>%04d / %04d Repository failed to fix with message: %s</error>',
                    ++$i,
                    $total,
                    $exception->getMessage()
                ));
        }

        return 0;
    }

    private function handleException(
        OutputInterface $output,
        Throwable $exception,
        bool $isDebug,
        string $message
    ): int
    {
        $output->writeln($message);

        if ($isDebug) {
            throw $exception;
        }

        return $exception->getCode() ?? 1;
    }
}
