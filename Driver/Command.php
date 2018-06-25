<?php
/**
 * Created by: Joseph Han
 * Date Time: 18-6-24 上午9:16
 * Email: joseph.bing.han@gmail.com
 * Blog: http://blog.joseph-han.net
 */

namespace joseph\lego\printer;


class Command
{
    const SERVO_X = 1;
    const SERVO_Y = 2;
    const SERVO_Z = 3;
    const SIGN = 123;
    private $command;

    function __construct(int $servo = 1, int $step = 1)
    {
        $this->command = [Command::SIGN, $servo, $step, 0, "\n"];

        return $this;
    }

    public function resetCommand(int $servo = 1, int $step = 1)
    {
        $this->command[1] = $servo;
        $this->command[2] = $step;
    }

    public function getCommand(): string
    {
        return pack(
            "c*",
            $this->command[0],
            $this->command[1],
            $this->command[2],
            $this->command[3],
            $this->command[4]
        );
    }
}