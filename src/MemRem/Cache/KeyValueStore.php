<?php
/**
 * Interface for all front-end and self-contained cache implementations
 */

namespace MemRem\Cache;

use ArrayAccess;

interface KeyValueStore extends ArrayAccess
{
    /**
     * Open the underlying data store
     */
    public function open();

    /**
     * Close the underlying data store
     */
    public function close();

    /**
     * Fetch all values as an associative array
     *
     * @return mixed[]
     */
    public function toArray();

    /**
     * Get all keys with associated values
     *
     * @return mixed[]
     */
    public function getKeys();

    /**
     * Remove all values
     */
    public function clear();
}
