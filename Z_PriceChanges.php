<?php
/* $Revision: 1.2 $ */
$PageSecurity=15;

$title="Update Pricing";

include("includes/session.inc");
include("includes/header.inc");


echo "<BR>This page updates already existing prices for a specified sales type (price list). Choose between updating only  customer special prices where the customer is set up under the price list selected, or all prices under the sales type or just a specific customer's prices for the stock category selected.";

echo "<FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>";

$SQL = "SELECT Sales_Type, TypeAbbrev FROM SalesTypes";

$result = DB_query($SQL,$db);

echo "<P><CENTER><TABLE><TR><TD>Select the Price List to update the costs for:</TD><TD><SELECT NAME='PriceList'>";

if (!isset($_POST['PriceList'])){
	echo "<OPTION SELECTED VALUE=0>No Price List Selected";
}

while ($PriceLists=DB_fetch_array($result)){
	echo "<OPTION VALUE='" . $PriceLists['TypeAbbrev'] . "'>" . $PriceLists['Sales_Type'];
}

echo "</SELECT></TD></TR>";

echo "<TR><TD>Category:</TD><TD><SELECT name='StkCat'>";

$sql = "SELECT CategoryID, CategoryDescription FROM StockCategory";
$result = DB_query($sql,$db);

if (DB_error_no($db) !=0) {
	echo "<BR>The stock categories could not be retrieved because - " . DB_error_msg($db);
	if ($debug==1){
		echo "<BR>The SQL used to retrieve stock categories - and failed was :<BR>$sql";
	}
	exit;
}

while ($myrow=DB_fetch_array($result)){
	if ($myrow["CategoryID"]==$_POST['StkCat']){
		echo "<OPTION SELECTED VALUE='". $myrow["CategoryID"] . "'>" . $myrow["CategoryDescription"];
	} else {
		echo "<OPTION VALUE='". $myrow["CategoryID"] . "'>" . $myrow["CategoryDescription"];
	}
}
echo "</SELECT></TD></TR>";

echo "<TR><TD>Which Prices to update:</TD><TD><SELECT NAME='WhichPrices'>";
	echo "<OPTION VALUE='Only Non-customer special prices'>Only Non-customer special prices";
	echo "<OPTION VALUE='Only customer special prices'>Only customer special prices";
	echo "<OPTION VALUE='Both customer special prices and non-customer special prices'>Both customer special prices and non-customer special prices";
	echo "<OPTION VALUE='Selected customer special prices only'>" . $_SESSION['CustomerID'] . " customer special prices only";
echo "</SELECT></TD></TR>";

if (!isset($_POST['IncreasePercent'])){
	$_POST['IncreasePercent']=0;
}

echo "<TR><TD>Percentage Increase (positive) or decrease (negative)</TD><TD><INPUT name='IncreasePercent' SIZE=4 MAXLENGTH=4 VALUE=" . $_POST['IncreasePercent'] . "></TD></TR></TABLE>";


echo "<P><INPUT TYPE=SUBMIT NAME='UpdatePrices' VALUE='Update Prices'></CENTER>";
echo "</FORM>";

if ($_POST['UpdatePrices']=="Update Prices" AND isset($_POST['StkCat'])){

	echo "<BR>So we are using a price list/sales type of : " . $_POST['PriceList'];
	echo "<BR>and a stock category code  of : " . $_POST['StkCat'];
	echo "<BR>and a increase percent of : " . $_POST['IncreasePercent'];

	if ($_POST['PriceList']=='0'){
		echo "<BR>The price list / sales type to be updated must be selected first.";
		include ("includes/footer.inc");
		exit;
	}

	if (ABS($_POST['IncreasePercent']) < 0.5 OR ABS($_POST['IncreasePercent'])>40 OR !is_numeric($_POST['IncreasePercent'])){

		echo "<BR>The increase or decrease to be applied is expected to be an integer between 1 and 40 it is not necessary to enter the % sign - the amount is assumed to be a percentage.";
		include ("includes/footer.inc");
		exit;

	}

	echo "<P>Price list " . $_POST['PriceList'] . " prices for " . $_POST['WhichPrices'] . " for the stock category " . $_POST['StkCat'] . " will been incremented by " . $_POST['IncreasePercent'] . " percent";

	$sql = "SELECT StockID FROM StockMaster WHERE CategoryID='" . $_POST['StkCat'] . "'";
	$PartsResult = DB_query($sql,$db);

	$IncrementPercentage = $_POST['IncreasePercent']/100;

	while ($myrow=DB_fetch_array($PartsResult)){

		if ($_POST['WhichPrices'] == 'Only Non-customer special prices'){

			$sql = "UPDATE Prices SET Price=Price*(1+" . $IncrementPercentage . ") WHERE TypeAbbrev='" . $_POST['PriceList'] . "' AND StockID='" . $myrow['StockID'] . "' AND TypeAbbrev='" . $_POST['PriceList'] . "' AND DebtorNo=''";

		}else if ($_POST['WhichPrices'] == 'Only customer special prices'){

			$sql = "UPDATE Prices SET Price=Price*(1+" . $IncrementPercentage . ") WHERE TypeAbbrev='" . $_POST['PriceList'] . "' AND StockID='" . $myrow['StockID'] . "' AND TypeAbbrev='" . $_POST['PriceList'] . "' AND DebtorNo!=''";

		} else if ($_POST['WhichPrices'] == 'Both customer special prices and non-customer special prices'){

			$sql = "UPDATE Prices SET Price=Price*(1+" . $IncrementPercentage . ") WHERE TypeAbbrev='" . $_POST['PriceList'] . "' AND StockID='" . $myrow['StockID'] . "' AND TypeAbbrev='" . $_POST['PriceList'] . "'";

		} else if ($_POST['WhichPrices'] == 'Selected customer special prices only'){

			$sql = "UPDATE Prices SET Price=Price*(1+" . $IncrementPercentage . ") WHERE TypeAbbrev='" . $_POST['PriceList'] . "' AND StockID='" . $myrow['StockID'] . "' AND TypeAbbrev='" . $_POST['PriceList'] . "' AND DebtorNo='" . $_SESSION['CustomerID'] . "'";

		}

		echo "<BR>" . $sql;

		$result = DB_query($sql,$db);
		if (DB_error_no($db)!=0){
			echo "<BR>Error updating prices for " . $myrow['StockID'] . " " . DB_error_msg($db) . " occurred.";
		} else {
			echo "<BR>Updating prices for " . $myrow['StockID'];
		}
	}

}
include("includes/footer.inc");
?>

