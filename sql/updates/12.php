<?php

$SQL = "UPDATE pricematrix SET enddate='9999-12-31' WHERE enddate='0000-00-00'";
$ErrMsg = _('There is a problem setting the default pricematrix enddate to 9999-12-31');
$Result = DB_query($SQL, $ErrMsg);

if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), _('Default end date for pricematrix set to 9999-12-31'));
}

?>