<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Dto\RepoOutputDto;
use Linkorb\MultiRepo\Factory\MiddlewareFactoryPool;
use Linkorb\MultiRepo\Services\Io\IoInterface;
use Linkorb\MultiRepo\Services\Manager\GitRepositoriesManager;
use Linkorb\MultiRepo\Middleware\Stack\MiddlewareStack;
use Throwable;

class RepositoryHandler implements RepositoryHandlerInterface
{
    private const CACHE_DIR = '.multi-repo-cache';

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

    public function handle(RepoInputDto $repoInputDto): RepoOutputDto
    {
        $output = new RepoOutputDto($repoInputDto->getName());

        $stack = $this->getMiddlewareStack();

        /**
         * @var string $fixerName
         * @var FixerInputDto $fixerDatum
         */
        foreach ($repoInputDto->getFixerData(true) as $fixerType => $fixerDatum) {
            $output->config[$fixerType] = $fixerDatum->getFixerData();
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

        return $output;
    }

    public function setupRepository(RepoInputDto $repoInputDto, bool $pullRepo = true): void
    {
        $repoInputDto->repositoryPath = implode(
            DIRECTORY_SEPARATOR,
            [$this->repositoriesBasePath, $repoInputDto->getName()]
        );

        $this->manager->setup(
            $repoInputDto->getName(),
            $repoInputDto->getDsn(),
            $this->repositoriesBasePath,
            $pullRepo
        );
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
                [$this->repositoriesBasePath, $repoName]
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
            [$this->repositoriesBasePath, $repoName]
        ));

        $this->io->moveDir(
            implode(
                DIRECTORY_SEPARATOR,
                [$this->repositoriesBasePath, static::CACHE_DIR, $repoName]
            ),
            implode(
                DIRECTORY_SEPARATOR,
                [$this->repositoriesBasePath]
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
