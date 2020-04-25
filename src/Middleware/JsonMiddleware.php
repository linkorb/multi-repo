<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Io\IoInterface;

class JsonMiddleware implements MiddlewareInterface
{
    private IoInterface $io;

    public function __construct(IoInterface $io)
    {
        $this->io = $io;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        foreach ($this->io->findBy('/^.+\.json$/i', $input->getRepositoryPath()) as $filePath => $fileResult) {
            if (preg_match('/^.\/vendor./', $filePath)) {
                // Do not fix dependencies :)
                continue;
            }

            $filePathComponents = explode(DIRECTORY_SEPARATOR, $filePath);
            $filename = array_pop($filePathComponents);
            $fileDir = implode(DIRECTORY_SEPARATOR, $filePathComponents);

            $this->io->write(
                $fileDir,
                $filename,
                json_encode(
                    json_decode($this->io->read($filePath)),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ) . PHP_EOL
            );
        }

        $next();
    }
}
