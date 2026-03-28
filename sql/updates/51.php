<?php

// INCREASE SIZE OF custitem.cust_part and custitem.cust_description to same as stockmaster.stockid and stockmaster.description
//
// The custitem.cust_part and custitem.cust_description table columns are the part code and description used by our customer in their system.
// The justification for this change is that webERP should assume the same data sizes for a customer system as it uses for itself.
// 
// Note custitem.customersuom is CHAR(50) is larger than unitsofmeasure.unitname (VARCHAR(15)) and will not be changed.
//
// References:
//   https://github.com/timschofield/webERP/issues/882
//   https://github.com/timschofield/webERP/discussions/812#discussioncomment-15980990


// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
ChangeColumnSize('cust_part', 'custitem',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('cust_description', 'custitem', 'VARCHAR(255)', ' NOT NULL ', '', '255');

if ($_SESSION['Updates']['Errors'] == 0) {
  UpdateDBNo(basename(__FILE__, '.php'), __('Increase customer part and description size'));
}
