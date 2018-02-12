<?php
declare(strict_types=1);

namespace ServerApp\lib\core;

use Swoole\Redis;

/**
 * Class RedisManager
 * @package server\lib
 */
class RedisManager
{
    private $client;

    /**
     * RedisManager constructor.
     * @param $host
     */
    public function __construct($host)
    {
        $this->client = new Redis();
    }

    /**
     * @param $key
     * @return string
     */
    public function getKey($key){
        return '';
    }
}