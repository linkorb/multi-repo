<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Generator;
use InvalidArgumentException;
use Linkorb\MultiRepo\Dto\RepoInputDto;
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
        foreach ($this->config['list'] as $repoName => $repoDsn) {
            $this->repositoryHandler->handle(
                new RepoInputDto(
                    $repoName,
                    $repoDsn,
                    array_replace_recursive($this->defaults() ?? [], $this->config['configs'][$repoName])
                )
            );

            yield $repoName;
        }
    }

    public function getRepositoriesCount(): int
    {
        return count($this->config['list']);
    }

    /**
     * @param string[] $repositories
     */
    public function replaceRepositories(array $repositories): void
    {
        $currentRepoList = $this->config['list'];
        $this->config['list'] = [];

        foreach ($repositories as $repositoryPath) {
            $repoPathComponents = explode(DIRECTORY_SEPARATOR, $repositoryPath);
            $repoName = end($repoPathComponents);

            if ($repoName === false) {
                throw new InvalidArgumentException('Wrong repository path passed');
            }

            if (!array_key_exists($repoName, $currentRepoList)) {
                throw new InvalidArgumentException(sprintf('Passed repository `%s` doesn\'t exists in config', $repoName));
            }

            $this->config['list'][$repoName] = $currentRepoList[$repoName];
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

        foreach ($this->config['list'] as $repoName => $repoDsn) {
            if ($this->config['configs'][$repoName]['fixers'] ?? false) {
                $this->config['configs'][$repoName]['fixers'] = array_intersect_key(
                    $this->config['configs'][$repoName]['fixers'],
                    $fixers
                );
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
