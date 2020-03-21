<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Io;

use Exception;

// TODO: Implement UnifiedIo which will use php functions for dir operations
class UnixIo implements IoInterface
{
    public function copyDir(string $origin, string $target): self
    {
        $result = `cp -a {$origin} {$target}`;

        if ($result !== '') {
            throw new Exception($result);
        }

        return $this;
    }

    public function moveDir(string $origin, string $target): self
    {
        $result = `mv {$origin} {$target}`;

        if ($result !== '') {
            throw new Exception($result);
        }

        return $this;
    }

    public function removeDir(string $path): self
    {
        $result = `rm -rf {$path}`;

        if ($result !== '') {
            throw new Exception($result);
        }

        return $this;
    }

    public function read(string $path): string
    {
        return file_get_contents($path);
    }

    public function write(string $dir, string $file, string $content): self
    {
        $path = $dir . DIRECTORY_SEPARATOR . $file;

        if (!is_dir($dir)) {
            mkdir($dir, '0775', true);
        }

        if (file_put_contents($path, $content) === false) {
            throw new Exception(sprintf('Unable to write to path %s', $path));
        }

        return $this;
    }
}
