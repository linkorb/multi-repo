<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Command;

use Linkorb\MultiRepo\Action\HasChangesCommandAction;
use Linkorb\MultiRepo\Handler\MultiRepositoryHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class ListUncommittedRepositoriesCommand extends Command
{
    use HandleInitializationAwareTrait;

    protected static $defaultName = 'linkorb:multi-repo:list-uncommitted';

    private MultiRepositoryHandler $multiRepositoryHandler;

    private HasChangesCommandAction $action;

    public function __construct(MultiRepositoryHandler $multiRepositoryHandler, HasChangesCommandAction $action)
    {
        $this->multiRepositoryHandler = $multiRepositoryHandler;
        $this->action = $action;

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

        foreach ($this->multiRepositoryHandler->iterateExecAction($this->action) as $repoName) {
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
