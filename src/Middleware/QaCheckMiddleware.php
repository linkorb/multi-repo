<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;

class QaCheckMiddleware implements MiddlewareInterface
{
    public function __invoke(FixerInputDto $input, MiddlewareInterface $next)
    {

    }
}
