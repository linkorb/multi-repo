<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Exception;
use InvalidArgumentException;
use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\ShExecHelper;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;
use Linkorb\MultiRepo\Services\Io\IoInterface;

class QaCheckMiddleware implements MiddlewareInterface
{
    private const PHPSTAN = 'phpstan';
    private const PHPCS = 'phpcs';
    private const PHPCPD = 'phpcpd';
    private const SECURITY_CHECKER = 'security-checker';
    private const CODE_FIXER = 'code-fixer';

    private IoInterface $io;

    private ShExecHelper $executor;

    private TemplateLocationHelper $templateLocationHelper;

    public function __construct(
        IoInterface $io,
        ShExecHelper $executor,
        TemplateLocationHelper $templateLocationHelper
    )
    {
        $this->io = $io;
        $this->executor = $executor;
        $this->templateLocationHelper = $templateLocationHelper;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        $this->operateGitHooks($input);
        $this->operateComposerPackages($input);

        $next();
    }

    private function operateGitHooks(FixerInputDto $input): void
    {
        if (!file_exists(implode(DIRECTORY_SEPARATOR, [$input->getRepositoryPath(), '.hooks', 'pre-push.sample']))) {
            $this->io->write(
                $input->getRepositoryPath(). DIRECTORY_SEPARATOR . '.hooks',
                'pre-push.sample',
                'composer run qa-checks' . PHP_EOL
            );
        }

        if (strpos(
                $this->io->read($input->getRepositoryPath() . DIRECTORY_SEPARATOR . 'README.md'),
                '## Git hooks'
            ) === false
        ) {
            $readmeAddContent = <<<DOC

## Git hooks

There are some git hooks under `.hooks` directory. Feel free to copy & adjust & use them

DOC;

            $this->io->write(
                $input->getRepositoryPath(),
                'README.md',
                $this->io->read($input->getRepositoryPath() . DIRECTORY_SEPARATOR . 'README.md') . $readmeAddContent
            );
        }
    }

    private function operateComposerPackages(FixerInputDto $input): void
    {
        $checks = $input->getFixerData()['checks'] ?? [
                static::PHPSTAN,
                static::PHPCS,
                static::PHPCPD,
                static::CODE_FIXER,
                static::SECURITY_CHECKER,
            ];

        $packages = $this->createPackages($checks);

        list($code) = $this->executor->exec(
            sprintf('composer require --no-scripts --dev %s', implode(' ', $packages)),
            $input->getRepositoryPath()
        );

        if ($code !== 0) {
            throw new Exception('QA checks composer installation failed with code: ' . $code);
        }

        $composerJson = json_decode(
            $this->io->read($input->getRepositoryPath(). DIRECTORY_SEPARATOR . 'composer.json'),
            true
        );

        $composerJson['scripts'] = array_merge(
            $composerJson['scripts'],
            $this->createScripts($checks)
        );

        $this->io->write(
            $input->getRepositoryPath(),
            'composer.json',
            json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        if (in_array(static::PHPCS, $checks, true) && isset($input->getVariables()['phpcsConfig'])) {
            $this->templateLocationHelper->putIfMissingFromLocation(
                $input->getVariables()['phpcsConfig'],
                [$input->getRepositoryPath(), 'phpcs.xml.dist']
            );
        }
    }

    private function createPackages(array $checks): array
    {
        $result = [];

        foreach ($checks as $check) {
            switch ($check) {
                case static::PHPSTAN:
                    $result[] = 'phpstan/phpstan';
                    $result[] = 'phpstan/phpstan-symfony';
                    break;
                case static::PHPCS:
                    $result[] = 'squizlabs/php_codesniffer';
                    break;
                case static::PHPCPD:
                    $result[] = 'sebastian/phpcpd';
                    break;
                case static::CODE_FIXER:
                    $result[] = 'wapmorgan/php-code-fixer';
                    break;
                case static::SECURITY_CHECKER:
                    $result[] = 'sensiolabs/security-checker';
                    break;
                default:
                    throw new InvalidArgumentException('Unknown QA check type');
            }
        }

        return $result;
    }

    private function createScripts(array $checks): array
    {
        $scripts = ['qa-checks' => []];

        foreach ($checks as $check) {
            switch ($check) {
                case static::PHPSTAN:
                    $scripts['phpstan'] = './vendor/bin/phpstan analyze --level=5 ./src/';
                    $scripts['qa-checks'][] = '@phpstan';
                    break;
                case static::PHPCS:
                    $scripts['phpcs'] = './vendor/bin/phpcs ./src/';
                    $scripts['qa-checks'][] = '@phpcs';
                    break;
                case static::PHPCPD:
                    $scripts['phpcpd'] = './vendor/bin/phpcpd --fuzzy ./src/';
                    $scripts['qa-checks'][] = '@phpcpd';
                    break;
                case static::CODE_FIXER:
                    $scripts['phpcf'] = './vendor/bin/phpcf --target 7.1 ./src/';
                    $scripts['qa-checks'][] = '@phpcf';
                    break;
                case static::SECURITY_CHECKER:
                    $scripts['security-check'] = './vendor/bin/security-checker security:check ./composer.lock';
                    $scripts['qa-checks'][] = '@security-check';
                    break;
                default:
                    throw new InvalidArgumentException('Unknown QA check type');
            }
        }

        $scripts['qa-checks'] = array_unique($scripts['qa-checks']);

        return $scripts;
    }
}
