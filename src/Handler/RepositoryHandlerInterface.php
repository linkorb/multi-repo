<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Dto\RepoOutputDto;

interface RepositoryHandlerInterface
{
    public function handle(RepoInputDto $repoInputDto): RepoOutputDto;

    public function refreshRepository(RepoInputDto $repoInputDto): void;
}
