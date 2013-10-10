<?php
/**
 * Stream socket wrapper for client-side of TCP server clients
 */

namespace MemRem\Sockets\TCP;

use MemRem\Sockets\ClientPeer as SocketClientPeer,
    MemRem\Sockets\ConnectException;

class ClientPeer extends SocketClientPeer
{
    /**
     * @var string Network address of remote host
     */
    private $address;

    /**
     * @var string TCP port of remote host
     */
    private $port;

    /**
     * Constructor
     *
     * @param string $address  Network address of remote host
     * @param int    $port     TCP port of remote host
     * @param bool   $blocking The blocking mode of the stream
     */
    public function __construct($address, $port, $blocking = false)
    {
        $this->address = (string) $address;
        $this->port = (int) $port;
        $this->setBlocking($blocking);
    }

    /**
     * Open a connection to the remote host
     *
     * @throws ConnectException
     */
    public function open()
    {
        $this->connect("tcp://{$this->address}:{$this->port}");
    }
}
