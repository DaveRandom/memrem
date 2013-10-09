<?php
/**
 * Stream socket wrapper for client-side of TCP server clients
 */

namespace MemRem\Sockets\TCP;

use MemRem\Sockets\ConnectException;

class ClientPeer extends Peer
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
        $address = "tcp://{$this->address}:{$this->port}";
        $flags = $this->isBlocking() ? STREAM_CLIENT_CONNECT : STREAM_CLIENT_ASYNC_CONNECT;

        if (!$socket = stream_socket_client($address, $errNo, $errStr, $flags)) {
            throw new ConnectException($errStr, $errNo);
        }

        $this->setStream($socket);
    }
}
