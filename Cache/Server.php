<?php
class Cache_Server
{
    const PARSER_RAW_SECTION_COMMAND    = 'raw_command';
    const PARSER_RAW_SECTION_DATA       = 'raw_data';
    
    const HANDLER_COMMAND_COMMON_PREFIX = '_hook_';
    const HANDLER_COMMAND_SET_KEY       = 'key';
    const HANDLER_COMMAND_SET_DATA      = 'data';
    const HANDLER_COMMAND_SET_SUCCESS   = 1;
    const HANDLER_COMMAND_SET_FAILURE   = 0;
    
    const SOCKET_BLOCK_READ_SIZE        = 64;
    const SOCKET_TERMINATE_BYTE         = "\0";
    
    const ERROR_SERVER_SETUP_MESSAGE    = "Could not setup server socket";
    const ERROR_SERVER_SETUP_CODE       = 2;
    
    protected $_sAddress                = '127.0.0.1';
    protected $_sPort                   = '23540';
    protected $_rgClients               = array();
    protected $_rgErrors                = array();
    private   $__rgCache                = array();
    private   $__rSocket                = null;
    
    public function __construct($sHost=null, $sPort=null) 
    {
        if(isset($sHost)&&isset($sPort))
        {           
            $this->_sAddress= $sHost;
            $this->_sPort   = $sPort;
        }
    }
    
    public function runServer()
    {
        if(!$this->__setup_socket())
        {
            $this->setError(self::ERROR_SERVER_SETUP_CODE, self::ERROR_SERVER_SETUP_MESSAGE);
            return false;
        }
        while(true)
        {
            $this->_check_clients();
            $this->_listen_clients();
        }
    }
    //errors functions:
    public function getLastError()
    {
        return $this->_rgErrors[count($this->_rgErrors)-1];
    }
    
    public function setError($iCode, $sMessage)
    {
        $this->_rgErrors[]  = array($iCode=>$sMessage);
    }
    //cache functions:
    public function getKey($mClient, $mKey)
    {
        $sKey   = $this->__generate_key_hash($mClient, $mKey);
        return array_key_exists($sKey, $this->__rgCache)?$this->__rgCache[$sKey]:null;
    }

    public function setKey($mClient, $mKey, $mValue)
    {
        if(!isset($mValue))
        {
            unset($this->__rgCache[$this->__generate_key_hash($mClient, $mKey)]);
        }
        else
        {
            $this->__rgCache[$this->__generate_key_hash($mClient, $mKey)]=$mValue;
        }
    }
    //clients functions:
    protected function _check_clients()
    {
        if(($rNewc = @socket_accept($this->__rSocket)) !== false)
        {
            socket_set_nonblock($rNewc);
            $this->_rgClients[] = $rNewc;
        }
    }
    
    protected function _listen_clients($bCheckAlive=false)
    {
        foreach($this->_rgClients as $iIndex => $rClient)
        {
            if($sData = $this->__get_socket_data($rClient))
            {
                if($rgCommand = $this->__parse_raw_command($sData))
                {
                    $this->_execute_command($iIndex, $rgCommand[self::PARSER_RAW_SECTION_COMMAND], $rgCommand[self::PARSER_RAW_SECTION_DATA]);
                }
            }
        }
    }
    //command handle functions:
    protected function _execute_command($mClient, $sCommand, $mParameters)
    {
        //need to use prefix, so call will be more safe:
        if(method_exists($this, $sCommand=self::HANDLER_COMMAND_COMMON_PREFIX.$sCommand))
        {
            $this->$sCommand($mClient, $mParameters);
        }
    }
    
    protected function _hook_get($mClient, $mParameters)
    {
        $mClient    = is_resource($mClient)?$mClient:$this->_rgClients[$mClient];
        $this->__send_socket_data($mClient, $this->getKey($mClient, (string)$mParameters));
    }
    
    protected function _hook_set($mClient, $mParameters)
    {
        $mClient    = is_resource($mClient)?$mClient:$this->_rgClients[$mClient];
        if($rgParameters = @unserialize($mParameters))
        {
            if(array_key_exists(self::HANDLER_COMMAND_SET_KEY, $rgParameters))
            {
                $sKey = $rgParameters[self::HANDLER_COMMAND_SET_KEY];
                $this->setKey($mClient, $sKey, null);
                if(array_key_exists(self::HANDLER_COMMAND_SET_DATA, $rgParameters))
                {
                    $this->setKey($mClient, $sKey, $rgParameters[self::HANDLER_COMMAND_SET_DATA]);                    
                }
                $this->__send_socket_data($mClient, self::HANDLER_COMMAND_SET_SUCCESS);
                return true;
            }
        }
        $this->__send_socket_data($mClient, self::HANDLER_COMMAND_SET_FAILURE);
        return false;
    }
    
    private function __generate_key_hash($mClient, $mKey)
    {
        return md5($mClient."\n\n".$mKey);
    }
    //socket functions:
    private function __setup_socket()
    {
        $this->__rSocket    = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        if(!@socket_bind($this->__rSocket,$this->_sAddress,$this->_sPort))
        {
            unset($this->__rSocket);
            return false;
        }
        socket_listen($this->__rSocket);
        socket_set_nonblock($this->__rSocket);
        return true;
    }
    
    private function __send_socket_data($rSocket, $sData)
    {
        return socket_write($rSocket, $sData.self::SOCKET_TERMINATE_BYTE);
    }
    
    private function __get_socket_data($rSocket)
    {
        $sResult = false;
        while($sBuffer = @socket_read($rSocket, self::SOCKET_BLOCK_READ_SIZE))
        {
            $sResult.=$sBuffer;
        }
        return $sResult===false?null:$sResult;
    }
    
    private function __parse_raw_command($sData)
    {
        $mData  = @unserialize($sData);
        if(is_array($mData) && array_key_exists(self::PARSER_RAW_SECTION_COMMAND, $mData))
        {
            return $mData;
        }
        return null;
    }
}