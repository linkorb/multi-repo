<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Linkorb\MultiRepo\Dto\RepoInputDto;

interface RepositoryHandlerInterface
{
    public function handle(RepoInputDto $repoInputDto): void;

    public function refreshRepository(RepoInputDto $repoInputDto): void;
}
