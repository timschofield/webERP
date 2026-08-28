<?php

ChangeColumnType('regoffice6', 'companies', 'varchar(40)', 'NOT NULL', '');
ChangeColumnType('address6', 'factorcompanies', 'varchar(40)', 'NOT NULL', '');
ChangeColumnType('deladd6', 'locations', 'varchar(40)', 'NOT NULL', '');
ChangeColumnType('deladd6', 'purchorders', 'varchar(40)', 'NOT NULL', '');
ChangeColumnType('suppdeladdress6', 'purchorders', 'varchar(40)', 'NOT NULL', '');
ChangeColumnType('deladd6', 'recurringsalesorders', 'varchar(40)', 'NOT NULL', '');
ChangeColumnType('deladd6', 'salesorders', 'varchar(40)', 'NOT NULL', '');
ChangeColumnType('address6', 'tenders', 'varchar(40)', 'NOT NULL', '');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Change varchar(15) address line 6 columns to varchar(40)'));
}
