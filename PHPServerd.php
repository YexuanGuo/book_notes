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


    //构造函数
    public function _init()
    {
        $this->showSystemInfo();
        print_R($this->settingUmaskCreatFiles());
    }


    //一些系统信息
    public function showSystemInfo()
    {
        $current_proccess_pid = posix_getpid(); //return pid_t val;

        echo str_pad('-',60,'-')."\n Current Proceess PID : $current_proccess_pid  Time:{$this->getCurrentTime()}\n".str_pad('-',60,'-')."\n";
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




    //设置umask然后创建文件测试创建权限
    public function settingUmaskCreatFiles()
    {
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
