<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Dto;

class FixerInputDto
{
    private string $name;

    private array $fixerData;

    private array $variables;

    public function __construct(string $name, array $fixerData, array $variables)
    {
        $this->fixerData = $fixerData;
        $this->variables = $variables;
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
