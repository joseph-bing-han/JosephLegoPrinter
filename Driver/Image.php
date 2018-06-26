<?php
/**
 * Created by: Joseph Han
 * Date Time: 18-6-24 上午9:38
 * Email: joseph.bing.han@gmail.com
 * Blog: http://blog.joseph-han.net
 */

namespace joseph\lego\printer;


class Image
{
    private $image;

    function __construct(string $image_path)
    {
        if (!empty($image_path) && file_exists($image_path)) {
            $this->image = new \Imagick($image_path);
            return $this;
        } else {
            return null;
        }
    }

    public function print()
    {
        $this->reset();

        $width = $this->image->getImageWidth();
        $height = $this->image->getImageHeight();
        $cmd = new Command();
        $line = 0;

        $pen_down = false;
        if ($width > 0 && $height > 0) {
            for ($y = 0; $y < $height; $y++) {

                // check this line has point
                $hasPoint = false;
                for ($x = 0; $x < $width; $x++) {
                    $pixel = $this->image->getImagePixelColor($x, $y);
                    $colors = $pixel->getColor();
                    if ($colors['r'] == 0 && $colors['g'] == 0 && $colors['b'] == 0) {
                        $hasPoint = true;
                        break;
                    }
                }


                if ($hasPoint) {
                    if ($line % 2 == 0) {
                        for ($x = 0; $x < $width; $x++) {
                            // set position x
                            echo("Position: x={$x}, y={$y}\n");

                            $cmd->resetCommand(Command::SERVO_X, 1);
                            UDP::sendCommand($cmd);
                            usleep(50000);

                            // check has black color point
                            $check = false;

                            $pixel = $this->image->getImagePixelColor($x, $y);
                            $colors = $pixel->getColor();
                            if ($colors['r'] == 0 && $colors['g'] == 0 && $colors['b'] == 0) {
                                $check = true;
                            }

                            if ($check) {   // has black color point
                                echo("Point: x={$x}, y={$y}\n");

                                // pen down
                                if (!$pen_down) {
                                    $cmd->resetCommand(Command::SERVO_Z, 1);
                                    UDP::sendCommand($cmd);
                                    $pen_down = true;
                                    usleep(50000);
                                }

                                // check next point
                                $check_next = false;
                                if ($x < $width) {
                                    $pixel = $this->image->getImagePixelColor($x + 1, $y);
                                    $colors = $pixel->getColor();
                                    if ($colors['r'] == 0 && $colors['g'] == 0 && $colors['b'] == 0) {
                                        $check_next = true;
                                    }
                                }

                                if (!$check_next) {
                                    // next point is not back, set pen up
                                    $cmd->resetCommand(Command::SERVO_Z, -1);
                                    UDP::sendCommand($cmd);
                                    $pen_down = false;
                                    usleep(50000);
                                }

                            }

                        }

                    } else {
                        for ($x = $width - 1; $x >= 0; $x--) {
                            // set position x
                            echo("Position: x={$x}, y={$y}\n");

                            $cmd->resetCommand(Command::SERVO_X, -1);
                            UDP::sendCommand($cmd);
                            usleep(50000);
                            // check has black color point
                            $check = false;

                            $pixel = $this->image->getImagePixelColor($x, $y);
                            $colors = $pixel->getColor();
                            if ($colors['r'] == 0 && $colors['g'] == 0 && $colors['b'] == 0) {
                                $check = true;
                            }

                            if ($check) {   // has black color point

                                echo("Point: x={$x}, y={$y}\n");

                                // pen down
                                if (!$pen_down) {
                                    $cmd->resetCommand(Command::SERVO_Z, 1);
                                    UDP::sendCommand($cmd);
                                    usleep(50000);
                                    $pen_down = true;
                                }


                                // check next point
                                $check_next = false;
                                if ($x > 0) {
                                    $pixel = $this->image->getImagePixelColor($x - 1, $y);
                                    $colors = $pixel->getColor();
                                    if ($colors['r'] == 0 && $colors['g'] == 0 && $colors['b'] == 0) {
                                        $check_next = true;
                                    }
                                }

                                if (!$check_next) {
                                    // next point is not back, set pen up
                                    $cmd->resetCommand(Command::SERVO_Z, -1);
                                    UDP::sendCommand($cmd);
                                    $pen_down = false;
                                    usleep(50000);
                                }
                            }
                        }

                    }

                    // set pen up
                    if ($pen_down) {
                        $cmd->resetCommand(Command::SERVO_Z, -1);
                        UDP::sendCommand($cmd);
                        $pen_down = false;
                    }


                    // reset position x to zero
                    if ($line % 2 != 0) {
                        for ($i = 0; $i < 10; $i++) {
                            $cmd->resetCommand(Command::SERVO_X, -3);
                            UDP::sendCommand($cmd);
                            usleep(200000);
                        }
                    }

                    // store line number
                    $line++;
                }

                // next line
                $cmd->resetCommand(Command::SERVO_Y, 1);
                UDP::sendCommand($cmd);
                usleep(100000);
            }

            // if the position is on right when printing complete, reset position to zero
            if ($line % 2 != 0) {
                for ($x = $width - 1; $x >= 0; $x--) {
                    echo("reset position:x={$x}\n");
                    $cmd->resetCommand(Command::SERVO_X, -1);
                    UDP::sendCommand($cmd);
                    usleep(50000);
                }
            }

        }
    }

    private function reset()
    {
        // pen up
        $cmd = new Command();
        $cmd->resetCommand(Command::SERVO_Z, -1);
        UDP::sendCommand($cmd);

        // reset position x to zero
        $cmd->resetCommand(Command::SERVO_X, -3);
        for ($i = 0; $i < 100; $i++) {
            echo("Reset position x to zero: {$i}\n");
            UDP::sendCommand($cmd);
            usleep(150000);
        }
    }
}