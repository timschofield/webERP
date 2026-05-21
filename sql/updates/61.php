<?php

/* Add rating scale support to performance reviews */
AddColumn('scaleid', 'hrperformancereviews', 'INT(11)', ' NULL', '', 'modifieddate');
AddConstraint('hrperformancereviews', 'hrperformancereviews_ibfk_1', 'scaleid', 'hrratingscales', 'scaleid');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Add rating scale support to performance reviews'));
}
