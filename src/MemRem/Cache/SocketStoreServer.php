<?php
/**
 * Back-end cache implementation for socket-based storage
 */

namespace MemRem\Cache;

use MemRem\Sockets\Peer,
    MemRem\Sockets\Server,
    MemRem\Sockets\PeerManager;

class SocketStoreServer extends SocketStoreEndpoint
{
    /**
     * @var KeyValueStore Underlying data store
     */
    private $dataStore;

    /**
     * @var Server[] Server listening sockets
     */
    private $serverSockets;

    /**
     * @var PeerManager
     */
    private $peerManager;

    /**
     * @var callable[] Map of request code => handler function
     */
    private $requestHandlers = [];

    /**
     * Register the default request handling routines
     */
    private function registerBuiltInRequestHandlers()
    {
        $this->registerRequestHandler(self::REQUEST_EXISTS, function($key) {
            if (!isset($key)) {
                return [self::RESPONSE_BADREQUEST, "Request data invalid"];
            } else {
                return [self::RESPONSE_OK, $this->dataStore->offsetExists($key)];
            }
        });

        $this->registerRequestHandler(self::REQUEST_GET, function($key) {
            if (!isset($key)) {
                return [self::RESPONSE_BADREQUEST, "Request data invalid"];
            } else if (!$this->dataStore->offsetExists($key)) {
                return [self::RESPONSE_NOTFOUND, "Key '{$key}' not found in data store"];
            } else {
                return [self::RESPONSE_OK, $this->dataStore->offsetGet($key)];
            }
        });

        $this->registerRequestHandler(self::REQUEST_SET, function($key, $value) {
            if (!isset($key)) {
                return [self::RESPONSE_BADREQUEST, "Request data invalid"];
            } else {
                $this->dataStore->offsetSet($key, $value);
                return [self::RESPONSE_OK, null];
            }
        });

        $this->registerRequestHandler(self::REQUEST_UNSET, function($key) {
            if (!isset($key)) {
                return [self::RESPONSE_BADREQUEST, "Request data invalid"];
            } else {
                $this->dataStore->offsetUnset($key);
                return [self::RESPONSE_OK, null];
            }
        });

        $this->registerRequestHandler(self::REQUEST_GETALL, function($key) {
            return [self::RESPONSE_OK, $this->dataStore->toArray()];
        });

        $this->registerRequestHandler(self::REQUEST_GETKEYS, function($key) {
            return [self::RESPONSE_OK, $this->dataStore->getKeys()];
        });

        $this->registerRequestHandler(self::REQUEST_CLEAR, function($key) {
            $this->dataStore->clear();
            return [self::RESPONSE_OK, null];
        });
    }

    /**
     * Initialise the data store, peer manager and request handlers
     */
    private function init()
    {
        $this->dataStore->open();

        foreach ($this->serverSockets as $serverSocket) {
            $serverSocket->setBlocking(false);
            $serverSocket->open();

            $this->peerManager->addServerSocket($serverSocket);
        }

        $this->registerBuiltInRequestHandlers();
    }

    /**
     * Receive a request from the wire, invoke the handler and send the response
     *
     * @param Peer $client The client with a pending request
     */
    private function handleRequest(Peer $client)
    {
        if (null !== $data = $client->readLine()) {
            $request = $this->decodeMessage($data);

            if (isset($this->requestHandlers[$request['code']])) {
                list($responseCode, $responseData) = call_user_func_array(
                    $this->requestHandlers[$request['code']],
                    (array) $request['data']
                );
            } else {
                $responseCode = self::RESPONSE_BADREQUEST;
                $responseData = "Unknown request code";
            }

            $client->write($this->encodeMessage($responseCode, $responseData));
        }
    }

    /**
     * Constructor
     *
     * @param KeyValueStore $dataStore     Underlying data store
     * @param PeerManager   $peerManager
     * @param Server[]      $serverSockets Server listening sockets
     */
    public function __construct(KeyValueStore $dataStore, PeerManager $peerManager, array $serverSockets = [])
    {
        $this->dataStore = $dataStore;
        $this->serverSockets = $serverSockets;
        $this->peerManager = $peerManager;
    }

    /**
     * Register a handler function for a request code
     *
     * The handler function should have the signature
     *   array function([mixed $arg...])
     *
     * The return array should have the response code at index 0 and the response
     * data at index 1
     *
     * @param int      $requestCode
     * @param callable $handler
     */
    public function registerRequestHandler($requestCode, callable $handler)
    {
        $requestCode = (int) $requestCode;
        if ($requestCode & 0x8000) {
            throw new \LogicException('Supplied code is a request identifier');
        }

        $this->requestHandlers[$requestCode & 0x7FFF] = $handler;
    }

    /**
     * Main server routine, never returns
     */
    public function run()
    {
        $this->init();

        while (true) {
            $clients = $this->peerManager->getPendingReads();

            foreach ($clients as $client) {
                try {
                    $this->handleRequest($client);
                } catch (Exception $e) {
                    $this->peerManager->removePeer($client);
                }
            }
        }
    }
}
