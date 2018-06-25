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
            for ($y = 0; $y < $height; $y += 2) {

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
                        $step = 1;
                        for ($x = 0; $x < $width; $x++) {
                            echo("x={$x}, y={$y}\n");
                            // check has black color point
                            $check = false;

                            $pixel = $this->image->getImagePixelColor($x, $y);
                            $colors = $pixel->getColor();
                            if ($colors['r'] == 0 && $colors['g'] == 0 && $colors['b'] == 0) {
                                $check = true;
                            }

                            if ($check) {   // has black color point

                                echo("point:x={$x} y={$y}\n");

                                // set x+1
                                $cmd->resetCommand(Command::SERVO_X, $step);
                                UDP::sendCommand($cmd);
                                sleep($step * 310000);

                                // pen down
                                if (!$pen_down) {
                                    $cmd->resetCommand(Command::SERVO_Z, 1);
                                    UDP::sendCommand($cmd);
                                    $pen_down = true;
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
                                }

                                $step = 1;
                            } else {

                                // flush every 10 step
                                if ($step == 10) {
                                    echo("Set position x step:{$step}\n");
                                    $cmd->resetCommand(Command::SERVO_X, $step);
                                    UDP::sendCommand($cmd);
                                    usleep($step * 310000);
                                    $step = 1;
                                } else {
                                    $step++;
                                }

                            }


                        }

                        // flush every step
                        if ($step > 1) {
                            echo("Set position x step:{$step}\n");
                            $cmd->resetCommand(Command::SERVO_X, $step);
                            UDP::sendCommand($cmd);
                            usleep($step * 310000);
                        }

                    } else {
                        $step = -1;
                        for ($x = $width - 1; $x >= 0; $x--) {
                            echo("x={$x}, y={$y}\n");

                            // check has black color point
                            $check = false;

                            $pixel = $this->image->getImagePixelColor($x, $y);
                            $colors = $pixel->getColor();
                            if ($colors['r'] == 0 && $colors['g'] == 0 && $colors['b'] == 0) {
                                $check = true;
                            }

                            if ($check) {   // has black color point

                                echo("point:x={$x} y={$y}\n");

                                // set x-1
                                $cmd->resetCommand(Command::SERVO_X, $step);
                                UDP::sendCommand($cmd);
                                usleep(abs($step) * 310000);

                                // pen down
                                if (!$pen_down) {
                                    $cmd->resetCommand(Command::SERVO_Z, 1);
                                    UDP::sendCommand($cmd);
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
                                }

                                $step = -1;


                            } else {

                                // flush every 10 step
                                if ($step == 10) {
                                    echo("Set position x step:{$step}\n");
                                    $cmd->resetCommand(Command::SERVO_X, $step);
                                    UDP::sendCommand($cmd);
                                    usleep(abs($step) * 310000);
                                    $step = -1;
                                } else {
                                    $step--;
                                }

                            }

                        }
                        // flush every step
                        if ($step < -1) {
                            echo("Set position x step:{$step}\n");
                            $cmd->resetCommand(Command::SERVO_X, $step);
                            UDP::sendCommand($cmd);
                            usleep(abs($step) * 310000);
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
                            usleep(1000);
                        }
                    }

                    // store line number
                    $line++;
                }

                // next line
                $cmd->resetCommand(Command::SERVO_Y, 1);
                UDP::sendCommand($cmd);
            }

            if ($line % 2 != 0) {
                for ($x = $width - 1; $x >= 0; $x--) {
                    echo("reset position:x={$x}\n");
                    $cmd->resetCommand(Command::SERVO_X, -1);
                    UDP::sendCommand($cmd);
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
        $cmd->resetCommand(Command::SERVO_X, -5);
        for ($i = 0; $i < 30; $i++) {
            echo("Reset position x to zero\n");
            UDP::sendCommand($cmd);
            usleep(2310000);
        }
    }
}