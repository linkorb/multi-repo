<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Manager;

use Linkorb\MultiRepo\Services\Git\LinkorbGitRepository;

class GitRepositoriesManager
{
    private string $repositoriesBasePath;

    /**
     * @var LinkorbGitRepository[]
     */
    private array $repositories = [];

    public function __construct(string $repositoriesBasePath)
    {
        $this->repositoriesBasePath = $repositoriesBasePath;
    }

    public function refresh(string $repositoryName, string $repositoryPath): void
    {
        $this->instantiateRepository($repositoryName, $repositoryPath)
            ->reset(true)
            ->pull();
    }

    public function getRepositoryPath(string $repositoryName): string
    {
        assert(isset($this->repositories[$repositoryName]));

        return $this->repositoriesBasePath . $repositoryName;
    }

    private function instantiateRepository(string $repositoryName, string $repositoryPath): LinkorbGitRepository
    {
        $this->repositories[$repositoryName] = new LinkorbGitRepository($repositoryPath);

        return $this->repositories[$repositoryName];
    }
}
