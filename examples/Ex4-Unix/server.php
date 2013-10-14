<?php

use MemRem\Cache\SocketStoreServer,
    MemRem\Cache\ArrayStore,
    MemRem\Sockets\PeerManager,
    MemRem\Sockets\Unix\Server as UnixServer;

require __DIR__ . '/../autoload.php';
require __DIR__ . '/config.php';

$cache = new SocketStoreServer(
    new ArrayStore,
    new PeerManager,
    [
        new UnixServer($socketPath)
    ]
);

$cache->run();
