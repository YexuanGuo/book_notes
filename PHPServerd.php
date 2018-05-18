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
    //构造函数
    public function _init()
    {
        $this->showSystemInfo();
        print_R($this->proccessControl());
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


    //进程管理
    public function proccessControl()
    {

        $pid = pcntl_fork();

        $worker_proccess_pid = 0;
        $master_proccess_pid = 0;
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
