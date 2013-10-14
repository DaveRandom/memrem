<?php

use MemRem\Cache\SocketStoreClient,
    MemRem\Sockets\TCP\ClientPeer as TCPClientPeer,
    MemRem\Sockets\TLS\ClientPeer as TLSClientPeer;

require __DIR__ . '/../autoload.php';
require __DIR__ . '/config.php';

$key = 'foo';
$value = 'bar';

$cache1 = new SocketStoreClient(new TCPClientPeer($socketAddress, $tcpSocketPort));
$cache1->open();

if (!isset($cache1[$key])) {
    echo "Setting key '$key' to '$value'\n";
    $cache1[$key] = $value;
} else {
    echo "Key '$key' is '{$cache1[$key]}'\n";
}

$cache2 = new SocketStoreClient(new TLSClientPeer($socketAddress, $tlsSocketPort));
$cache2->open();

if (!isset($cache2[$key])) {
    echo "Setting key '$key' to '$value'\n";
    $cache2[$key] = $value;
} else {
    echo "Key '$key' is '{$cache2[$key]}'\n";
}
