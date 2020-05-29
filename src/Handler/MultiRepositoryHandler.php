<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Generator;
use InvalidArgumentException;
use Linkorb\MultiRepo\Dto\RepoInputDto;
use Linkorb\MultiRepo\Exception\RepositoryHasUncommittedChangesException;
use Linkorb\MultiRepo\Services\Helper\TemplateLocationHelper;

class MultiRepositoryHandler
{
    private RepositoryHandler $repositoryHandler;

    private TemplateLocationHelper $templateLocationHelper;

    private array $config;

    public function __construct(
        RepositoryHandler $repositoryHandler,
        TemplateLocationHelper $templateLocationHelper,
        array $config
    ) {
        $this->repositoryHandler = $repositoryHandler;
        $this->templateLocationHelper = $templateLocationHelper;
        $this->config = $config;
    }

    public function iterateHandle(): Generator
    {
        foreach ($this->config['configs'] as $repoName => $repoData) {
            $this->repositoryHandler->handle(
                new RepoInputDto(
                    $repoName,
                    $repoData['gitUrl'],
                    array_replace_recursive($this->defaults() ?? [], $this->config['configs'][$repoName])
                )
            );

            yield $repoName;
        }
    }

    public function iterateHasChanges(): Generator
    {
        foreach ($this->config['configs'] as $repoName => $repoData) {
            try {
                $this->repositoryHandler->refreshRepository(
                    new RepoInputDto($repoName, $repoData['gitUrl'], [])
                );
            } catch (RepositoryHasUncommittedChangesException $exception) {
                yield $repoName;
            }
        }
    }

    public function getRepositoriesCount(): int
    {
        return count($this->config['configs']);
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
