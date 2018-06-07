<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2018/6/7
 * Time: 下午2:15
 */


class pthreads_read_file extends Thread
{
    protected $file_name;
    protected $file_fp;

    public function __construct($name,$fp)
    {
        $this->file_fp = $fp;
        $this->name    = $name;
    }

    public function run()
    {
        $this->readFile();
    }

    public function readFile()
    {
        while(!feof($this->file_fp))
        {
            //独占锁
            if(flock($this->file_fp,LOCK_EX))
            {
                $thread_id = Thread::getCurrentThreadId();

                $data = trim(fgets($this->file_fp));

                echo "Thread:{$thread_id} Name:{$this->name} Read: {$data} \r\n";
                sleep(1);
                flock($this->file_fp, LOCK_UN);
            }
        }
    }
}

$fp = fopen('./test.log', 'rb');

$threads[] = new pthreads_read_file('A',$fp);
$threads[] = new pthreads_read_file('B',$fp);

foreach ($threads as $thread)
{
    $thread->start();
}

foreach ($threads as $thread)
{
    $thread->join();
}
