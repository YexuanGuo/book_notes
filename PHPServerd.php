<?php
/**
 * Created by PhpStorm.
 * User: guoyexuan
 * Date: 2018/5/13
 * Time: 下午7:15
 */

ini_set('date.timezone','Asia/Shanghai');

class PHPServerd
{


    private $pid = null;    //当前进程pid

    public function __construct()
    {
        $this->foreground_colors['black'] = '0;30';
        $this->foreground_colors['dark_gray'] = '1;30';
        $this->foreground_colors['blue'] = '0;34';
        $this->foreground_colors['light_blue'] = '1;34';
        $this->foreground_colors['green'] = '0;32';
        $this->foreground_colors['light_green'] = '1;32';
        $this->foreground_colors['cyan'] = '0;36';
        $this->foreground_colors['light_cyan'] = '1;36';
        $this->foreground_colors['red'] = '0;31';
        $this->foreground_colors['light_red'] = '1;31';
        $this->foreground_colors['purple'] = '0;35';
        $this->foreground_colors['light_purple'] = '1;35';
        $this->foreground_colors['brown'] = '0;33';
        $this->foreground_colors['yellow'] = '1;33';
        $this->foreground_colors['light_gray'] = '0;37';
        $this->foreground_colors['white'] = '1;37';

        $this->background_colors['black'] = '40';
        $this->background_colors['red'] = '41';
        $this->background_colors['green'] = '42';
        $this->background_colors['yellow'] = '43';
        $this->background_colors['blue'] = '44';
        $this->background_colors['magenta'] = '45';
        $this->background_colors['cyan'] = '46';
        $this->background_colors['light_gray'] = '47';
    }

    public function displayUI($string, $foreground_color = null, $background_color = null)
    {
        $colored_string = "";

        if (isset($this->foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
        }
        if (isset($this->background_colors[$background_color])) {
            $colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
        }

        $colored_string .= $string . "\033[0m";
        if (!function_exists('posix_isatty') || posix_isatty(STDOUT)) {
            echo $colored_string . "\n";
        }
    }

    public function safeEcho($msg)
    {
        if (!function_exists('posix_isatty') || posix_isatty(STDOUT)) {
            echo $msg;
        }
    }

    public function getForgetTime()
    {
        return date("Y-m-d H:i:s", time());
    }



    //一些系统信息
    public function showSystemInfo()
    {

        $current_proccess_pid = posix_getpid(); //return pid_t val;
        $hostname = gethostname();

        $display_length = 16;

        $this->displayUI(str_pad('-',60,'-').
            "\n CurrentTime : ".str_pad('',$display_length + 5 - strlen('CurrentTime')).$this->getForgetTime()."\n".
            " Current Proceess PID : ".str_pad('',$display_length + 5 - strlen('Current Proceess PID')).$current_proccess_pid."\n".
            " PHPVersion : ".str_pad('',$display_length + 5 - strlen('PHPVersion')).PHP_VERSION."\n".
            " Hostname : ".str_pad('',$display_length + 5 - strlen('Hostname')).$hostname."\n".
            str_pad('-',60,'-')."\n",'green');
    }

    //构造函数
    public function _init()
    {
        $this->pid = getmypid();
        $this->showSystemInfo();
        print_R($this->install_signal());
    }


    /*
     * 安装信号函数,结构体如下
     * void (*signal(int signo,void(*func)(int)))(int),signo参数是信号名,func是接受到信号要调用的地址
     * 第三个参数说明:restart_syscalls
     *指定当信号到达时系统调用重启是否可用。
     * （译注：经查资料，此参数意为系统调用被信号打断时，系统调用是否从 开始处重新开始，但根据http://bugs.php.net/bug.php?id=52121，此参数存在bug无效。）
     */
    public function install_signal()
    {

        /*
         * 为了保证php环境的安全性和稳定性;
         * pcntl拓展在实现signal上使用了“延后执行”的机制;
         * 因此使用该功能时，必须先使用语句declare(ticks=1);
         * 否则注册的signal就不会执行了
         * ticks=1表示每执行1行PHP代码就回调此函数。实际上大部分时间都没有信号产生，但ticks的函数一直会执行。
         */
        declare(ticks = 1);


        //false参数这里有一个bug
        pcntl_signal(SIGUSR1,array($this,'recv_signal'),false);
        pcntl_signal(SIGUSR2,array($this,'recv_signal'),false);
        pcntl_signal(SIGINT,array($this,'recv_signal'),false);
//        pcntl_signal(SIGCLD,array($this,'recv_signal'),false);

        //睡眠两秒是为了测试打印结果,没有其他用处
        sleep(5);

        //向当前进程发送信号
        posix_kill($this->pid,SIGINT);
    }

    //接受到信号处理函数
    public function recv_signal($signal)
    {

        echo sprintf("signal is :%s \n",$signal);
        switch ($signal)
        {
            case SIGUSR1:
                echo (sprintf('收到信号:%s,USR1信号',$signal));
                break;
            case SIGUSR2:
                echo (sprintf('收到信号:%s,USR2信号',$signal));
                break;
            case SIGINT:
                echo (sprintf("收到信号:%s,SIGINT信号,程序退出 \n",$signal));
                break;
//            case SIGCLD:
//                call_user_func('sigcld_received_func','test');
//                break;
            default:
                echo ('没有捕捉到信号!');
                break;
        }
    }

//    public function sigcld_received_func($param)
//    {
//        print_R($param);
//    }


    //System函数
    public function systemFunc()
    {
        if(($status = system("date")) < 0)
        {
            exit('error');
        }

        if($status = system('who; exit 44') < 0)
        {
            exit('error');
        }

        if($status = system('nosuchcommand') <0)
        {
            //if not found cmd exit(127)
            exit('error');
        }
        echo sprintf('status : %s',$status);
    }

    //errorno 转换成字符串 var error_no type is int;
    public function errorNoToString($error_no)
    {
        return posix_strerror($error_no);
    }

    //获取用户ID和用户组ID
    public function getUidAndGidAndcwd()
    {
        return array(
            'uid' => posix_getuid(),
            'gid' => posix_getgid(),
            'cwd' => posix_getcwd(),
            'pwuid'=>posix_getpwuid(posix_getuid()),    //获取运行程序用户的登录名
            'pwname'=>posix_getpwnam('guest'),//通过用户名获取指定信息
            'login_name'=>posix_getlogin(),//获取登录用户名称

            /*PRIO_PROCCESS 表示进程,PRIO_PGRP表示进程组,PRIO_USER表示用户ID*/
            'proc_nice_val'=>pcntl_getpriority(PRIO_PROCESS),//获取进程nice值,进程调度优先级,可以用pcntl_getpriority设置优先级
            'usage'=>getrusage(2),  //返回CPU时间以及指示资源使用情况的另外14值
            'pgid'=>posix_getpgid(posix_getpid()),//进程组ID
            'sid' =>posix_getsid(posix_getpid()),   //获取会话首进程ID

        );

    }


    //读取FD
    public function readFD()
    {
        $filename = './README.md';

        /*
        while($line = fgets("README.md","R") !=null)
        {
            print_R($line);
        }*/
        $filename = fopen('./README.md','r');
        //返回文件指针读/写的位置
        $res = ftell($filename);

        print_R($res.PHP_EOL);
    }

    //时间函数
    public function getTime()
    {
        return array(
            'getTimeofday'=>gettimeofday(), //弃用,精度更高
            'localTime'   =>localtime(),
            'mktime'      =>mktime(),
            'strftime'    =>strftime('%c'), //格式化时间 strptime 将string时间转换成时间戳
        );
    }

    //waitpid & wait & waitid 取得进程终止状态

    public function waitPidFunc()
    {
        $pid = pcntl_fork();

        if($pid < 0)
        {
            exit('fork error!');
        }
        else if($pid === 0)  //子进程,在执行下面fork的时候,这里已经终止了,
        {
            $pid2 = pcntl_fork();
            if($pid2 < 0)
            {
                exit('fork error!');
            }
            else if($pid2 > 0)
            {
                exit(0);
            }
            else
            {
                /*
                 * 这里调用Sleep保证在打印父进程ID的时候第一个子进程已经终止,
                 * fork之后父进程和子进程都可以继续执行,但是无法确定Master,Child哪个先执行,
                 * 在fork之后如果不使用Sleep第二个子进程休眠的话,那么它可能比Master先执行,
                 * 所以打印的父进程ID将是创建它的父进程,而不是init进程,父进程也就是挂了的子进程,所以是1
                 */
                sleep(10);
                print_r(sprintf("second child,parent pid = %s",posix_getppid()));
                exit(0);
            }
        }
        else
        {
            /*
             * WCONTINUED 若实现支持作业控制,那么由pid指定的任一子进程在停止后已经继续,但其并没有报告,则返回其状态.
             * WNOHANG    若由pid指定的子进程并不是立即可用的,则waitpid 不阻塞,此时返回0
             * WUNTRACED  若某实现支持作业控制,而由pid指定的任一子进程已经处于停止状态,并且其状态自停止以来还从来没报告过
             * 则返回其状态,WIFSTOPPED 宏确定返回值是否对应于一个停止的子进程。
             */

            if(pcntl_waitpid($pid,$status,0) != $pid )
            {
                exit('waitpid error!');
            }
            exit(0);
        }

    }

    //进程管理
    public function proccessControl()
    {

        $pid = pcntl_fork();

        if($pid < 0)
        {
            //出错退出
            exit('fork fail!');
        }
        else if($pid == 0)
        {
            //这里是子进程先执行,因为父进程sleep了
            $worker_proccess_pid = posix_getpid();   //子进程
            echo sprintf('worker proccess pid = %s \n',$worker_proccess_pid);
        }
        else
        {
            $master_proccess_pid = posix_getpid();
            echo sprintf('master proccess pid = %s \n',$master_proccess_pid);

            sleep(10);               //父进程
        }

        die;


        //putenv setenv unsetenv

        return array(
            'getEnv' =>getenv('LANG'),//返回环境变量信息,例如:HOME USER LANG LC_ALL COLUMNS DATEMSK等等
            'procccess_info' =>array(
                'getpid'=>posix_getpid(),//调用进程的进程ID,也是当前进程ID
                'getppid'=>posix_getppid(),//调用进程的父进程ID
                'getuid'=>posix_getuid(),//调用进程的实际用户ID
                'geteuid'=>posix_geteuid(),//调用进程的实际用户ID
                'getgid' =>posix_getgid(),//调用进程的实际组ID
                'getegid'=>posix_getegid(),//调用进程的有效组ID
            ),
        );
    }



    //创建临时文件
    public function createTmpFiles()
    {

        $temp = tmpfile();

        fwrite($temp, "Testing, testing.");

        //倒回文件的开头
        rewind($temp);

        //从文件中读取 1k
        echo fread($temp,1024);
        //删除文件
        fclose($temp);
    }


    //设置umask然后创建文件测试创建权限
    public function settingUmaskCreatFiles()
    {
        //Owner 所有权限
        umask(0);
        fopen('test_umask0',w);
        umask(2);
        fopen('test_umask2',w);
    }


    //获取当前时间
    public function getCurrentTime()
    {
        return date("Y-m-d H:i:s",time());
    }

    //检查文件权限
    public function checkFilesAccess()
    {
        //R_OK 测试读权限
        //W_OK 测试写权限
        //X_OK 测试执行权限
        $filename = '/etc/php.ini';
        return array(
            'FIles_Access'=>posix_access($filename,POSIX_R_OK), //成功返回0,失败返回-1
        );
    }
    //link,unlink,linkat,unlinkat,remove func
    public function checkLink()
    {
        if(link('./','test_link'))
        {
            die('success!');
        }
    }
    //获取文件信息
    public function getFilesInfo()
    {
        $filename = './test_txt';
        return array(
            'stat'=>stat($filename),
            'lstat'=>lstat($filename),  //如果是符号链接,返回符号链接的信息,而不是返回返回符号链接引用信息,返回当前文件统计信息
            'fstatat'=>fstat($filename),
        );
    }
    //获取目录下所有文件
    public function getAllFilesForDir()
    {
        $dir = '/data/webdata/THelper';
        if(is_dir($dir))
        {
            if($dh = opendir($dir))
            {
                while(($file = readdir($dh)) != NULL)
                {
                    print_r($file.PHP_EOL);
                }
                closedir($dh);
            }
        }
    }
    public function forkOneWorker()
    {
        $parent_pid = getmypid();
        $pid = pcntl_fork();

        if($pid < 0)
        {
            die('fork error!');
        }
        elseif($pid == 0)
        {
            $_worker_pid = getmypid();
            echo "parent pid is :".$parent_pid.PHP_EOL;

            echo "child pid is :".$_worker_pid.PHP_EOL;

        }
        else
        {

        }
    }
}

$obj = new PHPServerd();

$obj->_init();
