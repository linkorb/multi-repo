<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Generator;
use InvalidArgumentException;
use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Dto\RepoOutputDto;
use Linkorb\MultiRepo\Exception\RepositoryHasUncommittedChangesException;
use Linkorb\MultiRepo\Services\ConfigResolver;

class MultiRepositoryHandler
{
    private RepositoryHandlerInterface $repositoryHandler;

    private ConfigResolver $configResolver;

    public function __construct(
        RepositoryHandlerInterface $repositoryHandler,
        ConfigResolver $configResolver
    ) {
        $this->repositoryHandler = $repositoryHandler;
        $this->configResolver = $configResolver;
    }

    /**
     * @return Generator|RepoOutputDto[]
     */
    public function iterateHandle(): Generator
    {
        foreach ($this->configResolver->iterateRepositories() as $repoName => $repoData) {
            $repoInputDto = new RepoInputDto($repoName, $repoData['gitUrl'], $repoData);

            $this->repositoryHandler->refreshRepository($repoInputDto);

            $repoInputDto = $this->configResolver->loadMetadata($repoInputDto);

            if (!$this->configResolver->isMatchingLabels($repoInputDto)) {
                continue;
            }

            yield $this->repositoryHandler->handle($repoInputDto);
        }
    }

    public function iterateHasChanges(): Generator
    {
        foreach ($this->configResolver->iterateRepositories() as $repoName => $repoData) {
            try {
                $repoInputDto = $this->configResolver->loadMetadata(
                    new RepoInputDto($repoName, $repoData['gitUrl'], $repoData)
                );

                $this->repositoryHandler->refreshRepository($repoInputDto);
            } catch (RepositoryHasUncommittedChangesException $exception) {
                if (isset($repoInputDto) && $this->configResolver->isMatchingLabels($repoInputDto)) {
                    yield $repoName;
                }
            }
        }
    }

    /**
     * @param string[] $repositories
     */
    public function replaceRepositories(array $repositories): void
    {
        $this->configResolver->replaceRepositories($repositories);
    }

    /**
     * @param string[] $fixersList
     */
    public function replaceFixers(array $fixersList): void
    {
        $this->configResolver->replaceFixers($fixersList);
    }

    public function setIntendedLabels(array $labels): void
    {
        $parsedLabels = [];
        foreach ($labels as $combinedLabelRow) {
            if (!str_contains($combinedLabelRow, '=')) {
                throw new InvalidArgumentException('Label should contain name and value separated by `=`');
            }

            [$key, $value] = explode('=', $combinedLabelRow, 2);

            $parsedLabels[$key] = $value;
        }

        $this->configResolver->setIntendedLabels($parsedLabels);
    }
}
