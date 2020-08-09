<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Services\Io\IoInterface;
use Symfony\Component\Yaml\Yaml;

class RepositoryHandlerProxy implements RepositoryHandlerInterface
{
    private RepositoryHandlerInterface $handler;

    private IoInterface $io;

    private string $repositoriesBasePath;

    private string $repositoriesConfigPath;

    public function __construct(
        RepositoryHandlerInterface $handler,
        IoInterface $io,
        string $repositoriesBasePath,
        string $repositoriesConfigPath
    ) {
        $this->handler = $handler;
        $this->io = $io;
        $this->repositoriesBasePath = $repositoriesBasePath;
        $this->repositoriesConfigPath = $repositoriesConfigPath;
    }

    public function handle(RepoInputDto $repoInputDto): void
    {
        $this->refreshRepository($repoInputDto);

        $this->handler->handle($this->loadMetadata($repoInputDto));
    }

    public function refreshRepository(RepoInputDto $repoInputDto): void
    {
        $this->handler->refreshRepository($repoInputDto);
    }

    private function loadMetadata(RepoInputDto $inputDto): RepoInputDto
    {
        $getYamlConfig = function (string $filename): array {
            if (file_exists($filename)) {
                return Yaml::parse($this->io->read($filename));
            }

            return [];
        };

        $repoRelativeConfig = $getYamlConfig(implode(
            DIRECTORY_SEPARATOR,
            [dirname(realpath($this->repositoriesConfigPath)), 'repos', $inputDto->getName(), 'repo.yaml']
        ));

        $repoInternalConfig = $getYamlConfig(implode(
            DIRECTORY_SEPARATOR,
            [$this->repositoriesBasePath, $inputDto->getName(), 'repo.yaml']
        ));

        return $inputDto->withMetadata(
            array_replace_recursive($repoInternalConfig['metadata'] ?? [],
                $repoRelativeConfig['metadata'] ?? [])
        );
    }
}
