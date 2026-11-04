<?php

$SQL = "UPDATE supptrans SET duedate='1000-01-01' WHERE duedate<'1000-01-01'";
$ErrMsg = __('There is a problem setting the default supptrans duedate to 1000-01-01');
$Result = DB_query($SQL, $ErrMsg);


AddCalculatedColumn('balance', 'supptrans', 'double', ' NOT NULL ', '(ovamount + ovgst  - alloc)', 'id');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Create calculated fields to improve speed of access'));
}
