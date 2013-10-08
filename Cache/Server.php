<?php
class Cache_Server
{
    const PARSER_RAW_SECTION_COMMAND    = 'raw_command';
    const PARSER_RAW_SECTION_DATA       = 'raw_data';
    
    const HANDLER_COMMAND_COMMON_PREFIX = 'hook_';
    const HANDLER_COMMAND_SET_KEY       = 'key';
    const HANDLER_COMMAND_SET_DATA      = 'data';
    const HANDLER_COMMAND_PING_DATA     = 'PONG';
    const HANDLER_COMMAND_SET_SUCCESS   = 1;
    const HANDLER_COMMAND_SET_FAILURE   = 0;
    
    protected $clientsList              = [];
    protected $errorsList               = [];
    private   $cacheStorage             = [];
    private   $transport                = null;
    
    public function __construct($ipAddress=null, $tcpPort=null) 
    {
        //todo: configurable transport type. Now only sockets available, hardcoded:
        $this->transport = Transport_Socket::createServerFromTCP($ipAddress, $tcpPort, true);
    }
    
    public function runServer()
    {
        while(true)
        {
            $this->checkNewClients();
            $this->listenClients();
            $this->releaseClients();
        }
    }
    //errors functions:
    public function getLastError()
    {
        return $this->errorsList[count($this->errorsList)-1];
    }
    
    public function setError($code, $error)
    {
        $this->errorsList[]  = [$code => $error];
    }
    //cache functions:
    public function getKey($client, $key)
    {
        $key       = $this->generateKeyHash($key);
        $client    = $this->generateClientHash($client);
        return array_key_exists($client, $this->cacheStorage) && 
               array_key_exists($key, $this->cacheStorage[$client])?$this->cacheStorage[$client][$key]:null;
    }

    public function setKey($client, $key, $value)
    {
        if(!isset($value))
        {
            unset($this->cacheStorage[$this->generateClientHash($client)]
                                     [$this->generateKeyHash($key)]);
        }
        else
        {
            $this->cacheStorage[$this->generateClientHash($client)]
                               [$this->generateKeyHash($key)] = $value;
        }
    }
    //clients functions:
    protected function checkNewClients()
    {
        if($client = $this->transport->createChannel())
        {
            $this->clientsList[] = $client;            
        }
    }
    
    protected function listenClients($bCheckAlive=false)
    {
        foreach($this->clientsList as $index=>$client)
        {
            if($data = $client->getData()) 
            {
                if($command = $this->parseRawCommand($data))
                {          
                    $client->setLastAction();
                    $this->executeCommand(
                            $index, 
                            $command[self::PARSER_RAW_SECTION_COMMAND], 
                            $command[self::PARSER_RAW_SECTION_DATA]
                    );                
                }
            }
        }
    }
    
    protected function releaseClients()
    {
        foreach($this->clientsList as $client)
        {
            if($client->isTimedOut())
            {
                $client->destroyChannel();
            }
        }
    }
    
    protected function executeCommand($client, $command, $args)
    {
        if(method_exists($this, $command=self::HANDLER_COMMAND_COMMON_PREFIX.$command))
        {
            $this->$command($client, $args);
        }
    }
    
    protected function hook_exit($clientNum, $args)
    {
        $this->clientsList[$clientNum]->destroyChannel();
        unset($this->clientsList[$clientNum]);
        unset($this->cacheStorage[$this->generateClientHash($clientNum)]);
    }
    
    protected function hook_ping($clientNum, $args)
    {
        $this->clientsList[$clientNum]->setLastAction();
        $this->clientsList[$clientNum]->sendData(self::HANDLER_COMMAND_PING_DATA, false);
    }
    
    protected function hook_get($clientNum, $args)
    {
        $this->clientsList[$clientNum]->sendData($this->getKey($clientNum, (string)$args), false);
    }
    
    protected function hook_set($clientNum, $args)
    {
        if($args = @unserialize($args))
        {
            if(array_key_exists(self::HANDLER_COMMAND_SET_KEY, $args))
            {
                $key    = $args[self::HANDLER_COMMAND_SET_KEY];
                $this->setKey($clientNum, $key, null);
                if(array_key_exists(self::HANDLER_COMMAND_SET_DATA, $args))
                {
                    $this->setKey($clientNum, $key, $args[self::HANDLER_COMMAND_SET_DATA]);                    
                }
                $this->clientsList[$clientNum]->sendData(self::HANDLER_COMMAND_SET_SUCCESS, false);
                return true;
            }
        }
        $this->clientsList[$clientNum]->sendData(self::HANDLER_COMMAND_SET_FAILURE, false);
        return false;
    }
    
    
    private function generateClientHash($client)
    {
        return md5($client);
    }
    
    private function generateKeyHash($key)
    {
        return md5($key);
    }
    
    private function parseRawCommand($command)
    {
        $commandData  = @unserialize($command);
        if(is_array($commandData) && 
           array_key_exists(self::PARSER_RAW_SECTION_COMMAND, $commandData))
        {
            return $commandData;
        }
        return null;//?
    }
}