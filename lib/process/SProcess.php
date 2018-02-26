<?php
declare(strict_types=1);

namespace ServerApp\lib\process;

use Swoole\Process;

/**
 * Class SProcess
 * @package txz\common\third_lib\model\process
 */
class SProcess extends Process
{
    /**
     * @param mixed $task
     * @param bool $redirect_pip_output
     * @param bool $createPip
     * @internal param mixed $callback 子进程的回调函数
     * @internal param bool $redirect_stdin_stdout 是否重定向标准输入输出
     * @internal param bool $create_pipe 是否创建管道
     */
    public function __construct($task, $redirect_pip_output, $createPip){
        parent::__construct([$task, 'run'], $redirect_pip_output, $createPip);
    }

    private $using = 0;

    /**
     * @param $string
     */
    public function work($string = ''): void
    {
        $this->using = 1;
        $this->write($string);
    }

    public function idel()
    {
        $this->using = 0;
    }

    /**
     * @return bool
     */
    public function isUsing(){
        return !!$this->using;
    }


}