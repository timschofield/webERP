#!/bin/bash

ROOT_DIR=$PWD
cd $ROOT_DIR/..
for f in `find . -name "*.php" -o -name "*.inc"`
do
    newname=`echo $f | cut -c3-`
    filename="$ROOT_DIR/../$newname"
    echo $filename
    output=$((php -l $filename ) 2>&1)

    if [ $? != 0 ]
    then
		echo '**Error** '$output >> ~/weberp$(date +%Y%m%d).log
		echo '' >> ~/weberp$(date +%Y%m%d).log
    fi
done
