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
    const SLEEP = 500000;
    const SKIP = 5;
    const PIXELS = 2;
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
            for ($y = 0; $y < $height; $y += Image::PIXELS) {


                // check this line has black point
                $hasPoint = false;
                for ($x = 0; $x < $width; $x += Image::PIXELS) {
                    if ($this->checkPixel($x, $y)) {
                        $hasPoint = true;
                        break;
                    }
                }

                // flag for skip blank pixels to end
                $skipBlank = false;

                if ($hasPoint) {
                    // skip first 30 pixels
                    $cmd->resetCommand(Command::SERVO_X, Image::SKIP);
                    UDP::sendCommand($cmd);
                    usleep(Image::SLEEP * Image::SKIP);


                    for ($x = 0; $x < $width; $x += Image::PIXELS) {

                        echo("Current position (x={$x}, y={$y}}\n");
                        $check = $this->checkPixel($x, $y);

                        if ($check) {
                            // set pen down
                            echo("Set pen down at position (x:{$x}, y:{$y})\n");
                            $cmd->resetCommand(Command::SERVO_Z, 1);
                            UDP::sendCommand($cmd);
                            usleep(250);

                            $cmd->resetCommand(Command::SERVO_Z, -1);
                            UDP::sendCommand($cmd);
                            usleep(200);
                        }


                        // check from current pixel to end is blank, CR
                        $isBlank = true;
                        for ($i = $x + 1; $i < $width; $i += Image::PIXELS) {
                            if ($this->checkPixel($i, $y)) {
                                $isBlank = false;
                                break;
                            }
                        }

                        // CR
                        if ($isBlank) {
                            echo("From (x:{" . ($x + 1) . "}, y:{$y}) to end all blanks. Skip this line.\n");
                            $this->reset($x + Image::SKIP * Image::PIXELS);
                            $skipBlank = true;
                            break;
                        }

                        $cmd->resetCommand(Command::SERVO_X, 1);
                        UDP::sendCommand($cmd);
                        usleep(Image::SLEEP);

                    }

                }

                // CR
                if ($hasPoint && !$skipBlank) {
                    $this->reset($width + Image::SKIP * Image::PIXELS);
                }

                echo("Current Y={$y},\nChange to next line.\n");
                // LF
                $cmd->resetCommand(Command::SERVO_Y, 1);
                UDP::sendCommand($cmd);
                sleep(1);
            }

            // CR
            $this->reset(width + Image::SKIP * Image::PIXELS);

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
            sleep(35);
        } else {
            usleep(110000 * $width);
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
        $count = 0;
        for ($m = 0; $m < Image::PIXELS; $m++) {
            for ($n = 0; $n < Image::PIXELS; $n++) {
                $pixel = $this->image->getImagePixelColor($x + $n, $y + $m);
                $colors = $pixel->getColor();
                if ($colors['r'] < 5 && $colors['g'] < 5 && $colors['b'] < 5) {
                    $count++;
                    if ($count > 3) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}