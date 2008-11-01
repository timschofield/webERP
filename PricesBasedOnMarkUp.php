<?php
/* $Revision: 1.1 $ */
$PageSecurity=2;


include('includes/session.inc');
$title=_('Update Pricing From Costs');
include('includes/header.inc');


echo '<BR>' . _('This page adds new prices or pdates already existing prices for a specified sales type (price list) and currency for the stock category selected - based on a percentage mark up from cost prices or from preferred supplier cost data');

echo "<FORM METHOD='POST' ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "'>";

$SQL = 'SELECT sales_type, typeabbrev FROM salestypes';

$result = DB_query($SQL,$db);

echo '<P><CENTER><TABLE>
                        <TR>
                            <TD>' . _('Select the Price List to update') .":</TD>
                            <TD><SELECT NAME='PriceList'>";

if (!isset($_POST['PriceList'])){
	echo '<OPTION SELECTED VALUE=0>' . _('No Price List Selected');
}

while ($PriceLists=DB_fetch_array($result)){
	echo "<OPTION VALUE='" . $PriceLists['typeabbrev'] . "'>" . $PriceLists['sales_type'];
}

echo '</SELECT></TD></TR>';

$SQL = 'SELECT currency, currabrev FROM currencies';

$result = DB_query($SQL,$db);

echo '<TR>
        <TD>' . _('Select the price list currency to update') . ':</TD>
                            <TD><SELECT NAME="CurrCode">';

if (!isset($_POST['CurrCode'])){
	echo '<option selected value=0>' . _('No Price List Currency Selected');
}

while ($Currencies=DB_fetch_array($result)){
	if ($Currencies['currabrev']==$_POST['CurrCode']){
		echo '<option selected value="' . $Currencies['currabrev'] . '">' . $Currencies['currency'] . '</option>';
	} else {
		echo '<option value="' . $Currencies['currabrev'] . '">' . $Currencies['currency'] . '</option>';
	}
}

echo '</select></td></tr>';

if ($_SESSION['WeightedAverageCosting']==1){
	$CostingBasis = _('Weighted Average Costs');
} else {
	$CostingBasis = _('Standard Costs');
}

echo '<tr><td>' . _('Cost or Preferred Supplier Data') . ':</td>
                <td><select name="CostType">';
if ($_POST['CostType']=='PreferredSupplier'){
     echo ' <option selected value="PreferredSupplier">' . _('Preferred Supplier Cost Data') . '</option>
            <option value="StandardCost">' . $CostingBasis . '</option>';
}else {
	 echo ' <option value="PreferredSupplier">' . _('Preferred Supplier Cost Data') . '</option>
            <option selected value="StandardCost">' . $CostingBasis . '</option>';
}
echo '</select></td></tr>';
                
echo '<TR><TD>' . _('Stock Category') . ':</TD>
                <TD><SELECT name="StkCat">';

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

if (!isset($_POST['IncreasePercent'])){
	$_POST['IncreasePercent']=0;
}

echo '<TR><TD>' . _('Percentage Increase (positive) or decrease (negative)') . "</TD>
                <TD><INPUT name='IncreasePercent' SIZE=4 MAXLENGTH=4 VALUE=" . $_POST['IncreasePercent'] . "></TD></TR></TABLE>";


echo "<P><INPUT TYPE=SUBMIT NAME='UpdatePrices' VALUE='" . _('Update Prices') . '\'  onclick="return confirm(\'' . _('Are you sure you wish to update all the prices according to the criteria selected?') . '\');"></CENTER>';

echo '</FORM>';

if (isset($_POST['UpdatePrices']) AND isset($_POST['StkCat'])){

	echo '<br>' . _('So we are using a price list/sales type of') .' : ' . $_POST['PriceList'];
	echo '<br>' . _('updating only prices in') . ' : ' . $_POST['CurrCode'];
	echo '<br>' . _('and in the stock category of') . ' : ' . $_POST['StkCat'];
	echo '<br>' . _('and we are applying a markup percent of') . ' : ' . $_POST['IncreasePercent'];
	echo '<br>' . _('against') . ' ';
	
	if ($_POST['CostType']=='PreferredSupplier'){
		echo _('Preferred Supplier Cost Data');
	} else {
		echo $CostingBasis;
	} 
		
	if ($_POST['PriceList']=='0'){
		echo '<BR>' . _('The price list/sales type to be updated must be selected first');
		include ('includes/footer.inc');
		exit;
	}
	if ($_POST['CurrCode']=='0'){
		echo '<br>' . _('The currency of prices to be updated must be selected first');
		include ('includes/footer.inc');
		exit;
	}
	if (ABS($_POST['IncreasePercent']) < 0.5 OR ABS($_POST['IncreasePercent'])>300 OR !is_numeric($_POST['IncreasePercent'])){

		echo '<BR>' . _('The increase or decrease to be applied is expected to be an integer between 1 and 300 it is not necessary to enter the % sign. The amount is assumed to be a percentage');
		include ('includes/footer.inc');
		exit;
	}
	
	$sql = "SELECT stockid, materialcost+labourcost+overheadcost AS cost FROM stockmaster WHERE categoryid='" . $_POST['StkCat'] . "'";
	$PartsResult = DB_query($sql,$db);

	$IncrementPercentage = $_POST['IncreasePercent']/100;

	$CurrenciesResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_POST['CurrCode'] . "'",$db);
	$CurrencyRow = DB_fetch_row($CurrenciesResult);
	$CurrencyRate = $CurrencyRow[0]; 

	while ($myrow=DB_fetch_array($PartsResult)){

//Figure out the cost to use
		if ($_POST['CostType']=='PreferredSupplier'){
			$sql = "SELECT purchdata.price/purchdata.conversionfactor/currencies.rate AS cost 
						FROM purchdata INNER JOIN suppliers
							ON purchdata.supplierno=suppliers.supplierid
							INNER JOIN currencies 
							ON suppliers.currcode=currencies.currabrev
						WHERE purchdata.preferred=1 AND purchdata.stockid='" . $myrow['stockid'] ."'";
			$ErrMsg = _('Could not get the supplier purchasing information for a preferred supplier for the item') . ' ' . $myrow['stockid'];
			$PrefSuppResult = DB_query($sql,$db,$ErrMsg);
			if (DB_num_rows($PrefSuppResult)==0){
				prnMsg(_('There is no preferred supplier data for the item') . ' ' . $myrow['stockid'] . ' ' . _('prices will not be updated for this item'),'warn');
				$Cost = 0;
			} elseif(DB_num_rows($PrefSuppResult)>1) {
				prnMsg(_('There is more than a single preferred supplier data for the item') . ' ' . $myrow['stockid'] . ' ' . _('prices will not be updated for this item'),'warn');
				$Cost = 0;
			} else {
				$PrefSuppRow = DB_fetch_row($PrefSuppResult);
				$Cost = $PrefSuppRow[0];	
			}
		} else { //Must be using standard/weighted average costs	  
			$Cost = $myrow['cost'];
			if ($Cost<=0){
				prnMsg(_('The cost for this item is not set up or is set up as less than or equal to zero - no price changes will be made based on zero cost items. The item concerned is:') . ' ' . $myrow['stockid'],'warn');
			}
		}
		if ($Cost > 0) {
			$CurrentPriceResult = DB_query("SELECT price FROM
											prices
											WHERE typeabbrev= '" . $_POST['PriceList'] . "' 
											AND currabrev='" . $_POST['CurrCode'] . "'
											AND stockid='" . $myrow['stockid'] . "'",$db);
			if (DB_num_rows($CurrentPriceResult)==1){
				$sql = 'UPDATE prices SET price=' . $Cost . '* (1+' . $IncrementPercentage . ')*' . $CurrencyRate . " 
						WHERE typeabbrev='" . $_POST['PriceList'] . "' 
						AND currabrev='" . $_POST['CurrCode'] . "'
						AND stockid='" . $myrow['stockid'] . "'";
				$ErrMsg =_('Error updating prices for') . ' ' . $myrow['stockid'] . ' ' . _('because');
				$result = DB_query($sql,$db,$ErrMsg);
				prnMsg(_('Updating prices for') . ' ' . $myrow['stockid'],'info');
			} else {
				$sql = "INSERT INTO prices (stockid,
											typeabbrev,
											currabrev,
											price)
								VALUES ('" . $myrow['stockid'] . "',
										'" . $_POST['PriceList'] . "',
										'" . $_POST['CurrCode'] . "',
								 		" . $Cost . '* (1+' . $IncrementPercentage . ')*' . $CurrencyRate . ")"; 
				$ErrMsg =_('Error inserting prices for') . ' ' . $myrow['stockid'] . ' ' . _('because');
				$result = DB_query($sql,$db,$ErrMsg);
				prnMsg(_('Inserting new price for') . ' ' . $myrow['stockid'],'info');
			} //end if update or insert
		}// end if cost > 0
	}//end while loop around items in the category
}
include('includes/footer.inc');
?>