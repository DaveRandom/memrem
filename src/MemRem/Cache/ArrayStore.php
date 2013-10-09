<?php
/**
 * Self-contained cache implementation for array-based storage
 */

namespace MemRem\Cache;

class ArrayStore implements KeyValueStore
{
    private $data = [];

    public function open() {}

    public function close() {}

    public function toArray()
    {
        return $this->data;
    }

    public function getKeys()
    {
        return array_keys($this->data);
    }

    public function clear()
    {
        $this->data = [];
    }

    public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    public function offsetGet($key)
    {
        return $this->data[$key];
    }

    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function offsetUnset($key)
    {
        unset($this->data[$key]);
    }
}
