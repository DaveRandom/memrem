<?php

use MemRem\Cache\SocketStoreServer,
    MemRem\Cache\ArrayStore,
    MemRem\Sockets\PeerManager,
    MemRem\Sockets\TCP\Server as TCPServer;

require __DIR__ . '/../autoload.php';
require __DIR__ . '/config.php';

$cache = new SocketStoreServer(
    new ArrayStore,
    new PeerManager,
    [
        new TCPServer($socketAddress, $socketPort)
    ]
);

$cache->run();
