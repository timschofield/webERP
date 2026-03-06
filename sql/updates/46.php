<?php

// Add a new column for unrealized currency diffrences due to change in rate in bank accounts.
AddColumn('unrealizedcurrencydiffact', 'companies', 'varchar(20)', 'NOT NULL', '65000', 'currencyexchangediffact');

UpdateDBNo(basename(__FILE__, '.php'), __('Add Unrealized PL due to Currency Exchange Differences GL Account'));
