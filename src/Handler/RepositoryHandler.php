<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Factory\MiddlewareFactoryPool;
use Linkorb\MultiRepo\Services\Manager\GitRepositoriesManager;
use Linkorb\MultiRepo\Middleware\Stack\MiddlewareStack;

class RepositoryHandler
{
    private GitRepositoriesManager $manager;

    private MiddlewareFactoryPool $middlewareFactory;

    public function __construct(GitRepositoriesManager $manager, MiddlewareFactoryPool $middlewareFactory)
    {
        $this->manager = $manager;
        $this->middlewareFactory = $middlewareFactory;
    }

    public function handle(RepoInputDto $repoInputDto): void
    {
        $this->manager->refresh($repoInputDto->getName(), $repoInputDto->getDsn());

        $stack = $this->getMiddlewareStack();

        foreach ($repoInputDto->getFixerData() as $fixerType => $fixerDatum) {
            $stack->add($this->middlewareFactory->createMiddleware($fixerType));
        }

        $stack($repoInputDto);
    }

    private function getMiddlewareStack(): MiddlewareStack
    {
        return new MiddlewareStack();
    }
}
