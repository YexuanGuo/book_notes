<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2018/6/5
 * Time: 上午1:01
 */



/*
 * pthread_equal(pthread_t,pthread_t) 成功返回非0,否则返回0 ,获取线程标识的数据结构
 * pthread_self(void) 获取当前线程ID
 * 创建线程
 * pthread_create(pthread+t *restrict tidp,const pthread_attr_t *restrict attr,void *(*start_rtn)(void *),void *restrict arg);
 * pthread_exit 线程终止, 1,从启动例程中返回,返回值是线程的退出码,线程可以被同一进程中的其他线程取消
 * pthread_join 线程通过调用此函数访问到这个指针
 * pthread_cancel(pthread_t tid) 请求取消同一进程中的其他线程,而不是线程终止
 * pthread_cleanup_push(void (*rtn)(void *),void *arg) 清理线程处理任务
 * pthread_cleanup_pop(int execute)
 */



$thread = new class extends Thread
{
    public function run()
    {
        echo "Hello World\n";
    }
};
$thread->start() && $thread->join();
