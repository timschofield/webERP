<?php

$SQL = "UPDATE stockmaster SET lastcostupdate='1000-01-01' WHERE lastcostupdate<'1000-01-01'";
$ErrMsg = __('There is a problem setting the default stockmaster lastcostupdate to 1000-01-01');
$Result = DB_query($SQL, $ErrMsg);

$SQL = "UPDATE salesorderdetails SET actualdispatchdate='1000-01-01' WHERE actualdispatchdate<'1000-01-01'";
$ErrMsg = __('There is a problem setting the default salesorderdetails actualdispatchdate to 1000-01-01');
$Result = DB_query($SQL, $ErrMsg);


DropColumn('actualcost', 'stockmaster');
AddCalculatedColumn('actualcost', 'stockmaster', 'decimal(20,4)', ' NOT NULL ', '(materialcost+labourcost+overheadcost)', 'overheadcost');
AddCalculatedColumn('balance', 'debtortrans', 'double', ' NOT NULL ', '(ovamount + ovgst + ovfreight + ovdiscount - alloc)', 'salesperson');
AddCalculatedColumn('pendingqty', 'loctransfers', 'double', ' NOT NULL ', '(shipqty-recqty)', 'recloc');
AddCalculatedColumn('linenetprice', 'salesorderdetails', 'double', ' NOT NULL ', '(qtyinvoiced * (unitprice * (1 - discountpercent)))', 'poline');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Create calculated fields to improve speed of access'));
}
