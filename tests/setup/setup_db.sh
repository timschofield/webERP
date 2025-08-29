#!/usr/bin/env bash

set -e

# @todo add support for 1st argument, being db type and 2nd being version

# @todo set up specific db config: strict mode, maybe enabling the query log?

BASE_DIR="$(dirname -- "$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")")";

cd "$BASE_DIR";

# Debugging
#echo '### /etc/mysql/'
#sudo ls -la /etc/mysql/
#echo
#echo '### /etc/mysql/debian-start'
#sudo cat /etc/mysql/debian-start
#echo
#echo '### /etc/mysql/debian.cnf'
#sudo cat /etc/mysql/debian.cnf
#echo
#echo '### /etc/mysql/my.cnf'
#sudo cat /etc/mysql/my.cnf
#echo
#echo '### /etc/mysql/my.cnf.fallback'
#sudo cat /etc/mysql/my.cnf.fallback
#echo
#echo '### /etc/mysql/mysql.cnf'
#sudo cat /etc/mysql/mysql.cnf
#echo
#sudo ls -la /etc/mysql/conf.d/
#echo
#sudo ls -la /etc/mysql/mysql.conf.d/

# Start the service
sudo systemctl start mysql.service
