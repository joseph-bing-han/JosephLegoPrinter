#!/bin/bash
cd "$( dirname "${BASH_SOURCE[0]}" )"
sudo rm /etc/cups/ppd/lego-printer.ppd
ppdc lego-printer.drv
sudo cp ./ppd/lego-printer.ppd /etc/cups/ppd/
if [ -f "/etc/cups/ppd/lego-printer.ppd" ]; then
    echo "PPD Install success"
else
    echo "PPD Install failed"
fi