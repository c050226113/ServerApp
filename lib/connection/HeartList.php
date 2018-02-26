<?php
declare(strict_types=1);

namespace ServerApp\lib\connection;

/**
 * Class Connection
 * @package ServerApp\lib\core
 */
class HeartList
{
    protected $heartList = [];
    protected $leaveList = [];
    protected $interval = 0;

    /**
     * HeartList constructor.
     * @param int $interval
     */
    public function __construct(int $interval){
        $this->interval = $interval;
    }

    /**
     * @return array
     */
    public function getFilterConnections(): array
    {
        $res = [];
        $nextTime = time() - $this->interval;
        foreach ($this->heartList as $time => $imeis){
            if($time < $nextTime){
                foreach ($imeis as $imei){
                    if(!in_array($imei, $this->leaveList)){
                        $res[$time][] = $imei;
                        unset($this->leaveList[$imei]);
                    }
                }
            }else{
                break;
            }
        }
        return $res;
    }

    /**
     * @param array $items
     */
    public function setConnectionsPushOk(array $items){
        foreach ($items as $time => $imeis){
            unset($this->heartList[$time]);
            $this->heartList[$time + $this->interval] = $imeis;
        }

    }

    /**
     * @param $imei
     */
    public function addConnection($imei){
        $index = array_search($imei, $this->leaveList);
        if($index !== false){
            unset($this->leaveList[$index]);
        }
        $this->heartList[time() + $this->interval][] = $imei;
    }

    /**
     * @param $imei
     */
    public function delConnection($imei){
        $this->leaveList[] = $imei;
    }
}