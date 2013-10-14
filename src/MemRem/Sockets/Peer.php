<?php
/**
 * Generic peer stream socket wrapper
 */

namespace MemRem\Sockets;

abstract class Peer extends Socket
{
    /**
     * @var string Pending read data buffer
     */
    private $readBuffer = '';

    /**
     * Read a complete newline-terminated data block from the socket
     *
     * If a partial block is received, for example because not all necessary
     * packets have arrived, the received data is stored and null is returned.
     *
     * @return string|null
     * @throws ReadException
     */
    protected function bufferedReadLine()
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
                $this->readBuffer .= $chunk;

                if (substr($chunk, -1) === "\n") {
                    $result = $this->readBuffer;
                    $this->readBuffer = '';
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Determine whether the socket has reached EOF
     *
     * @return bool
     */
    public function isEOF()
    {
        $stream = $this->getStream();
        return stream_get_meta_data($stream)['eof'] || feof($stream);
    }

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
     * Read a complete newline-terminated data block from the socket if one
     * is available
     *
     * @return string|null
     * @throws ReadException
     */
    public function readLine()
    {
        return $this->bufferedReadLine();
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
