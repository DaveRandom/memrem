<?php
/**
 * Stream socket wrapper for server-side of Unix server clients
 */

namespace MemRem\Sockets\Unix;

class ServerPeer extends Peer
{
    /**
     * Constructor
     *
     * @param resource $stream   The underlying stream resource
     * @param bool     $blocking The blocking mode of the stream
     */
    public function __construct($stream, $blocking = false)
    {
        $this->setStream($stream);
        $this->setBlocking($blocking);
    }

    /**
     * No operation
     */
    public function open() {}
}
