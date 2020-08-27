<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Action;

use Generator;
use Linkorb\MultiRepo\Handler\RepositoryHandlerInterface;
use Linkorb\MultiRepo\Services\ConfigResolver;

interface CommandActionInterface
{
    public function execute(ConfigResolver $configResolver, RepositoryHandlerInterface $repositoryHandler): Generator;
}
