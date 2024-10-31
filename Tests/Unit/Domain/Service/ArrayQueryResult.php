<?php
declare(strict_types=1);

namespace Netlogix\Migrations\Tests\Unit\Domain\Service;

use ArrayObject;
use Neos\Flow\Persistence\Doctrine\Query;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;

class ArrayQueryResult implements QueryResultInterface
{

    private $result;
    private $iterator;

    public function __construct(array $result)
    {
        $this->result = $result;
        $this->iterator = (new ArrayObject($result))->getIterator();
    }

    public function current(): mixed
    {
        return $this->iterator->current();
    }

    public function next(): void
    {
        $this->iterator->next();
    }

    public function key(): mixed
    {
        return $this->iterator->key();
    }

    public function valid(): bool
    {
        return $this->iterator->valid();
    }

    public function rewind(): void
    {
        $this->iterator->rewind();
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->iterator->offsetExists($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->iterator->offsetGet($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->iterator->offsetSet($offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->iterator->offsetUnset($offset);
    }

    public function count(): int
    {
        return $this->iterator->count();
    }

    public function getQuery(): QueryInterface
    {
        return new Query('foo');
    }

    public function getFirst()
    {
        reset($this->result);

        return current($this->result);
    }

    public function toArray(): array
    {
        return $this->result;
    }

}
