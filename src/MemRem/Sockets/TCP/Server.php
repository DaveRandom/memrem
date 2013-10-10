<?php
/**
 * Stream socket wrapper for server-side of TCP server clients
 */

namespace MemRem\Sockets\TCP;

use MemRem\Sockets\Server as SocketServer,
    MemRem\Sockets\AcceptException,
    MemRem\Sockets\BindException;

class Server extends SocketServer
{
    /**
     * @var string Network address of local socket
     */
    private $address;

    /**
     * @var int TCP port of local socket
     */
    private $port;

    /**
     * Constructor
     *
     * @param string $address  Network address of local socket
     * @param int    $port     TCP port of local socket
     * @param bool   $blocking Blocking mode to set on stream
     */
    public function __construct($address, $port, $blocking = false)
    {
        $this->address = (string) $address;
        $this->port = (int) $port;
        $this->setBlocking($blocking);
    }

    /**
     * Bind and listen on the underlying stream
     *
     * @throws BindException
     */
    public function open()
    {
        $this->bindAndListen("tcp://{$this->address}:{$this->port}");
    }
}
