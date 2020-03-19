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
            ->addOption('fixer', null, InputOption::VALUE_OPTIONAL, 'Apply only specified fixer')
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Run command on specified path only')
            ->setDescription('Setting up repositories specified in repos.yaml, applying fixers for them');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $total = $this->multiRepositoryHandler->getRepositoriesCount();
        $i = 0;

        try {
            while ($repoName = $this->multiRepositoryHandler->iterateHandle()) {
                $output->write(
                    sprintf('%04d / %04d Repository %s fixed successfully', ++$i, $total, $repoName),
                    true
                );
            }
        } catch (Throwable $exception) {
            $output->write(
                sprintf(
                    '<error>%04d / %04d Repository failed to fix with message: %s</error>',
                    ++$i,
                    $total,
                    $exception->getMessage()
                ),
                true
            );

            return 1;
        }

        return 0;
    }
}
