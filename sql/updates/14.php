<?php

DropColumn('tag', 'pcexpenses');
DropColumn('tag', 'pcashdetails');
DropColumn('defaulttag', 'pctabs');

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), _('Remove redundant fields from petty cash tables'));
}

?>