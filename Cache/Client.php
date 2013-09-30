<?php
class Cache_Client
{
    const PARSER_RAW_SECTION_COMMAND    = 'raw_command';
    const PARSER_RAW_SECTION_DATA       = 'raw_data';
    
    const COMMAND_GET                   = 'get';
    const COMMAND_SET                   = 'set';
    const COMMAND_SET_KEY               = 'key';
    const COMMAND_SET_DATA              = 'data';
    
    const SOCKET_TERMINATE_BYTE         = "\0";
    
    const ERROR_CLIENT_SETUP_MESSAGE    = "Could not setup client socket";
    const ERROR_CLIENT_SETUP_CODE       = 1;
    
    protected $_sAddress                = '127.0.0.1';
    protected $_sPort                   = '23540';
    protected $__rSocket                = null;
    protected $_rgErrors                = array();
    
    protected static $_rInstance        = null;
    
    private function __construct()
    {
        
    }
    
    public function setServerAddress($sAddress, $sPort)
    {
        $this->_sAddress    = $sAddress;
        $this->_sPort       = $sPort;
        $this->__setup_socket();
        return $this;
    }
    
    public static function getInstance()
    {
        if(isset(self::$_rInstance))
        {
            return self::$_rInstance;
        }
        self::$_rInstance = new Cache_Client;
        self::$_rInstance->__setup_socket();
        return self::$_rInstance;
    }
    
    public function getLastError()
    {
        return $this->_rgErrors[count($this->_rgErrors)-1];
    }
    
    public function setError($iCode, $sMessage)
    {
        $this->_rgErrors[]  = array($iCode=>$sMessage);
    }
    
    public function getKey($sKey)
    {
        $this->__send_socket_data($this->__rSocket, serialize(
                array(
                    self::PARSER_RAW_SECTION_COMMAND=> self::COMMAND_GET,
                    self::PARSER_RAW_SECTION_DATA   => $sKey
                )
        ));
        if($sData = @unserialize($this->__get_socket_data($this->__rSocket)))
        {
            return $sData;
        }
        return null;
    }
    
    public function setKey($sKey, $mValue)
    {
        $this->__send_socket_data($this->__rSocket, serialize(
                array(
                    self::PARSER_RAW_SECTION_COMMAND=> self::COMMAND_SET,
                    self::PARSER_RAW_SECTION_DATA   => serialize(array(
                        self::COMMAND_SET_KEY   => $sKey,
                        self::COMMAND_SET_DATA  => serialize($mValue)
                    ))
                )
        ));
        return (bool)$this->__get_socket_data($this->__rSocket);
    }
    
    public function unsetKey($sKey)
    {
        $this->__send_socket_data($this->__rSocket, serialize(
                array(
                    self::PARSER_RAW_SECTION_COMMAND=> self::COMMAND_SET,
                    self::PARSER_RAW_SECTION_DATA   => serialize(array(
                        self::COMMAND_SET_KEY   => $sKey
                    ))
                )
        ));
        return (bool)$this->__get_socket_data($this->__rSocket);
    }
    
    private function __setup_socket()
    {
        $this->__rSocket    = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        if(!@socket_connect($this->__rSocket, $this->_sAddress, $this->_sPort))
        {
            unset($this->__rSocket);
            $this->setError(self::ERROR_CLIENT_SETUP_CODE, self::ERROR_CLIENT_SETUP_MESSAGE);
            return false;
        }
        return true;
    }
    
    private function __send_socket_data($rSocket, $sData)
    {
        if(!$rSocket)
        {
            return null;
        }
        return socket_write($rSocket, $sData);
    }
    
    private function __get_socket_data($rSocket)
    {
        if(!$rSocket)
        {
            return null;
        }
        $sResult = false;
        while(($sBuffer = @socket_read($rSocket, 1))!=self::SOCKET_TERMINATE_BYTE)
        {
            $sResult.=$sBuffer;
        }
        return $sResult===false?null:$sResult;
    }
}
