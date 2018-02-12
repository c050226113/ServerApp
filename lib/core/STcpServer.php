<?php
declare(strict_types=1);

namespace ServerApp\lib\core;

use ServerApp\lib\core\interface_\IServer;
use Swoole\Server;

/**
 * Class SNormalServer
 * @package server\lib
 */
abstract class STcpServer extends SServer implements IServer
{
    /**
     * @return \Swoole\Server
     */
    final public function createServer()
    {
        return new Server($this->getHost(), $this->getPort(), SWOOLE_PROCESS, SWOOLE_TCP);
    }

    public function setCallBack(){
        parent::setCallBack();
        $this->server->on('Connect', function($server, $fd) {
            $this->Connect($server, $fd);
        });
        $this->server->on('Receive', function(Server $server, $fd, $from_id, $data) {
            $this->Receive($server, $fd, $from_id, $data);
        });
    }

    /**
     * @param $server
     * @param $fd
     */
    abstract protected function Connect(Server $server, $fd): void;

    /**
     * @param \Swoole\Server $server
     * @param $fd
     * @param $from_id
     * @param $data
     */
    abstract protected function Receive(Server $server, $fd, $from_id, $data): void;
}