<?php
/**
 * Stream socket wrapper for server-side of TCP server clients
 */

namespace MemRem\Sockets\TLS;

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
     * @var string Local filesystem path to SSL certificate
     */
    private $certPath;

    /**
     * Constructor
     *
     * @param string $address  Network address of local socket
     * @param int    $port     TCP port of local socket
     * @param string $certPath Local filesystem path to SSL certificate
     * @param bool   $blocking Blocking mode to set on stream
     */
    public function __construct($address, $port, $certPath, $blocking = false)
    {
        $this->address = (string) $address;
        $this->port = (int) $port;
        $this->certPath = realpath($certPath);
        $this->setBlocking($blocking);
    }

    /**
     * Bind and listen on the underlying stream
     *
     * @throws BindException
     */
    public function open()
    {
        $contextOpts = [
            'ssl' => [
                'local_cert' => $this->certPath,
                'disable_compression' => true
            ]
        ];

        $this->bind("tcp://{$this->address}:{$this->port}", STREAM_SERVER_LISTEN, $contextOpts);
    }

    /**
     * Accept a pending connection and returns a ServerPeer
     *
     * @return ServerPeer
     * @throws AcceptException
     */
    public function accept()
    {
        return new ServerPeer($this->acceptClient());
    }
}
