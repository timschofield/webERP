<?php
/* $Revision: 1.2 $ */
/*Script to insert a dummy sales order if one is not already set up - at least one order is needed for the sales order pages to work.
Also inserts a blank company record if one is not already set up */

$title = "UTILITY PAGE That sets up a new blank company record if not already existing";
$PageSecurity=15;
include ("includes/session.inc");
include("includes/header.inc");

$sql = "SELECT Count(CoyCode) FROM Companies";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){

	$sql = "INSERT INTO Companies (CoyCode, CoyName) VALUES (1,'Enter company name')";
	$Result = DB_query($sql,$db);
} else {
	echo "<P>An existing company record is set up already. No alterations have been made";
	exit;
}

/*Need to have a sales order record set up */

$sql = "SELECT Count(OrderNo) FROM SalesOrders WHERE DebtorNo='NULL999' AND BranchCode='NULL9'";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){
	$sql= "INSERT INTO SalesOrders VALUES ( '1', 'NULL999', 'NULL9', '', NULL, NULL, '1900-01-01 00:00:00', '99', '0', '', '', '', NULL, NULL, NULL, '', '0.00', 'NULL9', '0000-00-00 00:00:00')";
	$Result = DB_query($sql,$db);
}

/*The sales GL account group needs to be set up */

$sql = "SELECT Count(GroupName) FROM AccountGroups WHERE GroupName='Sales'";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){

	$sql = "INSERT INTO AccountGroups (GroupName, SectionInAccounts, PandL, SequenceInTB) VALUES ('Sales', 1, 1, 5)";
	$Result = DB_query($sql,$db);
}

/*At least 1 GL acount needs to be set up for sales transactions */

$sql = "SELECT Count(AccountCode) FROM ChartMaster WHERE AccountCode=1";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){

	$sql = "INSERT INTO ChartMaster (AccountCode, AccountName, Group_) VALUES (1,'Default Sales and Discounts', 'Sales')";
	$Result = DB_query($sql,$db);
}

/* The default COGS GL Posting table is required */

$sql = "SELECT Count(StkCat) FROM COGSGLPostings WHERE Area='AN' AND StkCat='ANY'";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){

	$sql = "INSERT INTO COGSGLPostings (Area, StkCat, GLCode) VALUES ('AN','ANY', 1)";
	$Result = DB_query($sql,$db);
}

/* The default Sales GL Posting table is required */

$sql = "SELECT Count(StkCat) FROM SalesGLPostings WHERE Area='AN' AND StkCat='ANY'";
$Result = DB_query($sql,$db);
$myrow = DB_fetch_row($Result);
if ($myrow[0]==0){

	$sql = "INSERT INTO SalesGLPostings (Area, StkCat, DiscountGLCode, SalesGLCode) VALUES ('AN','ANY', 1, 1)";
	$Result = DB_query($sql,$db);
}


echo "<P>Company record is now available for modification by clicking <A HREF='" . $rootpath . "/CompanyPreferences.php'>this link</A>";


include("includes/footer.inc");
?>

