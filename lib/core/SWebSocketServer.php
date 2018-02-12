<?php
declare(strict_types=1);

namespace ServerApp\lib\core;

use Exception;
use ServerApp\lib\core\interface_\IServer;
use Swoole\Websocket\Server;

/**
 * Class SWebSocketServer
 * @package server\lib
 */
abstract class SWebSocketServer extends SServer implements IServer
{
    /**
     * @return Server
     */
    final public function createServer()
    {
        $type = SWOOLE_SOCK_TCP;
        if($this->isSsl()){
            $type = $type | SWOOLE_SSL;
        }
        return new Server($this->getHost(), $this->getPort(), SWOOLE_PROCESS, $type);
    }

    public function setCallBack(){
        parent::setCallBack();
        $this->server->on('Open', function($server, $request) {
            try{
                $path = substr($request->server['path_info'], 1, strlen($request->server['path_info']) - 1);
                $this->Open($server, $request->fd, $path);
            }catch (Exception $e){
                if($e->getMessage())
                    var_dump($e->getMessage());
            }
        });
        $this->server->on('Message', function($server, $frame) {
            $this->Message($server, $frame->fd, $frame->data);
        });
    }

    /**
     * @param $fd
     * @param $message
     */
    final public function push($fd, $message)
    {
        $this->server->push($fd, $message);
    }

    /**
     * @param $server
     * @param $fd
     * @param $path
     */
    abstract protected function Open($server, $fd, $path): void;

    /**
     * @param $server
     * @param $fd
     * @param $data
     */
    abstract protected function Message($server, $fd, $data): void;

    /**
     * @return bool
     */
    abstract protected function isSsl(): bool;
}