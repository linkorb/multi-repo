<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware\Stack;

use Closure;
use Generator;
use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Middleware\MiddlewareInterface;

class MiddlewareStack
{
    private Closure $middlewareFn;

    public function __construct()
    {
        $middlewareClass = new class implements MiddlewareInterface {
            public function __invoke(FixerInputDto $input, MiddlewareInterface $next)
            {
                return true;
            }
        };
        $this->middlewareFn = function (Generator $fixerData) use ($middlewareClass): MiddlewareInterface {
            return $middlewareClass($fixerData->current(), $middlewareClass);
        };
    }

    public function add(MiddlewareInterface $middleware): self
    {
        $this->middlewareFn = function (Generator $fixerData) use ($middleware): MiddlewareInterface {
            $fixerDto = $fixerData->current();
            $fixerData->next();

            return $middleware($fixerDto, ($this->middlewareFn)($fixerData));
        };

        return $this;
    }

    public function __invoke(RepoInputDto $dto): void
    {
        ($this->middlewareFn)($dto->getFixerData());
    }
}
