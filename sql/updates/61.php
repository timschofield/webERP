<?php

/* Add rating scale support to performance reviews */

$SQL = "ALTER TABLE hrperformancereviews
		ADD COLUMN scaleid INT(11) DEFAULT NULL,
		ADD CONSTRAINT fk_review_scale FOREIGN KEY (scaleid) REFERENCES hrratingscales (scaleid) ON DELETE SET NULL";

DB_query($SQL, __('Failed to add scaleid to hrperformancereviews'));

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Add rating scale support to performance reviews'));
}
