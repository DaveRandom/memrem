<?php
/**
 * Generic server stream socket wrapper
 */

namespace MemRem\Sockets;

abstract class Server extends Socket
{
    /**
     * Accept a pending connection
     *
     * @return Peer
     */
    abstract public function accept();
}