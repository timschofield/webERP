#!/usr/bin/env bash

# @todo allow a '--uninstall' action

# Allow users to define the COMPOSER env var before running this script if they use the composer binary outside of PATH
# or with a different name
COMPOSER=${COMPOSER:-composer}

# @todo is there a composer command which does require us to know which packages to force-install?

$COMPOSER update --no-interaction --prefer-stable --prefer-dist phpunit/phpunit symfony/browser-kit symfony/css-selector symfony/http-client symfony/mime
