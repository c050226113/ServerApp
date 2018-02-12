<?php
declare(strict_types=1);

namespace ServerApp\lib\core;

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
     * @param \Swoole\Server $server
     * @throws \Exception
     */
    static public function decodeMsg(string $data, int $fd, \Swoole\Server $server){
        try{
            $arr = json_decode($data, true);
            $type = (int)$arr[0];
            $host = $arr[1];
            self::$hosts[$fd] = $host;
            if($type === ServerType::CENTER){
                tracker(I, 'center add');
                self::$types[$fd] = ServerType::CENTER;
                self::$servers[ServerType::CENTER][$fd] = self::$hosts[$fd];
                foreach (self::getServers() as $fd => $host){
                    $server->send($fd, json_encode([ServerType::CENTER, self::getCenters()]) . "\n");
                }
            }else if($type === ServerType::SERVER){
                tracker(I, 'server add');
                tracker(I, 'pull all centers and beanstalks');
                self::$types[$fd] = ServerType::SERVER;
                self::$servers[ServerType::SERVER][$fd] = self::$hosts[$fd];
                $server->send($fd, json_encode([ServerType::CENTER, self::getCenters()]) . "\n");
                $server->send($fd, json_encode([ServerType::BEANSTALK, self::$servers[ServerType::BEANSTALK]]) . "\n");
            }else if($type === ServerType::BEANSTALK){
                tracker(I, 'beanstalk add');
                self::$types[$fd] = ServerType::BEANSTALK;
                self::$servers[ServerType::BEANSTALK][$fd] = self::$hosts[$fd];
                foreach (self::$servers[ServerType::SERVER] as $fd => $host){
                    $server->send($fd, json_encode([ServerType::BEANSTALK, self::$servers[ServerType::BEANSTALK]]) . "\n");
                }
            }else{
                throw new \Exception();
            }
        }catch (\Throwable $e){
            throw new \Exception();
        }
    }

    /**
     * @return array
     */
    static function getServers(): array
    {
        return self::$servers[ServerType::SERVER];
    }

    /**
     * @return array
     */
    static function getCenters(): array
    {
        return self::$servers[ServerType::CENTER];
    }

    /**
     * @param $fd
     * @param $server
     */
    static public function fillHost($fd, $server){
        var_dump($server);
        exit();

    }

    /**
     * @param int $fd
     * @param \Swoole\Server $server
     */
    static function remove(int $fd, \Swoole\Server $server){
        unset(self::$hosts[$fd]);
        unset(self::$servers[self::$types[$fd]][$fd]);
        $server->send($fd, json_encode([ServerType::CENTER, self::$servers[ServerType::CENTER]]) . "\n");
        $server->send($fd, json_encode([ServerType::BEANSTALK, self::$servers[ServerType::BEANSTALK]]) . "\n");
    }
}