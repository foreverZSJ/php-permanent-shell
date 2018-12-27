<?php
/**
 * php多进程常驻shell
 *
 *
 * 多进程shell优点：
 * --------------------------------------------------------------
 * 使用多进程,子进程结束以后,内核会负责回收资源                          |
 * 使用多进程,子进程异常退出不会导致整个进程Thread退出,父进程还有机会重建流程 |
 * 一个常驻主进程,只负责任务分发,逻辑更清楚                             |
 * --------------------------------------------------------------
 *
 * 单进程shell弊端：
 * ----------------------
 * 没有专门的gc例程        |
 * 也没有有效的内存管理途径  |
 * ----------------------
 *
 * 参考文献：http://www.laruence.com/2009/06/11/930.html
 *
 * kill -USR2 pid
 *
 */

/** 判断是 fpm-fcgi or cli */
if (substr(php_sapi_name(), 0, 3) !== 'cli') {
    die("This Programe can only be run in CLI mode");
}

pcntl_signal(SIGUSR2, 'sig_handler');
function sig_handler($signo){
    switch ($signo){
        case SIGUSR2:
            exit(0);
            break;
        default:
            //处理其他信号
    }
}

$prompt = $argv[1];
$pid    = posix_getpid(); //取得主进程ID
$user   = posix_getlogin(); //取得用户名

while(true){
    $pid = pcntl_fork(); //创建子进程
    if($pid == 0){//子进程
        $pid = posix_getpid();
        echo "* Process {$pid} was created, and Executed:\n\n";

        doshell();

        exit;
    }else{//主进程

        $pid = pcntl_wait($status, WUNTRACED); //取得子进程结束状态
        if (pcntl_wifexited($status)) {
            declare(ticks = 1){ // 等待子进程处理完成后，调用sig_handler获取信号（kill -USR2 进程id），停止当前进程

                echo "\n\n* 等待子进程 Sub process: {$pid} exited with {$status} \n";

            }
        }
    }
}

exit(0);


function doshell(){
    for($i=1;$i<=20;$i++){
        system("echo $i >> /tmp/incr.log");
        sleep(1);
    }
}










