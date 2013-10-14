<?php

use MemRem\Cache\SocketStoreClient,
    MemRem\Sockets\TLS\ClientPeer as TLSClientPeer;

require __DIR__ . '/../autoload.php';
require __DIR__ . '/config.php';

$cache = new SocketStoreClient(new TLSClientPeer($socketAddress, $socketPort));
$cache->open();

$key = 'foo';
$value = 'bar';

if (!isset($cache['foo'])) {
    echo "Setting key '$key' to '$value'\n";
    $cache[$key] = $value;
} else {
    echo "Key '$key' is '{$cache[$key]}'\n";
}
