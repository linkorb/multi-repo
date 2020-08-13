<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Dto;

final class RepoOutputDto
{
    public array $config = [];

    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
