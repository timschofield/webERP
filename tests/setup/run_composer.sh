#!/usr/bin/env bash

# Allow users to define the COMPOSER env var before running this script if they use the composer binary outside of PATH
# or with a different name
COMPOSER=${COMPOSER:-composer}

# @todo is there a composer command which does require us to know which packages to force-install?

$COMPOSER update phpunit/phpunit symfony/browser-kit symfony/http-client
