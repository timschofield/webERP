<?php

ChangeColumnType('lastcost', 'stockmaster', 'decimal(24,8)', ' NOT NULL ', '0');
ChangeColumnType('materialcost', 'stockmaster', 'decimal(24,8)', ' NOT NULL ', '0');
ChangeColumnType('labourcost', 'stockmaster', 'decimal(24,8)', ' NOT NULL ', '0');
ChangeColumnType('overheadcost', 'stockmaster', 'decimal(24,8)', ' NOT NULL ', '0');
DropColumn('actualcost', 'stockmaster');
AddCalculatedColumn('actualcost', 'stockmaster', 'decimal(24,8)', ' NOT NULL ', '(materialcost+labourcost+overheadcost)', 'overheadcost');

ChangeColumnType('price', 'purchdata', 'decimal(24,8)', ' NOT NULL ', '0');

DropTable('lastcostrollup');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Increase precision of cost fields to 8 decimal places'));
}
