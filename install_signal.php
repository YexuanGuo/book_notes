<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2018/5/25
 * Time: 下午12:23
 */

class install_signal
{

    protected function install_master_signal()
    {
        pcntl_signal(SIGINT, array($this, "signalHandler"), false);
        pcntl_signal(SIGUSR2,array($this, "signalHandler"), false);
        pcntl_signal(SIGUSR1,array($this, "signalHandler"), false);

    }
    public function signalHandler($signal)
    {
        //declare(ticks = 1);
        switch ($signal) {
            case SIGINT: {
                exit("捕捉到信号SIGINT\n");
            }
                break;

            case SIGUSR2: {
                exit("捕捉到信号SIGUSR2\n");
            }

            case SIGUSR1:
                exit("捕捉到信号SIGUSR1\n");
        }
    }

    public function serve()
    {
        while (true)
        {
            // 接收到信号时，调用注册的signalHandler()
            pcntl_signal_dispatch();
        }
    }
    public function start()
    {
        $pid = posix_getpid();
        $this->install_master_signal();
        $this->serve();
        posix_kill($pid,SIGUSR1);
    }
}

$serve = new install_signal();
$serve->start();