<?php
/**
 * Stream socket wrapper for client-side of TCP server clients
 */

namespace MemRem\Sockets\TLS;

use MemRem\Sockets\ClientPeer as SocketClientPeer,
    MemRem\Sockets\TransportException,
    MemRem\Sockets\ConnectException;

class ClientPeer extends SocketClientPeer implements Peer
{
    /**
     * @var string Network address of remote host
     */
    private $address;

    /**
     * @var string TCP port of remote host
     */
    private $port;

    /**
     * @var int Current TLS state of the stream
     */
    private $cryptoState = self::CRYPTO_STATE_DISABLED;

    /**
     * Constructor
     *
     * @param string $address  Network address of remote host
     * @param int    $port     TCP port of remote host
     * @param bool   $blocking The blocking mode of the stream
     */
    public function __construct($address, $port, $blocking = true)
    {
        $this->address = (string) $address;
        $this->port = (int) $port;
        $this->setBlocking($blocking);
    }

    /**
     * Open a connection to the remote host
     *
     * @throws ConnectException
     */
    public function open()
    {
        $this->connect("tcp://{$this->address}:{$this->port}");
        $this->enableCrypto();
    }

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

            $result = stream_socket_enable_crypto($this->getStream(), true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

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

            $result = stream_socket_enable_crypto($this->getStream(), false, STREAM_CRYPTO_METHOD_TLS_CLIENT);

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