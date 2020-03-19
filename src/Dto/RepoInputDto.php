<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Dto;

use Generator;

class RepoInputDto
{
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

    public function getFixerData(): Generator
    {
        foreach ($this->config['fixers'] as $fixerName => $fixerConfig) {
            yield $fixerName => new FixerInputDto($fixerName, $fixerConfig, $this->config['variables']);
        }
    }
}
