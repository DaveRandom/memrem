<?php

use MemRem\Cache\SocketStoreClient,
    MemRem\Sockets\Unix\ClientPeer as UnixClientPeer;

require __DIR__ . '/../autoload.php';
require __DIR__ . '/config.php';

$cache = new SocketStoreClient(new UnixClientPeer($socketPath));
$cache->open();

$key = 'foo';
$value = 'bar';

if (!isset($cache[$key])) {
    echo "Setting key '$key' to '$value'\n";
    $cache[$key] = $value;
} else {
    echo "Key '$key' is '{$cache[$key]}'\n";
}
