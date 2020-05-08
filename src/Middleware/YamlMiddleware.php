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

            $identSize = $input->getFixerData()['indentSize'] ?? 2;

            $content = Yaml::dump(
                Yaml::parse($this->io->read($filePath)),
                $input->getFixerData()['inline'] ?? 100,
                $identSize,
                Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE | Yaml::DUMP_NULL_AS_TILDE | Yaml::DUMP_OBJECT | Yaml::DUMP_OBJECT_AS_MAP
            );

            if (!in_array($input->getFixerData()['indentStyle'] ?? null, [null, 'space'], true)) {
                $content = $this->fixIndent(
                    $content,
                    $identSize,
                    $input->getFixerData()['indentStyle'] ?? null,
                    $identSize
                );
            }

            $content = $this->fixLineBreaks($content, $input->getFixerData()['lineBreaks'] ?? null);

            $this->io->write($fileDir, $filename, $content);
        }

        $next();
    }
}
