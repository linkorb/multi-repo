<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

trait HandleInitializationAwareTrait
{
    abstract public function addOption(
        string $name,
        $shortcut = null,
        int $mode = null,
        string $description = '',
        $default = null
    );

    abstract protected function initializeOptions(InputInterface $input): void;

    private function configureBase(): void {
        $this->addOption(
            'debug',
            null,
            InputOption::VALUE_OPTIONAL,
            'Show debug information about errors'
        );
    }

    private function handleInitialization(InputInterface $input, OutputInterface $output): ?int
    {
        try {
            $this->initializeOptions($input);
        } catch (Throwable $exception) {
            return $this->handleException(
                $output,
                $exception,
                $input->hasOption('debug'),
                sprintf('Got error during initialization: %s', $exception->getMessage())
            );
        }

        return null;
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
