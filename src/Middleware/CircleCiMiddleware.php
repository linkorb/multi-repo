<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\DockerfileInitHelper;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;

class CircleCiMiddleware implements MiddlewareInterface
{
    private TemplateLocationHelper $templateHelper;

    private DockerfileInitHelper $dockerfileHelper;

    public function __construct(
        TemplateLocationHelper $templateHelper,
        DockerfileInitHelper $dockerfileHelper
    )
    {
        $this->templateHelper = $templateHelper;
        $this->dockerfileHelper = $dockerfileHelper;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        $dockerfileName = $this->dockerfileHelper->initDockerfile($input->getRepositoryPath());

        $this->templateHelper->putIfMissingFromLocation(
            $input->getFixerData()['template'],
            [$input->getRepositoryPath(), '.circleci', 'config.yml'],
            ['dockerfile_name' => $dockerfileName]
        );

        $next();
    }
}
