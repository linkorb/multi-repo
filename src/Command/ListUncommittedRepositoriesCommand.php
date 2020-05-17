<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Command;

use Linkorb\MultiRepo\Handler\MultiRepositoryHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ListUncommittedRepositoriesCommand extends Command
{
    protected static $defaultName = 'linkorb:multi-repo:list-uncommitted';

    private MultiRepositoryHandler $multiRepositoryHandler;

    public function __construct(MultiRepositoryHandler $multiRepositoryHandler)
    {
        $this->multiRepositoryHandler = $multiRepositoryHandler;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->multiRepositoryHandler->iterateHasChanges() as $repoName) {
            $output->writeln(sprintf('<fg=green>Repository %s has uncommitted changes</>', $repoName));
        }

        return 0;
    }
}
