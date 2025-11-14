<?php

ChangeColumnType('noteid', 'custnotes', 'int', ' NOT NULL ', '');

DropConstraint('contracts', 'contracts_ibfk_2');
DropConstraint('stockmaster', 'stockmaster_ibfk_1');
DropConstraint('stockcatproperties', 'stockcatproperties_ibfk_1');
DropConstraint('internalstockcatrole', 'internalstockcatrole_ibfk_1');
DropConstraint('internalstockcatrole', 'internalstockcatrole_ibfk_3');

ChangeColumnType('categoryid', 'stockcategory', 'varchar(6)', ' NOT NULL ', '');

ChangeColumnType('categoryid', 'contracts', 'varchar(6)', ' NOT NULL ', '');
ChangeColumnType('categoryid', 'stockmaster', 'varchar(6)', ' NOT NULL ', '');
ChangeColumnType('categoryid', 'stockcatproperties', 'varchar(6)', ' NOT NULL ', '');
ChangeColumnType('categoryid', 'internalstockcatrole', 'varchar(6)', ' NOT NULL ', '');

AddConstraint('contracts', 'contracts_ibfk_2', 'categoryid', 'stockcategory', 'categoryid');
AddConstraint('stockmaster', 'stockmaster_ibfk_1', 'categoryid', 'stockcategory', 'categoryid');
AddConstraint('stockcatproperties', 'stockcatproperties_ibfk_1', 'categoryid', 'stockcategory', 'categoryid');
AddConstraint('internalstockcatrole', 'internalstockcatrole_ibfk_1', 'categoryid', 'stockcategory', 'categoryid');
AddConstraint('internalstockcatrole', 'internalstockcatrole_ibfk_3', 'categoryid', 'stockcategory', 'categoryid');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Make column types agree across tables'));
}