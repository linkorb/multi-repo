<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Exception;
use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\ShExecHelper;
use Linkorb\MultiRepo\Services\Io\IoInterface;

class ComposerJsonDependencyBlacklistMiddleware implements MiddlewareInterface
{
    private IoInterface $io;

    private ShExecHelper $executor;

    public function __construct(IoInterface $io, ShExecHelper $executor)
    {
        $this->io = $io;
        $this->executor = $executor;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        $composerJson = json_decode(
            $this->io->read($input->getRepositoryPath() . DIRECTORY_SEPARATOR . 'composer.json'),
            true
        );

        $doComposerUpdate = false;
        foreach ($input->getFixerData()['replace'] as $replacedPackage => $replacementPackage) {
            if (isset($composerJson['replace'][$replacedPackage])) {
                continue;
            }

            $composerJson['replace'][$replacedPackage] = $replacementPackage ?? '*';
            $doComposerUpdate = true;
        }

        if ($doComposerUpdate) {
            $this->io->write(
                $input->getRepositoryPath(),
                'composer.json',
                json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
            );

            list($code) = $this->executor->exec('composer update', $input->getRepositoryPath());

            if ($code !== 0) {
                throw new Exception('Composer dependency blacklist composer update failed with code: ' . $code);
            }
        }

        $next();
    }
}
