<?php

/*This page is very largely the same as the SupplierInvoice.php script
the same result could have been acheived by using if statements in that script and just having the one
SupplierTransaction.php script. However, to aid readability - variable names have been changed  -
and reduce clutter (in the form of a heap of if statements) two separate scripts have been used,
both with very similar code.

This does mean that if the logic is to be changed for supplier transactions then it needs to be changed
in both scripts.

This is widely considered poor programming but in my view, much easier to read for the uninitiated

*/

/*The supplier transaction uses the SuppTrans class to hold the information about the credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing and also
an array of GLCodes objects - only used if the AP - GL link is effective */

include('includes/DefineSuppTransClass.php');

require(__DIR__ . '/includes/session.php');

$Title = __('Supplier Credit Note');
$ViewTopic = 'AccountsPayable';
$BookMark = '';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');
include('includes/StockFunctions.php');
include('includes/GLFunctions.php');

if (isset($_POST['TranDate'])){$_POST['TranDate'] = ConvertSQLDate($_POST['TranDate']);}

if (isset($_GET['New'])) {
	unset($_SESSION['SuppTrans']);
}

if (!isset($_SESSION['SuppTrans']->SupplierName)) {
	$SQL="SELECT suppname FROM suppliers WHERE supplierid='" . $_GET['SupplierID']."'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	$SupplierName=$MyRow[0];
} else {
	$SupplierName=$_SESSION['SuppTrans']->SupplierName;
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . __('Supplier Credit Note') . '" alt="" />' . ' '
		. __('Enter Supplier Credit Note:') . ' ' . $SupplierName;
echo '</p>';
if (isset($_GET['SupplierID']) and $_GET['SupplierID']!=''){

 /*It must be a new credit note entry - clear any existing credit note details from the SuppTrans object and initiate a newy*/

	if (isset($_SESSION['SuppTrans'])){
		unset ($_SESSION['SuppTrans']->GRNs);
		unset ($_SESSION['SuppTrans']->Shipts);
		unset ($_SESSION['SuppTrans']->GLCodes);
		unset($_SESSION['SuppTrans']->Assets);
		unset ($_SESSION['SuppTrans']);
	}

	 if (isset( $_SESSION['SuppTransTmp'])){
		unset ( $_SESSION['SuppTransTmp']->GRNs);
		unset ( $_SESSION['SuppTransTmp']->GLCodes);
		unset ( $_SESSION['SuppTransTmp']);
	}
	 $_SESSION['SuppTrans'] = new SuppTrans;

/*Now retrieve supplier information - name, currency, default ex rate, terms, tax rate etc */

	 $SQL = "SELECT suppliers.suppname,
					suppliers.supplierid,
					paymentterms.terms,
					paymentterms.daysbeforedue,
					paymentterms.dayinfollowingmonth,
					suppliers.currcode,
					currencies.rate AS exrate,
					currencies.decimalplaces AS currdecimalplaces,
					suppliers.taxgroupid,
					taxgroups.taxgroupdescription
				FROM suppliers INNER JOIN taxgroups
				ON suppliers.taxgroupid=taxgroups.taxgroupid
				INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
				INNER JOIN paymentterms
				ON suppliers.paymentterms=paymentterms.termsindicator
				WHERE suppliers.supplierid = '" . $_GET['SupplierID'] . "'";

	$ErrMsg = __('The supplier record selected') . ': ' . $_GET['SupplierID'] . ' ' .__('cannot be retrieved because');

	$Result = DB_query($SQL, $ErrMsg);

	$MyRow = DB_fetch_array($Result);

	$_SESSION['SuppTrans']->SupplierName = $MyRow['suppname'];
	$_SESSION['SuppTrans']->TermsDescription = $MyRow['terms'];
	$_SESSION['SuppTrans']->CurrCode = $MyRow['currcode'];
	$_SESSION['SuppTrans']->ExRate = $MyRow['exrate'];
	$_SESSION['SuppTrans']->TaxGroup = $MyRow['taxgroupid'];
	$_SESSION['SuppTrans']->TaxGroupDescription = $MyRow['taxgroupdescription'];
	$_SESSION['SuppTrans']->SupplierID = $MyRow['supplierid'];
	$_SESSION['SuppTrans']->CurrDecimalPlaces = $MyRow['currdecimalplaces'];

	if ($MyRow['daysbeforedue'] == 0){
		 $_SESSION['SuppTrans']->Terms = '1' . $MyRow['dayinfollowingmonth'];
	} else {
		 $_SESSION['SuppTrans']->Terms = '0' . $MyRow['daysbeforedue'];
	}
	$_SESSION['SuppTrans']->SupplierID = $_GET['SupplierID'];

	$LocalTaxProvinceResult = DB_query("SELECT taxprovinceid
										FROM locations
										WHERE loccode = '" . $_SESSION['UserStockLocation'] . "'");

	if(DB_num_rows($LocalTaxProvinceResult)==0){
		prnMsg(__('The tax province associated with your user account has not been set up in this database. Tax calculations are based on the tax group of the supplier and the tax province of the user entering the invoice. The system administrator should redefine your account with a valid default stocking location and this location should refer to a valid tax province'),'error');
		include('includes/footer.php');
		exit();
	}

	$LocalTaxProvinceRow = DB_fetch_row($LocalTaxProvinceResult);
	$_SESSION['SuppTrans']->LocalTaxProvince = $LocalTaxProvinceRow[0];

	$_SESSION['SuppTrans']->GetTaxes();


	$_SESSION['SuppTrans']->GLLink_Creditors = $_SESSION['CompanyRecord']['gllink_creditors'];
	$_SESSION['SuppTrans']->GRNAct = $_SESSION['CompanyRecord']['grnact'];
	$_SESSION['SuppTrans']->CreditorsAct = $_SESSION['CompanyRecord']['creditorsact'];

	$_SESSION['SuppTrans']->InvoiceOrCredit = 'Credit Note'; //note no gettext going on here

} elseif (!isset($_SESSION['SuppTrans'])){

	prnMsg(__('To enter a supplier credit note the supplier must first be selected from the supplier selection screen'),'warn');
	echo '<br /><a href="' . $RootPath . '/SelectSupplier.php">' . __('Select A Supplier to Enter an Credit Note For') . '</a>';
	include('includes/footer.php');
	exit();
	/*It all stops here if there aint no supplier selected */
}

/* Set the session variables to the posted data from the form if the page has called itself */

if (isset($_POST['ExRate'])){
	$_SESSION['SuppTrans']->ExRate = filter_number_format($_POST['ExRate']);
	$_SESSION['SuppTrans']->Comments = $_POST['Comments'];
	$_SESSION['SuppTrans']->TranDate = $_POST['TranDate'];

	if (mb_substr( $_SESSION['SuppTrans']->Terms,0,1)=='1') { /*Its a day in the following month when due */
		$DayInFollowingMonth = (int) mb_substr( $_SESSION['SuppTrans']->Terms,1);
		$DaysBeforeDue = 0;
	} else { /*Use the Days Before Due to add to the invoice date */
		$DayInFollowingMonth = 0;
		$DaysBeforeDue = (int) mb_substr( $_SESSION['SuppTrans']->Terms,1);
	}

	$_SESSION['SuppTrans']->DueDate = CalcDueDate($_SESSION['SuppTrans']->TranDate, $DayInFollowingMonth, $DaysBeforeDue);

	$_SESSION['SuppTrans']->SuppReference = $_POST['SuppReference'];


	if ( $_SESSION['SuppTrans']->GLLink_Creditors == 1){

/*The link to GL from creditors is active so the total should be built up from GLPostings and GRN entries
if the link is not active then OvAmount must be entered manually. */

		$_SESSION['SuppTrans']->OvAmount = 0; /* for starters */
		if (count($_SESSION['SuppTrans']->GRNs) > 0){
			foreach ( $_SESSION['SuppTrans']->GRNs as $GRN){
				$_SESSION['SuppTrans']->OvAmount = $_SESSION['SuppTrans']->OvAmount + ($GRN->This_QuantityInv * $GRN->ChgPrice);
			}
		}
		if (count($_SESSION['SuppTrans']->GLCodes) > 0){
			foreach ( $_SESSION['SuppTrans']->GLCodes as $GLLine){
				$_SESSION['SuppTrans']->OvAmount +=  $GLLine->Amount;
			}
		}
		if (count($_SESSION['SuppTrans']->Contracts) > 0){
			foreach ( $_SESSION['SuppTrans']->Contracts as $Contract){
				$_SESSION['SuppTrans']->OvAmount +=  $Contract->Amount;
			}
		}
		if (count($_SESSION['SuppTrans']->Shipts) > 0){
			foreach ( $_SESSION['SuppTrans']->Shipts as $ShiptLine){
				$_SESSION['SuppTrans']->OvAmount +=  $ShiptLine->Amount;
			}
		}
		if (count($_SESSION['SuppTrans']->Assets) > 0){
			foreach ( $_SESSION['SuppTrans']->Assets as $FixedAsset){
				$_SESSION['SuppTrans']->OvAmount +=  $FixedAsset->Amount;
			}
		}
		$_SESSION['SuppTrans']->OvAmount = round($_SESSION['SuppTrans']->OvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces);
	} else {
/*OvAmount must be entered manually */
		 $_SESSION['SuppTrans']->OvAmount = round(filter_number_format($_POST['OvAmount']),$_SESSION['SuppTrans']->CurrDecimalPlaces);
	}
}

if (isset($_POST['GRNS'])
	AND $_POST['GRNS'] == __('Purchase Orders')){

	/*This ensures that any changes in the page are stored in the session before calling the grn page */

	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/SuppCreditGRNs.php">';
	echo '<br />' .
		__('You should automatically be forwarded to the entry of credit notes against goods received page') . '. ' . __('If this does not happen') . ' (' . __('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/SuppCreditGRNs.php">' . __('click here') . '</a> ' . __('to continue') . '.
		<br />';
	include('includes/footer.php');
	exit();
}
if (isset($_POST['Shipts'])){

	/*This ensures that any changes in the page are stored in the session before calling the shipments page */

	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/SuppShiptChgs.php">';
	echo '<br />
		' . __('You should automatically be forwarded to the entry of credit notes against shipments page') . '. ' . __('If this does not happen') . ' (' . __('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/SuppShiptChgs.php">' . __('click here') . '</a> ' . __('to continue') . '.
		<br />';
	include('includes/footer.php');
	exit();
}
if (isset($_POST['GL'])
	AND $_POST['GL'] == __('General Ledger')){

	/*This ensures that any changes in the page are stored in the session before calling the shipments page */

	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/SuppTransGLAnalysis.php">';
	echo '<br />
		' . __('You should automatically be forwarded to the entry of credit notes against the general ledger page') . '. ' . __('If this does not happen') . ' (' . __('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/SuppTransGLAnalysis.php">' . __('click here') . '</a> ' . __('to continue') . '.
		<br />';
	include('includes/footer.php');
	exit();
}
if (isset($_POST['Contracts'])
	AND $_POST['Contracts'] == __('Contracts')){
		/*This ensures that any changes in the page are stored in the session before calling the shipments page */
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/SuppContractChgs.php">';
		echo '<div class="centre">
				' . __('You should automatically be forwarded to the entry of supplier credit notes against contracts page') . '. ' . __('If this does not happen') . ' (' . __('if the browser does not support META Refresh'). ') ' . '<a href="' . $RootPath . '/SuppContractChgs.php">' . __('click here') . '</a> ' . __('to continue') . '.
			</div>
			<br />';
		exit();
}
if (isset($_POST['FixedAssets'])
	AND $_POST['FixedAssets'] == __('Fixed Assets')){
		/*This ensures that any changes in the page are stored in the session before calling the shipments page */
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/SuppFixedAssetChgs.php">';
		echo '<div class="centre">
				' . __('You should automatically be forwarded to the entry of invoices against fixed assets page') . '. ' . __('If this does not happen') . ' (' . __('if the browser does not support META Refresh'). ') ' . '<a href="' . $RootPath . '/SuppFixedAssetChgs.php">' . __('click here') . '</a> ' . __('to continue') . '.
			</div>
			<br />';
		exit();
}
/* everything below here only do if a Supplier is selected
   fisrt add a header to show who we are making an credit note for */

echo '<table class="selection">
		<tr><th>' . __('Supplier') . '</th>
			<th>' . __('Currency') . '</th>
			<th>' . __('Terms') . '</th>
			<th>' . __('Tax Group') . '</th>
		</tr>';

echo '<tr>
		<th><b>' . $_SESSION['SuppTrans']->SupplierID . ' - ' . $_SESSION['SuppTrans']->SupplierName . '</b></th>
		<th><b>' .  $_SESSION['SuppTrans']->CurrCode . '</b></th>
		<td><b>' . $_SESSION['SuppTrans']->TermsDescription . '</b></td>
		<td><b>' . $_SESSION['SuppTrans']->TaxGroupDescription . '</b></td>
	</tr>
	</table>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" id="form1">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Credit Note Header'), '</legend>';
echo '<field>
		<label style="color:red">' . __('Supplier Credit Note Reference') . ':</label>
		<input type="text" required="required" size="20" maxlength="20" name="SuppReference" value="' . $_SESSION['SuppTrans']->SuppReference . '" />
	</field>';

if (!isset($_SESSION['SuppTrans']->TranDate)){
	$_SESSION['SuppTrans']->TranDate= Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d')-1,Date('y')));
}
echo '<field>
		<label style="color:red">' . __('Credit Note Date') . ') :</label>
		<input type="date" size="11" maxlength="10" name="TranDate" value="' . FormatDateForSQL($_SESSION['SuppTrans']->TranDate) . '" />
	</field>
	<field>
		<label style="color:red">' . __('Exchange Rate') . ':</label>
		<input type="text" class="number" size="11" maxlength="10" name="ExRate" value="' . locale_number_format($_SESSION['SuppTrans']->ExRate,'Variable') . '" />
	</field>
	</fieldset>';

echo '<div class="centre">
		<input type="submit" name="GRNS" value="' . __('Purchase Orders') . '"/>
		<input type="submit" name="Shipts" value="' . __('Shipments') . '" />
		<input type="submit" name="Contracts" value="' . __('Contracts') . '" /> ';
if ( $_SESSION['SuppTrans']->GLLink_Creditors ==1){
	echo '<input type="submit" name="GL" value="' . __('General Ledger') . '" /> ';
}
echo '<input type="submit" name="FixedAssets" value="' . __('Fixed Assets') . '" />
	</div>';

if (count($_SESSION['SuppTrans']->GRNs)>0){   /*if there are some GRNs selected for crediting then */

	/*Show all the selected GRNs so far from the SESSION['SuppInv']->GRNs array
	Note that the class for carrying GRNs refers to quantity invoiced read credited in this context*/

	echo '<table class="selection">
		<tr><th colspan="6">' . __('Purchase Order Credits') . '</th></tr>';
	$TableHeader = '<tr><th>' . __('GRN') . '</th>
					<th>' . __('Item Code') . '</th>
					<th>' . __('Description') . '</th>
					<th>' . __('Quantity') . '<br />' . __('Credited') . '</th>
					<th>' . __('Price Credited') . '<br />' . __('in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
					<th>' . __('Line Total') . '<br />' . __('in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
				</tr>';
	echo $TableHeader;
	$TotalGRNValue=0;

	foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

		echo '<tr><td>' . $EnteredGRN->GRNNo . '</td>
			<td>' . $EnteredGRN->ItemCode . '</td>
			<td>' . $EnteredGRN->ItemDescription . '</td>
			<td class="number">' . locale_number_format($EnteredGRN->This_QuantityInv,2) . '</td>
			<td class="number">' . locale_number_format($EnteredGRN->ChgPrice,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			<td class="number">' . locale_number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			<td></tr>';

		$TotalGRNValue = $TotalGRNValue + ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

	}

	echo '<tr><td colspan="5" class="number">' . __('Total Value of Goods Credited') . ':</td>
		<td class="number"><U>' . locale_number_format($TotalGRNValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</U></td></tr>';
	echo '</table>
		<br />';
}

if (count($_SESSION['SuppTrans']->Shipts)>0){   /*if there are any Shipment charges on the credit note*/

		echo '<table class="selection">
				<tr>
					<th colspan="2">' . __('Shipment Credits') . '</th>
				</tr>';
		$TableHeader = '<tr>
						<th>' . __('Shipment') . '</th>
						<th>' . __('Amount') . '</th>
					</tr>';
		echo $TableHeader;

	$TotalShiptValue=0;

	$i=0;

	foreach ($_SESSION['SuppTrans']->Shipts as $EnteredShiptRef){

		echo '<tr>
				<td>' . $EnteredShiptRef->ShiptRef . '</td>
				<td class="number">' . locale_number_format($EnteredShiptRef->Amount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			</tr>';
		$TotalShiptValue +=  $EnteredShiptRef->Amount;
	}

	echo '<tr>
			<td class="number" style="color:red">' . __('Total Credited Against Shipments') .  ':</td>
			<td class="number" style="color:red">' . locale_number_format($TotalShiptValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) .  '</td>
		</tr>
		</table><br />';
}

if (count( $_SESSION['SuppTrans']->Assets) > 0){   /*if there are any fixed assets on the invoice*/

	echo '<br />
		<table class="selection">
		<tr>
			<th colspan="3">' . __('Fixed Asset Credits') . '</th>
		</tr>';
	$TableHeader = '<tr><th>' . __('Asset ID') . '</th>
						<th>' . __('Description') . '</th>
						<th>' . __('Amount') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th></tr>';
	echo $TableHeader;

	$TotalAssetValue = 0;

	foreach ($_SESSION['SuppTrans']->Assets as $EnteredAsset){

		echo '<tr><td>' . $EnteredAsset->AssetID . '</td>
				<td>' . $EnteredAsset->Description . '</td>
				<td class="number">' .	locale_number_format($EnteredAsset->Amount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td></tr>';

		$TotalAssetValue += $EnteredAsset->Amount;

		$i++;
		if ($i > 15){
			$i = 0;
			echo $TableHeader;
		}
	}

	echo '<tr>
			<td colspan="2" class="number" style="color:red">' . __('Total') . ':</td>
			<td class="number" style="color:red">' .  locale_number_format($TotalAssetValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
		</tr>
		</table>';
} //end loop around fixed assets


if (count( $_SESSION['SuppTrans']->Contracts) > 0){   /*if there are any contract charges on the invoice*/

	echo '<table class="selection">
			<tr>
				<th colspan="3">' . __('Contract Charges') . '</th>
			</tr>';
	$TableHeader = '<tr><th>' . __('Contract') . '</th>
						<th>' . __('Narrative') . '</th>
						<th>' . __('Amount') . '<br />' . __('in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
					</tr>';
	echo $TableHeader;

	$TotalContractsValue = 0;
	$i=0;
	foreach ($_SESSION['SuppTrans']->Contracts as $Contract){

		echo '<tr><td>' . $Contract->ContractRef . '</td>
				<td>' . $Contract->Narrative . '</td>
				<td class="number">' . 	locale_number_format($Contract->Amount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			</tr>';

		$TotalContractsValue += $Contract->Amount;

		$i++;
		if ($i == 15){
			$i = 0;
			echo $TableHeader;
		}
	}

	echo '<tr><td class="number" colspan="2" style="color:red">' . __('Total Credited against Contracts') . ':</td>
			<td class="number">' .  locale_number_format($TotalContractsValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			</tr></table><br />';
}


if ($_SESSION['SuppTrans']->GLLink_Creditors ==1){

	if (count($_SESSION['SuppTrans']->GLCodes)>0){
		echo '<table class="selection">
			<tr>
				<th colspan="3">' . __('General Ledger Analysis') . '</th>
			</tr>';
		$TableHeader = '<tr>
							<th>' . __('Account') . '</th>
							<th>' . __('Account Name') . '</th>
							<th>' . __('Narrative') . '</th>
							<th>' . __('Tag') . '</th>
							<th>' . __('Amount') . '<br />' . __('in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
						</tr>';
		echo $TableHeader;

		$TotalGLValue=0;
		$i = 0;

		foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

			$DescriptionTag = GetDescriptionsFromTagArray($EnteredGLCode->Tag);

			echo '<tr>
					<td>' . $EnteredGLCode->GLCode . '</td>
					<td>' . $EnteredGLCode->GLActName . '</td>
					<td>' . $EnteredGLCode->Narrative . '</td>
					<td>' . $DescriptionTag . '</td>
					<td class="number">' . locale_number_format($EnteredGLCode->Amount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
					</tr>';

			$TotalGLValue += $EnteredGLCode->Amount;

			$i++;
			if ($i>15){
				$i=0;
				echo $TableHeader;
			}
		}

		echo '<tr>
				<td colspan="4" class="number" style="color:red">' . __('Total GL Analysis') . ':</td>
				<td class="number" style="color:red">' . locale_number_format($TotalGLValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
			</tr>
			</table>';
	}

	if (!isset($TotalGRNValue)) {
		$TotalGRNValue=0;
	}
	if (!isset($TotalGLValue)) {
		$TotalGLValue=0;
	}
	if (!isset($TotalShiptValue)) {
		$TotalShiptValue=0;
	}
	if (!isset($TotalContractsValue)){
		$TotalContractsValue = 0;
	}
	if (!isset($TotalAssetValue)){
			$TotalAssetValue = 0;
	}
	$_SESSION['SuppTrans']->OvAmount = round($TotalGRNValue + $TotalGLValue + $TotalAssetValue + $TotalShiptValue + $TotalContractsValue,$_SESSION['SuppTrans']->CurrDecimalPlaces);

	echo '<fieldset>
			<legend>', __('Credit Note Summary'), '</legend>
			<field>
				<label style="color:red">' . __('Credit Amount in Supplier Currency') . ':</label>
				<fieldtext class="number">' . locale_number_format($_SESSION['SuppTrans']->OvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces), '</fieldtext>
				<input type="hidden" name="OvAmount" value="' . locale_number_format($_SESSION['SuppTrans']->OvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />
			</field>';
} else {
	echo '<fieldset>
			<legend>', __('Credit Note Summary'), '</legend>
			<field>
				<label style="color:red">' . __('Credit Amount in Supplier Currency') .':</label>
				<input type="text" size="12" class="number" maxlength="10" name="OvAmount" value="' . locale_number_format($_SESSION['SuppTrans']->OvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />
			</field>';
}

echo '<field>
		<label><input type="submit" name="ToggleTaxMethod" value="' . __('Update Tax Calculation') .  '" /></label>
		<select name="OverRideTax" onchange="ReloadForm(form1.ToggleTaxMethod)">';

if (isset($_POST['OverRideTax']) AND $_POST['OverRideTax']=='Man'){
	echo '<option value="Auto">' . __('Automatic') . '</option>
			<option selected="selected" value="Man">' . __('Manual Entry') . '</option>';
} else {
	echo '<option selected="selected" value="Auto">' . __('Automatic') . '</option>
			<option value="Man">' . __('Manual Entry') . '</option>';
}

echo '</select>
	</field>';
$TaxTotal =0; //initialise tax total

foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {

	echo '<field>
			<label>'  . $Tax->TaxAuthDescription . '</label>';

	/*Set the tax rate to what was entered */
	if (isset($_POST['TaxRate'  . $Tax->TaxCalculationOrder])){
		$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate = filter_number_format($_POST['TaxRate'  . $Tax->TaxCalculationOrder])/100;
	}

	/*If a tax rate is entered that is not the same as it was previously then recalculate automatically the tax amounts */

	if (!isset($_POST['OverRideTax'])
		OR $_POST['OverRideTax']=='Auto'){

		echo  ' <input type="text" class="number" name="TaxRate' . $Tax->TaxCalculationOrder . '" maxlength="4" size="4" value="' . locale_number_format($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100,2) . '" />%';

		/*Now recaluclate the tax depending on the method */
		if ($Tax->TaxOnTax ==1){

			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * ($_SESSION['SuppTrans']->OvAmount + $TaxTotal);

		} else { /*Calculate tax without the tax on tax */

			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * $_SESSION['SuppTrans']->OvAmount;

		}

		echo '<input type="hidden" name="TaxAmount'  . $Tax->TaxCalculationOrder . '"  value="' . round($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />';

		echo '</td><td class="number">  =  ' . locale_number_format($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces);

	} else { /*Tax being entered manually accept the taxamount entered as is*/
		$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = filter_number_format($_POST['TaxAmount'  . $Tax->TaxCalculationOrder]);

		echo  ' <input type="hidden" name="TaxRate' . $Tax->TaxCalculationOrder . '" value="' . locale_number_format($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />';


		echo '</td>
				<td><input type="text" class="number" size="12" maxlength="12" name="TaxAmount'  . $Tax->TaxCalculationOrder . '"  value="' . locale_number_format(round($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces),$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />';

	}

	$TaxTotal += $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount;


	echo '</field>';
	}

$DisplayTotal = locale_number_format($_SESSION['SuppTrans']->OvAmount + $TaxTotal,$_SESSION['SuppTrans']->CurrDecimalPlaces);

echo '<field>
		<label style="color:red">' . __('Credit Note Total') . '</label>
		<fieldtext><b>' . $DisplayTotal. '</b></fieldtext>
	</field>';

echo '<field>
		<label style="color:red">' . __('Comments') . '</label>
		<textarea name="Comments" cols="40" rows="2">' . $_SESSION['SuppTrans']->Comments . '</textarea>
	</field>
</fieldset>';

echo '<div class="centre">
		<input type="submit" name="PostCreditNote" value="' . __('Enter Credit Note') . '" />
	</div>';


if (isset($_POST['PostCreditNote'])){

/*First do input reasonableness checks
then do the updates and inserts to process the credit note entered */
	$TaxTotal =0;
	foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {

		/*Set the tax rate to what was entered */
		if (isset($_POST['TaxRate'  . $Tax->TaxCalculationOrder])){
			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate = filter_number_format($_POST['TaxRate'  . $Tax->TaxCalculationOrder])/100;
		}


		if ($_POST['OverRideTax']=='Auto' OR !isset($_POST['OverRideTax'])){

			/*Now recaluclate the tax depending on the method */
			if ($Tax->TaxOnTax ==1){

				$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * ($_SESSION['SuppTrans']->OvAmount + $TaxTotal);

			} else { /*Calculate tax without the tax on tax */

				$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * $_SESSION['SuppTrans']->OvAmount;
			}

		} else { /*Tax being entered manually accept the taxamount entered as is*/

			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = filter_number_format($_POST['TaxAmount'  . $Tax->TaxCalculationOrder]);
		}
		$TaxTotal += $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount;
	}

	$InputError = false;
	if ( $TaxTotal + $_SESSION['SuppTrans']->OvAmount <= 0){
		$InputError = true;
		prnMsg(__('The credit note as entered cannot be processed because the total amount of the credit note is less than or equal to 0') . '. ' . 	__('Credit notes are expected to be entered as positive amounts to credit'),'warn');
	} elseif (mb_strlen($_SESSION['SuppTrans']->SuppReference) < 1){
		$InputError = true;
		prnMsg(__('The credit note as entered cannot be processed because the there is no suppliers credit note number or reference entered') . '. ' . __('The supplier credit note number must be entered'),'error');
	} elseif (!Is_Date($_SESSION['SuppTrans']->TranDate)){
		$InputError = true;
		prnMsg(__('The credit note as entered cannot be processed because the date entered is not in the format') . ' ' . $_SESSION['DefaultDateFormat'], 'error');
	} elseif (DateDiff(Date($_SESSION['DefaultDateFormat']), $_SESSION['SuppTrans']->TranDate, 'd') < 0){
		$InputError = true;
		prnMsg(__('The credit note as entered cannot be processed because the date is after today') . '. ' . __('Purchase credit notes are expected to have a date prior to or today'),'error');
	}elseif ($_SESSION['SuppTrans']->ExRate <= 0){
		$InputError = true;
		prnMsg(__('The credit note as entered cannot be processed because the exchange rate for the credit note has been entered as a negative or zero number') . '. ' . __('The exchange rate is expected to show how many of the suppliers currency there are in 1 of the local currency'),'warn');
	}elseif ($_SESSION['SuppTrans']->OvAmount < round($TotalShiptValue + $TotalGLValue + $TotalAssetValue + $TotalGRNValue,$_SESSION['SuppTrans']->CurrDecimalPlaces)){
		prnMsg(__('The credit note total as entered is less than the sum of the shipment charges') . ', ' . __('the general ledger entries (if any) and the charges for goods received') . '. ' . __('There must be a mistake somewhere') . ', ' . __('the credit note as entered will not be processed'),'error');
		$InputError = true;
	} else {

	/* SQL to process the postings for purchase credit note */

	/*Start an SQL transaction */

		DB_Txn_Begin();

		/*Get the next transaction number for internal purposes and the period to post GL transactions in based on the credit note date*/

		$CreditNoteNo = GetNextTransNo(21);
		$PeriodNo = GetPeriod($_SESSION['SuppTrans']->TranDate);
		$SQLCreditNoteDate = FormatDateForSQL($_SESSION['SuppTrans']->TranDate);


		if ($_SESSION['SuppTrans']->GLLink_Creditors == 1){

		/*Loop through the GL Entries and create a debit posting for each of the accounts entered */

			$LocalTotal = 0;

			/*the postings here are a little tricky, the logic goes like this:

			> if its a shipment entry then the cost must go against the GRN suspense account defined in the company record

			> if its a general ledger amount it goes straight to the account specified

			> if its a GRN amount credited then there are two possibilities:

			1 The PO line is on a shipment.
			The whole charge goes to the GRN suspense account pending the closure of the
			shipment where the variance is calculated on the shipment as a whole and the clearing entry to the GRN suspense
			is created. Also, shipment records are created for the charges in local currency.

			2. The order line item is not on a shipment
			The whole amount of the credit is written off to the purchase price variance account applicable to the
			stock category record of the stock item being credited.
			Or if its not a stock item but a nominal item then the GL account in the orignal order is used for the
			price variance account.
			*/

			foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

			/*GL Items are straight forward - just do the credit postings to the GL accounts specified -
			the debit is to creditors control act  done later for the total credit note value + tax*/

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
								 	VALUES (21,
										'" . $CreditNoteNo . "',
										'" . $SQLCreditNoteDate . "',
										'" . $PeriodNo . "',
										'" . $EnteredGLCode->GLCode . "',
										'" . mb_substr($_SESSION['SuppTrans']->SupplierID . " " . $EnteredGLCode->Narrative, 0, 200) . "',
								 		'" . -$EnteredGLCode->Amount/$_SESSION['SuppTrans']->ExRate ."')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction could not be added because');

				$Result = DB_query($SQL, $ErrMsg, '', true);
				InsertGLTags($EnteredGLCode->Tag);

				$LocalTotal += ($EnteredGLCode->Amount/$_SESSION['SuppTrans']->ExRate);
			}

			foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg){

			/*shipment postings are also straight forward - just do the credit postings to the GRN suspense account
			these entries are reversed from the GRN suspense when the shipment is closed - entries only to open shipts*/

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
								VALUES (21,
									'" . $CreditNoteNo . "',
									'" . $SQLCreditNoteDate . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['SuppTrans']->GRNAct . "',
									'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' ' .	 __('Shipment credit against') . ' ' . $ShiptChg->ShiptRef, 0, 200) . "',
									'" . -$ShiptChg->Amount/$_SESSION['SuppTrans']->ExRate . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction for the shipment') . ' ' . $ShiptChg->ShiptRef . ' ' . __('could not be added because');

				$Result = DB_query($SQL, $ErrMsg, '', true);

				$LocalTotal += $ShiptChg->Amount/$_SESSION['SuppTrans']->ExRate;

			}

			foreach ($_SESSION['SuppTrans']->Assets as $AssetAddition){
				/* only the GL entries if the creditors->GL integration is enabled */
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES ('21',
										'" . $CreditNoteNo . "',
										'" . $SQLCreditNoteDate . "',
										'" . $PeriodNo . "',
										'". $AssetAddition->CostAct . "',
										'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' ' . __('Asset Credit') . ' ' . $AssetAddition->AssetID . ': '  . $AssetAddition->Description, 0, 200) . "',
										'" . -$AssetAddition->Amount/ $_SESSION['SuppTrans']->ExRate . "')";
				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction for the asset addition could not be added because');
 				$Result = DB_query($SQL, $ErrMsg, '', true);

 				$LocalTotal += $AssetAddition->Amount/ $_SESSION['SuppTrans']->ExRate;
			}

			foreach ($_SESSION['SuppTrans']->Contracts as $Contract){

			/*contract postings need to get the WIP from the contract item's stock category record
			 *  debit postings to this WIP account
			 * the WIP account is tidied up when the contract is closed*/
				$Result = DB_query("SELECT wipact FROM stockcategory
									INNER JOIN stockmaster
									ON stockcategory.categoryid=stockmaster.categoryid
									WHERE stockmaster.stockid='" . $Contract->ContractRef . "'");
				$WIPRow = DB_fetch_row($Result);
				$WIPAccount = $WIPRow[0];

				$SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
							VALUES (21,
								'" .$CreditNoteNo . "',
								'" . $SQLCreditNoteDate. "',
								'" . $PeriodNo . "',
								'". $WIPAccount . "',
								'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' ' . __('Contract charge against') . ' ' . $Contract->ContractRef, 0, 200) . "',
								'" . (-$Contract->Amount/ $_SESSION['SuppTrans']->ExRate) . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction for the contract') . ' ' . $Contract->ContractRef . ' ' . __('could not be added because');

				$Result = DB_query($SQL, $ErrMsg, '', true);

				$LocalTotal += ($Contract->Amount/ $_SESSION['SuppTrans']->ExRate);

			}

			foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

				if (mb_strlen($EnteredGRN->ShiptRef)==0
					OR $EnteredGRN->ShiptRef==''
					OR $EnteredGRN->ShiptRef==0){ /*so its not a shipment item */
				/*so its not a shipment item
				  enter the GL entry to reverse the GRN suspense entry created on delivery at standard cost used on delivery */

					if ($EnteredGRN->StdCostUnit * $EnteredGRN->This_QuantityInv != 0) {
						$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
								VALUES ('21',
									'" . $CreditNoteNo . "',
									'" . $SQLCreditNoteDate . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['SuppTrans']->GRNAct . "',
									'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' - ' . __('GRN Credit Note') . ' ' . $EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemCode . ' x ' . $EnteredGRN->This_QuantityInv . ' @  ' . __('std cost of') . ' ' . $EnteredGRN->StdCostUnit, 0, 200) . "',
								 	'" . (-$EnteredGRN->StdCostUnit * $EnteredGRN->This_QuantityInv) . "')";

						$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction could not be added because');

						$Result = DB_query($SQL, $ErrMsg, '', true);

					}

				  $PurchPriceVar = $EnteredGRN->This_QuantityInv * (($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit);
					/*Yes but where to post this difference to - if its a stock item the variance account must be retrieved from the stock category record
					if its a nominal purchase order item with no stock item then  post it to the account specified in the purchase order detail record */

					if ($PurchPriceVar !=0){ /* don't bother with this lot if there is no difference ! */
						if (mb_strlen($EnteredGRN->ItemCode)>0 OR $EnteredGRN->ItemCode != ''){ /*so it is a stock item */

							/*need to get the stock category record for this stock item - this is function in SQL_CommonFunctions.php */
							$StockGLCode = GetStockGLCode($EnteredGRN->ItemCode);

							/*We have stock item and a purchase price variance need to see whether we are using Standard or WeightedAverageCosting */

							if ($_SESSION['WeightedAverageCosting']==1){ /*Weighted Average costing */

								/* First off figure out the new weighted average cost Need the following data:
								- How many in stock now
								- The quantity being invoiced here - $EnteredGRN->This_QuantityInv
								- The cost of these items - $EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate */

								$TotalQuantityOnHand = GetQuantityOnHand($EnteredGRN->ItemCode, 'ALL');

								/*The cost adjustment is the price variance / the total quantity in stock
								But thats only provided that the total quantity in stock is greater than the quantity charged on this invoice

								If the quantity on hand is less the amount charged on this invoice then some must have been sold and the price variance on these must be written off to price variances*/

								$WriteOffToVariances =0;

								if ($EnteredGRN->This_QuantityInv > $TotalQuantityOnHand){

									/*So we need to write off some of the variance to variances and only the balance of the quantity in stock to go to stock value */

									$WriteOffToVariances =  ($EnteredGRN->This_QuantityInv
										- $TotalQuantityOnHand)
									* (($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit);

									$SQL = "INSERT INTO gltrans (type,
																typeno,
																trandate,
																periodno,
																account,
																narrative,
																amount)
														VALUES (21,
															'" . $CreditNoteNo . "',
															'" . $SQLCreditNoteDate . "',
															'" . $PeriodNo . "',
															'" . $StockGLCode['purchpricevaract'] . "',
															'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' - ' . __('GRN Credit Note') . ' ' . $EnteredGRN->GRNNo .' - ' . $EnteredGRN->ItemCode . ' x ' . ($EnteredGRN->This_QuantityInv-$TotalQuantityOnHand) . ' x  ' . __('price var of') . ' ' . locale_number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,$_SESSION['CompanyRecord']['decimalplaces']), 0, 200) ."',
															'" . (-$WriteOffToVariances) . "')";

									$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction could not be added for the price variance of the stock item because');

									$Result = DB_query($SQL, $ErrMsg, '', true);
								}
								/*Now post any remaining price variance to stock rather than price variances */

								$SQL = "INSERT INTO gltrans (type,
															typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
													VALUES (21,
												'" . $CreditNoteNo . "',
												'" . $SQLCreditNoteDate . "',
												'" . $PeriodNo . "',
												'" . $StockGLCode['stockact'] . "',
												'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' - ' . __('Average Cost Adj') .
												' - ' . $EnteredGRN->ItemCode . ' x ' . $TotalQuantityOnHand  . ' x ' .
												locale_number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,$_SESSION['CompanyRecord']['decimalplaces']), 0, 200) . "',
												'" . (-($PurchPriceVar - $WriteOffToVariances)) . "')";

								$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction could not be added for the price variance of the stock item because');

								$Result = DB_query($SQL, $ErrMsg, '', true);

								/*Now to update the stock cost with the new weighted average */

								/*Need to consider what to do if the cost has been changed manually between receiving the stock and entering the invoice - this code assumes there has been no cost updates made manually and all the price variance is posted to stock.

								A nicety or important?? */


								$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The cost could not be updated because');

								if ($TotalQuantityOnHand>0) {

									$CostIncrement = ($PurchPriceVar - $WriteOffToVariances) / $TotalQuantityOnHand;

									$SQL = "UPDATE stockmaster SET lastcost=materialcost+overheadcost+labourcost,
																	materialcost=materialcost-" . $CostIncrement . "
											WHERE stockid='" . $EnteredGRN->ItemCode . "'";

									$Result = DB_query($SQL, $ErrMsg, '', true);
								} else {
									$SQL = "UPDATE stockmaster SET lastcost=materialcost+overheadcost+labourcost,
																	materialcost=" . ($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) . " WHERE stockid='" . $EnteredGRN->ItemCode . "'";
									$Result = DB_query($SQL, $ErrMsg, '', true);
								}
								/* End of Weighted Average Costing Code */

							} else { //It must be Standard Costing

								$SQL = "INSERT INTO gltrans (type,
															typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
													VALUES (21,
														'" .  $CreditNoteNo . "',
														'" . $SQLCreditNoteDate . "',
														'" . $PeriodNo . "',
														'" . $StockGLCode['purchpricevaract'] . "',
														'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' - ' . __('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemCode . ' x ' . $EnteredGRN->This_QuantityInv . ' x  ' . __('price var of') . ' ' . locale_number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,$_SESSION['CompanyRecord']['decimalplaces']), 0, 200) . "',
														'" . (-$PurchPriceVar) . "')";

								$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction could not be added for the price variance of the stock item because');

								$Result = DB_query($SQL, $ErrMsg, '', true);
							}
						} else {

						/* its a nominal purchase order item that is not on a shipment so post the whole lot to the GLCode specified in the order, the purchase price var is actually the diff between the
						order price and the actual invoice price since the std cost was made equal to the order price in local currency at the time
						the goods were received */

							$GLCode = $EnteredGRN->GLCode; //by default

							if ($EnteredGRN->AssetID!=0) { //then it is an asset

								/*Need to get the asset details  for posting */
								$Result = DB_query("SELECT costact
													FROM fixedassets INNER JOIN fixedassetcategories
													ON fixedassets.assetcategoryid= fixedassetcategories.categoryid
													WHERE assetid='" . $EnteredGRN->AssetID . "'");
								$AssetRow = DB_fetch_array($Result);
								$GLCode = $AssetRow['costact'];
							} //the item was an asset

							$SQL = "INSERT INTO gltrans (type,
														typeno,
														trandate,
														periodno,
														account,
														narrative,
														amount)
										VALUES (21,
											'" . $CreditNoteNo . "',
											'" . $SQLCreditNoteDate . "',
											'" . $PeriodNo . "',
											'" . $GLCode . "',
											'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' - ' . __('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemDescription . ' x ' . $EnteredGRN->This_QuantityInv . ' x  ' . __('price var') .
									 ' ' . locale_number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,$_SESSION['CompanyRecord']['decimalplaces']), 0, 200) . "',
											'" . (-$PurchPriceVar) . "')";

							$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction could not be added for the price variance of the stock item because');
							$Result = DB_query($SQL, $ErrMsg, '', true);
						}
					}
				} else {

					/*then its a purchase order item on a shipment - whole charge amount to GRN suspense pending closure of the shipment	when the variance is calculated and the GRN act cleared up for the shipment */

					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
								VALUES (
									21,
									'" . $CreditNoteNo . "',
									'" . $SQLCreditNoteDate . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['SuppTrans']->GRNAct . "',
									'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' - ' . __('GRN') .' ' . $EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemCode . ' x ' . $EnteredGRN->This_QuantityInv . ' @ ' . $_SESSION['SuppTrans']->CurrCode .' ' . $EnteredGRN->ChgPrice . ' @ ' . __('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate, 0, 200) . "',
									'" . (-$EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv / $_SESSION['SuppTrans']->ExRate) . "'
								)";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction could not be added because');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				}

				$LocalTotal += ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv / $_SESSION['SuppTrans']->ExRate);

			} /* end of GRN postings */

			foreach ($_SESSION['SuppTrans']->Taxes as $Tax){
				/* Now the TAX account */
				if ($Tax->TaxOvAmount/ $_SESSION['SuppTrans']->ExRate !=0){
					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES (21,
								'" . $CreditNoteNo . "',
								'" . $SQLCreditNoteDate . "',
								'" . $PeriodNo . "',
								'" . $Tax->TaxGLCode . "',
								'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' - ' . __('Credit note') . ' ' . $_SESSION['SuppTrans']->SuppReference . ' ' . $_SESSION['SuppTrans']->CurrCode . $Tax->TaxOvAmount  . ' @ ' . __('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate, 0, 200) . "',
								'" . (-$Tax->TaxOvAmount/ $_SESSION['SuppTrans']->ExRate) . "')";

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction for the tax could not be added because');

					$Result = DB_query($SQL, $ErrMsg, '', true);
				}/* if the tax is not 0 */
			} /*end of loop to post the tax */
			/* Now the control account */

			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
					 VALUES (21,
					 	'" . $CreditNoteNo . "',
						'" . $SQLCreditNoteDate . "',
						'" . $PeriodNo . "',
						'" . $_SESSION['SuppTrans']->CreditorsAct . "',
						'" . mb_substr($_SESSION['SuppTrans']->SupplierID . ' - ' . __('Credit Note') . ' ' . $_SESSION['SuppTrans']->SuppReference . ' ' .  $_SESSION['SuppTrans']->CurrCode . locale_number_format($_SESSION['SuppTrans']->OvAmount + $_SESSION['SuppTrans']->OvGST,$_SESSION['SuppTrans']->CurrDecimalPlaces)  . ' @ ' . __('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate, 0, 200) .  "',
						'" . ($LocalTotal + ($TaxTotal / $_SESSION['SuppTrans']->ExRate)) . "')";

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction for the control total could not be added because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

		} /*Thats the end of the GL postings */

	/*Now insert the credit note into the SuppTrans table*/

		$SQL = "INSERT INTO supptrans (transno,
						type,
						supplierno,
						suppreference,
						trandate,
						duedate,
						inputdate,
						ovamount,
						ovgst,
						rate,
						transtext)
				VALUES (
					'". $CreditNoteNo . "',
					21,
					'" . $_SESSION['SuppTrans']->SupplierID . "',
					'" . $_SESSION['SuppTrans']->SuppReference . "',
					'" . $SQLCreditNoteDate . "',
					'" . FormatDateForSQL($_SESSION['SuppTrans']->DueDate) . "',
					'" . Date('Y-m-d H-i-s') . "',
					'" . -$_SESSION['SuppTrans']->OvAmount . "',
					'" . -$TaxTotal . "',
					'" . $_SESSION['SuppTrans']->ExRate . "',
					'" . $_SESSION['SuppTrans']->Comments . "')";

		$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The supplier credit note transaction could not be added to the database because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		$SuppTransID = DB_Last_Insert_ID('supptrans','id');

		/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		foreach ($_SESSION['SuppTrans']->Taxes AS $TaxTotals) {

			$SQL = "INSERT INTO supptranstaxes (supptransid,
												taxauthid,
												taxamount)
									VALUES ('" . $SuppTransID . "',
											'" . $TaxTotals->TaxAuthID . "',
											'" . -$TaxTotals->TaxOvAmount . "')";

			$ErrMsg =__('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The supplier transaction taxes records could not be inserted because');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		}

		/* Now update the GRN and PurchOrderDetails records for amounts invoiced
		 * can't use the previous loop around GRNs as this was only for where the creditors->GL link was active*/

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

			$SQL = "UPDATE purchorderdetails SET qtyinvoiced = qtyinvoiced - " .$EnteredGRN->This_QuantityInv . "
					WHERE podetailitem = '" . $EnteredGRN->PODetailItem ."'";

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The quantity credited of the purchase order line could not be updated because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			$SQL = "UPDATE grns SET quantityinv = quantityinv - " .
					 $EnteredGRN->This_QuantityInv . " WHERE grnno = '" . $EnteredGRN->GRNNo . "'";

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The quantity credited off the goods received record could not be updated because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			/*Update the shipment's accum value for the total local cost of shipment items being credited
			the total value credited against shipments is apportioned between all the items on the shipment
			later when the shipment is closed*/

			if (mb_strlen($EnteredGRN->ShiptRef)>0 AND $EnteredGRN->ShiptRef!=0){

				/* and insert the shipment charge records */
				$SQL = "INSERT INTO shipmentcharges (shiptref,
													transtype,
													transno,
													stockid,
													value)
											VALUES ('" . $EnteredGRN->ShiptRef . "',
													21,
													'" . $CreditNoteNo . "',
													'" . $EnteredGRN->ItemCode . "',
													'" . (-$EnteredGRN->This_QuantityInv * $EnteredGRN->ChgPrice / $_SESSION['SuppTrans']->ExRate) . "')";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The shipment charge record for the shipment') . ' ' . $EnteredGRN->ShiptRef . ' ' . __('could not be added because');

				$Result = DB_query($SQL, $ErrMsg, '', true);
			}
			if ($EnteredGRN->AssetID!=0) { //then it is an asset
				$PurchPriceVar = $EnteredGRN->This_QuantityInv * (($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit);
				if ($PurchPriceVar !=0){
					/*Add the fixed asset trans for the difference in the cost */
					$SQL = "INSERT INTO fixedassettrans (assetid,
														transtype,
														transno,
														transdate,
														periodno,
														inputdate,
														fixedassettranstype,
														amount)
									VALUES ('" . $EnteredGRN->AssetID . "',
											21,
											'" . $CreditNoteNo . "',
											'" . $SQLCreditNoteDate . "',
											'" . $PeriodNo . "',
											CURRENT_DATE,
											'cost',
											'" . -($PurchPriceVar) . "')";
					$ErrMsg = __('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE The fixed asset transaction could not be inserted because');
					$Result = DB_query($SQL, $ErrMsg, '', true);

					/*Now update the asset cost in fixedassets table */
					$SQL = "UPDATE fixedassets SET cost = cost - " . $PurchPriceVar  . "
							WHERE assetid = '" . $EnteredGRN->AssetID . "'";
					$ErrMsg = __('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE. The fixed asset cost was not able to be updated because:');
					$Result = DB_query($SQL, $ErrMsg, '', true);
				} //end if there is a cost difference on invoice compared to purchase order for the fixed asset
			}//the line is a fixed asset
		} /* end of the loop to do the updates for the quantity of order items the supplier has credited */

		/*Add shipment charges records as necessary */

		foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg){

			$SQL = "INSERT INTO shipmentcharges (shiptref,
								transtype,
								transno,
								value)
							VALUES (
								'" . $ShiptChg->ShiptRef . "',
								'21',
								'" . $CreditNoteNo . "',
								'" . (-$ShiptChg->Amount/$_SESSION['SuppTrans']->ExRate) . "'
							)";

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The shipment charge record for the shipment') . ' ' . $ShiptChg->ShiptRef . ' ' . __('could not be added because');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		}

		/*Add contract charges records as necessary */

		foreach ($_SESSION['SuppTrans']->Contracts as $Contract){

			if($Contract->AnticipatedCost ==true){
				$Anticipated =1;
			} else {
				$Anticipated =0;
			}
			$SQL = "INSERT INTO contractcharges (contractref,
												transtype,
												transno,
												amount,
												narrative,
												anticipated)
											VALUES (
												'" . $Contract->ContractRef . "',
												'21',
												'" . $CreditNoteNo  . "',
												'" . -$Contract->Amount/ $_SESSION['SuppTrans']->ExRate . "',
												'" . $Contract->Narrative . "',
												'" . $Anticipated . "')";

			$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The contract charge record for contract') . ' ' . $Contract->ContractRef . ' ' . __('could not be added because');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		} //end of loop around contracts on credit note


		foreach ($_SESSION['SuppTrans']->Assets as $AssetAddition){

			/*Asset additions need to have
			 * 	1. A fixed asset transaction inserted for the cost
			 * 	2. A general ledger transaction to fixed asset cost account if creditors linked - done in the GLCreditors Link above
			 * 	3. The fixedasset table cost updated by the negative addition
			 */

			/* First the fixed asset transaction */
			$SQL = "INSERT INTO fixedassettrans (assetid,
												transtype,
												transno,
												transdate,
												periodno,
												inputdate,
												fixedassettranstype,
												amount)
							VALUES ('" . $AssetAddition->AssetID . "',
											21,
											'" . $CreditNoteNo . "',
											'" . $SQLCreditNoteDate . "',
											'" . $PeriodNo . "',
											CURRENT_DATE,
											'cost',
											'" . (-$AssetAddition->Amount  / $_SESSION['SuppTrans']->ExRate)  . "')";
			$ErrMsg = __('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE The fixed asset transaction could not be inserted because');
			$Result = DB_query($SQL, $ErrMsg, '', true);

			/*Now update the asset cost in fixedassets table */
			$SQL = "UPDATE fixedassets SET cost = cost - " . ($AssetAddition->Amount  / $_SESSION['SuppTrans']->ExRate) . "
					WHERE assetid = '" . $AssetAddition->AssetID . "'";
			$ErrMsg = __('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE. The fixed asset cost  was not able to be updated because:');
			$Result = DB_query($SQL, $ErrMsg, '', true);
		} //end of non-gl fixed asset stuff

		DB_Txn_Commit();

		prnMsg(__('Supplier credit note number') . ' ' . $CreditNoteNo . ' ' . __('has been processed'),'success');
		echo '<br /><div class="centre"><a href="' . $RootPath . '/SupplierCredit.php?&SupplierID=' .$_SESSION['SuppTrans']->SupplierID . '">' . __('Enter another Credit Note for this Supplier') . '</a></div>';
		unset($_SESSION['SuppTrans']->GRNs);
		unset($_SESSION['SuppTrans']->Shipts);
		unset($_SESSION['SuppTrans']->GLCodes);
		unset($_SESSION['SuppTrans']);


	}

} /*end of process credit note */

echo '</div>
	  </form>';
include('includes/footer.php');
