#!/usr/bin/env bash

set -e

BASE_DIR="$(dirname -- "$(dirname -- "$(realpath "${BASH_SOURCE[0]}")")")";

cd "$BASE_DIR";

# xgettext: Extracts translatable strings from given input file paths

# @todo use `find` to avoid having to specify all directories manually
xgettext --no-wrap --from-code=utf-8 -L php -o locale/en_GB.utf8/LC_MESSAGES/messages.pot *.php api/*.php dashboard/*.php \
	doc/Manual/*.php includes/*.php install/*.php reportwriter/*.inc reportwriter/*.php reportwriter/admin/*.php \
	reportwriter/admin/forms/*.php reportwriter/forms/*.php reportwriter/languages/en_US/*.php

# msgmerge: Merges two Uniforum style .po files together

# @todo use a list with all the languages and a for loop
msgmerge -U -N --backup=off --no-wrap locale/ar_EG.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/ar_SY.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/cs_CZ.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/de_DE.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/el_GR.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/en_US.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/es_ES.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/et_EE.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/fa_IR.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/fi_FI.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/fr_CA.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/fr_FR.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/he_IL.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/hi_IN.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/hr_HR.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/hu_HU.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/id_ID.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/it_IT.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/ja_JP.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/ko_KR.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/lv_LV.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/mr_IN.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/nl_NL.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/pl_PL.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/pt_BR.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/pt_PT.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/ro_RO.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/ru_RU.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/sq_AL.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/sv_SE.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/sw_KE.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/tr_TR.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/vi_VN.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/zh_CN.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/zh_HK.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot
msgmerge -U -N --backup=off --no-wrap locale/zh_TW.utf8/LC_MESSAGES/messages.po locale/en_GB.utf8/LC_MESSAGES/messages.pot

# msgfmt: Generates a binary message catalog from a textual translation description

# @todo use a list with all the languages and a for loop
msgfmt -o locale/ar_EG.utf8/LC_MESSAGES/messages.mo locale/ar_EG.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/ar_SY.utf8/LC_MESSAGES/messages.mo locale/ar_SY.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/cs_CZ.utf8/LC_MESSAGES/messages.mo locale/cs_CZ.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/de_DE.utf8/LC_MESSAGES/messages.mo locale/de_DE.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/el_GR.utf8/LC_MESSAGES/messages.mo locale/el_GR.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/en_US.utf8/LC_MESSAGES/messages.mo locale/en_US.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/es_ES.utf8/LC_MESSAGES/messages.mo locale/es_ES.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/et_EE.utf8/LC_MESSAGES/messages.mo locale/et_EE.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/fa_IR.utf8/LC_MESSAGES/messages.mo locale/fa_IR.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/fi_FI.utf8/LC_MESSAGES/messages.mo locale/fi_FI.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/fr_CA.utf8/LC_MESSAGES/messages.mo locale/fr_CA.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/fr_FR.utf8/LC_MESSAGES/messages.mo locale/fr_FR.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/he_IL.utf8/LC_MESSAGES/messages.mo locale/he_IL.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/hi_IN.utf8/LC_MESSAGES/messages.mo locale/hi_IN.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/hr_HR.utf8/LC_MESSAGES/messages.mo locale/hr_HR.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/hu_HU.utf8/LC_MESSAGES/messages.mo locale/hu_HU.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/id_ID.utf8/LC_MESSAGES/messages.mo locale/id_ID.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/it_IT.utf8/LC_MESSAGES/messages.mo locale/it_IT.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/ja_JP.utf8/LC_MESSAGES/messages.mo locale/ja_JP.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/ko_KR.utf8/LC_MESSAGES/messages.mo locale/ko_KR.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/lv_LV.utf8/LC_MESSAGES/messages.mo locale/lv_LV.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/mr_IN.utf8/LC_MESSAGES/messages.mo locale/mr_IN.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/nl_NL.utf8/LC_MESSAGES/messages.mo locale/nl_NL.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/pl_PL.utf8/LC_MESSAGES/messages.mo locale/pl_PL.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/pt_BR.utf8/LC_MESSAGES/messages.mo locale/pt_BR.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/pt_PT.utf8/LC_MESSAGES/messages.mo locale/pt_PT.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/ro_RO.utf8/LC_MESSAGES/messages.mo locale/ro_RO.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/ru_RU.utf8/LC_MESSAGES/messages.mo locale/ru_RU.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/sq_AL.utf8/LC_MESSAGES/messages.mo locale/sq_AL.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/sv_SE.utf8/LC_MESSAGES/messages.mo locale/sv_SE.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/sw_KE.utf8/LC_MESSAGES/messages.mo locale/sw_KE.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/tr_TR.utf8/LC_MESSAGES/messages.mo locale/tr_TR.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/vi_VN.utf8/LC_MESSAGES/messages.mo locale/vi_VN.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/zh_CN.utf8/LC_MESSAGES/messages.mo locale/zh_CN.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/zh_HK.utf8/LC_MESSAGES/messages.mo locale/zh_HK.utf8/LC_MESSAGES/messages.po
msgfmt -o locale/zh_TW.utf8/LC_MESSAGES/messages.mo locale/zh_TW.utf8/LC_MESSAGES/messages.po
