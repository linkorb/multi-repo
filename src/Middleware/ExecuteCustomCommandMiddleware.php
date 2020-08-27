<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Exception;
use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\ShExecHelper;

class ExecuteCustomCommandMiddleware implements MiddlewareInterface
{
    private ShExecHelper $executor;

    public function __construct(ShExecHelper $executor)
    {
        $this->executor = $executor;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        list($code) = $this->executor->exec(
            $input->getFixerData()['command'],
            $input->getRepositoryPath()
        );

        if ($code !== 0) {
            throw new Exception('Composer version constraint composer update failed with code: ' . $code);
        }

        $next();
    }
}
