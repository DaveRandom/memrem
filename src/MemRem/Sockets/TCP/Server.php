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
     * @var string TCP port of local socket
     */
    private $port;

    /**
     * Constructor
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
        $address = "tcp://{$this->address}:{$this->port}";

        if (false === $socket = stream_socket_server($address, $errNo, $errStr)) {
            throw new BindException($errStr, $errNo);
        }

        $this->setStream($socket);
    }

    /**
     * Close and unreference the underlying stream
     */
    public function close()
    {
        fclose($this->socket);
        $this->unsetStream();
    }

    /**
     * Accept a pending connection
     *
     * @return Peer
     * @throws AcceptException
     */
    public function accept()
    {
        if (!$client = stream_socket_accept($this->getStream())) {
            throw new AcceptException('accept() operation failed');
        }

        return new ServerPeer($client, $this->isBlocking());
    }
}
