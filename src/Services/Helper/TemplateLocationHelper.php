<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Helper;

use Linkorb\MultiRepo\Services\Io\IoInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use UnexpectedValueException;

final class TemplateLocationHelper
{
    public const LOCAL_PATH = 'local_path';
    public const REMOTE_URL = 'remote_url';

    private IoInterface $io;

    private HttpClientInterface $client;

    public function __construct(IoInterface $io, HttpClientInterface $client)
    {
        $this->io = $io;
        $this->client = $client;
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

    private function getFileLocationType(string $dsn): string
    {
        if (filter_var($dsn, FILTER_VALIDATE_URL)) {
            return static::REMOTE_URL;
        } else {
            return static::LOCAL_PATH;
        }
    }
}
