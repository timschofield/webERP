#!/usr/bin/env bash

set -e

# @todo add support for 1st argument, being either apache2 or nginx

BASE_DIR="$(dirname -- "$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")")";

cd "$BASE_DIR";

if [ -z "$GITHUB_ACTION" ]; then
	# @todo check - do we need more packages than this?
	DEBIAN_FRONTEND=noninteractive apt-get install -y apache2
fi

if [ -f /etc/apache2/sites-enabled/000-default.conf ]; then
	sudo rm /etc/apache2/sites-enabled/000-default.conf
fi
if [ -f /etc/apache2/sites-available/000-default.conf ]; then
	sudo rm /etc/apache2/sites-available/000-default.conf
fi
if [ -n "$(ls /etc/apache2/mods-enabled/php* 2>/dev/null)" ]; then
	sudo rm /etc/apache2/mods-enabled/php*
fi

# @todo check usage of apache envvars file to simplify the sed commands

sudo cp ./tests/setup/config/apache2/000-default.conf /etc/apache2/sites-available/
sudo sed -r -i -e "s|DocumentRoot /var/www/html|DocumentRoot $BASE_DIR|" /etc/apache2/sites-available/000-default.conf
sudo sed -r -i -e "s|<Directory /var/www/html>|<Directory $BASE_DIR>|" /etc/apache2/sites-available/000-default.conf
sudo ln -s /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-enabled/000-default.conf

sudo a2enmod proxy_fcgi
sudo cp ./tests/setup/config/apache2/php_fpm_proxyfcgi.conf /etc/apache2/mods-available
sudo ln -s /etc/apache2/mods-available/php_fpm_proxyfcgi.conf /etc/apache2/mods-enabled/php_fpm_proxyfcgi.conf
PHPVER=$(php -r 'echo implode(".",array_slice(explode(".",PHP_VERSION),0,2));' 2>/dev/null)
SOCKET=$(cat "/etc/php/$PHPVER/fpm/pool.d/www.conf" | grep -E '^listen *=' | sed -e 's/^listen *= *//')
sudo sed -r -i -e "s|proxy:unix:/run/php/php-fpm.sock|proxy:unix:$SOCKET|" /etc/apache2/mods-available/php_fpm_proxyfcgi.conf

# Debugging
#echo '### /etc/apache2/'
#sudo ls -la /etc/apache2/
#echo
#sudo cat /etc/apache2/*.conf
#echo
#sudo ls -la /etc/apache2/mods-enabled
#sudo cat /etc/apache2/mods-enabled/*
#echo
#sudo ls -la /etc/apache2/conf-enabled
#sudo cat /etc/apache2/conf-enabled/*
#echo
#sudo ls -la /etc/apache2/sites-enabled
#sudo cat /etc/apache2/sites-enabled/*
#echo
#sudo cat /etc/apache2/mods-enabled/php_fpm_proxyfcgi.conf

# Start the service
sudo systemctl restart apache2.service
