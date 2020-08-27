<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Action;

use Generator;
use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Handler\RepositoryHandlerInterface;
use Linkorb\MultiRepo\Services\ConfigResolver;

class FixCommandAction implements CommandActionInterface
{
    public function execute(ConfigResolver $configResolver, RepositoryHandlerInterface $repositoryHandler): Generator
    {
        foreach ($configResolver->iterateRepositories() as $repoName => $repoData) {
            $repoInputDto = new RepoInputDto($repoName, $repoData['gitUrl'], $repoData);

            $repositoryHandler->setupRepository($repoInputDto);

            $repoInputDto = $configResolver->loadMetadata($repoInputDto);

            if (!$configResolver->isMatchingLabels($repoInputDto)) {
                continue;
            }

            yield $repositoryHandler->handle($repoInputDto);
        }
    }
}
