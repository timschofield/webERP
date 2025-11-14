<?php

ChangeColumnType('noteid', 'custnotes', 'int', ' NOT NULL ', '');

ChangeColumnType('categoryid', 'stockcategory', 'varchar(6)', ' NOT NULL ', '');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Make column types agree across tables'));
}