#!/usr/bin/php
<?php
/**
 * Created by: Joseph Han
 * Date Time: 18-6-24 上午9:29
 * Email: joseph.bing.han@gmail.com
 * Blog: http://blog.joseph-han.net
 */

namespace joseph\lego\printer;

require_once 'vendor/autoload.php';


if (PHP_SAPI != 'cli' || $argc != 3) {
    echo("Please use this format command:\nDriver.php [printer ip] [image file path]\n");
    return 111;
} else {
    $printerIP = $argv[1];
    $imagePath = $argv[2];
    UDP::connect2Server($printerIP);
    $image = new Image($imagePath);
    $image->print();
    return 0;
}