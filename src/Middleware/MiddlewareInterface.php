<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;

interface MiddlewareInterface
{
    public function __invoke(FixerInputDto $input, callable $next);
}
