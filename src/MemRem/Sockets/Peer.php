<?php
/**
 * Generic peer stream socket wrapper
 */

namespace MemRem\Sockets;

abstract class Peer extends Socket
{
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
     * Recieve data from the socket
     *
     * @return string|null
     * @throws ReadException
     */
    abstract public function readLine();

    /**
     * Send data to the socket
     *
     * @param string $data The data to send
     * @throws WriteException
     */
    abstract public function write($data);
}
