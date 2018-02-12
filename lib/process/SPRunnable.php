<?php
declare(strict_types=1);

namespace ServerApp\lib\process;

use Swoole\Process;

/**
 * Interface SPRunnable
 * @package ServerApp\lib\process
 */
interface SPRunnable
{
    /**
     * @param Process $worker
     * @return mixed
     */
    public function run(Process $worker): void;
}