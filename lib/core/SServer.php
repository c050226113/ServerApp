<?php
declare(strict_types=1);

namespace ServerApp\lib\core;

use Swoole\Server;

/**
 * Class SServer
 * @package server\lib
 */
abstract class SServer
{
    /**
     * @var \Swoole\Server
     */
    public $server;
    protected $ip;
    protected $port;
    private $master_name;
    private $manager_name;
    private $worker_name;
    private $setting;
    protected $sock;

    /**
     * @return string
     */
    abstract protected function getMaster(): string;

    /**
     * @return string
     */
    abstract protected function getManager(): string;

    /**
     * @return string
     */
    abstract protected function getWorker(): string;

    /**
     * @return int
     */
    abstract protected function getPort(): int;

    /**
     * @return string
     */
    abstract protected function getBind(): string;

    /**
     * @return string
     */
    public function getHost():string
    {
        return "$this->ip:$this->port";
    }

    /**
     * @return array
     */
    abstract protected function getSetting(): array;

    /**
     * SServer constructor.
     */
    public function __construct()
    {
        $this->ip = $this->matchIp();
        $this->master_name = $this->getMaster();
        $this->manager_name = $this->getManager();
        $this->worker_name = $this->getWorker();
        $this->port = $this->getPort();
        $this->setting = $this->getSetting();
        $this->server = $this->createServer();

        if(!$this->port){
            die('no port');
        }

        $this->setting = array_merge([
            'worker_num' => 1,
            'max_request' => 20000,
            'heartbeat_check_interval' => 300,
            'heartbeat_idle_time' => 600,
            'open_cpu_affinity' => true,
            'open_tcp_nodelay' => true,
        ], $this->setting ?? []);
        $this->server->set($this->setting);
        $this->setCallBack();
    }

    abstract protected function createServer();

    final public function run(){
        tracker('process', "server 已经监听".$this->getPort()."端口 :)");
        $this->server->start();
    }

    public function setCallBack(){
        $this->server->on('Start', function (){
            swoole_set_process_name($this->master_name);
            $this->Start();
        });
        $this->server->on('ManagerStart', function (){
            swoole_set_process_name($this->manager_name);
            $this->ManagerStart();
        });
        $this->server->on('WorkerStart', function ($server, $worker_id){
            ServerStation::$wId = $worker_id;
            ServerStation::$ip = $this->ip;
            ServerStation::$port = $this->port;
            swoole_set_process_name($this->worker_name);
            date_default_timezone_set('Asia/Shanghai');
            $this->WorkerStart($server, $worker_id);
        });
        $this->server->on('Close', function ($server, $fd){
            $this->Close($server, $fd);
        });
        $this->server->on('WorkerStop', function ($server, $worker_id) {
            $this->WorkerStop($server, $worker_id);
            unset($server);
            tracker('process', "worker{$worker_id} -- stop\n");
        });
    }

    abstract protected function Start(): void;
    abstract protected function ManagerStart(): void;

    /**
     * @param $server
     * @param $worker_id
     */
    abstract protected function WorkerStart(Server $server, $worker_id): void;

    /**
     * @param $server
     * @param $worker_id
     */
    abstract protected function WorkerStop(Server $server, $worker_id): void;

    /**
     * @param $server
     * @param $fd
     */
    abstract protected function Close(Server $server, $fd): void;

//    final private function reStart(){
//        $cmd = "netstat -antp | grep ".$this->master_name." | grep ".$this->port." | awk '{print $7}'";
////        echo $cmd;
//        exec($cmd, $res);
//        if ($res){
//            $pidStr = shell_exec("echo $(pidof ".$this->worker_name.")");
//            if($pidStr){
//                $pidArr = explode(' ',$pidStr);
//                foreach ($pidArr as $pid){`
//                    shell_exec("kill -2 {$pid}");
//                    usleep(90000);
//                }
//            }
//            die("服务器重启在端口：".$this->port." :)\n");
//        }
//    }
//
    /**
     * @return string
     */
    final private function matchIp(){
//        $res = swoole_get_local_ip();
//        $res = shell_exec('curl ifconfig.me/all.json');
//        var_dump($res);
//        if(isset($res["eth0"])){
//            return $res["eth0"];
//        }
        $path = '/home/host';
        if(!is_file($path)){
            $res = shell_exec('curl ifconfig.me/all.json');
            $arr = json_decode($res, true);
            $val = trim($arr['ip_addr']);
            file_put_contents($path, $val);
        }else{
            $val = file_get_contents($path);
        }

        return $val;
    }
}