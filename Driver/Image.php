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
    // imagemagick object reference
    private $image;

    // sleep time
    const SLEEP = 160000;
    const SKIP = 30;

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

        if ($width > 0 && $height > 0) {
            for ($y = 0; $y < $height; $y++) {

                // check this line has black point
                $hasPoint = false;
                for ($x = 0; $x < $width; $x++) {
                    if ($this->checkPixel($x, $y)) {
                        $hasPoint = true;
                        break;
                    }
                }


                if ($hasPoint) {
                    // skip first 30 pixels
                    $cmd->resetCommand(Command::SERVO_X, Image::SKIP);
                    UDP::sendCommand($cmd);
                    usleep(Image::SLEEP * Image::SKIP);


                    // flag for skip blank pixels to end
                    $skipBlank = false;

                    $step = 0;
                    for ($x = 0; $x < $width; $x++) {
                        $check = $this->checkPixel($x, $y);

                        if ($check) {
                            // set pen down
                            echo("Set pen down at position (x:{$x}, y:{$y})\n");
                            $cmd->resetCommand(Command::SERVO_Z, 1);
                            UDP::sendCommand($cmd);
                            usleep(Image::SLEEP);
                        } else {
                            // set pen up
                            echo("Set pen up at position (x:{$x}, y:{$y})\n");
                            $cmd->resetCommand(Command::SERVO_Z, -1);
                            UDP::sendCommand($cmd);
                            usleep(Image::SLEEP);
                        }


                        // check from current pixel to end is blank, CR
                        if (!$check) {
                            $isBlank = true;
                            for ($i = $x; $i < $width; $i++) {
                                if ($this->checkPixel($i, $y)) {
                                    $isBlank = false;
                                    break;
                                }
                            }

                            // CR
                            if ($isBlank) {
                                echo("From (x:{$x}, y:{$y}) to end all blanks. Skip this line.\n");
                                $this->reset($x + Image::SKIP);
                                $skipBlank = true;
                                break;
                            }
                        }


                        // check follow pixels
                        for (; $x < $width; ++$x) {
                            $step++;

                            // next pixel not same as the first pixel, break
                            if ($this->checkPixel($x, $y) != $check) {

                                // if step > 1 move position x to x+step
                                if ($step > 1) {
                                    if ($check) {
                                        echo("Draw line from (x:" . ($x - $step + 1) .
                                            ", y:{$y}) to (x:" . ($x - 1) . ", y:{$y})\n");
                                        $cmd->resetCommand(Command::SERVO_X, $step - 1);
                                        UDP::sendCommand($cmd);
                                        usleep(Image::SLEEP * ($step - 1));


                                        // set pen up

                                        $cmd->resetCommand(Command::SERVO_Z, -1);
                                        UDP::sendCommand($cmd);
                                        usleep(Image::SLEEP);

                                        // move to next pixel
                                        echo("Move position to (x:{$x}, y:{$y})\n");
                                        $cmd->resetCommand(Command::SERVO_X, 1);
                                        UDP::sendCommand($cmd);
                                        usleep(Image::SLEEP);

                                    } else {
                                        echo("Move position from (x:" . ($x - $step + 1) .
                                            ", y:{$y}) to (x:" . ($x - 1) . ", y:{$y})\n");
                                        $cmd->resetCommand(Command::SERVO_X, $step);
                                        UDP::sendCommand($cmd);
                                        usleep(Image::SLEEP * ($step));
                                    }


                                }


                                $x--;
                                $step = 0;
                                // next pixel not same as the first pixel, break
                                break;
                            }

                            // flush every 50 pixel
                            if ($step == 50) {
                                // if step > 0 move position x to x+step
                                if ($check) {
                                    echo("Draw line from (x:" . ($x - $step + 2) . ", y:{$y}) to (x:{$x}, y:{$y})\n");
                                } else {
                                    echo("Move position from (x:" . ($x - $step + 2) . ", y:{$y}) to (x:{$x}, y:{$y})\n");
                                }
                                $cmd->resetCommand(Command::SERVO_X, $step - 1);
                                UDP::sendCommand($cmd);
                                usleep(Image::SLEEP * (($step - 1)));
                                $step = 0;
                                break;
                            }

                        }

                    }

                    // CR
                    if (!$skipBlank) {
                        $this->reset($width + Image::SKIP);
                    }
                }

                echo("Change to next line.\n");
                // LF
                $cmd->resetCommand(Command::SERVO_Y, 1);
                UDP::sendCommand($cmd);
                usleep(10000);
            }

            // CR
            $this->reset(width + Image::SKIP);

        }
    }

    private function reset(int $width = 0)
    {
        echo("Reset position x to 0\n");
        // pen up
        $cmd = new Command();
        $cmd->resetCommand(Command::SERVO_Z, -1);
        UDP::sendCommand($cmd);

        // reset position x to zero
        $cmd->resetCommand(Command::SERVO_X, -1);
        UDP::sendCommand($cmd);
        if ($width == 0) {
            sleep(40);
        } else {
            usleep(58000 * $width);
        }

    }

    /**
     * check the pixel has black point
     * @param int $x
     * @param int $y
     * @return bool
     * @throws \ImagickPixelException
     */
    private function checkPixel(int $x, int $y): bool
    {
        $result = false;
        $pixel = $this->image->getImagePixelColor($x, $y);
        $colors = $pixel->getColor();
        if ($colors['r'] < 5 && $colors['g'] < 5 && $colors['b'] < 5) {
            $result = true;
        }
        return $result;
    }
}