<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Factory;

use Linkorb\MultiRepo\Middleware\GithubWorkflowMiddleware;
use Linkorb\MultiRepo\Middleware\QaCheckMiddleware;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;

class MiddlewareFactory
{
    public static function createQaFactory(): callable
    {
        return function () {
            return new QaCheckMiddleware();
        };
    }

    public static function createGithubActionsFactory(TemplateLocationHelper $helper): callable
    {
        return function () use ($helper) {
            return new GithubWorkflowMiddleware($helper);
        };
    }
}
