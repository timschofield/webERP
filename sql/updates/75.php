<?php

/* See https://github.com/timschofield/webERP/pull/1034

Duplicate of later additions to 8/12/33. The late additions to 8/12/33
  will not run for systems already upgraded, those systems either didn't
  have old/bad 0000-00-00 dates or their maintainers worked around the failed
  updates. 
For systems that have been upgraded and still have old/bad 0000-00-00 dates, 
  this will clean those up going forward.
Running these twice has no negative affects (a system not prior updated 
  and getting 8/12/33/75).
*/


//8.php
$SQL = "UPDATE stockmaster SET lastcostupdate='1000-01-01' WHERE lastcostupdate<'1000-01-01'";
$ErrMsg = __('There is a problem setting the default stockmaster lastcostupdate to 1000-01-01');
$Result = DB_query($SQL, $ErrMsg);

$SQL = "UPDATE salesorderdetails SET actualdispatchdate='1000-01-01' WHERE actualdispatchdate<'1000-01-01'";
$ErrMsg = __('There is a problem setting the default salesorderdetails actualdispatchdate to 1000-01-01');
$Result = DB_query($SQL, $ErrMsg);


//12.php
$SQL = "UPDATE pricematrix SET enddate='9999-12-31' WHERE enddate<='1000-01-02'";
$ErrMsg = __('There is a problem setting the default pricematrix enddate to 9999-12-31');
$Result = DB_query($SQL, $ErrMsg);


//33.php
$SQL = "UPDATE supptrans SET duedate='1000-01-01' WHERE duedate<'1000-01-01'";
$ErrMsg = __('There is a problem setting the default supptrans duedate to 1000-01-01');
$Result = DB_query($SQL, $ErrMsg);


if ($_SESSION['Updates']['Errors'] == 0) {
	UpdateDBNo(basename(__FILE__, '.php'), __('Create calculated fields to improve speed of access'));
}



