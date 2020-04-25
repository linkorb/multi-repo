<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Factory;

use Linkorb\MultiRepo\Middleware\CircleCiMiddleware;
use Linkorb\MultiRepo\Middleware\GithubWorkflowMiddleware;
use Linkorb\MultiRepo\Middleware\JsonMiddleware;
use Linkorb\MultiRepo\Middleware\QaCheckMiddleware;
use Linkorb\MultiRepo\Services\Helper\DockerfileInitHelper;
use Linkorb\MultiRepo\Services\Helper\ShExecHelper;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;
use Linkorb\MultiRepo\Services\Io\IoInterface;
use Twig\Environment;

class MiddlewareFactory
{
    public static function createQaFactory(IoInterface $io, ShExecHelper $executor): callable
    {
        return function () use ($io, $executor): QaCheckMiddleware {
            return new QaCheckMiddleware($io, $executor);
        };
    }

    public static function createGithubActionsFactory(
        TemplateLocationHelper $helper,
        Environment $twig,
        IoInterface $io,
        DockerfileInitHelper $dockerfileHelper
    ): callable
    {
        return function () use ($helper, $twig, $io, $dockerfileHelper): GithubWorkflowMiddleware {
            return new GithubWorkflowMiddleware($helper, $twig, $io, $dockerfileHelper);
        };
    }

    public static function createCircleCiFactory(
        TemplateLocationHelper $helper,
        Environment $twig,
        IoInterface $io,
        DockerfileInitHelper $dockerfileHelper
    ): callable
    {
        return function () use ($helper, $twig, $io, $dockerfileHelper): CircleCiMiddleware {
            return new CircleCiMiddleware($helper, $twig, $io, $dockerfileHelper);
        };
    }

    public static function createJsonFactory(IoInterface $io): callable
    {
        return function () use ($io): JsonMiddleware {
            return new JsonMiddleware($io);
        };
    }
}
