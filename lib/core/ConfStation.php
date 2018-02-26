<?php
declare(strict_types=1);

namespace ServerApp\lib\core;

use Exception;
use Swoole\Server;
use Tao\src\model\redis\tao\ConfServer;
use Tao\src\model\swoole\ConfTable;

/**
 * Class ConfStation
 * @package server\lib
 */
class ConfStation
{
    /**
     * ConfStation constructor.
     */
    private function __construct()
    {
    }

    static public $types = [];
    static public $servers = [
        ServerType::CENTER=>[],
        ServerType::SERVER=>[],
        ServerType::BEANSTALK=>[]
    ];
    static public $hosts = [];

    /**
     * @param string $data
     * @param int $fd
     * @param Server $server
     * @throws Exception
     */
    static public function decodeMsg(string $data, int $fd, Server $server){
        $arr = json_decode($data, true);
        $type = $arr[0];
        $host = $arr[1];
//        $worker_id = $arr[2] ?? 0;
//        ConfTable::getInstance()->set($fd . '', [
//            ConfTable::TYPE=> $type,
//            ConfTable::HOST=> $host,
//            ConfTable::WORKER_ID=> $worker_id
//        ]);
        ConfServer::getInstance()->hSet($fd.'', $host . "|" . $type);

        if($type === ServerType::SERVER){
            var_dump('server add');
            $all = ConfStation::getAll();
            $servers = self::getServers($all);
            tracker('all_server', $servers);
            foreach ($all as $fd=>$row){
                $server->send($fd, json_encode($servers) . "\n");
            }
        }else{
            $all = ConfStation::getAll();
            $servers = self::getServers($all);
            $server->send($fd, json_encode($servers) . "\n");
        }


//        if($type === ServerType::CENTER){
//            tracker(I, 'center add');
//            $server->send($fd, json_encode([ServerType::BEANSTALK, self::getBeans()]) . "\n");
//            foreach (self::getServers() as $fd => $host){
//                $server->send($fd, json_encode([ServerType::CENTER, self::getCenters()]) . "\n");
//            }
//        }else if($type === ServerType::SERVER){ // server 上线
//            tracker(I, 'server add');
//            $server->send($fd, json_encode([ServerType::CENTER, self::getCenters()]) . "\n");
//            foreach (self::getBeans() as $fd => $host){
//                $server->send($fd, json_encode([ServerType::SERVER, self::getServers()]) . "\n");
//            }
//        }else if($type === ServerType::BEANSTALK){
//            tracker(I, 'beanstalk add');
//            $server->send($fd, json_encode([ServerType::BEANSTALK, self::getServers()]) . "\n");
//            foreach (self::getCenters() as $fd => $host){
//                $server->send($fd, json_encode([ServerType::BEANSTALK, self::getBeans()]) . "\n");
//            }
//        }else{
//            throw new Exception();
//        }
    }

    /**
     * @return array
     */
    public static function getAll(){
        $all = ConfServer::getInstance()->hGetAll();
        $res = [];
        foreach ($all as $index => $val){
            if($index & 1 === 1){
                $res[$all[$index - 1]] = $val;
            }
        }
        return $res;
    }

    /**
     * @param $all
     * @return array
     */
    public static function getServers($all): array
    {
        $res = [];
//        foreach (ConfTable::getInstance() as $fd => $row){
        foreach ($all as $fd => $row){
            $arr = explode('|', $row);
            $type = (int)$arr[1];
            $host = $arr[0];
            if ($type === ServerType::SERVER){
                $res[(int)$fd] = $host;
            }
        }
        return $res;
    }

    /**
     * @return array
     */
    static function getBeans(): array
    {
        $res = [];
        foreach (ConfTable::getInstance() as $fd => $row){
            if ($row[ConfTable::TYPE] === ServerType::BEANSTALK){
                $res[(int)$fd] = $row[ConfTable::HOST];
            }
        }
        return $res;
    }

    /**
     * @return array
     */
    static function getCenters(): array
    {
        $res = [];
        foreach (ConfTable::getInstance() as $fd => $row){
            if ($row[ConfTable::TYPE] === ServerType::CENTER){
                $res[(int)$fd] = $row[ConfTable::HOST];
            }
        }
        return $res;
    }
}