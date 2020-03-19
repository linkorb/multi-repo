<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services;

use Exception;
use UnexpectedValueException;

/**
 * In case of memory issues use APCU to cache
 * @package Linkorb\MultiRepo\Services
 */
class Io
{
    private array $cache;

    public function read(string $path): string
    {
        if (isset($this->cache[$path])) {
            return $this->cache[$path];
        }

        return file_get_contents($path);
    }

    public function cache(string $path, string $content): self
    {
        $this->cache[$path] = $content;

        return $this;
    }

    public function write(string $path): self
    {
        if (!isset($this->cache[$path])) {
            throw new UnexpectedValueException('You should cache content to actually write to file');
        }

        if (file_put_contents($path, $this->cache[$path]) === false) {
            throw new Exception(sprintf('Unable to write to path %s', $path));
        }

        unset($this->cache[$path]);

        return $this;
    }

    public function writeAll(): self
    {
        foreach ($this->cache as $path => $content) {
            $this->write($path);
        }

        return $this;
    }
}
