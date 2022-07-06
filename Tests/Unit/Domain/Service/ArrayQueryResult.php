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

    public function current()
    {
        return $this->iterator->current();
    }

    public function next()
    {
        $this->iterator->next();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function valid()
    {
        return $this->iterator->valid();
    }

    public function rewind()
    {
        $this->iterator->rewind();
    }

    public function offsetExists($offset)
    {
        return $this->iterator->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->iterator->offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->iterator->offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->iterator->offsetUnset($offset);
    }

    public function count()
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
