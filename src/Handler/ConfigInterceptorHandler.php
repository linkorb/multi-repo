<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Dto\RepoOutputDto;

class ConfigInterceptorHandler implements RepositoryHandlerInterface
{
    public function handle(RepoInputDto $repoInputDto): RepoOutputDto
    {
        $output = new RepoOutputDto($repoInputDto->getName());
        $output->config['metadata'] = $repoInputDto->getMetadata();
        $output->config['fixers'] = [];

        /**
         * @var string $fixerName
         * @var FixerInputDto $fixerDatum
         */
        foreach ($repoInputDto->getFixerData() as $fixerName => $fixerDatum) {
            $output->config['fixers'][$fixerName] = $fixerDatum->getFixerData();
        }

        if ($fixerDatum) {
            $output->config['variables'] = $fixerDatum->getVariables();
        }

        return $output;
    }

    public function refreshRepository(RepoInputDto $repoInputDto): void
    {
        $repoInputDto->repositoryPath = $repoInputDto->getName();
    }
}
