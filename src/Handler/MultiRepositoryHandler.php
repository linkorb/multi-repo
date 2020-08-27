<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Handler;

use Generator;
use InvalidArgumentException;
use Linkorb\MultiRepo\Action\CommandActionInterface;
use Linkorb\MultiRepo\Services\ConfigResolver;

class MultiRepositoryHandler
{
    private RepositoryHandlerInterface $repositoryHandler;

    private ConfigResolver $configResolver;

    public function __construct(
        RepositoryHandlerInterface $repositoryHandler,
        ConfigResolver $configResolver
    ) {
        $this->repositoryHandler = $repositoryHandler;
        $this->configResolver = $configResolver;
    }

    public function iterateExecAction(CommandActionInterface $action): Generator
    {
        return $action->execute($this->configResolver, $this->repositoryHandler);
    }
    /**
     * @param string[] $repositories
     */
    public function replaceRepositories(array $repositories): void
    {
        $this->configResolver->replaceRepositories($repositories);
    }

    /**
     * @param string[] $fixersList
     */
    public function intersectFixers(array $fixersList): void
    {
        $this->configResolver->intersectFixers($fixersList);
    }

    public function replaceFixers(array $fixersData): void
    {
        $this->configResolver->replaceFixers($fixersData);
    }

    public function setIntendedLabels(array $labels): void
    {
        $parsedLabels = [];
        foreach ($labels as $combinedLabelRow) {
            if (!str_contains($combinedLabelRow, '=')) {
                throw new InvalidArgumentException('Label should contain name and value separated by `=`');
            }

            [$key, $value] = explode('=', $combinedLabelRow, 2);

            $parsedLabels[$key] = $value;
        }

        $this->configResolver->setIntendedLabels($parsedLabels);
    }
}
