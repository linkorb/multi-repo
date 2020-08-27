<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Command;

use Linkorb\MultiRepo\Action\FixCommandAction;
use Linkorb\MultiRepo\Handler\MultiRepositoryHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class DumpConfigCommand extends Command
{
    use HandleInitializationAwareTrait;

    protected static $defaultName = 'linkorb:multi-repo:config';

    private MultiRepositoryHandler $multiRepositoryHandler;

    private FixCommandAction $action;

    public function __construct(MultiRepositoryHandler $multiRepositoryHandler, FixCommandAction $action)
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
            ->setDescription('Dump parsed repositories config values');

        $this->configureBase();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (is_int($code = $this->handleInitialization($input, $output))) {
            return $code;
        }

        $config = [];
        foreach ($this->multiRepositoryHandler->iterateExecAction($this->action) as $repoOutput) {
            $config[$repoOutput->getName()] = $repoOutput->config;
        }

        dump($config);

        return 0;
    }

    protected function initializeOptions(InputInterface $input): void
    {
        if ($input->getOption('label') !== []) {
            $this->multiRepositoryHandler->setIntendedLabels($input->getOption('label'));
        }
    }
}
