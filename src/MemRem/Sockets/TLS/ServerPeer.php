<?php
/**
 * Stream socket wrapper for server-side of TCP server clients
 */

namespace MemRem\Sockets\TLS;

use MemRem\Sockets\ServerPeer as SocketServerPeer,
    MemRem\Sockets\TransportException,
    MemRem\Sockets\ConnectException;

class ServerPeer extends SocketServerPeer implements Peer
{
    /**
     * @var int Current TLS state of the stream
     */
    private $cryptoState = self::CRYPTO_STATE_DISABLED;

    /**
     * Constructor
     *
     * @param resource $stream   The underlying stream resource
     * @param bool     $blocking The blocking mode of the stream
     */
    public function __construct($stream, $blocking = false)
    {
        $this->setStream($stream);
        $this->setBlocking($blocking);
        $this->enableCrypto();
    }

    /**
     * No operation
     */
    public function open() {}

    /**
     * Check whether TLS encryption is currently enabled on the stream
     *
     * @return bool
     */
    public function getCryptoState()
    {
        return $this->cryptoState;
    }

    /**
     * Enable encryption on the stream
     *
     * @throws TransportException
     */
    public function enableCrypto()
    {
        if ($this->cryptoState & self::CRYPTO_STATE_CAN_ENABLE) {
            $this->cryptoState = self::CRYPTO_STATE_ENABLING;

            $result = stream_socket_enable_crypto($this->getStream(), true, STREAM_CRYPTO_METHOD_TLS_SERVER);

            if ($result === false) {
                throw new TransportException('Enabling encryption on stream failed');
            } else if ($result) {
                $this->cryptoState = self::CRYPTO_STATE_ENABLED;
            }
        }
    }

    /**
     * Disable encryption on the stream
     *
     * @throws TransportException
     */
    public function disableCrypto()
    {
        if ($this->cryptoState & self::CRYPTO_STATE_CAN_DISABLE) {
            $this->cryptoState = self::CRYPTO_STATE_DISABLING;

            $result = stream_socket_enable_crypto($this->getStream(), false, STREAM_CRYPTO_METHOD_TLS_SERVER);

            if ($result === false) {
                throw new TransportException('Disabling encryption on stream failed');
            } else if ($result) {
                $this->cryptoState = self::CRYPTO_STATE_DISABLED;
            }
        }
    }

    /**
     * Read a complete newline-terminated data block from the socket if one
     * is available
     *
     * @return string|null
     * @throws ReadException
     * @throws TransportException
     */
    public function readLine()
    {
        if ($this->cryptoState & self::CRYPTO_STATE_TRANSITIONAL) {
            if ($this->cryptoState === self::CRYPTO_STATE_ENABLING) {
                $this->enableCrypto();
            } else {
                $this->disableCrypto();
            }

            return null;
        } else {
            return $this->bufferedReadLine();
        }
    }
}
