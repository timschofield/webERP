#!/usr/bin/env bash

help() {
    printf "Usage: check_syntax.sh

Checks the validity of every php file within the local installation. Errors are written to stderr
"
}

# parse cli options and arguments
while getopts ":h" opt
do
    case $opt in
        h)
            help
            exit 0
        ;;
        \?)
            printf "\n\e[31mERROR: unknown option -${OPTARG}\e[0m\n\n" >&2
            help
            exit 1
        ;;
    esac
done
shift $((OPTIND-1))

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";

cd "$BASE_DIR";

#date_suffix="$(date +%Y%m%d)"

# NB: this will break if some developer starts using spaces in file names...
files="$(find . -name '*.php' | grep -v './vendor/' | sort | tr '\n' ' ')"

for filename in $files; do
	echo "Checking $filename ..."
	output="$(php -l "$filename" 2>&1)"
    if [ $? != 0 ]; then
    	echo "**ERROR** $output" >&2
    fi
done
