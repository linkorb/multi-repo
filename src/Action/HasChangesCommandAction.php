<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Action;

use Generator;
use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Exception\RepositoryHasUncommittedChangesException;
use Linkorb\MultiRepo\Handler\RepositoryHandlerInterface;
use Linkorb\MultiRepo\Services\ConfigResolver;

class HasChangesCommandAction implements CommandActionInterface
{
    public function execute(ConfigResolver $configResolver, RepositoryHandlerInterface $repositoryHandler): Generator
    {
        foreach ($configResolver->iterateRepositories() as $repoName => $repoData) {
            try {
                $repoInputDto = $configResolver->loadMetadata(
                    new RepoInputDto($repoName, $repoData['gitUrl'], $repoData)
                );

                $repositoryHandler->setupRepository($repoInputDto, false);
            } catch (RepositoryHasUncommittedChangesException $exception) {
                if (isset($repoInputDto) && $configResolver->isMatchingLabels($repoInputDto)) {
                    yield $repoName;
                }
            }
        }
    }
}
