<?php

// Remove space characters from constraint (foreign key) names in custitem table
// created by installer after commit 81252b5 August 2025.
// https://github.com/timschofield/webERP/discussions/812#discussioncomment-15568263

if (ConstraintExists('custitem', ' custitem _ibfk_1')) {
	DropConstraint('custitem', ' custitem _ibfk_1');
	AddConstraint('custitem', 'custitem_ibfk_1', 'stockid', 'stockmaster', 'stockid');
	};

if (ConstraintExists('custitem', ' custitem _ibfk_2')) {
	DropConstraint('custitem', ' custitem _ibfk_2');
	AddConstraint('custitem', 'custitem_ibfk_2', 'debtorno', 'debtorsmaster', 'debtorno');
	};

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Correct custitem table fk names'));
}
