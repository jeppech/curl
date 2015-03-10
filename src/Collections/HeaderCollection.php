<?php

namespace Jeppech\Curl;

class HeaderCollection implements \ArrayAccess, \Countable {

    protected $collection;

    /**
     *
     *
     * @param string $offset
     * @param mixed $value
     * @return bool|void
     * @throws \InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        if (!is_string($offset)) {
            throw new \InvalidArgumentException("Offset must be of type string");
        }

        if ($this->offsetExists($offset)) {
            array_push($this->collection[$offset], $value);
        } else {
            $this->collection[$offset] = [$value];
        }

        return true;
    }

    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->collection[$offset];
        }

        return false;
    }

    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    public function count()
    {
        return (count($this->collection, 1) - count($this->collection));
    }
}