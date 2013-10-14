<?php
/**
 * Base class for front- and back-end of socket-based cache implementation
 */

namespace MemRem\Cache;

use RuntimeException;

abstract class SocketStoreEndpoint
{
    /**
     * Request codes
     */
    const REQUEST_EXISTS  = 0x0001;
    const REQUEST_GET     = 0x0002;
    const REQUEST_SET     = 0x0003;
    const REQUEST_UNSET   = 0x0004;
    const REQUEST_GETALL  = 0x0005;
    const REQUEST_GETKEYS = 0x0006;
    const REQUEST_CLEAR   = 0x0007;

    /**
     * Response success
     */
    const RESPONSE_OK = 0x8000;

    /**
     * Client errors - 0x9xxx
     */
    const ERRCLASS_CLIENT = 1;
    const RESPONSE_BADREQUEST = 0x9001;
    const RESPONSE_NOTFOUND   = 0x9002;

    /**
     * Server errors - 0xAxxx
     */
    const ERRCLASS_SERVER = 2;

    /**
     * Split a message code into its component parts
     *
     * Returns an array of the form
     * [
     *    bool $isResponse
     *    int  $messageClass
     *    int  $messageCode
     * ]
     *
     * @param int $code The message code
     * @return mixed[]
     */
    protected function parseMessageCode($code)
    {
        $code = ((int) $code) & 0xFFFF;

        return [
            (bool) $code & 0x8000,
            ($code & 0x7000) >> 12,
            $code & 0x0FFF
        ];
    }

    /**
     * Build a message code from its component parts
     *
     * @param bool $isResponse   true if the message is a response
     * @param int  $messageClass Single octal digit for message class
     * @param bool $messageCode  Message code
     * @return int
     */
    protected function buildMessageCode($isResponse, $messageClass, $messageCode)
    {
        return (((int)(bool) $isResponse) << 15)
             | ((((int) $messageClass) & 0x0007) << 11)
             | (((int) $messageCode) & 0x0FFF);
    }

    /**
     * Encode a message for transmission over the wire
     *
     * @param int   $code The message code
     * @param mixed $data The message payload
     * @return string
     */
    protected function encodeMessage($code, $data = null)
    {
        return serialize(['code' => $code, 'data' => $data]) . "\n";
    }

    /**
     * Decode a message received from the wire
     *
     * @param string $message The raw message
     * @return mixed[] Associative array with the key 'code' and 'data'
     * @throws ProtocolErrorException
     */
    protected function decodeMessage($message)
    {
        if (!($result = unserialize(rtrim($message))) || !isset($result['code']) || !array_key_exists('data', $result)) {
            throw new ProtocolErrorException('Message format invalid');
        }

        return $result;
    }
}
