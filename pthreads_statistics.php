<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2018/6/7
 * Time: 下午5:01
 */


class pthreads_statistics extends Thread
{
    protected $flag;

    public function __construct($number)
    {
        $this->flag = $number;
    }

    public function run()
    {
        echo 'ThreadID:'.Thread::getCurrentThreadId().'  Number:'.$this->flag.PHP_EOL;
    }
};

$thread_count = 10;

for($i=1;$i<=$thread_count;$i++)
{
    $thread = new pthreads_statistics($i);

    $thread->start();

    $threads[] = $thread;

}

foreach ($threads as $thread)
{
    $thread->join();
}