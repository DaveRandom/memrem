<?php

use MemRem\Cache\SocketStoreServer,
    MemRem\Cache\ArrayStore,
    MemRem\Sockets\PeerManager,
    MemRem\Sockets\TLS\Server as TLSServer;

require __DIR__ . '/../autoload.php';
require __DIR__ . '/config.php';

$cache = new SocketStoreServer(
    new ArrayStore,
    new PeerManager,
    [
        new TLSServer($socketAddress, $socketPort, $certPath)
    ]
);

$cache->run();
