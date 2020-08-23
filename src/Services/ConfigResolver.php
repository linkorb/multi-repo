<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services;

use Generator;
use InvalidArgumentException;
use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;
use Linkorb\MultiRepo\Services\Io\IoInterface;
use Symfony\Component\Yaml\Yaml;

class ConfigResolver
{
    private TemplateLocationHelper $templateLocationHelper;

    private IoInterface $io;

    private string $repositoriesBasePath;

    private string $repositoriesConfigPath;

    private array $config;

    private array $intendedLabels = [];

    public function __construct(
        TemplateLocationHelper $templateLocationHelper,
        IoInterface $io,
        string $repositoriesBasePath,
        string $repositoriesConfigPath,
        array $config
    ) {
        $this->templateLocationHelper = $templateLocationHelper;
        $this->io = $io;
        $this->repositoriesBasePath = $repositoriesBasePath;
        $this->repositoriesConfigPath = $repositoriesConfigPath;
        $this->config = $config;
    }

    /**
     * @param string[] $repositories
     */
    public function replaceRepositories(array $repositories): void
    {
        $currentRepoList = $this->config['configs'];
        $this->config['configs'] = [];

        foreach ($repositories as $repoName) {
            if (!array_key_exists($repoName, $currentRepoList)) {
                throw new InvalidArgumentException(sprintf('Passed repository `%s` doesn\'t exists in config', $repoName));
            }

            $this->config['configs'][$repoName] = $currentRepoList[$repoName];
        }
    }

    /**
     * @param string[] $fixersList
     */
    public function replaceFixers(array $fixersList): void
    {
        $fixers = array_flip($fixersList);

        if ($this->defaults()['fixers'] ?? false) {
            $this->defaults()['fixers'] = array_intersect_key($this->defaults()['fixers'], $fixers);
        }

        foreach ($this->config['configs'] as $repoName => $repoData) {
            if ($repoData['fixers'] ?? false) {
                $repoData['fixers'] = array_intersect_key($repoData['fixers'], $fixers);
            }
        }
    }

    public function setIntendedLabels(array $labels): void
    {
        $this->intendedLabels = $labels;
    }

    public function isMatchingLabels(RepoInputDto $inputDto): bool
    {
        if (empty($this->intendedLabels)) {
            return true;
        }

        return empty(array_diff_assoc($this->intendedLabels, $inputDto->getMetadata()['labels']));
    }

    public function iterateRepositories(): Generator
    {
        foreach ($this->config['configs'] as $repoName => $repoData) {
            yield $repoName => array_replace_recursive($this->defaults() ?? [], $repoData);
        }
    }

    public function loadMetadata(RepoInputDto $inputDto): RepoInputDto
    {
        $getYamlConfig = function (string $filename): array {
            if (file_exists($filename)) {
                return Yaml::parse($this->io->read($filename));
            }

            return [];
        };

        $repoRelativeConfig = $getYamlConfig(implode(
            DIRECTORY_SEPARATOR,
            [dirname(realpath($this->repositoriesConfigPath)), 'repos', $inputDto->getName(), 'repo.yaml']
        ));

        $repoInternalConfig = $getYamlConfig(implode(
            DIRECTORY_SEPARATOR,
            [$this->repositoriesBasePath, $inputDto->getName(), 'repo.yaml']
        ));

        return $inputDto->withMetadata(
            array_replace_recursive($repoInternalConfig['metadata'] ?? [],
                $repoRelativeConfig['metadata'] ?? [])
        );
    }

    private function defaults(): array
    {
        if (is_array($this->config['defaults'])) {
            return $this->config['defaults'];
        }

        if (!isset($this->config['defaults']) || empty($this->config['defaults'])) {
            return [];
        }

        $this->config['defaults'] = $this->templateLocationHelper->getYamlTemplate($this->config['defaults']);

        return $this->config['defaults'];
    }
}
