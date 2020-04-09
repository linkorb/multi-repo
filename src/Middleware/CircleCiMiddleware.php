<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\DockerfileInitHelper;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;
use Linkorb\MultiRepo\Services\Io\IoInterface;
use Twig\Environment;

class CircleCiMiddleware implements MiddlewareInterface
{
    private TemplateLocationHelper $templateHelper;

    private Environment $twig;

    private IoInterface $io;

    private DockerfileInitHelper $dockerfileHelper;

    public function __construct(
        TemplateLocationHelper $templateHelper,
        Environment $twig,
        IoInterface $io,
        DockerfileInitHelper $dockerfileHelper
    )
    {
        $this->templateHelper = $templateHelper;
        $this->twig = $twig;
        $this->io = $io;
        $this->dockerfileHelper = $dockerfileHelper;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        $dockerfileName = $this->dockerfileHelper->initDockerfile($input->getRepositoryPath());

        if ($input->getFixerData()['template']
            && !file_exists(implode(DIRECTORY_SEPARATOR, [$input->getRepositoryPath(), '.circleci', 'config.yml']))
        ) {
            $this->io->write(
                implode(DIRECTORY_SEPARATOR, [$input->getRepositoryPath(), '.circleci']),
                'config.yml',
                $this->twig->render(
                    $this->templateHelper->getTemplate($input->getFixerData()['template']),
                    ['dockerfile_name' => $dockerfileName]
                )
            );
        }

        $next();
    }
}
