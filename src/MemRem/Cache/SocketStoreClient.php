<?php
/**
 * Front-end cache implementation for socket-based storage
 */

namespace MemRem\Cache;

use MemRem\Sockets\Peer;

class SocketStoreClient extends SocketStoreEndpoint implements KeyValueStore
{
    /**
     * @var PeerSocket Underlying socket
     */
    private $socket;

    /**
     * Send a request to the back-end and return the response data
     *
     * @param int   $code Request code
     * @param mixed $data Request data
     * @return mixed
     * @throws ClientErrorException
     * @throws ServerErrorException
     * @throws UnknownErrorException
     */
    private function sendRequest($code, $data)
    {
        $this->socket->write($this->encodeMessage($code, $data));

        $result = $this->decodeMessage($this->socket->readLine());

        if ($result['code'] !== self::RESPONSE_OK) {
            $errCode  = (int) $result['code'];
            $errClass = ($errCode & 0x7000) >> 12;
            $errNo    = $errCode & 0x0FFF;
            $errStr   = (string) $result['data'];

            switch ($errClass) {
                case self::ERRCLASS_CLIENT:
                    throw new ClientErrorException("{$errClass}/{$errNo}: {$errStr}", $errCode);

                case self::ERRCLASS_SERVER:
                    throw new ServerErrorException("{$errClass}/{$errNo}: {$errStr}", $errCode);

                default:
                    throw new UnknownErrorException("{$errClass}/{$errNo}: {$errStr}", $errCode);
            }
        }

        return $result['data'];
    }

    /**
     * Constructor
     *
     * @param Peer $socket Underlying socket
     */
    public function __construct(Peer $socket)
    {
        $this->socket = $socket;
    }

    /**
     * Destructor - Alias of close()
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Open the underlying socket and set it to blocking mode
     */
    public function open()
    {
        $this->socket->open();
        $this->socket->setBlocking(true);
    }

    /**
     * Close and unreference the underlying socket
     */
    public function close()
    {
        if ($this->socket) {
            $this->socket->close();
            $this->socket = null;
        }
    }

    public function toArray()
    {
        return $this->sendRequest(self::REQUEST_GETALL);
    }

    public function getKeys()
    {
        return $this->sendRequest(self::REQUEST_GETKEYS);
    }

    public function clear()
    {
        $this->sendRequest(self::REQUEST_CLEAR);
    }

    public function offsetExists($key)
    {
        return $this->sendRequest(self::REQUEST_EXISTS, $key);
    }

    public function offsetGet($key)
    {
        return $this->sendRequest(self::REQUEST_GET, $key);
    }

    public function offsetSet($key, $value)
    {
        $this->sendRequest(self::REQUEST_SET, [$key, $value]);
    }

    public function offsetUnset($key)
    {
        $this->sendRequest(self::REQUEST_UNSET, $key);
    }
}
