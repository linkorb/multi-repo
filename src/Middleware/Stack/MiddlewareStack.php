<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware\Stack;

use Closure;
use Generator;
use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Middleware\MiddlewareInterface;

class MiddlewareStack
{
    private Closure $middlewareFn;

    public function __construct()
    {
        $this->middlewareFn = function (): callable {
            return function () {
                return;
            };
        };
    }

    public function add(MiddlewareInterface $middleware): self
    {
        $fn = $this->middlewareFn;

        $this->middlewareFn = function (Generator $fixerData) use ($middleware, $fn): callable {
            $fixerDto = $fixerData->current();
            $fixerData->next();

            return function () use ($middleware, $fixerDto, $fixerData, $fn) {
                return $middleware($fixerDto, ($fn)($fixerData));
            };
        };

        return $this;
    }

    public function __invoke(RepoInputDto $dto): void
    {
        ($this->middlewareFn)($dto->getFixerData())();
    }
}
