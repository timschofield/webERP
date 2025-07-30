#!/usr/bin/env bash

set -e

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";

echo "Updating translation files..."

"$BASE_DIR/build/update_translations.sh"

echo "Cleaning up and dumping the database..."

"$BASE_DIR/build/dump_database.sh"

echo "Creating the final tarball..."

"$BASE_DIR/build/make_tarball.sh"
