<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\IndentionFormatAwareTrait;
use Linkorb\MultiRepo\Services\Io\IoInterface;

class JsonMiddleware implements MiddlewareInterface
{
    use IndentionFormatAwareTrait;

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

            $content = json_encode(
                    json_decode($this->io->read($filePath)),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ) . PHP_EOL;

            $content = $this->fixIndent(
                $content,
                4,
                $input->getFixerData()['indentStyle'] ?? null,
                $input->getFixerData()['indentSize'] ?? 2
            );

            $content = $this->fixLineBreaks($content, $input->getFixerData()['lineBreaks'] ?? null);

            $this->io->write($fileDir, $filename, $content);
        }

        $next();
    }
}
