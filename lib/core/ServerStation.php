<?php
declare(strict_types=1);

namespace ServerApp\lib\core;

/**
 * Class ServerStation
 * @package server\lib
 */
class ServerStation
{
    /**
     * ServerStation constructor.
     */
    private function __construct()
    {
    }

    static public $ip;
    static public $port;
    static public $wId;

    static public $servers = [];
    static public $clients = [];
    static public $connections = [];
}