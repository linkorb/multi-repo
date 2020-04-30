<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Helper;

use Linkorb\MultiRepo\Services\Io\IoInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Twig\Environment;
use UnexpectedValueException;

final class TemplateLocationHelper
{
    public const LOCAL_PATH = 'local_path';
    public const REMOTE_URL = 'remote_url';

    private IoInterface $io;

    private Environment $twig;

    private HttpClientInterface $client;

    public function __construct(IoInterface $io, HttpClientInterface $client, Environment $twig)
    {
        $this->io = $io;
        $this->client = $client;
        $this->twig = $twig;
    }

    public function getYamlTemplate(string $dsn): array
    {
        return Yaml::parse($this->getTemplate($dsn));
    }

    public function getTemplate(string $dsn): string
    {
        switch ($this->getFileLocationType($dsn)) {
            case static::LOCAL_PATH:
                return $this->io->read($dsn);
            case static::REMOTE_URL:
                return $this->client->request('GET', $dsn)->getContent();
            default:
                throw new UnexpectedValueException();
        }
    }

    public function putIfMissingFromLocation(
        ?string $templateLocation,
        array $filePathComponents,
        array $renderContext = []
    ): void
    {
        $fileName = end($filePathComponents);
        $fileDirComponents = array_slice($filePathComponents, 0, -1);

        if ($templateLocation && !file_exists(implode(DIRECTORY_SEPARATOR, $filePathComponents))) {
            $this->io->write(
                implode(DIRECTORY_SEPARATOR, $fileDirComponents),
                $fileName,
                $this->twig
                    ->createTemplate($this->getTemplate($templateLocation))
                    ->render($renderContext)
            );
        }
    }

    public function putIfMissingFromString(string $content, array $filePathComponents): void
    {
        $fileName = end($filePathComponents);
        $fileDirComponents = array_slice($filePathComponents, 0, -1);

        if (!file_exists(implode(DIRECTORY_SEPARATOR, $filePathComponents))) {
            $this->io->write(implode(DIRECTORY_SEPARATOR, $fileDirComponents), $fileName, $content);
        }
    }

    private function getFileLocationType(string $dsn): string
    {
        if (filter_var($dsn, FILTER_VALIDATE_URL)) {
            return static::REMOTE_URL;
        } else {
            return static::LOCAL_PATH;
        }
    }
}
