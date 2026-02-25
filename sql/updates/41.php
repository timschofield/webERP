<?php

// Change the name of the existing column to reflect that it is for exchange differences on sales
ChangeColumnName('exchangediffact', 'companies', 'varchar(20)', 'NOT NULL', '65000', 'salesexchangediffact', '');

// Add a new column for exchange differences on currency conversions.
AddColumn('currencyexchangediffact', 'companies', 'varchar(20)', 'NOT NULL', '65000', 'purchasesexchangediffact');

// Set the new column to the same value as the sales exchange difference account for now, to avoid breaking things. 
// The user can change it later if they want to use a different account for this.
executeSQL("UPDATE companies SET currencyexchangediffact = salesexchangediffact"); 

UpdateDBNo(basename(__FILE__, '.php'), __('Add Currency Exchange Rate Difference GL Account'));
