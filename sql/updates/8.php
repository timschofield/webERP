<?php

DropColumn('actualcost', 'stockmaster');
AddCalculatedColumn('actualcost', 'stockmaster', 'decimal(20,4)', ' NOT NULL ', '(materialcost+labourcost+overheadcost)', 'overheadcost');
AddCalculatedColumn('balance', 'debtortrans', 'double', ' NOT NULL ', '(ovamount + ovgst + ovfreight + ovdiscount - alloc)', 'salesperson');
AddCalculatedColumn('pendingqty', 'loctransfers', 'double', ' NOT NULL ', '(shipqty-recqty)', 'recloc');
AddCalculatedColumn('linenetprice', 'salesorderdetails', 'double', ' NOT NULL ', '(qtyinvoiced * (unitprice * (1 - discountpercent)))', 'poline');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Create calculated fields to improve speed of access'));
}
