<?php
/**
 * Generic stream socket wrapper
 */

namespace MemRem\Sockets;

abstract class Socket
{
    /**
     * @var resource Underlying stream
     */
    private $stream;

    /**
     * @var int Resource ID of stream
     */
    private $id;

    /**
     * @var bool Whether the stream is blocking
     */
    private $blocking = true;

    /**
     * Destructor - close the underlying stream if stream resource still set
     */
    public function __destruct()
    {
        if ($this->stream) {
            $this->close();
        }
    }

    /**
     * Set the underlying stream resource
     *
     * @param resource $stream
     */
    protected function setStream($stream)
    {
        stream_set_blocking($stream, $this->blocking);
        $this->stream = $stream;
        $this->id = (int) $stream;
    }

    /**
     * Unreference the underlying stream resource
     */
    protected function unsetStream()
    {
        $this->stream = $this->id = null;
    }

    /**
     * Get the underlying stream resource
     *
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Get the underlying stream resource ID
     *
     * @return int
     */
    public function getID()
    {
        return $this->id;
    }

    /**
     * Get the blocking attribute of the underlying stream
     *
     * @return bool
     */
    public function isBlocking()
    {
        return $this->blocking;
    }

    /**
     * Set the blocking attribute of the underlying stream
     *
     * @param bool $blocking
     */
    public function setBlocking($blocking)
    {
        $this->blocking = (bool) $blocking;

        if ($this->stream) {
            stream_set_blocking($this->stream, $this->blocking);
        }
    }

    /**
     * Open the connection
     */
    abstract public function open();

    /**
     * Close the connection
     */
    abstract public function close();
}
