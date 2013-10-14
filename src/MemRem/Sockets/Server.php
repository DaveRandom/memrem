<?php
/**
 * Generic server stream socket wrapper
 */

namespace MemRem\Sockets;

abstract class Server extends Socket
{
    /**
     * Binds and listens on the local socket
     *
     * @param string $address Local socket spec
     * @throws BindException
     */
    protected function bind($address, $extraFlags = 0, array $contextOpts = [])
    {
        $flags = STREAM_SERVER_BIND | $extraFlags;
        $context = stream_context_create($contextOpts);

        if (false === $stream = stream_socket_server($address, $errNo, $errStr, $flags, $context)) {
            throw new BindException($errStr, $errNo);
        }

        $this->setStream($stream);
    }

    /**
     * Accepts a pending connection and returns the stream
     *
     * @return resource
     * @throws AcceptException
     */
    protected function acceptClient()
    {
        if (!$stream = stream_socket_accept($this->getStream())) {
            throw new AcceptException('accept() operation failed');
        }

        return $stream;
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