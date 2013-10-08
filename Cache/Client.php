<?php
class Cache_Client
{
    const PARSER_RAW_SECTION_COMMAND    = 'raw_command';
    const PARSER_RAW_SECTION_DATA       = 'raw_data';
    
    const COMMAND_GET                   = 'get';
    const COMMAND_SET                   = 'set';
    const COMMAND_SET_KEY               = 'key';
    const COMMAND_SET_DATA              = 'data';
    const COMMAND_EXIT                  = 'exit';
    const COMMAND_PING                  = 'ping';
    
    protected   $errorsList             = [];
    private     $transport              = null;
    
    
    public function __construct($ipAddress, $tcpPort)
    {
        $this->transport = Transport_Socket::createClientFromTCP($ipAddress, $tcpPort);
    }
    
    public function __destruct() 
    {
        $this->transport->sendData(serialize(
                [
                    self::PARSER_RAW_SECTION_COMMAND=> self::COMMAND_EXIT,
                    self::PARSER_RAW_SECTION_DATA   => []
                ]));
    }
    
    public function getLastError()
    {
        return $this->errorsList[count($this->errorsList)-1];
    }
    
    public function setError($code, $error)
    {
        $this->errorsList[]  = [$code => $error];
    }
    
    public function getKey($key)
    {
        $this->transport->sendData(serialize(
                [
                    self::PARSER_RAW_SECTION_COMMAND=> self::COMMAND_GET,
                    self::PARSER_RAW_SECTION_DATA   => $key
                ]
        ));
        if($data = @unserialize($this->transport->getData(false)))
        {
            return $data;
        }
        return null;
    }
    
    public function setKey($key, $value)
    {      
        $this->transport->sendData(serialize(
                [
                    self::PARSER_RAW_SECTION_COMMAND=> self::COMMAND_SET,
                    self::PARSER_RAW_SECTION_DATA   => serialize([
                        self::COMMAND_SET_KEY   => $key,
                        self::COMMAND_SET_DATA  => serialize($value)
                    ])
                ]
        ));
        return (bool)$this->transport->getData(false);
    }
    
    public function unsetKey($key)
    {
        $this->transport->sendData(serialize(
                [
                    self::PARSER_RAW_SECTION_COMMAND=> self::COMMAND_SET,
                    self::PARSER_RAW_SECTION_DATA   => serialize([
                        self::COMMAND_SET_KEY   => $key
                    ])
                ]
        ));
        return (bool)$this->transport->getData(false);
    }
}
