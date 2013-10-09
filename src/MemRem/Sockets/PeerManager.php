<?php
/**
 * Manages one or more server sockets and the associated client pool
 */

namespace MemRem\Sockets;

class PeerManager
{
    /**
     * @var Server[] Server sockets mapped by ID
     */
    private $serverSockets = [];

    /**
     * @var resource[] Server socket resources mapped by ID
     */
    private $serverResources = [];

    /**
     * @var Peer[] Peer sockets mapped by ID
     */
    private $peerSockets = [];

    /**
     * @var resource[] Peer socket resources mapped by ID
     */
    private $peerResources = [];

    /**
     * Accept a pending connection from a server socket and add it to the pool
     *
     * @param Server $serverSocket Server socket with a pending connection
     */
    private function acceptPeer(Server $serverSocket)
    {
        $peerSocket = $serverSocket->accept();

        $id = $peerSocket->getID();
        $this->peerSockets[$id] = $peerSocket;
        $this->peerResources[$id] = $peerSocket->getStream();
    }

    /**
     * Remove a peer socket from the pool
     *
     * @param Peer $peerSocket Peer socket to remove
     */
    public function removePeer(Peer $peerSocket)
    {
        $id = $peerSocket->getID();
        unset($this->peerSockets[$id], $this->peerResources[$id]);

        $peerSocket->close();
    }

    /**
     * Add a server socket to manage
     *
     * @param Server $serverSocket Server socket to manage
     */
    public function addServerSocket(Server $serverSocket)
    {
        $id = $serverSocket->getID();
        $this->serverSockets[$id] = $serverSocket;
        $this->serverResources[$id] = $serverSocket->getStream();
    }

    /**
     * Remove a server socket from management
     *
     * @param Server $serverSocket Server socket to remove
     */
    public function removeServerSocket(Server $serverSocket)
    {
        $id = $serverSocket->getID();
        unset($this->serverSockets[$id], $this->serverResources[$id]);

        $serverSocket->close();
    }

    /**
     * Fetch a flat list of peer sockets in the pool with pending data
     *
     * This blocks until at least one socket has pending data. New peer requests
     * will be automatically handled.
     *
     * @return Peer[]
     */
    public function getPendingReads()
    {
        $result = [];

        while (!$result) {
            $r = array_merge($this->serverResources, $this->peerResources);
            $w = $e = $t = null;

            if (false === stream_select($r, $w, $e, $t)) {
                throw new \RuntimeException('select() operation failed');
            }

            foreach ($r as $resource) {
                $id = (int) $resource;

                if (isset($this->serverSockets[$id])) {
                    $this->acceptPeer($this->serverSockets[$id]);
                } else {
                    $peer = $this->peerSockets[(int) $resource];

                    if ($peer->isEOF()) {
                        $this->removePeer($peer);
                    } else {
                        $result[] = $peer;
                    }
                }
            }
        }

        return $result;
    }
}
