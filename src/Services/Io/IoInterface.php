<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Io;

interface IoInterface
{
    public function copyDir(string $origin, string $target): self;

    public function moveDir(string $origin, string $target): self;

    public function removeDir(string $path): self;

    public function read(string $path): string;

    public function write(string $dir, string $file, string $content, int $mode = null): self;

    public function findBy(string $pattern, string $directory = './'): iterable;
}
