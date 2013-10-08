<?php
interface Transport_Generic
{
    /**
     * Generic create new channel linked to current one
     */
    public function createChannel();
    /**
     * Generic destroy current transport channel
     */
    public function destroyChannel();
    /**
     * Generic send $data via transport
     * @param mixed $data
     */
    public function sendData($data);
    /**
     * Generic get data through transport
     */
    public function getData();
}