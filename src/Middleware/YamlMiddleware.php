<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Io\IoInterface;
use Symfony\Component\Yaml\Yaml;

class YamlMiddleware implements MiddlewareInterface
{
    private IoInterface $io;

    public function __construct(IoInterface $io)
    {
        $this->io = $io;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        foreach ($this->io->findBy('/^.+\.(yml|yaml)/i', $input->getRepositoryPath()) as $filePath => $fileResult) {
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
                preg_replace(
                    '/^---\n(.*)\.\.\.$/ms',
                    '$1',
                    yaml_emit(
                        Yaml::parse($this->io->read($filePath))
                    )
                ),
            );
        }

        $next();
    }
}
