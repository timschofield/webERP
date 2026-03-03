<?php

// INCREASE SIZE OF stockmaster.stockid FROM 20 CHAR TO 64 CHAR
//    https://github.com/timschofield/webERP/discussions/812
//      - Parts&Vendors allows 50 char
//      - @pakricard OpenCart store allows 64 char
// 
// 1. delete foreign key constraints (Bottom-Up-Drop aka BUD)
//
// SQL query to list foreign key constraints on stockmaster.stockid:
//
//     SELECT 
//         TABLE_NAME AS 'Referencing Table', 
//         COLUMN_NAME AS 'Foreign Key Column', 
//         CONSTRAINT_NAME AS 'Constraint Name'
//     FROM 
//         information_schema.KEY_COLUMN_USAGE
//     WHERE 
//         REFERENCED_TABLE_NAME = 'stockmaster' 
//         AND REFERENCED_COLUMN_NAME = 'stockid'
//         AND TABLE_SCHEMA = DATABASE(); -- Limits results to your current webERP database
//
//   +-----------------------------+--------------------+------------------------------------+
//   | Referencing Table           | Foreign Key Column | Constraint Name                    |
//   +-----------------------------+--------------------+------------------------------------+
//   | bom                         | parent             | bom_ibfk_1                         |
//   | bom                         | component          | bom_ibfk_2                         |
//   | contractbom                 | stockid            | contractbom_ibfk_3                 |
//   | custitem                    | stockid            | custitem_ibfk_1                    |
//   | locstock                    | stockid            | locstock_ibfk_2                    |
//   | loctransfers                | stockid            | loctransfers_ibfk_3                |
//   | mrpdemands                  | stockid            | mrpdemands_ibfk_2                  |
//   | offers                      | stockid            | offers_ibfk_2                      |
//   | orderdeliverydifferenceslog | stockid            | orderdeliverydifferenceslog_ibfk_1 |
//   | pickreqdetails              | stockid            | pickreqdetails_ibfk_1              |
//   | prices                      | stockid            | prices_ibfk_1                      |
//   | purchdata                   | stockid            | purchdata_ibfk_1                   |
//   | recurrsalesorderdetails     | stkcode            | recurrsalesorderdetails_ibfk_2     |
//   | salescatprod                | stockid            | salescatprod_ibfk_1                |
//   | salesorderdetails           | stkcode            | salesorderdetails_ibfk_2           |
//   | stockcheckfreeze            | stockid            | stockcheckfreeze_ibfk_1            |
//   | stockcounts                 | stockid            | stockcounts_ibfk_1                 |
//   | stockitemproperties         | stockid            | stockitemproperties_ibfk_1         |
//   | stockitemproperties         | stockid            | stockitemproperties_ibfk_3         |
//   | stockitemproperties         | stockid            | stockitemproperties_ibfk_5         |
//   | stockmoves                  | stockid            | stockmoves_ibfk_1                  |
//   | stockrequestitems           | stockid            | stockrequestitems_ibfk_2           |
//   | stockrequestitems           | stockid            | stockrequestitems_ibfk_4           |
//   | stockserialitems            | stockid            | stockserialitems_ibfk_1            |
//   | woitems                     | stockid            | woitems_ibfk_1                     |
//   | worequirements              | stockid            | worequirements_ibfk_2              |
//   +-----------------------------+--------------------+------------------------------------+
//   26 rows

// 1.1 drop grandchild fk constraints
// - indicated by error when attempting to resize grandchild foreign key columns without first deleting constraints
// DropConstraint($Table, $Constraint)
DropConstraint('stockserialmoves', 'stockserialmoves_ibfk_2'); // compound fk constraint
DropConstraint('worequirements', 'worequirements_ibfk_3'); // compound fk constraint

// 1.2 drop child fk constraints
// DropConstraint($Table, $Constraint)
DropConstraint('bom', 'bom_ibfk_1');
DropConstraint('bom', 'bom_ibfk_2');
DropConstraint('contractbom', 'contractbom_ibfk_3');
DropConstraint('custitem', 'custitem_ibfk_1');
DropConstraint('locstock', 'locstock_ibfk_2');
DropConstraint('loctransfers', 'loctransfers_ibfk_3');
DropConstraint('mrpdemands', 'mrpdemands_ibfk_2');
DropConstraint('offers', 'offers_ibfk_2');
DropConstraint('orderdeliverydifferenceslog', 'orderdeliverydifferenceslog_ibfk_1');
DropConstraint('pickreqdetails', 'pickreqdetails_ibfk_1');
DropConstraint('prices', 'prices_ibfk_1');
DropConstraint('purchdata', 'purchdata_ibfk_1');
DropConstraint('recurrsalesorderdetails', 'recurrsalesorderdetails_ibfk_2');
DropConstraint('salescatprod', 'salescatprod_ibfk_1');
DropConstraint('salesorderdetails', 'salesorderdetails_ibfk_2');
DropConstraint('stockcheckfreeze', 'stockcheckfreeze_ibfk_1');
DropConstraint('stockcounts', 'stockcounts_ibfk_1');
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_1');
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_3');
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_5');
DropConstraint('stockmoves', 'stockmoves_ibfk_1');
DropConstraint('stockrequestitems', 'stockrequestitems_ibfk_2');
DropConstraint('stockrequestitems', 'stockrequestitems_ibfk_4');
DropConstraint('stockserialitems', 'stockserialitems_ibfk_1');
DropConstraint('woitems', 'woitems_ibfk_1');
DropConstraint('worequirements', 'worequirements_ibfk_2');

// 2. change "stockid" parent, child and grandchild foreign key columns (bottom-up order, same as BUD for fk constraints)
// 2.1 change grandchild fk columns
// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
ChangeColumnSize('stockid', 'stockserialitems', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'worequirements', 'VARCHAR(64)', ' NOT NULL ', '', '64');

// 2.2 change child fk columns
// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
ChangeColumnSize('parent', 'bom', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('component', 'bom', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'contractbom', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'custitem', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'locstock', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'loctransfers',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'mrpdemands', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'offers', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'orderdeliverydifferenceslog', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'pickreqdetails', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'prices', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'purchdata', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stkcode', 'recurrsalesorderdetails', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'salescatprod', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stkcode', 'salesorderdetails', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'stockcheckfreeze', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'stockcounts', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'stockitemproperties', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'stockmoves', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'stockrequestitems', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'woitems', 'VARCHAR(64)', ' NOT NULL ', '', '64');

// 2.3 change parent key column (stockmaster.stockid)
// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
ChangeColumnSize('stockid', 'stockmaster',  'VARCHAR(64)', ' NOT NULL ', '', '64');


// 3. add (re-add) fk constraints (Top-Down-Add aka TDA)
// 3.1 add child fk constraints
// AddConstraint($Table, $Constraint, $Field, $ReferenceTable, $ReferenceField)
AddConstraint('bom', 'bom_ibfk_1', 'parent', 'stockmaster', 'stockid');
AddConstraint('bom', 'bom_ibfk_2', 'component', 'stockmaster', 'stockid');
AddConstraint('contractbom', 'contractbom_ibfk_3', 'stockid', 'stockmaster', 'stockid');
AddConstraint('custitem', 'custitem_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('locstock', 'locstock_ibfk_2', 'stockid', 'stockmaster', 'stockid');
AddConstraint('loctransfers', 'loctransfers_ibfk_3', 'stockid', 'stockmaster', 'stockid');
AddConstraint('mrpdemands', 'mrpdemands_ibfk_2', 'stockid', 'stockmaster', 'stockid');
AddConstraint('offers', 'offers_ibfk_2', 'stockid', 'stockmaster', 'stockid');
AddConstraint('orderdeliverydifferenceslog', 'orderdeliverydifferenceslog_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('pickreqdetails', 'pickreqdetails_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('prices', 'prices_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('purchdata', 'purchdata_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('recurrsalesorderdetails', 'recurrsalesorderdetails_ibfk_2', 'stkcode', 'stockmaster', 'stockid');
AddConstraint('salescatprod', 'salescatprod_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('salesorderdetails', 'salesorderdetails_ibfk_2', 'stkcode', 'stockmaster', 'stockid');
AddConstraint('stockcheckfreeze', 'stockcheckfreeze_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('stockcounts', 'stockcounts_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('stockitemproperties', 'stockitemproperties_ibfk_1', 'stockid', 'stockmaster', 'stockid');
// TODO determine if next 2 stockitemproperties constraints are redundant and if so delete
AddConstraint('stockitemproperties', 'stockitemproperties_ibfk_3', 'stockid', 'stockmaster', 'stockid');
AddConstraint('stockitemproperties', 'stockitemproperties_ibfk_5', 'stockid', 'stockmaster', 'stockid');

// TODO review foreign key constraints in stockitemproperties table for redundant constraints (and delete)

AddConstraint('stockmoves', 'stockmoves_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('stockrequestitems', 'stockrequestitems_ibfk_2', 'stockid', 'stockmaster', 'stockid');
// TODO TODO determine if next stockrequestitems constraint is redundant and if so delete
AddConstraint('stockrequestitems', 'stockrequestitems_ibfk_4', 'stockid', 'stockmaster', 'stockid');
AddConstraint('stockserialitems', 'stockserialitems_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('woitems', 'woitems_ibfk_1', 'stockid', 'stockmaster', 'stockid');
AddConstraint('worequirements', 'worequirements_ibfk_2', 'stockid', 'stockmaster', 'stockid');

// 3.2 add grandchild fk constraints
AddConstraint('stockserialmoves', 'stockserialmoves_ibfk_2', array('stockid', 'serialno'), 'stockserialitems', array('stockid', 'serialno'));
AddConstraint('worequirements', 'worequirements_ibfk_3', array('wo', 'parentstockid'), 'woitems', array('wo', 'stockid'));


// 4. change size of IMPLICIT stockid child foreign key columns
// 4.1 columns named stockid (same as parent)
// 
// Use SQL query to obtain columns named "stockid" and manually remove columns
// listed in step 1.1
//
//   SELECT 
//       TABLE_NAME, 
//       COLUMN_NAME, 
//       DATA_TYPE
//   FROM 
//       information_schema.COLUMNS
//   WHERE 
//       COLUMN_NAME = 'stockid'
//       AND TABLE_SCHEMA = DATABASE()
//       AND TABLE_NAME != 'stockmaster';
//   
//   +------------------------------+-------------+-----------+
//   | TABLE_NAME                   | COLUMN_NAME | DATA_TYPE |
//   +------------------------------+-------------+-----------+
//   | assetmanager                 | stockid     | varchar   |
//   | ediitemmapping               | stockid     | varchar   |
//   | employees                    | stockid     | varchar   |
//   | lastcostrollup               | stockid     | char      |
//   | loctransfercancellations     | stockid     | varchar   |
//   | pickserialdetails            | stockid     | varchar   |
//   | pricematrix                  | stockid     | varchar   |
//   | relateditems                 | stockid     | varchar   |
//   | salesanalysis                | stockid     | varchar   |
//   | sellthroughsupport           | stockid     | varchar   |
//   | shipmentcharges              | stockid     | varchar   |
//   | stockdescriptiontranslations | stockid     | varchar   |
//   | stockserialmoves             | stockid     | varchar   |
//   | supplierdiscounts            | stockid     | varchar   |
//   | tenderitems                  | stockid     | varchar   |
//   | woserialnos                  | stockid     | varchar   |
//   +------------------------------+-------------+-----------+
//   16 columns

// 4.1.1 drop foreign key constraint on stockserialmoves.stockid
// DropConstraint($Table, $Constraint)
DropConstraint('stockserialmoves', 'stockserialmoves_ibfk_2');  // fwiw this key was also dropped in 1.1.1 (and re-added in 1.3.2)

// 4.1.2 changes sizes of implicit child fk columns
// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
ChangeColumnSize('stockid', 'assetmanager',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'ediitemmapping',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'employees',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'lastcostrollup',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'loctransfercancellations',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'pickserialdetails',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'pricematrix',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'relateditems',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'salesanalysis',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'sellthroughsupport',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'shipmentcharges',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'stockdescriptiontranslations',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'stockserialmoves',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'supplierdiscounts',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'tenderitems',  'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockid', 'woserialnos',  'VARCHAR(64)', ' NOT NULL ', '', '64');

// 4.1.3 add (re-add) foreign key constraint
// AddConstraint($Table, $Constraint, $Field, $ReferenceTable, $ReferenceField)
AddConstraint('stockserialmoves', 'stockserialmoves_ibfk_2', array('stockid', 'serialno'), 'stockserialitems', array('stockid', 'serialno'));


// 4.2 search for related columns named stkcode and change size (because "stkcode" was used as an explit fk constraint name)
//
// Use SQL query to obtain list of constraints named "stkcode" (because the name had been used
// as an explit fk contraint) and remove explicit constraints having the same name as those found in step 1.1
//
//   SELECT 
//       TABLE_NAME, 
//       COLUMN_NAME, 
//       DATA_TYPE
//   FROM 
//       information_schema.COLUMNS
//   WHERE 
//       COLUMN_NAME = 'stkcode'
//       AND TABLE_SCHEMA = DATABASE()
//       AND TABLE_NAME != 'stockmaster';
//   	
//   +-------------------------+-------------+-----------+
//   | TABLE_NAME              | COLUMN_NAME | DATA_TYPE |
//   +-------------------------+-------------+-----------+
//   | recurrsalesorderdetails | stkcode     | varchar   |
//   | salesorderdetails       | stkcode     | varchar   |
//   +-------------------------+-------------+-----------+
//   2 rows

// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
// - no need, already changed in 2.2
//ChangeColumnSize('stkcode', 'recurrsalesorderdetails', 'VARCHAR(64)', ' NOT NULL ', '', '64');
// - no need, already changed in 2.2
//ChangeColumnSize('stkcode', 'salesorderdetails', 'VARCHAR(64)', ' NOT NULL ', '', '64');


// 4.3 search for related columns defined as "varchar(20)" (original size of stockmaster.stockid) and change size
//   - per https://github.com/timschofield/webERP/wiki/PLM-Features-Increasing-stockid-size
//   - TODO review by someone with more understanding of the code than the author
//
// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
ChangeColumnSize('related', 'relateditems', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockact', 'lastcostrollup', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('itemcode', 'purchorderdetails', 'VARCHAR(64)', ' NOT NULL ', '', '64');
ChangeColumnSize('stockact', 'stockcategory', 'VARCHAR(64)', ' NOT NULL ', '', '64');

// constraint on parentstockid has to be dropped first and re-added after changing size
DropConstraint('worequirements', 'worequirements_ibfk_3'); // compound fk constraint
ChangeColumnSize('parentstockid', 'worequirements', 'VARCHAR(64)', ' NOT NULL ', '', '64');
AddConstraint('worequirements', 'worequirements_ibfk_3', array('wo', 'parentstockid'), 'woitems', array('wo', 'stockid'));


// 4.4 search for related columns with "code" in their name and change size
//   - per https://github.com/timschofield/webERP/wiki/PLM-Features-Increasing-stockid-size
//   - TODO review by someone with more understanding of the code than the author

// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
ChangeColumnSize('itemcode', 'grns', 'VARCHAR(64)', ' NOT NULL ', '', '64');
// - no need, already changed in 4.3
//ChangeColumnSize('itemcode', 'purchorderdetails', 'VARCHAR(64)', ' NOT NULL ', '', '64');


// 4.5 search for related columns with "id" in their name and change size
//   - https://github.com/timschofield/webERP/wiki/PLM-Features-Increasing-stockid-size
//   - TODO review by someone with more understanding of the code than the author
//   - note this column is the ONLY one with the size being changed that is _NOT_ the same
//     size as stockmaster.stockid (ediitemmapping is VARCHAR(50) not VARCHAR(20)). 
//     It will be assumed the size was a mistake and not investigated further.

// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
ChangeColumnSize('partnerstockid', 'ediitemmapping', 'VARCHAR(64)', ' NOT NULL ', '', '64');


// 5. INCREASE SIZE OF stockmaster.description FROM 50 CHAR TO 255 CHAR
//    - https://github.com/timschofield/webERP/discussions/812
//    - Parts&Vendors allows 255 char
//    - @pakricard OpenCart store allows 255 char 

// ChangeColumnSize($Column, $Table, $Type, $Null, $Default, $Size)
ChangeColumnSize('description', 'stockmaster', 'VARCHAR(255)', ' NOT NULL ', '', '255');


if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Increase stockmaster.stockid and description size'));
}
