<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Exception;
use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\ShExecHelper;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;
use Linkorb\MultiRepo\Services\Io\IoInterface;

class ConventionalCommitMiddleware implements MiddlewareInterface
{
    private TemplateLocationHelper $templateHelper;

    private ShExecHelper $executor;

    private IoInterface $io;

    public function __construct(TemplateLocationHelper $templateHelper, ShExecHelper $executor, IoInterface $io)
    {
        $this->templateHelper = $templateHelper;
        $this->executor = $executor;
        $this->io = $io;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        $this->templateHelper->putIfMissingFromLocation(
            $input->getFixerData()['versionrc'],
            [$input->getRepositoryPath(), '.versionrc']
        );

        $this->templateHelper->putIfMissingFromLocation(
            $input->getFixerData()['config'],
            [$input->getRepositoryPath(), 'commitlint.config.js']
        );

        $this->templateHelper->putIfMissingFromString(
            $this->generatePackageJson(),
            [$input->getRepositoryPath(), 'package.json']
        );

        list($code) = $this->executor->exec(
            'npm install @commitlint/cli @commitlint/config-conventional husky --save-dev',
            $input->getRepositoryPath()
        );

        if ($code !== 0) {
            throw new Exception('Conventional commit dependencies installation failed with code: ' . $code);
        }

        $packageJson = json_decode(
            $this->io->read($input->getRepositoryPath(). DIRECTORY_SEPARATOR . 'package.json'),
            true
        );

        if (($packageJson['husky']['hooks']['commit-msg'] ?? null) === null) {
            $packageJson = array_replace_recursive(
                $packageJson,
                [
                    'husky' => [
                        'hooks' => [
                            'commit-msg' => 'commitlint -E HUSKY_GIT_PARAMS',
                        ],
                    ],
                ]
            );
        }

        $this->io->write(
            $input->getRepositoryPath(),
            'package.json',
            json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $next();
    }

    private function generatePackageJson(): string
    {
        return <<<JSON
{
  "devDependencies": {
  },
  "license": "UNLICENSED",
  "private": true,
  "scripts": {
  },
  "dependencies": {
  }
}
JSON;
    }
}
