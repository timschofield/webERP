<?php
/* $Revision: 1.7 $ */
$PageSecurity=15;


include('includes/session.inc');
$title=_('Update Pricing');
include('includes/header.inc');


echo '<BR>' . _('This page updates already existing prices for a specified sales type (price list)') . '. ' . _('Choose between updating only customer special prices where the customer is set up under the price list selected, or all prices under the sales type or just specific prices for a customer for the stock category selected');

echo "<FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";

$SQL = 'SELECT sales_type, typeabbrev FROM salestypes';

$result = DB_query($SQL,$db);

echo '<P><CENTER><TABLE>
                        <TR>
                            <TD>' . _('Select the Price List to update the costs for') .":</TD>
                            <TD><SELECT NAME='PriceList'>";

if (!isset($_POST['PriceList'])){
	echo '<OPTION SELECTED VALUE=0>' . _('No Price List Selected');
}

while ($PriceLists=DB_fetch_array($result)){
	echo "<OPTION VALUE='" . $PriceLists['typeabbrev'] . "'>" . $PriceLists['sales_type'];
}

echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Category') . ":</TD>
                <TD><SELECT name='StkCat'>";

$sql = 'SELECT categoryid, categorydescription FROM stockcategory';

$ErrMsg = _('The stock categories could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve stock categories and failed was');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

while ($myrow=DB_fetch_array($result)){
	if ($myrow['categoryid']==$_POST['StkCat']){
		echo "<OPTION SELECTED VALUE='". $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
	} else {
		echo "<OPTION VALUE='". $myrow['categoryid'] . "'>" . $myrow['categorydescription'];
	}
}
echo '</SELECT></TD></TR>';

echo '<TR><TD>' . _('Which Prices to update') . ":</TD>
                <TD><SELECT NAME='WhichPrices'>";
	echo "<OPTION VALUE='Only Non-customer special prices'>" . _('Only Non-customer special prices');
	echo "<OPTION VALUE='Only customer special prices'>" . _('Only customer special prices');
	echo "<OPTION VALUE='Both customer special prices and non-customer special prices'>" . _('Both customer special prices and non-customer special prices');
	echo "<OPTION VALUE='Selected customer special prices only'>" . $_SESSION['CustomerID'] . ' ' . _('customer special prices only');
echo '</SELECT></TD></TR>';

if (!isset($_POST['IncreasePercent'])){
	$_POST['IncreasePercent']=0;
}

echo '<TR><TD>' . _('Percentage Increase (positive) or decrease (negative)') . "</TD>
                <TD><INPUT name='IncreasePercent' SIZE=4 MAXLENGTH=4 VALUE=" . $_POST['IncreasePercent'] . "></TD></TR></TABLE>";


echo "<P><INPUT TYPE=SUBMIT NAME='UpdatePrices' VALUE='" . _('Update Prices') . '\'  onclick="return confirm(\'' . _('Are you sure you wish to update all the prices according to the criteria selected?') . '\');"></CENTER>';

echo '</FORM>';

if (isset($_POST['UpdatePrices']) AND isset($_POST['StkCat'])){

	echo '<BR>' . _('So we are using a price list/sales type of') .' : ' . $_POST['PriceList'];
	echo '<BR>' . _('and a stock category code of') . ' : ' . $_POST['StkCat'];
	echo '<BR>' . _('and a increase percent of') . ' : ' . $_POST['IncreasePercent'];

	if ($_POST['PriceList']=='0'){
		echo '<BR>' . _('The price list/sales type to be updated must be selected first');
		include ('includes/footer.inc');
		exit;
	}

	if (ABS($_POST['IncreasePercent']) < 0.5 OR ABS($_POST['IncreasePercent'])>40 OR !is_numeric($_POST['IncreasePercent'])){

		echo '<BR>' . _('The increase or decrease to be applied is expected to be an integer between 1 and 40 it is not necessary to enter the % sign') . ' - ' . _('the amount is assumed to be a percentage');
		include ('includes/footer.inc');
		exit;
	}

	echo '<P>' . _('Price list') . ' ' . $_POST['PriceList'] . ' ' . _('prices for') . ' ' . $_POST['WhichPrices'] . ' ' . _('for the stock category') . ' ' . $_POST['StkCat'] . ' ' . _('will been incremented by') . ' ' . $_POST['IncreasePercent'] . ' ' . _('percent');

	$sql = "SELECT stockid FROM stockmaster WHERE categoryid='" . $_POST['StkCat'] . "'";
	$PartsResult = DB_query($sql,$db);

	$IncrementPercentage = $_POST['IncreasePercent']/100;

	while ($myrow=DB_fetch_array($PartsResult)){

		if ($_POST['WhichPrices'] == 'Only Non-customer special prices'){

			$sql = 'UPDATE prices SET price=price*(1+' . $IncrementPercentage . ") 
					WHERE typeabbrev='" . $_POST['PriceList'] . "' 
					AND stockid='" . $myrow['stockid'] . "' 
					AND typeabbrev='" . $_POST['PriceList'] . "' 
					AND debtorno=''";

		}else if ($_POST['WhichPrices'] == 'Only customer special prices'){

			$sql = "UPDATE prices SET price=price*(1+" . $IncrementPercentage . ") 
					WHERE typeabbrev='" . $_POST['PriceList'] . "' 
					AND stockid='" . $myrow['stockid'] . "' 
					AND typeabbrev='" . $_POST['PriceList'] . "' 
					AND debtorno!=''";

		} else if ($_POST['WhichPrices'] == 'Both customer special prices and non-customer special prices'){

			$sql = "UPDATE prices SET price=price*(1+" . $IncrementPercentage . ") 
					WHERE typeabbrev='" . $_POST['PriceList'] . "' 
					AND stockd='" . $myrow['stockid'] . "' 
					AND typeabbrev='" . $_POST['PriceList'] . "'";

		} else if ($_POST['WhichPrices'] == 'Selected customer special prices only'){

			$sql = 'UPDATE prices SET price=price*(1+' . $IncrementPercentage . ") 
					WHERE typeabbrev='" . $_POST['PriceList'] . "' 
					AND stockid='" . $myrow['stockid'] . "' 
					AND typeabbrev='" . $_POST['PriceList'] . "' 
					AND debtorno='" . $_SESSION['CustomerID'] . "'";

		}

		$result = DB_query($sql,$db);
                $ErrMsg =_('Error updating prices for') . ' ' . $myrow['stockid'] . ' ' . _('because');
		prnMsg(_('Updating prices for') . ' ' . $myrow['stockid'],'info');
	}

}
include('includes/footer.inc');
?>