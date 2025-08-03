#!/bin/sh

TEMPLATE=pigs.pot
SOURCE=pigs_dropin.php

help() {
  echo "Usage: $0 [-p|<basename>]"
  echo "  using no arg will generate translation file $TEMPLATE from translatable strings in $SOURCE"
  echo "  using -p will generate binary messages.mo file from $TEMPLATE"
  echo "  passing in a filename will merge the new po file into the given one then generate messages.mo"
}

if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
  help
  exit 0
fi

if [ "$1" = "" ]; then
  xgettext -kngettext:1,2 -k_ -L PHP -o "$TEMPLATE" "$SOURCE"
elif [ "$1" = "-p" ]; then
  msgfmt --statistics "$TEMPLATE"
else
  if [ -f "$1.po" ]; then
	  msgmerge -o ".tmp$1.po" "$1.po" $TEMPLATE
	  mv ".tmp$1.po" "$1.po"
	  msgfmt --statistics "$1.po"
  else
	  help
	  exit 1
  fi
fi
