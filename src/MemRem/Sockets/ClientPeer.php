<?php
/**
 * Stream socket wrapper for clients
 */

namespace MemRem\Sockets;

abstract class ClientPeer extends Peer
{
    /**
     * Open a connection to the remote host
     *
     * @param string $address Remote socket address
     * @throws ConnectException
     */
    protected function connect($address)
    {
        $flags = $this->isBlocking() ? STREAM_CLIENT_CONNECT : STREAM_CLIENT_ASYNC_CONNECT;

        if (!$stream = stream_socket_client($address, $errNo, $errStr, $flags)) {
            throw new ConnectException($errStr, $errNo);
        }

        $this->setStream($stream);
    }
}
