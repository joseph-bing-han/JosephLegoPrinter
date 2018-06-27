#!/usr/bin/php
<?php
/**
 * Created by: Joseph Han
 * Date Time: 18-6-24 下午1:18
 * Email: joseph.bing.han@gmail.com
 * Blog: http://blog.joseph-han.net
 */

namespace joseph\lego\printer;

require_once 'vendor/autoload.php';


if (PHP_SAPI != 'cli' || $argc != 3) {
    echo("Please use this format command:\nReset.php [printer ip] [1/-1]\n");
    return 111;
} else {
    $printerIP = $argv[1];
    UDP::connect2Server($printerIP);
    $cmd = new Command(Command::SERVO_Z, -1);
    UDP::sendCommand($cmd);
    usleep(200);
    if ($argv[2] == -1) {
        $cmd->resetCommand(Command::SERVO_X, -1);
        UDP::sendCommand($cmd);
        echo("Send Command\n");
    } else {
        $cmd->resetCommand(Command::SERVO_X, $argv[2]);
        $index = 1;
        while (1) {
            echo("Send Command: {$index} times.\n");
            UDP::sendCommand($cmd);

            $index++;
            usleep(abs($argv[2]) * 10000);
            echo("finish\n");
        }
    }


}