<?php

namespace MemRem\Sockets\TLS;

use MemRem\Sockets\TransportException;

interface Peer
{
    /**
     * Stream encryption statuses
     */
    const CRYPTO_STATE_DISABLED     = 0x04; // 0b100
    const CRYPTO_STATE_ENABLING     = 0x06; // 0b110
    const CRYPTO_STATE_ENABLED      = 0x01; // 0b001
    const CRYPTO_STATE_DISABLING    = 0x03; // 0b011
    const CRYPTO_STATE_CAN_ENABLE   = 0x04; // 0b100
    const CRYPTO_STATE_TRANSITIONAL = 0x02; // 0b010
    const CRYPTO_STATE_CAN_DISABLE  = 0x01; // 0b001

    /**
     * Get the current stream encryption status
     *
     * @return bool
     */
    public function getCryptoState();

    /**
     * Enable encryption on the stream
     *
     * @throws TransportException
     */
    public function enableCrypto();

    /**
     * Disable encryption on the stream
     *
     * @throws TransportException
     */
    public function disableCrypto();
}
