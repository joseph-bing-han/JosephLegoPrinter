<?php
/**
 * Created by: Joseph Han
 * Date Time: 18-6-24 上午9:03
 * Email: joseph.bing.han@gmail.com
 * Blog: http://blog.joseph-han.net
 */

namespace joseph\lego\printer;


class UDP
{
    private static $socket = null;


    public static function connect2Server($host, $port = 9000): bool
    {
        if (empty($host) || empty($port)) {
            return false;
        }
        UDP::$socket = stream_socket_client("udp://{$host}:{$port}", $errno, $errstr);
        if (!UDP::$socket) {
            die("ERROR: {$errno} - {$errstr}\n");
        }
        stream_set_timeout(UDP::$socket, 3);
        return true;
    }

    public static function close(): void
    {
        if (UDP::$socket != null) {
            fclose(UDP::$socket);
        }

    }

    public static function sendCommand(Command $cmd = null): string
    {
        if ($cmd == null || UDP::$socket == null) {
            return '';
        }
        $command = $cmd->getCommand();
        $size = strlen($command);
        if ($size == 5) {
            @fwrite(UDP::$socket, $command, $size);
            $result = @fread(UDP::$socket, 20);
            echo("receive:" . $result . "\n");
            return $result;
        } else {
            return '';
        }
    }
}