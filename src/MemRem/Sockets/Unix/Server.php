<?php
/**
 * Stream socket wrapper for server-side of Unix server clients
 */

namespace MemRem\Sockets\Unix;

use MemRem\Sockets\Server as SocketServer,
    MemRem\Sockets\AcceptException,
    MemRem\Sockets\BindException;

class Server extends SocketServer
{
    /**
     * @var string File system path of socket
     */
    private $path;

    /**
     * Constructor
     *
     * @param string $path     File system path of socket
     * @param bool   $blocking Blocking mode to set on stream
     */
    public function __construct($path, $blocking = false)
    {
        $this->path = (string) $path;
        $this->setBlocking($blocking);
    }

    /**
     * Bind and listen on the underlying stream
     *
     * @throws BindException
     */
    public function open()
    {
        $this->bindAndListen("unix://{$this->path}");
    }
}
