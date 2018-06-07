<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2018/6/5
 * Time: 上午1:01
 */



/*
 *
 * 线程安全资源管理器(Thread Safe Resource Manager)
 * 这是个尝尝被忽视，并很少被人说起的“层”(layer), 她在PHP源码的/TSRM目录下。
 * 一般的情况下，这个层只会在被指明需要的时候才会被启用(比如,Apache2+worker MPM,一个基于线程的MPM),
 * 对于Win32下的Apache来说，是基于多线程的，所以这个层在Win32下总是被启用的。
 *
 * Zend线程安全(Zend Thread Safety)，当TSRM被启用的时候，就会定义这个名为ZTS的宏。
 *
 *
 * tsrm_ls
 * TSRM存储器(TSRM Local Storage)，这个是在扩展和Zend中真正被实际使用的指代TSRM存储的变量名。
 *
 * 所有的线程共享同一个进程的地址空间，也就说，多个线程共用一个全局变量，这个时候就会产生竞争。
 * 用C程序员的方式来说:这个时候的全局变量是非线程安全的。
 *
 * 和单线程模式兼容，Zend使用了称作“Non_global Globals”的机制。
 * 这个机制的主要思想就是，对于多线程模型来说，每当一个新的线程被创建，就单独的分配一块内存，这块内存存储着一个全局变量的副本。
 * 而这块内存会被一个Vector串起来，由Zend统一管理。
 *
 * 在ZTS没有被设置的情况下，宏MYEXTENSION_G(V)简单的被等价于全局变量myextension_globals.v，而对于启用了TSRM的情况，
 * MYEXTENSION_G(V)会被转化成在Vector中根据my_extension_globals_id来查找到要访问的全局变量。
 * 现在，只要你在你的代码中，使用MYEXTENSION_G来访问你的全局变量，并在要使用这个全局变量的函数参数列表中添加上TSRMLS_CC，
 * 那么就能保证在单线程和多线程模型下的线程安全，和代码一致性。
 *
 * 以上Via:https://blog.csdn.net/laruence/article/details/2761219
 *
 * pthread_equal(pthread_t,pthread_t) 成功返回非0,否则返回0 ,获取线程标识的数据结构
 * pthread_self(void) 获取当前线程ID
 * 创建线程
 * pthread_create(pthread+t *restrict tidp,const pthread_attr_t *restrict attr,void *(*start_rtn)(void *),void *restrict arg);
 * pthread_exit 线程终止, 1,从启动例程中返回,返回值是线程的退出码,线程可以被同一进程中的其他线程取消
 * pthread_join 线程通过调用此函数访问到这个指针
 * pthread_cancel(pthread_t tid) 请求取消同一进程中的其他线程,而不是线程终止
 * pthread_cleanup_push(void (*rtn)(void *),void *arg) 清理线程处理任务
 * pthread_cleanup_pop(int execute)
 * pthread_detach(pthread_t tid) 分离线程
 * pthread_mutex_init ()  互斥变量
 * pthread_mutex_destroy()销毁互斥变量
 * pthread_mutex_unlocak() 互斥变量解锁
 * pthread_mutex_trylock 尝试对互斥变量进行加锁
 *
 *
 * fork == pthread_create
 * exit == pthread_exit
 * waitpid == pthread_join
 * atexit == pthread_cancel_push
 * getpid == pthread_self
 * abort  == pthread_cancel
 *
 *
 *
 * run()：此方法是一个抽象方法，每个线程都要实现此方法，线程开始运行后，此方法中的代码会自动执行；
 * start()：在主线程内调用此方法以开始运行一个线程；
 * join()：各个线程相对于主线程都是异步执行，调用此方法会等待线程执行结束；
 * kill()：强制线程结束；
 * isRunning()：返回线程的运行状态，线程正在执行run()方法的代码时会返回 true；
 */

$argv = 'TestCmd';

$thread = new class($argv) extends Thread
{
    protected $arg;

    public function __construct($argv)
    {
        $this->arg = $argv;
    }

    //当调用start方法时，该对象的run方法中的代码将在独立线程中异步执行。
    public function run()
    {
        $Thread_id = Thread::getCurrentThreadId();
        echo "Hello World,Arg is :{$this->arg},ThreadId:{$Thread_id}\n";
    }
};

//直接调用start方法，而没有调用join。主线程不会等待，而是在输出main thread。子线程等待3秒才输出Hello World。
if($thread->start())
{
    //join方法的作用是让当前主线程等待该线程执行完毕,确认被join的线程执行结束，和线程执行顺序没关系。
    //也就是当主线程需要子线程的处理结果,主线程需要等待子线程执行完毕,拿到子线程的结果,然后处理后续代码。
    // 与多进程的 waitpid 对等
    $thread->join();

    //这里是子线程执行完毕之后处理的代码
    echo "Main Thread;\n";
}
