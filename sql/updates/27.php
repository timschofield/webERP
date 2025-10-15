<?php

// Drop invalid foreign keys that violate MySQL 8.4+ requirements
// MySQL 8.4 and later require foreign keys to reference columns with unique constraints

// Drop qasamples_ibfk_1: references prodspecs.keyval which is not unique
// (keyval is part of composite PK with testid, so keyval alone is not unique)
DropConstraint('qasamples', 'qasamples_ibfk_1');

// Drop pickserialdetails_ibfk_2: references stockserialitems(stockid, serialno) which is not unique
// (stockserialitems PK is stockid, serialno, loccode - so stockid+serialno alone is not unique)
DropConstraint('pickserialdetails', 'pickserialdetails_ibfk_2');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Drop foreign keys that violate MySQL 8.4+ unique constraint requirements'));
}
