#!/bin/bash
cd "$( dirname "${BASH_SOURCE[0]}" )"
sudo rm /usr/lib/cups/backend/lego-printer
sudo cp ./lego-printer /usr/lib/cups/backend
if [ -f "/usr/lib/cups/backend/lego-printer" ]; then
    echo "Backend Install success"
else
    echo "Backend Install failed"
fi