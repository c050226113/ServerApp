<?php
declare(strict_types=1);

namespace ServerApp\lib\core;

use Exception;
use ServerApp\lib\core\interface_\IServer;
use WebApp\lib\app\SwooleApp;
use Swoole\Http\Server;

/**
 * Class SHttpServer
 * @package server\lib
 */
abstract class SHttpServer extends SServer implements IServer
{
    private $module = '';

    /**
     * SServer constructor.
     * @param string $module
     */
    public function __construct(string $module = '')
    {
        $this->module = $module;
        parent::__construct();
    }

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
        $this->server->on('Request', function($request, $respons) {
            $app = new SwooleApp($request, $respons);
            try {
                $app->run($this->module);
            } catch (Exception $e) {
                if ($e->getMessage() !== '0') //'0' : 正常 退出
                    throw $e;
            }
        });
    }

    /**
     * @return bool
     */
    abstract protected function isSsl(): bool;
}