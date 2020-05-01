<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\IndentionFormatAwareTrait;
use Linkorb\MultiRepo\Services\Io\IoInterface;
use Symfony\Component\Yaml\Yaml;

class YamlMiddleware implements MiddlewareInterface
{
    use IndentionFormatAwareTrait;

    private IoInterface $io;

    public function __construct(IoInterface $io)
    {
        $this->io = $io;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        foreach ($this->io->findBy('/^.+\.(yml|yaml)$/i', $input->getRepositoryPath()) as $filePath => $fileResult) {
            if (preg_match('/^.\/vendor./', $filePath)) {
                // Do not fix dependencies :)
                continue;
            }

            $filePathComponents = explode(DIRECTORY_SEPARATOR, $filePath);
            $filename = array_pop($filePathComponents);
            $fileDir = implode(DIRECTORY_SEPARATOR, $filePathComponents);

            $content = preg_replace(
                '/^---\n(.*)\.\.\.$/ms',
                '$1',
                yaml_emit(
                    Yaml::parse($this->io->read($filePath))
                )
            );

            $content = $this->fixIndent(
                $content,
                2,
                $input->getFixerData()['indentStyle'] ?? null,
                $input->getFixerData()['indentSize'] ?? 2
            );

            $content = $this->fixLineBreaks($content, $input->getFixerData()['lineBreaks'] ?? null);

            $this->io->write($fileDir, $filename, $content);
        }

        $next();
    }
}
