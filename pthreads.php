<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2018/6/5
 * Time: 上午1:01
 */

$thread = new class extends Thread
{
    public function run()
    {
        echo "Hello World\n";
    }
};

$thread->start() && $thread->join();