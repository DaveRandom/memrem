<?php
/**
 * Stream socket wrapper for client-side of Unix server clients
 */

namespace MemRem\Sockets\Unix;

use MemRem\Sockets\ClientPeer as SocketClientPeer,
    MemRem\Sockets\ConnectException;

class ClientPeer extends SocketClientPeer
{
    /**
     * @var string File system path of socket
     */
    private $path;

    /**
     * Constructor
     *
     * @param string $path     File system path of socket
     * @param bool   $blocking The blocking mode of the stream
     */
    public function __construct($path, $blocking = false)
    {
        $this->path = (string) $path;
        $this->setBlocking($blocking);
    }

    /**
     * Open a connection to the remote host
     *
     * @throws ConnectException
     */
    public function open()
    {
        $this->connect("unix://{$this->path}");
    }
}
