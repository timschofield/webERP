<?php

ChangeColumnType('lastcost', 'stockmaster', 'decimal(24,8)', ' NOT NULL ', '');
ChangeColumnType('materialcost', 'stockmaster', 'decimal(24,8)', ' NOT NULL ', '');
ChangeColumnType('labourcost', 'stockmaster', 'decimal(24,8)', ' NOT NULL ', '');
ChangeColumnType('overheadcost', 'stockmaster', 'decimal(24,8)', ' NOT NULL ', '');
DropColumn('actualcost', 'stockmaster');
AddCalculatedColumn('actualcost', 'stockmaster', 'decimal(24,8)', ' NOT NULL ', '(materialcost+labourcost+overheadcost)', 'overheadcost');

ChangeColumnType('price', 'purchdata', 'decimal(24,8)', ' NOT NULL ', '');

DropTable('lastcostrollup');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Increase precision of cost fields to 8 decimal places'), true);
}
