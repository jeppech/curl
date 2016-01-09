<?php
/**
 * Created by PhpStorm.
 * User: jc
 * Date: 08/10/15
 * Time: 12:50
 */

namespace Codr\Curl\Collections;


class Collection implements \ArrayAccess, \Countable
{
    /**
     * Stores the collection
     *
     * @var array
     */
    protected $collection;

    /**
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

    /**
     * @param string $offset
     * @return bool|array
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->collection[$offset];
        }

        return false;
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->collection[$offset]);
    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    /**
     * @return int
     */
    public function count()
    {
        return (count($this->collection, 1) - count($this->collection));
    }
}