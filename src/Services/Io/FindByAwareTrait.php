<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Services\Io;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

trait FindByAwareTrait
{
    public function findBy(string $pattern, string $directory = './'): RegexIterator
    {
        return new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory)
            ),
            $pattern,
            RecursiveRegexIterator::GET_MATCH
        );
    }
}
