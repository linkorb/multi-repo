<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Action;

use Generator;
use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Handler\RepositoryHandlerInterface;
use Linkorb\MultiRepo\Services\ConfigResolver;

class UpdateCommandAction implements CommandActionInterface
{
    public function execute(ConfigResolver $configResolver, RepositoryHandlerInterface $repositoryHandler): Generator
    {
        foreach ($configResolver->iterateRepositories() as $repoName => $repoData) {
            $repoInputDto = $configResolver->loadMetadata(
                new RepoInputDto($repoName, $repoData['gitUrl'], $repoData)
            );

            if ($configResolver->isMatchingLabels($repoInputDto)) {
                $repositoryHandler->setupRepository($repoInputDto);

                yield $repoName;
            }
        }
    }
}
