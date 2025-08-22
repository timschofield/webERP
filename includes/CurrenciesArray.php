<?php

trigger_error("Including CurrenciesArray.php is deprecated. Please use \webERP\CurrencyManager::getCurrencyNames() instead", E_USER_DEPRECATED);

$CurrencyName = \webERP\CurrencyManager::getCurrencyNames();
