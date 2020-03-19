<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Generator;
use Linkorb\MultiRepo\Dto\RepoInputDto;

class MultiRepositoryHandler
{
    private RepositoryHandler $repositoryHandler;

    private array $config;

    public function __construct(RepositoryHandler $repositoryHandler, array $config)
    {
        $this->repositoryHandler = $repositoryHandler;
        $this->config = $config;
    }

    public function iterateHandle(): Generator
    {
        foreach ($this->config['list'] as $repoName => $repoDsn) {
            $this->repositoryHandler->handle(
                new RepoInputDto(
                    $repoName,
                    $repoDsn,
                    array_replace_recursive($this->config['defaults'], $this->config['configs'][$repoName])
                )
            );

            yield $repoName;
        }
    }

    public function getRepositoriesCount(): int
    {
        return count($this->config['list']);
    }
}
