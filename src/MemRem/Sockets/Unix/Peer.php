<?php
/**
 * Generic Unix peer stream socket wrapper
 */

namespace MemRem\Sockets\Unix;

use MemRem\Sockets\Peer as SocketPeer,
    MemRem\Sockets\DisconnectException,
    MemRem\Sockets\ReadException,
    MemRem\Sockets\WriteException;

abstract class Peer extends SocketPeer
{
    /**
     * @var string Pending read data buffer
     */
    private $buffer = '';

    /**
     * Shutdown, close and destroy the underlying stream
     */
    public function close()
    {
        $socket = $this->getStream();

        stream_socket_shutdown($socket, STREAM_SHUT_RDWR);
        fclose($socket);

        $this->unsetStream();
    }

    /**
     * Read a complete newline-terminated data block from the socket
     *
     * If a partial block is received, for example because not all necessary
     * packets have arrived, the received data is stored and null is returned.
     *
     * @return string|null
     * @throws ReadException
     */
    public function readLine()
    {
        $socket = $this->getStream();
        $result = null;

        while (true) {
            if (false === $chunk = fgets($socket)) {
                if ($this->isEOF()) {
                    throw new DisconnectException('The remote host closed the connection unexpectedly');
                } else {
                    throw new ReadException('Read operation on socket failed');
                }
            }

            if ($chunk === '') {
                break;
            } else {
                $this->buffer .= $chunk;

                if (substr($chunk, -1) === "\n") {
                    $result = $this->buffer;
                    $this->buffer = '';
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Send data to the socket
     *
     * @param string $data The data to send
     * @throws WriteException
     */
    public function write($data)
    {
        if (false === fwrite($this->getStream(), (string) $data)) {
            throw new WriteException('Unable to write to socket');
        }
    }
}
