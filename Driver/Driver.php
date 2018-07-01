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