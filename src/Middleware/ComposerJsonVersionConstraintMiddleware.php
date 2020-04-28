<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Exception;
use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\ShExecHelper;
use Linkorb\MultiRepo\Services\Io\IoInterface;

class ComposerJsonVersionConstraintMiddleware implements MiddlewareInterface
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

        if ($alignedRequire = $this->alignPackages($composerJson['require'])) {
            $composerJson['require']= array_replace($composerJson['require'], $alignedRequire);
        }

        if ($alignedRequireDev = $this->alignPackages($composerJson['require-dev'])) {
            $composerJson['require-dev']= array_replace($composerJson['require-dev'], $alignedRequireDev);
        }

        if (empty($alignedRequireDev) && empty($alignedRequire)) {
            $next();

            return;
        }

        $this->io->write(
            $input->getRepositoryPath(),
            'composer.json',
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        list($code) = $this->executor->exec(
            sprintf(
                'composer update %s',
                implode(
                    ' ',
                    array_merge(array_keys($alignedRequire), array_keys($alignedRequireDev))
                )
            ),
            $input->getRepositoryPath()
        );

        if ($code !== 0) {
            throw new Exception('Composer version constraint composer update failed with code: ' . $code);
        }

        $next();
    }

    private function alignPackages(array $require): array
    {
        $composerRequire = [];
        foreach ($require as $packageName => $packageVersion) {
            $composerRequire = array_merge(
                $composerRequire,
                $this->alignPackageVersion($packageName, $packageVersion)
            );
        }

        return $composerRequire;
    }

    /**
     * @return array[string]string
     */
    private function alignPackageVersion(string $packageName, string $packageVersion): array
    {
        if (strpos($packageVersion, '~') === 0) {
            return [$packageName => '^' . substr($packageVersion, 1)];
        }

        return [];
    }
}
