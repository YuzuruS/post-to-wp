#!/bin/bash
# install phpredis extension.

set -e

sudo apt-get update -qq

major_version=`php -v | head -n 1 | cut -c 5`
if [ $major_version == "7" ]
then
  sudo apt-get install php7.0-xmlrpc
else
  sudo apt-get install php5-xmlrpc
fi