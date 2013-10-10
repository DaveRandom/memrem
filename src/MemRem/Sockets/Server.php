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
    protected function bindAndListen($address)
    {
        if (false === $stream = stream_socket_server($address, $errNo, $errStr)) {
            throw new BindException($errStr, $errNo);
        }

        $this->setStream($stream);
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
     * @return ServerPeer
     * @throws AcceptException
     */
    public function accept()
    {
        if (!$stream = stream_socket_accept($this->getStream())) {
            throw new AcceptException('accept() operation failed');
        }

        return new ServerPeer($stream);
    }
}