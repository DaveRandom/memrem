<?php
/**
 * Exception thrown when a socket is closed by the remote host unexpectedly
 */

namespace MemRem\Sockets;

class DisconnectException extends TransportException {};
