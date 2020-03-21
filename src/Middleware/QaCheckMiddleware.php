<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Exception;
use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Io\IoInterface;

class QaCheckMiddleware implements MiddlewareInterface
{
    private IoInterface $io;

    public function __construct(IoInterface $io)
    {
        $this->io = $io;
    }

    public function __invoke(FixerInputDto $input, callable $next)
    {
        $this->operateGitHooks($input);
        $this->operateComposerPackages($input);

        $next();
    }

    private function operateGitHooks(FixerInputDto $input): void
    {
        $this->io->write(
            $input->getRepositoryPath(). DIRECTORY_SEPARATOR . '.hooks',
            'pre-push.sample',
            'composer run qa-checks' . PHP_EOL
        );

        $readmeAddContent = <<<DOC

## Git hooks

There are some git hooks under `.hooks` directory. Feel free to copy & adjust & use them

DOC;

        $this->io->write(
            $input->getRepositoryPath(),
            'README.md',
            $this->io->read($input->getRepositoryPath(). DIRECTORY_SEPARATOR . 'README.md') . $readmeAddContent
        );
    }

    private function operateComposerPackages(FixerInputDto $input): void
    {
        $packages = [
            'phpstan/phpstan',
            'phpstan/phpstan-symfony',
            'sebastian/phpcpd',
            'sensiolabs/security-checker',
            'squizlabs/php_codesniffer',
            'wapmorgan/php-code-fixer'
        ];

        exec(sprintf('composer require %s', implode(' ', $packages)), $output, $code);

        if ($code !== 0) {
            throw new Exception('QA checks composer installation failed with code: ' . $code);
        }

        $composerJson = json_decode(
            $this->io->read($input->getRepositoryPath(). DIRECTORY_SEPARATOR . 'composer.json'),
            true
        );

        $composerJson['scripts'] = array_merge(
            $composerJson['scripts'],
            [
                'qa-checks' => [
                    "@phpcs",
                    "@phpstan",
                    "@phpcf",
                    "@phpcpd",
                    "@security-check",
                ],
                'phpcs' => './vendor/bin/phpcs ./src/',
                'phpstan' => './vendor/bin/phpstan analyze --level=5 ./src/',
                'phpcf' => './vendor/bin/phpcf --target 7.1 ./src/',
                'phpcpd' => './vendor/bin/phpcpd --fuzzy ./src/',
                'security-check' => './vendor/bin/security-checker security:check ./composer.lock',
            ]
        );

        $this->io->write($input->getRepositoryPath(), 'composer.json', json_encode($composerJson, JSON_PRETTY_PRINT));
    }
}
