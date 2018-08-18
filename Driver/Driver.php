#!/usr/bin/php
<?php
/**
 * Created by: Joseph Han
 * Date Time: 18-6-24 上午9:29
 * Email: joseph.bing.han@gmail.com
 * Blog: http://blog.joseph-han.net
 */

set_time_limit(0);
ini_set('memory_limit', '256M');
require_once 'vendor/autoload.php';

use joseph\lego\printer\UDP;
use joseph\lego\printer\Image;

if (PHP_SAPI != 'cli' || $argc != 3) {
    echo("Please use this format command:\nDriver.php [local ip (e.g.: 192.168.1)] [image file path]\n");
    return 111;
} else {
    $ip = $argv[1];
    $imagePath = $argv[2];
    if (UDP::connect2Server($ip)) {
        $image = new Image($imagePath);
        $image->print();
    } else {
        echo("Cannot find Lego Printer\n");
    }

    return 0;
}