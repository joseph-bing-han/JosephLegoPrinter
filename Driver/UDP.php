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

    private static function getHost($ip = '192.168.1')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_NOSIGNAL, 1);    //注意，毫秒超时一定要设置这个
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);
        for ($i = 2; $i < 255; $i++) {
            $url = "http://{$ip}.{$i}/";
            curl_setopt($ch, CURLOPT_URL, $url);
            $body = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            if ($curl_errno == 0) {
                if (preg_match('/<html><body>IP:(\d+\.\d+\.\d+\.\d+)<\/body><\/html>/i', $body, $match)) {
                    curl_close($ch);
                    return $match[1];
                }
            }
        }
        curl_close($ch);
        return null;
    }

    public static function connect2Server($ip, $port = 9000): bool
    {
        if (empty($ip) || empty($ip)) {
            return false;
        }

        $host = UDP::getHost($ip);

        if ($host === null) {
            return false;
        }

        echo("Get Lego Printer IP: {$host}\n");

        UDP::$socket = stream_socket_client("udp://{$host}:{$port}", $errno, $errstr);
        if (!UDP::$socket) {
            die("ERROR: {$errno} - {$errstr}\n");
        }
        stream_set_timeout(UDP::$socket, 2);
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