<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Dto;

class FixerInputDto
{
    private string $repositoryPath;

    private array $fixerData;

    private array $variables;

    public function __construct(string $repositoryPath, array $fixerData, array $variables)
    {
        $this->repositoryPath = $repositoryPath;
        $this->fixerData = $fixerData;
        $this->variables = $variables;
    }

    public function getRepositoryPath(): string
    {
        return $this->repositoryPath;
    }

    public function getFixerData(): array
    {
        return $this->fixerData;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }
}
