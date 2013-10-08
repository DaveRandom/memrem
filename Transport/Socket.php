<?php
class Transport_Socket implements Transport_Generic
{
    const SOCKET_TERMINATE_BYTE         = "\0";
    const SOCKET_WAIT_TIMEOUT           = 120;
    
    private $tcpSocket      = null;
    private $lastActionTime = null;
    
    protected function __construct($tcpSocket)
    {
        $this->tcpSocket        = $tcpSocket;
        $this->lastActionTime   = time();
    }
    
    public function __destruct() 
    {
        $this->destroyChannel();
    }
    
    public function __toString()
    {
        return (string)$this->tcpSocket;
    }
    
    public static function createServerFromTCP($ipAddress, $tcpPort, $setNonBlock=false)
    {
        $socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        if(!@socket_bind($socket, $ipAddress, $tcpPort))
        {
            throw new Exception(sprintf("Failed to setup transport socket: error %s", socket_last_error($socket)));
        }
        socket_listen($socket);
        if($setNonBlock)
        {
            socket_set_nonblock($socket);
        }
        return new self($socket);
    }
    
    public static function createClientFromTCP($ipAddress, $tcpPort)
    {
        $socket    = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
        if(!@socket_connect($socket, $ipAddress, $tcpPort))
        {
            throw new Exception(sprintf("Failed to setup transport socket: error %s", socket_last_error($socket)));
        }
        return new self($socket);
    }
    
    public static function createFromSocket($tcpSocket)
    {
        if(!is_resource($tcpSocket))
        {
            throw new InvalidArgumentException("Failed to setup transport socket: direct initializer must be valid resource");
        }
        return new self($tcpSocket);
    }
    
    public function createChannel()
    {
        if(($socket = @socket_accept($this->tcpSocket)) !== false)
        {
            socket_set_nonblock($socket);
            return new self($socket);
        }
        return null;//?
    }
    
    public function destroyChannel()
    {
        socket_close($this->tcpSocket);
    }
    
    public function sendData($data, $terminator=null)
    {
        if(isset($terminator))
        {
            $data   .= $terminator?$terminator:self::SOCKET_TERMINATE_BYTE;
        }
        return socket_write($this->tcpSocket, $data);
    }
    
    public function getData($terminator=null)
    {
        if(isset($terminator))
        {
            $terminator = $terminator?$terminator:self::SOCKET_TERMINATE_BYTE;
        }
        $result = false;
        while(false !== ($buffer=@socket_read($this->tcpSocket, 1)))
        {
            if(isset($terminator) && $buffer===$terminator)
            {
                break;
            }
            $result .= $buffer;
        }
        return $result===false?null:$result;
    }
    
    public function isTimedOut()
    {
        return time()-$this->lastActionTime>self::SOCKET_WAIT_TIMEOUT;
    }
    
    public function setLastAction()
    {
        $this->lastActionTime = time();
    }
}