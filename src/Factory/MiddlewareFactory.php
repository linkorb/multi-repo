<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Factory;

use Linkorb\MultiRepo\Middleware\CircleCiMiddleware;
use Linkorb\MultiRepo\Middleware\ComposerJsonDependencyBlacklistMiddleware;
use Linkorb\MultiRepo\Middleware\ComposerJsonVersionConstraintMiddleware;
use Linkorb\MultiRepo\Middleware\ConventionalCommitMiddleware;
use Linkorb\MultiRepo\Middleware\EditorConfigMiddleware;
use Linkorb\MultiRepo\Middleware\ExecuteCustomCommandMiddleware;
use Linkorb\MultiRepo\Middleware\GithubWorkflowMiddleware;
use Linkorb\MultiRepo\Middleware\JsonMiddleware;
use Linkorb\MultiRepo\Middleware\QaCheckMiddleware;
use Linkorb\MultiRepo\Middleware\YamlMiddleware;
use Linkorb\MultiRepo\Services\Helper\DockerfileInitHelper;
use Linkorb\MultiRepo\Services\Helper\ShExecHelper;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;
use Linkorb\MultiRepo\Services\Io\IoInterface;

class MiddlewareFactory
{
    public static function createQaFactory(
        IoInterface $io,
        ShExecHelper $executor,
        TemplateLocationHelper $templateLocationHelper
    ): callable
    {
        return function () use ($io, $executor, $templateLocationHelper): QaCheckMiddleware {
            return new QaCheckMiddleware($io, $executor, $templateLocationHelper);
        };
    }

    public static function createGithubActionsFactory(
        TemplateLocationHelper $helper,
        DockerfileInitHelper $dockerfileHelper
    ): callable
    {
        return function () use ($helper, $dockerfileHelper): GithubWorkflowMiddleware {
            return new GithubWorkflowMiddleware($helper, $dockerfileHelper);
        };
    }

    public static function createCircleCiFactory(
        TemplateLocationHelper $helper,
        DockerfileInitHelper $dockerfileHelper
    ): callable
    {
        return function () use ($helper, $dockerfileHelper): CircleCiMiddleware {
            return new CircleCiMiddleware($helper, $dockerfileHelper);
        };
    }

    public static function createJsonFactory(IoInterface $io): callable
    {
        return function () use ($io): JsonMiddleware {
            return new JsonMiddleware($io);
        };
    }

    public static function createYamlFactory(IoInterface $io): callable
    {
        return function () use ($io): YamlMiddleware {
            return new YamlMiddleware($io);
        };
    }

    public static function createComposerJsonVersionConstraint(IoInterface $io, ShExecHelper $executor): callable
    {
        return function () use ($io, $executor): ComposerJsonVersionConstraintMiddleware {
            return new ComposerJsonVersionConstraintMiddleware($io, $executor);
        };
    }

    public static function createComposerJsonDependencyBlacklist(IoInterface $io, ShExecHelper $executor): callable
    {
        return function () use ($io, $executor): ComposerJsonDependencyBlacklistMiddleware {
            return new ComposerJsonDependencyBlacklistMiddleware($io, $executor);
        };
    }

    public static function createEditorConfig(TemplateLocationHelper $helper): callable
    {
        return function () use ($helper): EditorConfigMiddleware {
            return new EditorConfigMiddleware($helper);
        };
    }

    public static function createConventionalCommit(
        TemplateLocationHelper $helper,
        ShExecHelper $executor,
        IoInterface $io
    ): callable
    {
        return function () use ($helper, $executor, $io): ConventionalCommitMiddleware {
            return new ConventionalCommitMiddleware($helper, $executor, $io);
        };
    }

    public static function createCustomCommandExecutor(ShExecHelper $executor): callable
    {
        return function () use ($executor): ExecuteCustomCommandMiddleware {
            return new ExecuteCustomCommandMiddleware($executor);
        };
    }
}
