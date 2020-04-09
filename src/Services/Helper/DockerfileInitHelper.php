<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Helper;

use Linkorb\MultiRepo\Services\Io\IoInterface;
use Twig\Environment;

class DockerfileInitHelper
{
    private Environment $twig;

    private IoInterface $io;

    private string $templatesDir;

    public function __construct(Environment $twig, IoInterface $io, string $templatesDir)
    {
        $this->twig = $twig;
        $this->io = $io;
        $this->templatesDir = $templatesDir;
    }

    public function initDockerfile(string $repositoryPath): string
    {
        switch (true) {
            case file_exists($repositoryPath . DIRECTORY_SEPARATOR . 'Dockerfile'):
                return 'Dockerfile';
            case file_exists($repositoryPath . DIRECTORY_SEPARATOR . 'Dockerfile.qa'):
                return 'Dockerfile.qa';
            default:
                $this->io
                    ->write(
                        $repositoryPath,
                        'Dockerfile.qa',
                        $this->twig->render($this->templatesDir . DIRECTORY_SEPARATOR . 'Dockerfile.qa.twig')
                    )
                    ->write(
                        $repositoryPath,
                        'dinit.sh',
                        $this->twig->render($this->templatesDir . DIRECTORY_SEPARATOR . 'dinit.sh.twig')
                    );

                return 'Dockerfile.qa';
        }
    }
}
