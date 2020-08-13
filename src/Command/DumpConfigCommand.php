<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Command;

use Linkorb\MultiRepo\Handler\MultiRepositoryHandler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpConfigCommand extends Command
{
    protected static $defaultName = 'linkorb:multi-repo:config';

    private MultiRepositoryHandler $multiRepositoryHandler;

    public function __construct(MultiRepositoryHandler $multiRepositoryHandler)
    {
        $this->multiRepositoryHandler = $multiRepositoryHandler;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Dump parsed repositories config values');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = [];
        foreach ($this->multiRepositoryHandler->iterateHandle() as $repoOutput) {
            $config[$repoOutput->getName()] = $repoOutput->config;
        }

        dump($config);

        return 0;
    }
}
