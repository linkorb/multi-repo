<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Git;

use Cz\Git\GitRepository;

class LinkorbGitRepository extends GitRepository
{
    public function reset(bool $hard = false): self
    {
        return $this->begin()
            ->run(sprintf('git reset %s origin HEAD', $hard ? '--hard' : ''))
            ->end();
    }
}
