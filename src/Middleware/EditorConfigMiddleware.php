<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Middleware;

use Linkorb\MultiRepo\Dto\FixerInputDto;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;
use Linkorb\MultiRepo\Services\Io\IoInterface;
use Twig\Environment;

class EditorConfigMiddleware implements MiddlewareInterface
{
    private TemplateLocationHelper $templateHelper;

    private Environment $twig;

    private IoInterface $io;

    public function __construct(TemplateLocationHelper $templateHelper, Environment $twig, IoInterface $io)
    {
        $this->templateHelper = $templateHelper;
        $this->twig = $twig;
        $this->io = $io;
    }

    public function __invoke(FixerInputDto $input, callable $next): void
    {
        if ($input->getFixerData()['template']
            && !file_exists($input->getRepositoryPath() . DIRECTORY_SEPARATOR . '.editorconfig')
        ) {
            $this->io->write(
                $input->getRepositoryPath(),
                '.editorconfig',
                $this->twig
                    ->createTemplate($this->templateHelper->getTemplate($input->getFixerData()['template']))
                    ->render()
            );
        }

        $next();
    }
}
