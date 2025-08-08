#!/usr/bin/env bash

set -e

help() {
    printf "Usage: update_translations.sh
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

LOCALES="ar_EG.utf8 ar_SY.utf8 cs_CZ.utf8 de_DE.utf8 el_GR.utf8 en_US.utf8 es_ES.utf8 et_EE.utf8 fa_IR.utf8 fi_FI.utf8"
LOCALES="$LOCALES fr_CA.utf8 fr_FR.utf8 he_IL.utf8 hi_IN.utf8 hr_HR.utf8 hu_HU.utf8 id_ID.utf8 it_IT.utf8 ja_JP.utf8"
LOCALES="$LOCALES ko_KR.utf8 lv_LV.utf8 lv_LV.utf8 mr_IN.utf8 nl_NL.utf8 pl_PL.utf8 pt_BR.utf8 pt_PT.utf8 ro_RO.utf8"
LOCALES="$LOCALES ru_RU.utf8 sq_AL.utf8 sv_SE.utf8 sw_KE.utf8 tr_TR.utf8 vi_VN.utf8 zh_CN.utf8 zh_HK.utf8 zh_TW.utf8"

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";

cd "$BASE_DIR";

# xgettext: Extracts translatable strings from given input file paths

# @todo use `find` to avoid having to specify all directories manually
xgettext --no-wrap --from-code=utf-8 -L php -o locale/en_GB.utf8/LC_MESSAGES/messages.pot ./*.php ./api/*.php ./dashboard/*.php \
	./doc/Manual/*.php ./includes/*.php ./install/*.php ./install/pages/*.php ./reportwriter/*.php ./reportwriter/admin/*.php \
	./reportwriter/admin/forms/*.php ./reportwriter/forms/*.php ./reportwriter/includes/*.php ./reportwriter/install/*.php \
	./reportwriter/languages/en_US/*.php

# msgmerge: Merges two Uniforum style .po files together
for TOUPDATE in $LOCALES; do
	msgmerge -U -N --backup=off --no-wrap "./locale/${TOUPDATE}/LC_MESSAGES/messages.po" "./locale/en_GB.utf8/LC_MESSAGES/messages.pot"
done

# msgfmt: Generates a binary message catalog from a textual translation description

for TOUPDATE in $LOCALES; do
	msgfmt -o "./locale/${TOUPDATE}/LC_MESSAGES/messages.mo" "./locale/${TOUPDATE}/LC_MESSAGES/messages.po"
done
