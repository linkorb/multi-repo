<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\DockerfileInitHelper;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;

class GithubWorkflowMiddleware implements MiddlewareInterface
{
    private TemplateLocationHelper $templateHelper;

    private DockerfileInitHelper $dockerfileHelper;

    public function __construct(TemplateLocationHelper $templateHelper, DockerfileInitHelper $dockerfileHelper)
    {
        $this->templateHelper = $templateHelper;
        $this->dockerfileHelper = $dockerfileHelper;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        $templates = $input->getFixerData()['templates'];
        $dockerfileName = $this->dockerfileHelper->initDockerfile($input->getRepositoryPath());

        $this->templateHelper->putIfMissingFromLocation(
            $templates['.github/workflows/staging.yml'],
            [$input->getRepositoryPath(), '.github', 'workflows', 'staging.yml'],
            ['dockerfile_name' => $dockerfileName]
        );

        $this->templateHelper->putIfMissingFromLocation(
            $templates['.github/workflows/production.yml'],
            [$input->getRepositoryPath(), '.github', 'workflows', 'production.yml'],
            ['dockerfile_name' => $dockerfileName]
        );

        $next();
    }
}
