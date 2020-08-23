<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Helper;

final class ShExecHelper
{
    public function exec(string $command, string $directory = null): array
    {
        if ($directory) {
            $command = sprintf('(cd %s && %s)', $directory, $command);
        }

        exec($command, $output, $code);

        return [$code, implode(PHP_EOL, $output)];
    }
}
