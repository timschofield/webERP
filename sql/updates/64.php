<?php

AddColumn('positionid', 'hrperformancecriteria', 'int(11)', 'NOT NULL', '', 'description');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('HR Module updates'));
}
