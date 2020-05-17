<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Manager;

use Cz\Git\GitException;
use Cz\Git\GitRepository;
use Cz\Git\IGit;
use Linkorb\MultiRepo\Exception\RepositoryHasUncommittedChangesException;
use UnderflowException;

class GitRepositoriesManager
{
    /**
     * @var IGit[]
     */
    private array $repositories = [];

    public function refresh(string $repositoryName, string $repositoryDsn, string $path): void
    {
        $repo = $this->instantiateRepository($repositoryName, $repositoryDsn, $path);

        if ($repo->hasChanges()) {
            throw new RepositoryHasUncommittedChangesException(
                sprintf(
                    'Repository %s already contains uncommitted changes. Please either commit or discard them',
                    $repositoryName
                )
            );
        }

        $repo->pull();
    }

    private function instantiateRepository(string $repositoryName, string $repositoryDsn, string $path): IGit
    {
        $repoFullPath = $path . DIRECTORY_SEPARATOR . $repositoryName;

        if (isset($this->repositories[$repositoryName])) {
            return $this->repositories[$repositoryName];
        }

        try {
            $this->repositories[$repositoryName] = new GitRepository($repoFullPath);
        } catch (GitException $exception) {
            $this->repositories[$repositoryName] = GitRepository::cloneRepository($repositoryDsn, $repoFullPath);
        }

        return $this->repositories[$repositoryName];
    }
}
