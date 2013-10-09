<?php
/**
 * Stream socket wrapper for client-side of Unix server clients
 */

namespace MemRem\Sockets\Unix;

use MemRem\Sockets\ConnectException;

class ClientPeer extends Peer
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
        $address = "unix://{$this->path}";
        $flags = $this->isBlocking() ? STREAM_CLIENT_CONNECT : STREAM_CLIENT_ASYNC_CONNECT;

        if (!$socket = stream_socket_client($address, $errNo, $errStr, $flags)) {
            throw new ConnectException($errStr, $errNo);
        }

        $this->setStream($socket);
    }
}
