<?php

require(__DIR__ . '/includes/session.php');

$Title=__('Update Pricing');
$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');

if (isset($_POST['PriceStartDate'])){$_POST['PriceStartDate'] = ConvertSQLDate($_POST['PriceStartDate']);}
if (isset($_POST['PriceEndDate'])){$_POST['PriceEndDate'] = ConvertSQLDate($_POST['PriceEndDate']);}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . __('Search') . '" alt="" />' . $Title . '</p>';

echo '<div class="page_help_text">' . __('This page adds new prices or updates already existing prices for a specified sales type (price list) and currency for the stock category selected - based on a percentage mark up from cost prices or from preferred supplier cost data or from another price list. The rounding factor ensures that prices are at least this amount or a multiple of it. A rounding factor of 5 would mean that prices would be a minimum of 5 and other prices would be expressed as multiples of 5.') . '</div><br />';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$SQL = 'SELECT sales_type, typeabbrev FROM salestypes';

$PricesResult = DB_query($SQL);

echo '<fieldset>
		<legend>', __('Price Update Criteria'), '</legend>
		<field>
			<label for="PriceList">' . __('Select the Price List to update') .':</label>
			<select name="PriceList">';

if (!isset($_POST['PriceList']) OR $_POST['PriceList']=='0'){
	echo '<option selected="selected" value="0">' . __('No Price List Selected') . '</option>';
}

while ($PriceLists=DB_fetch_array($PricesResult)){
	if (isset($_POST['PriceList']) and $_POST['PriceList']==$PriceLists['typeabbrev']){
		echo '<option selected="selected" value="' . $PriceLists['typeabbrev'] . '">' . $PriceLists['sales_type'] . '</option>';
	} else {
		echo '<option value="' . $PriceLists['typeabbrev'] . '">' . $PriceLists['sales_type'] . '</option>';
	}
}

echo '</select>
	</field>';

$SQL = "SELECT currency, currabrev FROM currencies";

$Result = DB_query($SQL);

echo '<field>
		<label for="CurrCode">' . __('Select the price list currency to update') . ':</label>
		<select name="CurrCode">';

if (!isset($_POST['CurrCode'])){
	echo '<option selected="selected" value="0">' . __('No Price List Currency Selected') . '</option>';
}

while ($Currencies=DB_fetch_array($Result)){
	if (isset($_POST['CurrCode']) and $_POST['CurrCode']==$Currencies['currabrev']) {
		echo '<option selected="selected" value="' . $Currencies['currabrev'] . '">' . $Currencies['currency'] . '</option>';
	} else {
		echo '<option value="' . $Currencies['currabrev'] . '">' . $Currencies['currency'] . '</option>';
	}
}

echo '</select>
	</field>';

if ($_SESSION['WeightedAverageCosting']==1){
	$CostingBasis = __('Weighted Average Costs');
} else {
	$CostingBasis = __('Standard Costs');
}

echo '<field>
		<label for="CostType">' . __('Cost/Preferred Supplier Data Or Other Price List') . ':</label>
		<select name="CostType">';
if ($_POST['CostType']=='PreferredSupplier'){
	 echo ' <option selected="selected" value="PreferredSupplier">' . __('Preferred Supplier Cost Data') . '</option>
			<option value="StandardCost">' . $CostingBasis . '</option>
			<option value="OtherPriceList">' . __('Another Price List') . '</option>';
} elseif ($_POST['CostType']=='StandardCost'){
	 echo ' <option value="PreferredSupplier">' . __('Preferred Supplier Cost Data') . '</option>
			<option selected="selected" value="StandardCost">' . $CostingBasis . '</option>
			<option value="OtherPriceList">' . __('Another Price List') . '</option>';
} else {
	echo ' <option value="PreferredSupplier">' . __('Preferred Supplier Cost Data') . '</option>
			<option value="StandardCost">' . $CostingBasis . '</option>
			<option selected="selected" value="OtherPriceList">' . __('Another Price List') . '</option>';
}
echo '</select>
	</field>';

DB_data_seek($PricesResult,0);

if (isset($_POST['CostType']) and $_POST['CostType']=='OtherPriceList'){
	 echo '<field>
			<label for="BasePriceList">' . __('Select the Base Price List to Use') . ':</label>
			<select name="BasePriceList">';

	if (!isset($_POST['BasePriceList']) OR $_POST['BasePriceList']=='0'){
		echo '<option selected="selected" value="0">' . __('No Price List Selected') . '</option>';
	}
	while ($PriceLists=DB_fetch_array($PricesResult)){
		if (isset($_POST['BasePriceList']) AND $_POST['BasePriceList']==$PriceLists['typeabbrev']){
			echo '<option selected="selected" value="' . $PriceLists['typeabbrev'] . '">' . $PriceLists['sales_type'] . '</option>';
		} else {
			echo '<option value="' . $PriceLists['typeabbrev'] . '">' . $PriceLists['sales_type'] . '</option>';
		}
	}
	echo '</select>
		</field>';
}

echo '<field>
		<label for="StkCatFrom">' . __('Stock Category From') . ':</label>
		<select name="StkCatFrom">';

$SQL = "SELECT categoryid, categorydescription FROM stockcategory ORDER BY categoryid";

$ErrMsg = __('The stock categories could not be retrieved because');
$Result = DB_query($SQL, $ErrMsg);

while ($MyRow=DB_fetch_array($Result)){
	if (isset($_POST['StkCatFrom']) and $MyRow['categoryid']==$_POST['StkCatFrom']){
		echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';
	} else {
		echo '<option value="' . $MyRow['categoryid'] . '">'  . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';
	}
}
echo '</select>
	</field>';

DB_data_seek($Result,0);

echo '<field>
		<label for="StkCatTo">' . __('Stock Category To') . ':</label>
		<select name="StkCatTo">';

while ($MyRow=DB_fetch_array($Result)){
	if (isset($_POST['StkCatFrom']) and $MyRow['categoryid']==$_POST['StkCatTo']){
		echo '<option selected="selected" value="'. $MyRow['categoryid'] . '">' . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';
	} else {
		echo '<option  value="'. $MyRow['categoryid'] . '">'  . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';
	}
}
echo '</select>
	</field>';

if (!isset($_POST['RoundingFactor'])){
	$_POST['RoundingFactor']=0.01;
}

if (!isset($_POST['PriceStartDate'])) {
	$_POST['PriceStartDate']=DateAdd(date($_SESSION['DefaultDateFormat']),'d',1);
}

if (!isset($_POST['PriceEndDate'])) {
	$_POST['PriceEndDate']=DateAdd(date($_SESSION['DefaultDateFormat']), 'y', 1);
}

echo '<field>
		<label for="RoundingFactor">' . __('Rounding Factor') . ':</label>
		<input type="text" class="number" name="RoundingFactor" size="6" title="" maxlength="6" value="' . $_POST['RoundingFactor'] . '" />
		<fieldhelp>' . __('To round to the nearest cent enter 0.01. To round to the nearest whole dollar enter 1. To round to the nearest 5 dollars enter 5 etc') . '</fieldhelp>
	</field>';

echo '<field>
		<label for="PriceStartDate">' . __('New Price To Be Effective From') . ':</label>
		<input type="date" name="PriceStartDate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['PriceStartDate']) . '" />
	</field>';

echo '<field>
		<label for="PriceEndDate">' . __('New Price To Be Effective To (Blank = No End Date)') . ':</label>
		<input type="date" name="PriceEndDate" size="11" maxlength="10" value="' . FormatDateForSQL($_POST['PriceEndDate']) . '" />
	</field>';

if (!isset($_POST['IncreasePercent'])){
	$_POST['IncreasePercent']=0;
}

echo '<field>
		<label for="IncreasePercent">' . __('Percentage Increase (positive) or decrease (negative)') . '</label>
		<input type="text" name="IncreasePercent" class="number" size="4" maxlength="4" value="' . $_POST['IncreasePercent'] . '" />
	</field>
</fieldset>';

echo '<div class="centre">
		<input type="submit" name="UpdatePrices" value="' . __('Update Prices') . '"  onclick="return confirm(\'' . __('Are you sure you wish to update or add all the prices according to the criteria selected?') . '\');" />
	</div>';

echo '</form>';

if (isset($_POST['UpdatePrices'])){
	$InputError =0; //assume the best
	if ($_POST['PriceList']=='0'){
		prnMsg(__('No price list is selected to update. No updates will take place'),'error');
		$InputError =1;
	}
	if ($_POST['CurrCode']=='0'){
		prnMsg(__('No price list currency is selected to update. No updates will take place'),'error');
		$InputError =1;
	}

	if (! Is_Date($_POST['PriceEndDate']) AND $_POST['PriceEndDate']!=''){
		$InputError =1;
		prnMsg(__('The date the new price is to be in effect to must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (! Is_Date($_POST['PriceStartDate'])){
		$InputError =1;
		prnMsg(__('The date this price is to take effect from must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
	}
	if (Date1GreaterThanDate2($_POST['PriceStartDate'],$_POST['PriceEndDate']) AND $_POST['PriceEndDate']!=''){
		$InputError =1;
		prnMsg(__('The end date is expected to be after the start date, enter an end date after the start date for this price'),'error');
	}
	if (Date1GreaterThanDate2(Date($_SESSION['DefaultDateFormat']),$_POST['PriceStartDate'])){
		$InputError =1;
		prnMsg(__('The date this new price is to start from is expected to be after today'),'error');
	}
	if ($_POST['StkCatTo']<$_POST['StkCatFrom']){
		prnMsg(__('The stock category from must be before the stock category to - there would be not items in the range to update'),'error');
		$InputError =1;
	}
	if ($_POST['CostType']=='OtherPriceList' AND $_POST['BasePriceList']=='0'){
		echo '<br />' . __('Base price list selected') . ': ' .$_POST['BasePriceList'];
		prnMsg(__('When you are updating prices based on another price list - the other price list must also be selected. No updates will take place until the other price list is selected'),'error');
		$InputError =1;
	}
	if ($_POST['CostType']=='OtherPriceList' AND $_POST['BasePriceList']==$_POST['PriceList']){
		prnMsg(__('When you are updating prices based on another price list - the other price list cannot be the same as the price list being used for the calculation. No updates will take place until the other price list selected is different from the price list to be updated' ),'error');
		$InputError =1;
	}

	if ($InputError==0) {
		prnMsg(__('For a log of all the prices changed this page should be printed with CTRL+P'),'info');
		echo '<br />' . __('So we are using a price list/sales type of') .' : ' . $_POST['PriceList'];
		echo '<br />' . __('updating only prices in') . ' : ' . $_POST['CurrCode'];
		echo '<br />' . __('and the stock category range from') . ' : ' . $_POST['StkCatFrom'] . ' ' . __('to') . ' ' . $_POST['StkCatTo'];
		echo '<br />' . __('and we are applying a markup percent of') . ' : ' . $_POST['IncreasePercent'];
		echo '<br />' . __('against') . ' ';

		if ($_POST['CostType']=='PreferredSupplier'){
			echo __('Preferred Supplier Cost Data');
		} elseif ($_POST['CostType']=='OtherPriceList') {
			echo __('Price List')  . ' ' . $_POST['BasePriceList'];
		} else {
			echo $CostingBasis;
		}

		if ($_POST['PriceList']=='0'){
			echo '<br />' . __('The price list/sales type to be updated must be selected first');
			include('includes/footer.php');
			exit();
		}
		if ($_POST['CurrCode']=='0'){
			echo '<br />' . __('The currency of prices to be updated must be selected first');
			include('includes/footer.php');
			exit();
		}
		if (Is_Date($_POST['PriceEndDate'])){
			$SQLEndDate = FormatDateForSQL($_POST['PriceEndDate']);
		} else {
			$SQLEndDate = '9999-12-31';
		}
		$SQL = "SELECT stockid,
						actualcost AS cost
				FROM stockmaster
				WHERE categoryid>='" . $_POST['StkCatFrom'] . "'
				AND categoryid <='" . $_POST['StkCatTo'] . "'";
		$PartsResult = DB_query($SQL);

		$IncrementPercentage = filter_number_format($_POST['IncreasePercent']/100);

		$CurrenciesResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_POST['CurrCode'] . "'");
		$CurrencyRow = DB_fetch_row($CurrenciesResult);
		$CurrencyRate = $CurrencyRow[0];

		while ($MyRow=DB_fetch_array($PartsResult)){

	//Figure out the cost to use
			if ($_POST['CostType']=='PreferredSupplier'){
				$SQL = "SELECT purchdata.price/purchdata.conversionfactor/currencies.rate AS cost
							FROM purchdata INNER JOIN suppliers
								ON purchdata.supplierno=suppliers.supplierid
								INNER JOIN currencies
								ON suppliers.currcode=currencies.currabrev
							WHERE purchdata.preferred=1 AND purchdata.stockid='" . $MyRow['stockid'] ."'";
				$ErrMsg = __('Could not get the supplier purchasing information for a preferred supplier for the item') . ' ' . $MyRow['stockid'];
				$PrefSuppResult = DB_query($SQL, $ErrMsg);
				if (DB_num_rows($PrefSuppResult)==0){
					prnMsg(__('There is no preferred supplier data for the item') . ' ' . $MyRow['stockid'] . ' ' . __('prices will not be updated for this item'),'warn');
					$Cost = 0;
				} elseif(DB_num_rows($PrefSuppResult)>1) {
					prnMsg(__('There is more than a single preferred supplier data for the item') . ' ' . $MyRow['stockid'] . ' ' . __('prices will not be updated for this item'),'warn');
					$Cost = 0;
				} else {
					$PrefSuppRow = DB_fetch_row($PrefSuppResult);
					$Cost = $PrefSuppRow[0];
				}
			} elseif ($_POST['CostType']=='OtherPriceList'){
				$SQL = "SELECT price FROM
								prices
							WHERE typeabbrev= '" . $_POST['BasePriceList'] . "'
								AND currabrev='" . $_POST['CurrCode'] . "'
								AND debtorno=''
								AND startdate <= CURRENT_DATE
								AND enddate >= CURRENT_DATE
								AND stockid='" . $MyRow['stockid'] . "'
							ORDER BY startdate DESC";
				$ErrMsg = __('Could not get the base price for the item') . ' ' . $MyRow['stockid'] . __('from the price list') . ' ' . $_POST['BasePriceList'];
				$BasePriceResult = DB_query($SQL, $ErrMsg);
				if (DB_num_rows($BasePriceResult)==0){
					prnMsg(__('There is no default price defined in the base price list for the item') . ' ' . $MyRow['stockid'] . ' ' . __('prices will not be updated for this item'),'warn');
					$Cost = 0;
				} else {
					$BasePriceRow = DB_fetch_row($BasePriceResult);
					$Cost = $BasePriceRow[0];
				}
			} else { //Must be using standard/weighted average costs
				$Cost = $MyRow['cost'];
				if ($Cost<=0){
					prnMsg(__('The cost for this item is not set up or is set up as less than or equal to zero - no price changes will be made based on zero cost items. The item concerned is:') . ' ' . $MyRow['stockid'],'warn');
				}
			}
			$_POST['RoundingFactor'] = filter_number_format($_POST['RoundingFactor']);
			if ($_POST['CostType']!='OtherPriceList'){
				$RoundedPrice = round(($Cost * (1+ $IncrementPercentage) * $CurrencyRate+($_POST['RoundingFactor']/2))/$_POST['RoundingFactor']) * $_POST['RoundingFactor'];
				if ($RoundedPrice <=0){
					$RoundedPrice = $_POST['RoundingFactor'];
				}
			} else {
				$RoundedPrice = round(($Cost * (1+ $IncrementPercentage)+($_POST['RoundingFactor']/2))/$_POST['RoundingFactor']) * $_POST['RoundingFactor'];
				if ($RoundedPrice <=0){
					$RoundedPrice = $_POST['RoundingFactor'];
				}
			}

			if ($Cost > 0) {
				$CurrentPriceResult = DB_query("SELECT price,
											 		   startdate,
													   enddate
													FROM prices
													WHERE typeabbrev= '" . $_POST['PriceList'] . "'
													AND debtorno =''
													AND currabrev='" . $_POST['CurrCode'] . "'
													AND startdate <= CURRENT_DATE
													AND enddate >= CURRENT_DATE
													AND stockid='" . $MyRow['stockid'] . "'");
				if (DB_num_rows($CurrentPriceResult)==1){
					$DayPriorToNewPrice = DateAdd($_POST['PriceStartDate'],'d',-1);
					$CurrentPriceRow = DB_fetch_array($CurrentPriceResult);
					$UpdateSQL = "UPDATE prices SET enddate='" . FormatDateForSQL($DayPriorToNewPrice) . "'
												WHERE typeabbrev='" . $_POST['PriceList'] . "'
												AND currabrev='" . $_POST['CurrCode'] . "'
												AND debtorno=''
												AND startdate ='" . $CurrentPriceRow['startdate'] . "'
												AND enddate ='" . $CurrentPriceRow['enddate'] . "'
												AND stockid='" . $MyRow['stockid'] . "'";
					$ErrMsg =__('Error updating prices for') . ' ' . $MyRow['stockid'] . ' ' . __('because');
					$Result = DB_query($UpdateSQL, $ErrMsg);

				}
				$SQL = "INSERT INTO prices (stockid,
												typeabbrev,
												currabrev,
												startdate,
												enddate,
												price)
								VALUES ('" . $MyRow['stockid'] . "',
										'" . $_POST['PriceList'] . "',
										'" . $_POST['CurrCode'] . "',
										'" . FormatDateForSQL($_POST['PriceStartDate']) . "',
										'" . $SQLEndDate . "',
								 		'" . filter_number_format($RoundedPrice) . "')";
				$ErrMsg =__('Error inserting new price for') . ' ' . $MyRow['stockid'] . ' ' . __('because');
				$Result = DB_query($SQL, $ErrMsg);
				prnMsg(__('Inserting new price for') . ' ' . $MyRow['stockid'] . ' ' . __('to') . ' ' . $RoundedPrice,'info');

			}// end if cost > 0
		}//end while loop around items in the category
	}
}
include('includes/footer.php');
