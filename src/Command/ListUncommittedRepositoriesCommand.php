<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Command;

use Linkorb\MultiRepo\Handler\MultiRepositoryHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ListUncommittedRepositoriesCommand extends Command
{
    use HandleInitializationAwareTrait;

    protected static $defaultName = 'linkorb:multi-repo:list-uncommitted';

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
                'label',
                'l',
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Only repos with matching label'
            )
            ->setDescription('List repositories which have uncommitted changes');

        $this->configureBase();;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (is_int($code = $this->handleInitialization($input, $output))) {
            return $code;
        }

        foreach ($this->multiRepositoryHandler->iterateHasChanges() as $repoName) {
            $output->writeln(sprintf('<fg=green>Repository %s has uncommitted changes</>', $repoName));
        }

        return 0;
    }

    protected function initializeOptions(InputInterface $input): void
    {
        if ($input->getOption('label') !== []) {
            $this->multiRepositoryHandler->setIntendedLabels($input->getOption('label'));
        }
    }
}
