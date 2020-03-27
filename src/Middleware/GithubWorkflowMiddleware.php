<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;
use Twig\Environment;

class GithubWorkflowMiddleware implements MiddlewareInterface
{
    private TemplateLocationHelper $templateHelper;

    private Environment $twig;

    public function __construct(TemplateLocationHelper $templateHelper, Environment $twig)
    {
        $this->templateHelper = $templateHelper;
        $this->twig = $twig;
    }

    public function __invoke(FixerInputDto $input, callable $next)
    {
        // TODO: Add logic for github workflow

        echo 'called pre step for github actions' . PHP_EOL;
        $next();
        echo 'called post step for github actions' . PHP_EOL;
    }
}
