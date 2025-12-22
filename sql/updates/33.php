<?php

AddCalculatedColumn('balance', 'supptrans', 'double', ' NOT NULL ', '(ovamount + ovgst  - alloc)', 'id');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Create calculated fields to improve speed of access'));
}
