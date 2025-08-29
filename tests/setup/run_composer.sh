#!/usr/bin/env bash

# @todo is there a composer command which does require us to know which packages to force-install?

composer update phpunit/phpunit symfony/browser-kit symfony/http-client
