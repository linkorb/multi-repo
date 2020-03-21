<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Factory\MiddlewareFactoryPool;
use Linkorb\MultiRepo\Services\Io\IoInterface;
use Linkorb\MultiRepo\Services\Manager\GitRepositoriesManager;
use Linkorb\MultiRepo\Middleware\Stack\MiddlewareStack;
use Throwable;

class RepositoryHandler
{
    private const SOURCE_DIR = 'source';
    private const CACHE_DIR = 'cache';

    private GitRepositoriesManager $manager;

    private MiddlewareFactoryPool $middlewareFactory;

    private string $repositoriesBasePath;

    private IoInterface $io;

    public function __construct(
        GitRepositoriesManager $manager,
        MiddlewareFactoryPool $middlewareFactory,
        IoInterface $io,
        string $repositoriesBasePath
    )
    {
        $this->manager = $manager;
        $this->middlewareFactory = $middlewareFactory;
        $this->io = $io;
        $this->repositoriesBasePath = $repositoriesBasePath;
    }

    public function handle(RepoInputDto $repoInputDto): void
    {
        $repoInputDto->repositoryPath = implode(
            DIRECTORY_SEPARATOR,
            [$this->repositoriesBasePath, static::SOURCE_DIR, $repoInputDto->getName()]
        );

        $this->manager->refresh(
            $repoInputDto->getName(),
            $repoInputDto->getDsn(),
            $this->repositoriesBasePath . DIRECTORY_SEPARATOR . static::SOURCE_DIR
        );

        $stack = $this->getMiddlewareStack();

        foreach ($repoInputDto->getFixerData() as $fixerType => $fixerDatum) {
            $stack->add($this->middlewareFactory->createMiddleware($fixerType));
        }

        $this->backup($repoInputDto->getName());

        try {
            $stack($repoInputDto);
        } catch (Throwable $exception) {
            $this->restoreBackup($repoInputDto->getName());

            throw $exception;
        }

        $this->removeBackup($repoInputDto->getName());
    }

    private function getMiddlewareStack(): MiddlewareStack
    {
        return new MiddlewareStack();
    }

    private function backup(string $repoName): void
    {
        $this->io->copyDir(
            implode(
                DIRECTORY_SEPARATOR,
                [$this->repositoriesBasePath, static::SOURCE_DIR, $repoName]
            ),
            implode(
                DIRECTORY_SEPARATOR,
                [$this->repositoriesBasePath, static::CACHE_DIR]
            )
        );
    }

    private function restoreBackup(string $repoName): void
    {
        $this->io->removeDir(implode(
            DIRECTORY_SEPARATOR,
            [$this->repositoriesBasePath, static::SOURCE_DIR, $repoName]
        ));

        $this->io->moveDir(
            implode(
                DIRECTORY_SEPARATOR,
                [$this->repositoriesBasePath, static::CACHE_DIR, $repoName]
            ),
            implode(
                DIRECTORY_SEPARATOR,
                [$this->repositoriesBasePath, static::SOURCE_DIR]
            )
        );
    }

    private function removeBackup(string $repoName): void
    {
        $this->io->removeDir(implode(
            DIRECTORY_SEPARATOR,
            [$this->repositoriesBasePath, static::CACHE_DIR, $repoName]
        ));
    }
}
