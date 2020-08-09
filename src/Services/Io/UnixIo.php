<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Io;

use Exception;

// TODO: Use https://symfony.com/doc/current/components/process.html instead
class UnixIo implements IoInterface
{
    use FindByAwareTrait;

    public function copyDir(string $origin, string $target): self
    {
        $this->handleUnixCommandResult(`cp -a {$origin} {$target}`);

        return $this;
    }

    public function moveDir(string $origin, string $target): self
    {
        $this->handleUnixCommandResult(`mv {$origin} {$target}`);

        return $this;
    }

    public function removeDir(string $path): self
    {
        $this->handleUnixCommandResult(`rm -rf {$path}`);

        return $this;
    }

    public function read(string $path): string
    {
        return file_get_contents($path);
    }

    public function write(string $dir, string $file, string $content, int $mode = null): self
    {
        $path = $dir . DIRECTORY_SEPARATOR . $file;

        if (!is_dir($dir)) {
            mkdir($dir, 775, true);
        }

        if (file_put_contents($path, $content) === false) {
            throw new Exception(sprintf('Unable to write to path %s', $path));
        }

        if ($mode) {
            chmod($path, $mode);
        }

        return $this;
    }

    private function handleUnixCommandResult($result): void
    {
        if ($result !== null && $result !== '') {
            throw new Exception($result);
        }
    }
}
