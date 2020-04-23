<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Dto;

use Generator;

class RepoInputDto
{
    public string $repositoryPath;

    private string $name;

    private string $dsn;

    private array $config;

    public function __construct(string $name, string $dsn, array $config)
    {
        $this->name = $name;
        $this->dsn = $dsn;
        $this->config = $config;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDsn(): string
    {
        return $this->dsn;
    }

    public function getFixerData(bool $reverse = false): Generator
    {
        $fixers = $reverse ? array_reverse($this->config['fixers']) :  $this->config['fixers'];

        foreach ($fixers as $fixerName => $fixerConfig) {
            yield $fixerName => new FixerInputDto(
                $this->repositoryPath,
                $fixerConfig ?? [],
                $this->config['variables'] ?? []
            );
        }
    }
}
