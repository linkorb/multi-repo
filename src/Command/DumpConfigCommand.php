<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpConfigCommand extends Command
{
    protected static $defaultName = 'linkorb:multi-repo:config';

    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Dump parsed repositories config values');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        dump($this->config);

        return 0;
    }
}
