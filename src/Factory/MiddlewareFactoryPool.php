<?php declare(strict_types=1);

namespace Linkorb\MultiRepo\Factory;

use InvalidArgumentException;
use Linkorb\MultiRepo\Middleware\MiddlewareInterface;

class MiddlewareFactoryPool
{
    /** @var callable[] */
    private array $pool = [];

    public function addToPool(string $type, callable $fn): self
    {
        $this->pool[$type] = $fn;

        return $this;
    }

    public function createMiddleware(string $type): MiddlewareInterface
    {
        if (!isset($this->pool[$type])) {
            throw new InvalidArgumentException('Incorrect type passed');
        }

        return $this->pool[$type]();
    }
}
