<?php

trigger_error("Including LanguagesArray.php is deprecated. Please use \webERP\LanguageManager::getLanguagesArray() instead", E_USER_DEPRECATED);

$LanguagesArray = \webERP\LanguageManager::getLanguagesArray();
