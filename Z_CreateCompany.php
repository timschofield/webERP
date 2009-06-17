<?php
/* $Revision: 1.5 $ */
/*Script to insert a dummy sales order if one is not already set up - at least one order is needed for the sales order pages to work.
Also inserts a blank company record if one is not already set up */

$PageSecurity=15;
include ("includes/session.inc");

$title = _('UTILITY PAGE That sets up a new blank company record if not already existing');

include("includes/header.inc");

$sql = "SELECT COUNT(coycode) FROM companies";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){

	$sql = "INSERT INTO companies (coycode, coyname) VALUES (1,'Enter company name')";
	$Result = DB_query($sql,$db);
} else {
	echo '<p>' . _('An existing company record is set up already. No alterations have been made');
	exit;
}

/*Need to have a sales order record set up */

$sql = "SELECT COUNT(orderno) FROM salesorders WHERE debtorno='NULL999' AND branchcode='NULL9'";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){
	$sql= "INSERT INTO salesorders VALUES ( '1',
						'NULL999',
						'NULL9',
						'',
						NULL,
						NULL,
						'1900-01-01 00:00:00',
						'99',
						'0',
						'',
						'',
						'',
						NULL,
						NULL,
						NULL,
						'',
						'0.00',
						'NULL9',
						'0000-00-00 00:00:00')";
	$Result = DB_query($sql,$db);
}

/*The sales GL account group needs to be set up */

$sql = "SELECT COUNT(groupname) FROM accountgroups WHERE groupname='Sales'";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){

	$sql = "INSERT INTO accountgroups (groupname, sectioninaccounts, pandl, sequenceintb) VALUES ('Sales', 1, 1, 5)";
	$Result = DB_query($sql,$db);
}

/*At least 1 GL acount needs to be set up for sales transactions */

$sql = "SELECT COUNT(accountcode) FROM chartmaster WHERE accountcode=1";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){

	$sql = "INSERT INTO chartmaster (accountcode, accountname, group_) VALUES (1,'Default Sales and Discounts', 'Sales')";
	$Result = DB_query($sql,$db);
}

/* The default COGS GL Posting table is required */

$sql = "SELECT COUNT(stkcat) FROM cogsglpostings WHERE area='AN' AND stkcat='ANY'";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){

	$sql = "INSERT INTO cogsglpostings (area, stkcat, glcode) VALUES ('AN','ANY', 1)";
	$Result = DB_query($sql,$db);
}

/* The default Sales GL Posting table is required */

$sql = "SELECT COUNT(stkcat) FROM salesglpostings WHERE area='AN' AND stkcat='ANY'";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){

	$sql = "INSERT INTO salesglpostings (area, stkcat, discountglcode, salesglcode) VALUES ('AN','ANY', 1, 1)";
	$Result = DB_query($sql,$db);
}


echo '<p>' . _('Company record is now available for modification by clicking') . " <a href='" . $rootpath . "/CompanyPreferences.php'>" . _('this link') . "</a>";


include("includes/footer.inc");
?>