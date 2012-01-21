<?php

/* $Id$ */

/*The supplier transaction uses the SuppTrans class to hold the information about the invoice
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing
Also an array of GLCodes objects - only used if the AP - GL link is effective
Also an array of shipment charges for charges to shipments to be apportioned accross the cost of stock items */

include('includes/DefineSuppTransClass.php');
/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Enter Supplier Invoice');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


if (!isset($_SESSION['SuppTrans']->SupplierName)) {
	$sql="SELECT suppname FROM suppliers WHERE supplierid='" . $_GET['SupplierID'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	$SupplierName=$myrow[0];
} else {
	$SupplierName=$_SESSION['SuppTrans']->SupplierName;
}

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('Supplier Invoice') . '" alt="" />
     ' . ' ' . _('Enter Supplier Invoice:') . ' ' . $SupplierName;
echo '</p>';
if (isset($_GET['SupplierID']) and $_GET['SupplierID']!=''){

 /*It must be a new invoice entry - clear any existing invoice details from the SuppTrans object and initiate a newy*/
	if (isset( $_SESSION['SuppTrans'])){
		unset ( $_SESSION['SuppTrans']->GRNs);
		unset ( $_SESSION['SuppTrans']->GLCodes);
		unset($_SESSION['SuppTrans']->Assets);
		unset ( $_SESSION['SuppTrans']);
	}

	 if (isset( $_SESSION['SuppTransTmp'])){
		unset ( $_SESSION['SuppTransTmp']->GRNs);
		unset ( $_SESSION['SuppTransTmp']->GLCodes);
		unset ( $_SESSION['SuppTransTmp']);
	}
	  $_SESSION['SuppTrans'] = new SuppTrans;

/*Now retrieve supplier information - name, currency, default ex rate, terms, tax rate etc */

	 $sql = "SELECT suppliers.suppname,
					suppliers.supplierid,
					paymentterms.terms,
					paymentterms.daysbeforedue,
					paymentterms.dayinfollowingmonth,
					suppliers.currcode,
					currencies.rate AS exrate,
					currencies.decimalplaces,
					suppliers.taxgroupid,
					taxgroups.taxgroupdescription
				FROM suppliers,
					taxgroups,
					currencies,
					paymentterms,
					taxauthorities
				WHERE suppliers.taxgroupid=taxgroups.taxgroupid
				AND suppliers.currcode=currencies.currabrev
				AND suppliers.paymentterms=paymentterms.termsindicator
				AND suppliers.supplierid = '" . $_GET['SupplierID'] . "'";

	$ErrMsg = _('The supplier record selected') . ': ' . $_GET['SupplierID'] . ' ' ._('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');

	$result = DB_query($sql, $db, $ErrMsg, $DbgMsg);

	$myrow = DB_fetch_array($result);

	$_SESSION['SuppTrans']->SupplierName = $myrow['suppname'];
	$_SESSION['SuppTrans']->TermsDescription = $myrow['terms'];
	$_SESSION['SuppTrans']->CurrCode = $myrow['currcode'];
	$_SESSION['SuppTrans']->ExRate = $myrow['exrate'];
	$_SESSION['SuppTrans']->CurrDecimalPlaces = $myrow['decimalplaces'];
	$_SESSION['SuppTrans']->TaxGroup = $myrow['taxgroupid'];
	$_SESSION['SuppTrans']->TaxGroupDescription = $myrow['taxgroupdescription'];
	$_SESSION['SuppTrans']->SupplierID = $myrow['supplierid'];

	if ($myrow['daysbeforedue'] == 0){
		 $_SESSION['SuppTrans']->Terms = '1' . $myrow['dayinfollowingmonth'];
	} else {
		 $_SESSION['SuppTrans']->Terms = '0' . $myrow['daysbeforedue'];
	}
	$_SESSION['SuppTrans']->SupplierID = $_GET['SupplierID'];

	$LocalTaxProvinceResult = DB_query("SELECT taxprovinceid
								FROM locations
								WHERE loccode = '" . $_SESSION['UserStockLocation'] . "'", $db);

	if(DB_num_rows($LocalTaxProvinceResult)==0){
		prnMsg(_('The tax province associated with your user account has not been set up in this database. Tax calculations are based on the tax group of the supplier and the tax province of the user entering the invoice. The system administrator should redefine your account with a valid default stocking location and this location should refer to a valid tax province'),'error');
		include('includes/footer.inc');
		exit;
	}

	$LocalTaxProvinceRow = DB_fetch_row($LocalTaxProvinceResult);
	$_SESSION['SuppTrans']->LocalTaxProvince = $LocalTaxProvinceRow[0];

	$_SESSION['SuppTrans']->GetTaxes();


	$_SESSION['SuppTrans']->GLLink_Creditors = $_SESSION['CompanyRecord']['gllink_creditors'];
	$_SESSION['SuppTrans']->GRNAct = $_SESSION['CompanyRecord']['grnact'];
	$_SESSION['SuppTrans']->CreditorsAct = $_SESSION['CompanyRecord']['creditorsact'];

	$_SESSION['SuppTrans']->InvoiceOrCredit = 'Invoice';

} elseif (!isset( $_SESSION['SuppTrans'])){

	prnMsg( _('To enter a supplier invoice the supplier must first be selected from the supplier selection screen'),'warn');
	echo '<br /><a href="' . $rootpath . '/SelectSupplier.php">' . _('Select A Supplier to Enter an Invoice For') . '</a>';
	include('includes/footer.inc');
	exit;

	/*It all stops here if there ain't no supplier selected */
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
				$_SESSION['SuppTrans']->OvAmount += ($GRN->This_QuantityInv * $GRN->ChgPrice);
			}
		}
		if (count($_SESSION['SuppTrans']->GLCodes) > 0){
			foreach ( $_SESSION['SuppTrans']->GLCodes as $GLLine){
				$_SESSION['SuppTrans']->OvAmount += $GLLine->Amount;
			}
		}
		if (count($_SESSION['SuppTrans']->Shipts) > 0){
			foreach ( $_SESSION['SuppTrans']->Shipts as $ShiptLine){
				$_SESSION['SuppTrans']->OvAmount +=  $ShiptLine->Amount;
			}
		}
		if (count($_SESSION['SuppTrans']->Contracts) > 0){
			foreach ( $_SESSION['SuppTrans']->Contracts as $Contract){
				$_SESSION['SuppTrans']->OvAmount +=  $Contract->Amount;
			}
		}
		if (count($_SESSION['SuppTrans']->Assets) > 0){
			foreach ( $_SESSION['SuppTrans']->Assets as $FixedAsset){
				$_SESSION['SuppTrans']->OvAmount +=  $FixedAsset->Amount;
			}
		}
		$_SESSION['SuppTrans']->OvAmount = round($_SESSION['SuppTrans']->OvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces);
	}else {
/*OvAmount must be entered manually */
		 $_SESSION['SuppTrans']->OvAmount = round(filter_number_format($_POST['OvAmount']),$_SESSION['SuppTrans']->CurrDecimalPlaces);
	}
}


if (!isset($_POST['PostInvoice'])){

	if (isset($_POST['GRNS']) 
		AND $_POST['GRNS'] == _('Purchase Orders')){
		/*This ensures that any changes in the page are stored in the session before calling the grn page */
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/SuppInvGRNs.php">';
		echo '<div class="centre">' . _('You should automatically be forwarded to the entry of invoices against goods received page') .
			'. ' . _('If this does not happen') .' (' . _('if the browser does not support META Refresh') . ') ' .
			'<a href="' . $rootpath . '/SuppInvGRNs.php">' . _('click here') . '</a> ' . _('to continue') . '</div>
			<br />';
		exit;
	}
	if (isset($_POST['Shipts']) AND $_POST['Shipts'] == _('Shipments')){
		/*This ensures that any changes in the page are stored in the session before calling the shipments page */
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/SuppShiptChgs.php">';
		echo '<div class="centre">' . _('You should automatically be forwarded to the entry of invoices against shipments page') .
			'. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh'). ') ' .
			'<a href="' . $rootpath . '/SuppShiptChgs.php">' . _('click here') . '</a> ' . _('to continue') . '.</div><br />';
		exit;
	}
	if (isset($_POST['GL']) AND $_POST['GL'] == _('General Ledger')){
		/*This ensures that any changes in the page are stored in the session before calling the shipments page */
		echo '<meta http-equiv="Refresh" content="0; url=' . $rootpath . '/SuppTransGLAnalysis.php">';
		echo '<div class="centre">' . _('You should automatically be forwarded to the entry of invoices against the general ledger page') .
			'. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh'). ') ' .
			'<a href="' . $rootpath . '/SuppTransGLAnalysis.php">' . _('click here') . '</a> ' . _('to continue') . '.</div><br />';
		exit;
	}
	if (isset($_POST['Contracts']) AND $_POST['Contracts'] == _('Contracts')){
		/*This ensures that any changes in the page are stored in the session before calling the shipments page */
		echo '<meta http-equiv="refresh" content="0; url=' . $rootpath . '/SuppContractChgs.php">';
		echo '<div class="centre">' . _('You should automatically be forwarded to the entry of invoices against contracts page') .
			'. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh'). ') ' .
			'<a href="' . $rootpath . '/SuppContractChgs.php">' . _('click here') . '</a> ' . _('to continue') . '.</div>
			<br />';
		exit;
	}
	if (isset($_POST['FixedAssets']) 
		AND $_POST['FixedAssets'] == _('Fixed Assets')){
		/*This ensures that any changes in the page are stored in the session before calling the shipments page */
		echo '<meta http-equiv="refresh" content="0; url=' . $rootpath . '/SuppFixedAssetChgs.php">';
		echo '<div class="centre">' . _('You should automatically be forwarded to the entry of invoice amounts against fixed assets page') .
			'. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh'). ') ' .
			'<a href="' . $rootpath . '/SuppFixedAssetChgs.php">' . _('click here') . '</a> ' . _('to continue') . '.</DIV><br />';
		exit;
	}
	/* everything below here only do if a Supplier is selected
	fisrt add a header to show who we are making an invoice for */

	echo '<br /><table class="selection" colspan="4">
			<tr>
				<th>' . _('Supplier') . '</th>
				<th>' . _('Currency') .  '</th>
				<th>' . _('Terms') .		'</th>
				<th>' . _('Tax Authority') . '</th>
			</tr>';

	echo '<tr>
			<td><font color=blue><b>' . $_SESSION['SuppTrans']->SupplierID . ' - ' .
		$_SESSION['SuppTrans']->SupplierName . '</b></font></td>
			<th><font color=blue><b>' .  $_SESSION['SuppTrans']->CurrCode . '</b></font></th>
			<td><font color=blue><b>' . $_SESSION['SuppTrans']->TermsDescription . '</b></font></td>
			<td><font color=blue><b>' . $_SESSION['SuppTrans']->TaxGroupDescription . '</b></font></td>
		</tr>
		</table>';

	echo '<br /><form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post" name="form1" />';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<br /><table class="selection">';

	echo '<tr>
			<td>' . _('Supplier Invoice Reference') . ':</td>
			<td><input type="text" size="20" maxlength="20" name="SuppReference" value="' . $_SESSION['SuppTrans']->SuppReference . '" /></td>';

	if (!isset($_SESSION['SuppTrans']->TranDate)){
		$_SESSION['SuppTrans']->TranDate= Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),Date('d')-1,Date('y')));
	}
	echo '<td>' . _('Invoice Date') . ' (' . _('in format') . ' ' . $_SESSION['DefaultDateFormat'] . ') :</td>
		<td><input type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" size="11" maxlength="10" name="TranDate" value="' . $_SESSION['SuppTrans']->TranDate . '" /></td>
		<td>' . _('Exchange Rate') . ':</td>
		<td><input type="text" class="number" size="11" maxlength="10" name="ExRate" value="' . locale_number_format($_SESSION['SuppTrans']->ExRate,'Variable') . '" /></td>
	</tr>
	</table>';

	echo '<br />
		<div class="centre">
			<input type="submit" name="GRNS" value="' . _('Purchase Orders') . '" /> 
			<input type="submit" name="Shipts" value="' . _('Shipments') . '" /> 
			<input type="submit" name="Contracts" value="' . _('Contracts') . '" /> ';

	if ( $_SESSION['SuppTrans']->GLLink_Creditors == 1){
		echo '<input type="submit" name="GL" value="' . _('General Ledger') . '" /> ';
	}
	echo ' <input type="submit" name="FixedAssets" value="' . _('Fixed Assets') . '" />
		</div>';

	$TotalGRNValue = 0;
	
	if (count( $_SESSION['SuppTrans']->GRNs)>0){   /*if there are any GRNs selected for invoicing then */
		/*Show all the selected GRNs so far from the SESSION['SuppInv']->GRNs array */

		echo '<br />
				<table class="selection">
			<tr>
				<th colspan="6">' . _('Purchase Order Charges') . '</th>
			</tr>';
		$tableheader = '<tr bgcolor=#800000>
							<th>' . _('Seq') . ' #</th>
							<th>' . _('Item Code') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('Quantity Charged') . '</th>
							<th>' . _('Price in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
							<th>' . _('Line Total') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
						</tr>';
		echo $tableheader;

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

			echo '<tr>
					<td>' . $EnteredGRN->GRNNo . '</td>
					<td>' . $EnteredGRN->ItemCode .	'</td>
					<td>' . $EnteredGRN->ItemDescription . '</td>
					<td class="number">' . locale_number_format($EnteredGRN->This_QuantityInv,$EnteredGRN->DecimalPlaces) . '</td>
					<td class="number">' . locale_number_format($EnteredGRN->ChgPrice,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
					<td class="number">' . locale_number_format($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
				</tr>';

			$TotalGRNValue += ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv);

		}

		echo '<tr>
				<td colspan="5" class="number"><font color="blue">' . _('Total Value of Goods Charged') . ':</font></td>
				<td class="number"><font color="blue"><U>' . locale_number_format($TotalGRNValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</U></font></td>
			</tr>
			</table>';
	}

	$TotalShiptValue = 0;

	if (count( $_SESSION['SuppTrans']->Shipts) > 0){   /*if there are any Shipment charges on the invoice*/

		echo '<br />
				<table class="selection">
				<tr>
					<th colspan="2">' . _('Shipment Charges') . '</th>
				</tr>';
		$TableHeader = '<tr>
							<th>' . _('Shipment') . '</th>
							<th>' . _('Amount') . '</th>
						</tr>';
		echo $TableHeader;
		
		$i=0; //row counter
		
		foreach ($_SESSION['SuppTrans']->Shipts as $EnteredShiptRef){

			echo '<tr>
					<td>' . $EnteredShiptRef->ShiptRef . '</td>
					<td class="number">' . locale_number_format($EnteredShiptRef->Amount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
				</tr>';

			$TotalShiptValue += $EnteredShiptRef->Amount;

			$i++;
			if ($i > 15){
				$i = 0;
				echo $TableHeader;
			}
		}

		echo '<tr>
				<td class="number"><font color="blue">' . _('Total shipment charges') . ':</font></td>
				<td class="number"><font color="blue"><u>' .  locale_number_format($TotalShiptValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</u></font></td>
			</tr>
			</table>';
	}

	$TotalAssetValue = 0;

	if (count( $_SESSION['SuppTrans']->Assets) > 0){   /*if there are any fixed assets on the invoice*/

		echo '<br />
			<table class="selection">
			<tr>
				<th colspan="3">' . _('Fixed Asset Additions') . '</th>
			</tr>';
		$TableHeader = '<tr>
							<th>' . _('Asset ID') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('Amount') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
						</tr>';
		echo $TableHeader;

		foreach ($_SESSION['SuppTrans']->Assets as $EnteredAsset){

			echo '<tr>
					<td>' . $EnteredAsset->AssetID . '</td>
					<td>' . $EnteredAsset->Description . '</td>
					<td class="number">' .	locale_number_format($EnteredAsset->Amount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
				</tr>';

			$TotalAssetValue += $EnteredAsset->Amount;

			$i++;
			if ($i > 15){
				$i = 0;
				echo $TableHeader;
			}
		}

		echo '<tr>
				<td colspan="2" class="number"><font color="blue">' . _('Total asset additions') . ':</font></td>
				<td class="number"><font color="blue"><u>' .  locale_number_format($TotalAssetValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</u></font></td>
			</tr>
			</table>';
	} //end loop around assets added to invocie

	$TotalContractsValue = 0;

	if (count( $_SESSION['SuppTrans']->Contracts) > 0){   /*if there are any contract charges on the invoice*/

		echo '<br />
			<table class="selection">
			<tr>
				<th colspan="3">' . _('Contract Charges') . '</th>
			</tr>';
		$TableHeader = '<tr>
							<th>' . _('Contract') . '</th>
							<th>' . _('Narrative') . '</th>
							<th>' . _('Amount') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
						</tr>';
		echo $TableHeader;

		
		$i=0;
		foreach ($_SESSION['SuppTrans']->Contracts as $Contract){

			echo '<tr>
					<td>' . $Contract->ContractRef . '</td>
					<td>' . $Contract->Narrative . '</td>
					<td class="number">' .    locale_number_format($Contract->Amount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
				</tr>';

			$TotalContractsValue += $Contract->Amount;

			$i++;
			if ($i == 15){
				$i = 0;
				echo $TableHeader;
			}
		}

		echo '<tr>
				<td colspan="2" class="number"><font color="blue">' . _('Total contract charges') . ':</font></td>
				<td class="number"><font color="blue">' .  locale_number_format($TotalContractsValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</font></td>
			</tr>
			</table>';
	}

	$TotalGLValue = 0;
	
	if ( $_SESSION['SuppTrans']->GLLink_Creditors == 1){

		if (count($_SESSION['SuppTrans']->GLCodes) > 0){
			echo '<br />
					<table class="selection">
					<tr>
						<th colspan="5">' . _('General Ledger Analysis') . '</th>
					</tr>';
			$TableHeader = '<tr>
								<th>' . _('Account') . '</th>
								<th>' . _('Account Name') .     '</th>
								<th>' . _('Narrative') . '</th>
								<th>' . _('Amount') . '<br />' . _('in') . ' ' . $_SESSION['SuppTrans']->CurrCode . '</th>
							</tr>';
			echo $TableHeader;

			foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

				echo '<tr>
						<td>' . $EnteredGLCode->GLCode . '</td>
						<td>' . $EnteredGLCode->GLActName . '</td>
						<td>' . $EnteredGLCode->Narrative . '</td>
						<td class=number>' . locale_number_format($EnteredGLCode->Amount,$_SESSION['SuppTrans']->CurrDecimalPlaces) .  '</td>
					</tr>';

				$TotalGLValue += $EnteredGLCode->Amount;

			}

			echo '<tr>
					<td colspan="3" class="number"><font color="blue">' . _('Total GL Analysis') .  ':</font></td>
					<td class="number"><font color="blue">' .  locale_number_format($TotalGLValue,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</font></td>
				</tr>
				</table>';
		}

		$_SESSION['SuppTrans']->OvAmount = ($TotalGRNValue + $TotalGLValue + $TotalAssetValue + $TotalShiptValue + $TotalContractsValue);

		echo '<br />
				<table class="selection">
				<tr>
					<td>' . _('Amount in supplier currency') . ':</td>
					<td colspan="2" class=number>' . locale_number_format( $_SESSION['SuppTrans']->OvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '</td>
				</tr>';
	} else {
		echo '<br />
				<table class="selection">
				<tr>
					<td>' . _('Amount in supplier currency') . ':</td>
					<td colspan="2" class="number"><input type="text" size="12" maxlength="10" name="OvAmount" value="' . locale_number_format( $_SESSION['SuppTrans']->OvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" /></td>
				</tr>';
	}

	echo '<tr>
			<td colspan="2"><input type="submit" name="ToggleTaxMethod" value="' . _('Update Tax Calculation') . '" /></td>
			<td><select name="OverRideTax" onChange="ReloadForm(form1.ToggleTaxMethod)">';

	if ($_POST['OverRideTax']=='Man'){
		echo '<option value="Auto">' . _('Automatic') . '</option>
				<option selected value="Man">' . _('Manual') . '</option>';
	} else {
		echo '<option selected value="Auto">' . _('Automatic') . '</option>
				<option  value="Man">' . _('Manual') . '</option>';
	}

	echo '</select></td>
		</tr>';
	$TaxTotal =0; //initialise tax total

	foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {

		echo '<tr>
				<td>'  . $Tax->TaxAuthDescription . '</td>
				<td>';

		/*Set the tax rate to what was entered */
		if (isset($_POST['TaxRate'  . $Tax->TaxCalculationOrder])){
			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate = filter_number_format($_POST['TaxRate'  . $Tax->TaxCalculationOrder])/100;
		}

		/*If a tax rate is entered that is not the same as it was previously then recalculate automatically the tax amounts */

		if (!isset($_POST['OverRideTax']) 
			OR $_POST['OverRideTax']=='Auto'){

			echo  ' <input type="text" class="number" name=TaxRate' . $Tax->TaxCalculationOrder . ' maxlength="4" size="4" value="' . locale_number_format($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100,$_SESSION['SuppTrans']->CurrDecimalPlaces)  . '" />%';

			/*Now recaluclate the tax depending on the method */
			if ($Tax->TaxOnTax ==1){

				$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * ($_SESSION['SuppTrans']->OvAmount + $TaxTotal);

			} else { /*Calculate tax without the tax on tax */

				$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * $_SESSION['SuppTrans']->OvAmount;

			}

			echo '<input type="hidden" name="TaxAmount'  . $Tax->TaxCalculationOrder . '"  value="' . locale_number_format(round($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces),$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />';

			echo '</td><td class="number">' . locale_number_format($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces);

		} else { /*Tax being entered manually accept the taxamount entered as is*/
//			if (!isset($_POST['TaxAmount'  . $Tax->TaxCalculationOrder])) {
//				$_POST['TaxAmount'  . $Tax->TaxCalculationOrder]=0;
//		}
			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount = filter_number_format($_POST['TaxAmount'  . $Tax->TaxCalculationOrder]);

			echo  ' <input type="hidden" name="TaxRate"' . $Tax->TaxCalculationOrder . ' value="' . locale_number_format($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate * 100,$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />';

			echo '</td>
				<td><input type="text" class="number" size="12" maxlength="12" name="TaxAmount'  . $Tax->TaxCalculationOrder . '"  value="' . locale_number_format(round($_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces),$_SESSION['SuppTrans']->CurrDecimalPlaces) . '" />';
		}

		$TaxTotal += $_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxOvAmount;
		echo '</td>
			</tr>';
	}

	$_SESSION['SuppTrans']->OvAmount = round($_SESSION['SuppTrans']->OvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces);

	$DisplayTotal = locale_number_format(( $_SESSION['SuppTrans']->OvAmount + $TaxTotal), $_SESSION['SuppTrans']->CurrDecimalPlaces);

	echo '<tr>
			<td>' . _('Invoice Total') . ':</td>
			<td colspan="2" class="number"><b>' . $DisplayTotal . '</b></td>
		</tr>
		</table>';

	echo '<br />
		<table class="selection">
			<tr>
				<td>' . _('Comments') . '</td>
				<td><textarea name="Comments" cols="40" rows="2">' . $_SESSION['SuppTrans']->Comments . '</textarea></td>
			</tr>
		</table>';

	echo '<br />
			<div class="centre">
				<input type="submit" name="PostInvoice" value="' . _('Enter Invoice') . '" />
			</div>';

} else { // $_POST['PostInvoice'] is set so do the postings -and dont show the button to process

/*First do input reasonableness checks
then do the updates and inserts to process the invoice entered */
	$TaxTotal =0;
	foreach ($_SESSION['SuppTrans']->Taxes as $Tax) {
		/*Set the tax rate to what was entered */
		if (isset($_POST['TaxRate'  . $Tax->TaxCalculationOrder])){
			$_SESSION['SuppTrans']->Taxes[$Tax->TaxCalculationOrder]->TaxRate = filter_number_format($_POST['TaxRate'  . $Tax->TaxCalculationOrder])/100;
		}
		if ($_POST['OverRideTax']=='Auto' OR !isset($_POST['OverRideTax'])){
			/*Now recaluclate the tax depending on the method */
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


	$InputError = False;
	if ( $TaxTotal + $_SESSION['SuppTrans']->OvAmount < 0){
	
		$InputError = True;
		prnMsg(_('The invoice as entered cannot be processed because the total amount of the invoice is less than  0') . '. ' . _('Invoices are expected to have a positive charge'),'error');
		echo '<p>' . _('The tax total is') . ' : ' . locale_number_format($TaxTotal,$_SESSION['SuppTrans']->CurrDecimalPlaces);
		echo '<p>' . _('The ovamount is') . ' : ' . locale_number_format($_SESSION['SuppTrans']->OvAmount,$_SESSION['SuppTrans']->CurrDecimalPlaces);
	
	} elseif ( $TaxTotal + $_SESSION['SuppTrans']->OvAmount == 0){
	
		prnMsg(_('The invoice as entered will be processed but be warned the amount of the invoice is  zero!') . '. ' . _('Invoices are normally expected to have a positive charge'),'warn');
	
	} elseif (mb_strlen( $_SESSION['SuppTrans']->SuppReference)<1){
		
		$InputError = True;
		prnMsg(_('The invoice as entered cannot be processed because the there is no suppliers invoice number or reference entered') . '. ' . _('The supplier invoice number must be entered'),'error');

	} elseif (!Is_date( $_SESSION['SuppTrans']->TranDate)){
	
		$InputError = True;
		prnMsg( _('The invoice as entered cannot be processed because the invoice date entered is not in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');

	} elseif (DateDiff(Date($_SESSION['DefaultDateFormat']), $_SESSION['SuppTrans']->TranDate, 'd') < 0){
	
		$InputError = True;
		prnMsg(_('The invoice as entered cannot be processed because the invoice date is after today') . '. ' . _('Purchase invoices are expected to have a date prior to or today'),'error');

	}elseif ( $_SESSION['SuppTrans']->ExRate <= 0){
	
		$InputError = True;
		prnMsg( _('The invoice as entered cannot be processed because the exchange rate for the invoice has been entered as a negative or zero number') . '. ' . _('The exchange rate is expected to show how many of the suppliers currency there are in 1 of the local currency'),'error');
	
	} elseif ( $_SESSION['SuppTrans']->OvAmount < round($_SESSION['SuppTrans']->Total_Shipts_Value() + $_SESSION['SuppTrans']->Total_GL_Value() + $_SESSION['SuppTrans']->Total_Contracts_Value()+ $_SESSION['SuppTrans']->Total_Assets_Value()+$_SESSION['SuppTrans']->Total_GRN_Value(),$_SESSION['SuppTrans']->CurrDecimalPlaces)){
	
		prnMsg( _('The invoice total as entered is less than the sum of the shipment charges, the general ledger entries (if any), the charges for goods received, contract charges and fixed asset charges. There must be a mistake somewhere, the invoice as entered will not be processed'),'error');
		$InputError = True;

	} else {
	
		$sql = "SELECT count(*)
				FROM supptrans
				WHERE supplierno='" . $_SESSION['SuppTrans']->SupplierID . "'
				AND supptrans.suppreference='" . $_POST['SuppReference'] . "'";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sql to check for the previous entry of the same invoice failed');
		$DbgMsg = _('The following SQL to test for a previous invoice with the same reference from the same supplier was used');
		$result=DB_query($sql, $db, $ErrMsg, $DbgMsg, True);

		$myrow=DB_fetch_row($result);
		if ($myrow[0] == 1){ /*Transaction reference already entered */
			prnMsg( _('The invoice number') . ' : ' . $_POST['SuppReference'] . ' ' . _('has already been entered') . '. ' . _('It cannot be entered again'),'error');
			$InputError = True;
		}
	}

	if ($InputError == False){

	/* SQL to process the postings for purchase invoice */
	/*Start an SQL transaction */

		$Result = DB_Txn_Begin($db);

		/*Get the next transaction number for internal purposes and the period to post GL transactions in based on the invoice date*/
		$InvoiceNo = GetNextTransNo(20, $db);
		$PeriodNo = GetPeriod( $_SESSION['SuppTrans']->TranDate, $db);
		$SQLInvoiceDate = FormatDateForSQL( $_SESSION['SuppTrans']->TranDate);

		if ( $_SESSION['SuppTrans']->GLLink_Creditors == 1){
		/*Loop through the GL Entries and create a debit posting for each of the accounts entered */
			$LocalTotal = 0;

			/*the postings here are a little tricky, the logic goes like this:
			if its a shipment entry then the cost must go against the GRN suspense account defined in the company record

			if its a general ledger amount it goes straight to the account specified

			if its a GRN amount invoiced then there are two possibilities:

			1 The PO line is on a shipment.
			The whole charge goes to the GRN suspense account pending the closure of the
			shipment where the variance is calculated on the shipment as a whole and the clearing entry to the GRN suspense
			is created. Also, shipment records are created for the charges in local currency.

			2. The order line item is not on a shipment
			The cost as originally credited to GRN suspense on arrival of goods is debited to GRN suspense.
			Depending on the setting of WeightedAverageCosting:
			If the order line item is a stock item and WeightedAverageCosting set to OFF then use standard costing .....
				Any difference
				between the std cost and the currency cost charged as converted at the ex rate of of the invoice is written off
				to the purchase price variance account applicable to the stock item being invoiced.
			Otherwise
				Recalculate the new weighted average cost of the stock and update the cost - post the difference to the appropriate stock code

			Or if its not a stock item
			but a nominal item then the GL account in the orignal order is used for the price variance account.
			*/

			foreach ($_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

			/*GL Items are straight forward - just do the debit postings to the GL accounts specified -
			the credit is to creditors control act  done later for the total invoice value + tax*/

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (20,
										'" . $InvoiceNo . "',
										'" . $SQLInvoiceDate . "',
										'" . $PeriodNo . "',
										'" . $EnteredGLCode->GLCode . "',
										'" . $_SESSION['SuppTrans']->SupplierID . ' ' . $EnteredGLCode->Narrative . "',
										'" . $EnteredGLCode->Amount/ $_SESSION['SuppTrans']->ExRate ."')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');
				$DbgMsg = _('The following SQL to insert the GL transaction was used');

				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$LocalTotal += $EnteredGLCode->Amount/ $_SESSION['SuppTrans']->ExRate;
			}

			foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg){

			/*shipment postings are also straight forward - just do the debit postings to the GRN suspense account
			these entries are reversed from the GRN suspense when the shipment is closed*/

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
							VALUES (20,
									'" . $InvoiceNo . "',
									'" . $SQLInvoiceDate . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['SuppTrans']->GRNAct . "',
									'" . $_SESSION['SuppTrans']->SupplierID . ' ' . _('Shipment charge against') . ' ' . $ShiptChg->ShiptRef . "',
									'" . $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the shipment') .
							' ' . $ShiptChg->ShiptRef . ' ' . _('could not be added because');

				$DbgMsg = _('The following SQL to insert the GL transaction was used');

				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

				$LocalTotal += $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate;

			}

			foreach ($_SESSION['SuppTrans']->Assets as $AssetAddition){
				/* only the GL entries if the creditors/GL integration is enabled */
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
								VALUES ('20', 
									'" . $InvoiceNo . "',
									'" . $SQLInvoiceDate . "',
									'" . $PeriodNo . "',
									'". $AssetAddition->CostAct . "',
									'" . $_SESSION['SuppTrans']->SupplierID . ' ' . _('Asset Addition') . ' ' . $AssetAddition->AssetID . ': '  . $AssetAddition->Description . "',
									'" . ($AssetAddition->Amount/ $_SESSION['SuppTrans']->ExRate) . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the asset addition could not be added because');
 				$DbgMsg = _('The following SQL to insert the GL transaction was used');
 				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

 				$LocalTotal += ($AssetAddition->Amount/ $_SESSION['SuppTrans']->ExRate);
			}

			foreach ($_SESSION['SuppTrans']->Contracts as $Contract){

			/*contract postings need to get the WIP from the contract items stock category record
			*  debit postings to this WIP account
			* the WIP account is tidied up when the contract is closed*/
				$result = DB_query("SELECT wipact FROM stockcategory
									INNER JOIN stockmaster ON
									stockcategory.categoryid=stockmaster.categoryid
									WHERE stockmaster.stockid='" . $Contract->ContractRef . "'",$db);
				$WIPRow = DB_fetch_row($result);
				$WIPAccount = $WIPRow[0];
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES ('20', 
											'" . $InvoiceNo . "',
											'" . $SQLInvoiceDate . "',
											'" . $PeriodNo . "',
											'". $WIPAccount . "',
											'" . $_SESSION['SuppTrans']->SupplierID . ' ' . _('Contract charge against') . ' ' . $Contract->ContractRef . "',
											'" . ($Contract->Amount/ $_SESSION['SuppTrans']->ExRate) . "')";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the contract') . ' ' . $Contract->ContractRef . ' ' . _('could not be added because');
				$DbgMsg = _('The following SQL to insert the GL transaction was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
				$LocalTotal += ($Contract->Amount/ $_SESSION['SuppTrans']->ExRate);
			}

			foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){

				if (mb_strlen($EnteredGRN->ShiptRef) == 0 
					OR $EnteredGRN->ShiptRef == 0){
				/*so its not a GRN shipment item
				  enter the GL entry to reverse the GRN suspense entry created on delivery
				  * at standard cost/or weighted average cost used on delivery */

				 /*Always do this - for weighted average costing and also for standard costing */

					if ($EnteredGRN->StdCostUnit * ($EnteredGRN->This_QuantityInv ) != 0) {
						$SQL = "INSERT INTO gltrans (type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount)
								VALUES ('20', 
									'" . $InvoiceNo . "',
									'" . $SQLInvoiceDate . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['SuppTrans']->GRNAct . "',
									'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemCode . ' x ' . $EnteredGRN->This_QuantityInv . ' @  ' .
								 _('std cost of') . ' ' . $EnteredGRN->StdCostUnit  . "',
								 	'" . ($EnteredGRN->StdCostUnit * $EnteredGRN->This_QuantityInv) . "')";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');
						$DbgMsg = _('The following SQL to insert the GL transaction was used');
						$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
					}

					$PurchPriceVar = $EnteredGRN->This_QuantityInv * (($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit);

					/*Yes.... but where to post this difference to - if its a stock item the variance account must be retrieved from the stock category record
					if its a nominal purchase order item with no stock item then there will be no standard cost and it will all be variance so post it to the
					account specified in the purchase order detail record */

					if ($PurchPriceVar !=0){ /* don't bother with this lot if there is no difference ! */
						if (mb_strlen($EnteredGRN->ItemCode)>0 OR $EnteredGRN->ItemCode != ''){ /*so it is a stock item */

							/*need to get the stock category record for this stock item - this is function in SQL_CommonFunctions.inc */
							$StockGLCode = GetStockGLCode($EnteredGRN->ItemCode,$db);
							
							/*We have stock item and a purchase price variance need to see whether we are using Standard or WeightedAverageCosting */

							if ($_SESSION['WeightedAverageCosting']==1){ /*Weighted Average costing */

								/*
								First off figure out the new weighted average cost Need the following data:

								How many in stock now
								The quantity being invoiced here - $EnteredGRN->This_QuantityInv
								The cost of these items - $EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate
								*/

								$sql ="SELECT SUM(quantity) FROM locstock WHERE stockid='" . $EnteredGRN->ItemCode . "'";
								$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity on hand could not be retrieved from the database');
								$DbgMsg = _('The following SQL to retrieve the total stock quantity was used');
								$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, True);
								$QtyRow = DB_fetch_row($Result);
								$TotalQuantityOnHand = $QtyRow[0];

								/*The cost adjustment is the price variance / the total quantity in stock
								But that is only provided that the total quantity in stock is greater than the quantity charged on this invoice

								If the quantity on hand is less the amount charged on this invoice then some must have been sold and the price variance on these must be written off to price variances*/

								$WriteOffToVariances =0;

								if ($EnteredGRN->This_QuantityInv > $TotalQuantityOnHand){

									/*So we need to write off some of the variance to variances and only the balance of the quantity in stock to go to stock value */
									
									/*if the TotalQuantityOnHand is negative then this variance to write off is inflated by the negative quantity - which makes sense */
									
									$WriteOffToVariances =  ($EnteredGRN->This_QuantityInv - $TotalQuantityOnHand) * (($EnteredGRN->ChgPrice /  $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit);
									
									$SQL = "INSERT INTO gltrans (type,
																typeno,
																trandate,
																periodno,
																account,
																narrative,
																amount)
														VALUES (20,
															'" .  $InvoiceNo . "',
															'" . $SQLInvoiceDate . "',
															'" . $PeriodNo . "',
															'" . $StockGLCode['purchpricevaract'] . "',
															'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo .  ' - ' . $EnteredGRN->ItemCode . ' x ' . ($EnteredGRN->This_QuantityInv -$TotalQuantityOnHand) . ' x  ' . _('price var of') . ' ' . round(($EnteredGRN->ChgPrice / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,2)  . "',
															'" . $WriteOffToVariances . "')";

									$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');
									$DbgMsg = _('The following SQL to insert the GL transaction was used');


									$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
								} // end if the quantity being invoiced here is greater than the current stock on hand
								
								/*Now post any remaining price variance to stock rather than price variances */

								$SQL = "INSERT INTO gltrans (type,
															typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
													VALUES (20,
													'" . $InvoiceNo . "',
													'" . $SQLInvoiceDate . "',
													'" . $PeriodNo . "',
													'" . $StockGLCode['stockact'] . "',
													'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('Average Cost Adj') .
													 ' - ' . $EnteredGRN->ItemCode . ' x ' . $TotalQuantityOnHand  . ' x ' .
													 round(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,$_SESSION['CompanyRecord']['decimalplaces'])  . "',
													'" . ($PurchPriceVar - $WriteOffToVariances) . "')";

								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');
								$DbgMsg = _('The following SQL to insert the GL transaction was used');

								$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

							} else { //It must be Standard Costing

								$SQL = "INSERT INTO gltrans (type,
															typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
													VALUES (20,
														'" . $InvoiceNo . "',
														'" . $SQLInvoiceDate . "',
														'" . $PeriodNo . "',
														'" . $StockGLCode['purchpricevaract'] . "',
														'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemCode . ' x ' . $EnteredGRN->This_QuantityInv . ' x  ' . _('price var of') . ' ' . round(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,2)  .  "',
														'" . $PurchPriceVar . "')";

								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');
								$DbgMsg = _('The following SQL to insert the GL transaction was used');
								$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
							}
						} else {
							/* its a nominal purchase order item that is not on a shipment so post the whole lot to the GLCode specified in the order, the purchase price var is actually the diff between the
							order price and the actual invoice price since the std cost was made equal to the order price in local currency at the time
							the goods were received */
							$GLCode = $EnteredGRN->GLCode; //by default
							if ($EnteredGRN->AssetID!=0) { //then it is an asset

								/*Need to get the asset details  for posting */
								$result = DB_query("SELECT costact
													FROM fixedassets INNER JOIN fixedassetcategories
													ON fixedassets.assetcategoryid= fixedassetcategories.categoryid
													WHERE assetid='" . $EnteredGRN->AssetID . "'",$db);
								if (DB_num_rows($result)!=0){ // the asset exists
									$AssetRow = DB_fetch_array($result);
									$GLCode = $AssetRow['costact'];
								}
							} //the item was an asset received on a purchase order

							$SQL = "INSERT INTO gltrans (type,
														typeno,
														trandate,
														periodno,
														account,
														narrative,
														amount)
									VALUES (20,
											'" . $InvoiceNo . "',
											'" . $SQLInvoiceDate . "',
											'" . $PeriodNo . "',
											'" . $GLCode . "',
											'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' .  $EnteredGRN->ItemDescription . ' x ' . $EnteredGRN->This_QuantityInv . ' x  ' . _('price var') . ' ' . locale_number_format(($EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate) - $EnteredGRN->StdCostUnit,$_SESSION['SuppTrans']->CurrDecimalPlaces) . "',
											'" . $PurchPriceVar . "')";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added for the price variance of the stock item because');

							$DbgMsg = _('The following SQL to insert the GL transaction was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
						}
					}

				} else {
					/*then its a purchase order item on a shipment - whole charge amount to GRN suspense pending closure of the shipment when the variance is calculated and the GRN act cleared up for the shipment */

					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES (20,
											'" . $InvoiceNo . "',
											'" . $SQLInvoiceDate . "',
											'" . $PeriodNo . "',
											'" . $_SESSION['SuppTrans']->GRNAct . "',
											'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('GRN') . ' ' . $EnteredGRN->GRNNo . ' - ' . $EnteredGRN->ItemCode . ' x ' . $EnteredGRN->This_QuantityInv . ' @ ' . $_SESSION['SuppTrans']->CurrCode . ' ' . $EnteredGRN->ChgPrice . ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate . "',
											'" . (($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) / $_SESSION['SuppTrans']->ExRate) . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction could not be added because');
					$DbgMsg = _('The following SQL to insert the GL transaction was used');
					$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
				}
				$LocalTotal += ($EnteredGRN->ChgPrice * $EnteredGRN->This_QuantityInv) / $_SESSION['SuppTrans']->ExRate;
			} /* end of GRN postings */

			if ($debug == 1 AND ( abs($_SESSION['SuppTrans']->OvAmount/ $_SESSION['SuppTrans']->ExRate) - $LocalTotal) >0.009999){

				echo '<p>' . _('The total posted to the debit accounts is') . ' ' .
						$LocalTotal . ' ' . _('but the sum of OvAmount converted at ExRate') . ' = ' .
						( $_SESSION['SuppTrans']->OvAmount / $_SESSION['SuppTrans']->ExRate);
			}

			foreach ($_SESSION['SuppTrans']->Taxes as $Tax){
				/* Now the TAX account */
                                if ($Tax->TaxOvAmount <>0){
                                	$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
										VALUES (20,
												'" . $InvoiceNo . "',
												'" . $SQLInvoiceDate . "',
												'" . $PeriodNo . "',
												'" . $Tax->TaxGLCode . "',
												'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('Inv') . ' ' .
										 $_SESSION['SuppTrans']->SuppReference . ' ' . $Tax->TaxAuthDescription . ' ' . locale_number_format($Tax->TaxRate*100,2) . '% ' . $_SESSION['SuppTrans']->CurrCode .
										 $Tax->TaxOvAmount  . ' @ ' . _('exch rate') . ' ' . $_SESSION['SuppTrans']->ExRate . "',
												'" . ($Tax->TaxOvAmount/ $_SESSION['SuppTrans']->ExRate) . "')";

				        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the tax could not be added because');
				        $DbgMsg = _('The following SQL to insert the GL transaction was used');
				        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
                                }

			} /*end of loop to post the tax */
			/* Now the control account */

			$SQL = "INSERT INTO gltrans (type,
										typeno,
										trandate,
										periodno,
										account,
										narrative,
										amount)
								VALUES (20,
									'" . $InvoiceNo . "',
									'" . $SQLInvoiceDate . "',
									'" . $PeriodNo . "',
									'" . $_SESSION['SuppTrans']->CreditorsAct .  "',
									'" . $_SESSION['SuppTrans']->SupplierID . ' - ' . _('Inv') . ' ' .
								 $_SESSION['SuppTrans']->SuppReference . ' ' . $_SESSION['SuppTrans']->CurrCode .
								 locale_number_format( $_SESSION['SuppTrans']->OvAmount + $TaxTotal,$_SESSION['SuppTrans']->CurrDecimalPlaces)  .
								 ' @ ' . _('a rate of') . ' ' . $_SESSION['SuppTrans']->ExRate . "',
									'" .  -($LocalTotal + ( $TaxTotal / $_SESSION['SuppTrans']->ExRate)) . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction for the control total could not be added because');
			$DbgMsg = _('The following SQL to insert the GL transaction was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
			
			EnsureGLEntriesBalance(20, $InvoiceNo, $db);
		} /*Thats the end of the GL postings */

	/*Now insert the invoice into the SuppTrans table*/

		$SQL = "INSERT INTO supptrans (transno,
										type,
										supplierno,
										suppreference,
										trandate,
										duedate,
										ovamount,
										ovgst,
										rate,
										transtext,
										inputdate)
							VALUES (
								'". $InvoiceNo . "',
								20 ,
								'" . $_SESSION['SuppTrans']->SupplierID . "',
								'" . $_SESSION['SuppTrans']->SuppReference . "',
								'" . $SQLInvoiceDate . "',
								'" . FormatDateForSQL($_SESSION['SuppTrans']->DueDate) . "',
								'" . $_SESSION['SuppTrans']->OvAmount . "',
								'" . $TaxTotal . "',
								'" .  $_SESSION['SuppTrans']->ExRate . "',
								'" . $_SESSION['SuppTrans']->Comments . "',
								'" . Date('Y-m-d') ."')";

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier invoice transaction could not be added to the database because');
		$DbgMsg = _('The following SQL to insert the supplier invoice was used');
		$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		$SuppTransID = DB_Last_Insert_ID($db,'supptrans','id');

		/* Insert the tax totals for each tax authority where tax was charged on the invoice */
		foreach ($_SESSION['SuppTrans']->Taxes AS $TaxTotals) {

			$SQL = "INSERT INTO supptranstaxes (supptransid,
												taxauthid,
												taxamount)
									VALUES (
										'" . $SuppTransID . "',
										'" . $TaxTotals->TaxAuthID . "',
										'" . $TaxTotals->TaxOvAmount . "')";

			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The supplier transaction taxes records could not be inserted because');
			$DbgMsg = _('The following SQL to insert the supplier transaction taxes record was used:');
 			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}

		/* Now update the GRN and PurchOrderDetails records for amounts invoiced  - can't use the other loop through the GRNs as this was only where the GL link to credtors is active */

		foreach ($_SESSION['SuppTrans']->GRNs as $EnteredGRN){
			
			//in local currency
			$ActualCost = $EnteredGRN->ChgPrice  / $_SESSION['SuppTrans']->ExRate; 
			$PurchPriceVar = $EnteredGRN->This_QuantityInv * ($ActualCost - $EnteredGRN->StdCostUnit);
			
			$SQL = "UPDATE purchorderdetails 
					SET qtyinvoiced = qtyinvoiced + " . $EnteredGRN->This_QuantityInv .",
						actprice = '" . $EnteredGRN->ChgPrice . "'
					WHERE podetailitem = '" . $EnteredGRN->PODetailItem . "'";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity invoiced of the purchase order line could not be updated because');

			$DbgMsg = _('The following SQL to update the purchase order details was used');

			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			$SQL = "UPDATE grns 
					SET quantityinv = quantityinv + " . $EnteredGRN->This_QuantityInv . " 
					WHERE grnno = '" . $EnteredGRN->GRNNo . "'";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity invoiced off the goods received record could not be updated because');
			$DbgMsg = _('The following SQL to update the GRN quantity invoiced was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			if (mb_strlen($EnteredGRN->ShiptRef)>0 AND $EnteredGRN->ShiptRef != '0'){
				/* insert the shipment charge records */
				$SQL = "INSERT INTO shipmentcharges (shiptref,
													transtype,
													transno,
													stockid,
													value)
										VALUES (
											'" . $EnteredGRN->ShiptRef . "',
											20,
											'" . $InvoiceNo . "',
											'" . $EnteredGRN->ItemCode . "',
											'" . ($EnteredGRN->This_QuantityInv * $EnteredGRN->ChgPrice) / $_SESSION['SuppTrans']->ExRate . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment charge record for the shipment') .
							 ' ' . $EnteredGRN->ShiptRef . ' ' . _('could not be added because');
				$DbgMsg = _('The following SQL to insert the Shipment charge record was used');
				$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

			} //end of adding GRN shipment charges
				else {
			/*so its not a GRN shipment item its a plain old stock item */
				
				if ($PurchPriceVar !=0){ /* don't bother with any of this lot if there is no difference ! */
					
					if (mb_strlen($EnteredGRN->ItemCode)>0 OR $EnteredGRN->ItemCode != ''){ /*so it is a stock item */

						/*We need to: 
						 *
						 * a) update the stockmove for the delivery to reflect the actual cost of the delivery
						 *
						 * b) If a WeightedAverageCosting system and the stock quantity on hand now is negative then the cost that has gone to sales analysis and the cost of sales stock movement records will have been incorrect ... attempt to fix it retrospectively
						 */
						/*Get the location that the stock was booked into */
						$result = DB_query("SELECT intostocklocation 
											FROM purchorders 
											WHERE orderno='" . $EnteredGRN->PONo . "'",
											$db);
						$LocRow = DB_fetch_array($result);
						$LocCode = $LocRow['intostocklocation'];
						
						/* First update the stockmoves delivery cost */
						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record for the delivery could not have the cost updated to the actual cost');
						$result = DB_query("UPDATE stockmoves SET price = '" . $ActualCost . "' 
											WHERE stockid='" .$EnteredGRN->ItemCode . "' 
											AND type=25
											AND loccode='" . $LocCode . "' 
											AND transno='" . $EnteredGRN->GRNNo . "'",
											$db,$ErrMsg,$DbgMsg,True);
						
						if ($_SESSION['WeightedAverageCosting']==1){
							/*
							 * 	How many in stock now?
							 *  The quantity being invoiced here - $EnteredGRN->This_QuantityInv
							 *  If the quantity in stock now is less than the quantity being invoiced
							 *  here then some items sold will not have had this cost factored in
							 * The cost of these items = $ActualCost
							*/
		
							$sql ="SELECT quantity 
									FROM locstock 
									WHERE loccode='" . $LocCode . "'
									AND stockid='" . $EnteredGRN->ItemCode . "'";
							$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The quantity on hand could not be retrieved from the database');
							$DbgMsg = _('The following SQL to retrieve the total stock quantity was used');
							$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg);
							$QtyRow = DB_fetch_row($Result);
							$TotalQuantityOnHand = $QtyRow[0];
		
							/* If the quantity on hand is less the quantity charged on this invoice then some must have been sold and the price variance should be reflected in the cost of sales*/
		
							if ($EnteredGRN->This_QuantityInv > $TotalQuantityOnHand){
								
								/* The variance to the extent of the quantity invoiced should also be written off against the sales analysis cost - as sales analysis would have been created using the cost at the time the sale was made... this was incorrect as hind-sight has shown here. However, how to determine when these were last sold? To update the sales analysis cost. Work through the last 6 months sales analysis from the latest period in which this invoice is being posted and prior. 
								
								The assumption here is that the goods have been sold prior to the purchase invoice  being entered so it is necessary to back track on the sales analysis cost.
								* Note that this will mean that posting to GL COGS will not agree to the cost of sales from the sales analysis
								* Of course the price variances will need to be included in COGS as well
								* */
		
								$QuantityVarianceAllocated = $EnteredGRN->This_QuantityInv;
								$CostVarPerUnit = $ActualCost - $EnteredGRN->StdCostUnit;
								$PeriodAllocated = $PeriodNo;
								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales analysis records could not be updated for the cost variances on this purchase invoice');
								
								while ($QuantityVarianceAllocated >0) {
									$SalesAnalResult=DB_query("SELECT cust,
																	custbranch,
																	typeabbrev,
																	periodno,
																	stkcategory,
																	area,
																	salesperson,
																	cost,
																	qty
																FROM salesanalysis
																WHERE salesanalysis.stockid = '" . $EnteredGRN->ItemCode . "'
																AND salesanalysis.budgetoractual=1
																AND periodno='" . $PeriodAllocated . "'",
																$db);
									if (DB_num_rows($SalesAnalResult)>0){
										while ($SalesAnalRow = DB_fetch_array($SalesAnalResult) AND $QuantityVarianceAllocated >0){
											if ($SalesAnalRow['qty']<=$QuantityVarianceAllocated){
												$QuantityVarianceAllocated -= $SalesAnalRow['qty'];
												$QuantityAllocated = $SalesAnalRow['qty'];
											} else {
												$QuantityAllocated = $QuantityVarianceAllocated;
												$QuantityVarianceAllocated=0;
											}
											$UpdSalAnalResult = DB_query("UPDATE salesanalysis
																			SET cost = cost + " . ($CostVarPerUnit * $QuantityAllocated) . "
																			WHERE cust ='" . $SalesAnalRow['cust'] . "'
																			AND stockid='" . $EnteredGRN->ItemCode . "'
																			AND custbranch='" . $SalesAnalRow['custbranch'] . "'
																			AND typeabbrev='" . $SalesAnalRow['typeabbrev'] . "'
																			AND periodno='" . $PeriodAllocated . "'
																			AND area='" . $SalesAnalRow['area'] . "'
																			AND salesperson='" . $SalesAnalRow['salesperson'] . "'
																			AND stkcategory='" . $SalesAnalRow['stkcategory'] . "'
																			AND budgetoractual=1",
																			$db,$ErrMsg,$DbgMsg,True);
										}
									} //end if there were sales in that period
									$PeriodAllocated--; //decrement the period
									if ($PeriodNo - $PeriodAllocated >6) {
										/*if more than 6 months ago when sales were made then forget it */
										break;
									}
								} /*end loop around different periods to see which sales analysis records to update */
								
								/*now we need to work back through the sales stockmoves up to the quantity on this purchase invoice to update costs
								 * Only go back up to 6 months looking for stockmoves and 
								 * Only in the stock location where the purchase order was received
								 * into - if the stock was transferred to another location then
								 * we cannot adjust for this */
								$result = DB_query("SELECT stkmoveno,
															type,
															qty,
															standardcost
													FROM stockmoves 
													WHERE loccode='" . $LocCode . "'
													AND qty < 0
													AND stockid='" . $EnteredGRN->ItemCode . "'
													AND trandate>='" . FormatDateForSQL(DateAdd($_SESSION['SuppTrans']->TranDate,'m',-6)) . "'
													ORDER BY stkmoveno DESC",
													$db);
								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movements for invoices cannot be updated for the cost variances on this purchase invoice');
								$QuantityVarianceAllocated = $EnteredGRN->This_QuantityInv;
								while ($StkMoveRow = DB_fetch_array($result) AND $QuantityVarianceAllocated >0){
									if ($StkMoveRow['qty']+$QuantityVarianceAllocated>0){
										if ($StkMoveRow['type']==10) { //its a sales invoice
											$result = DB_query("UPDATE stockmoves
																SET standardcost = '" . $ActualCost . "'
																WHERE stkmoveno = '" . $StkMoveRow['stkmoveno'] . "'",
																$db,$ErrMsg,$DbgMsg,True);
										}
									} //end if the invoice qty is more than is left to allocate
									$QuantityVarianceAllocated+=$StkMoveRow['qty'];
								}
							} // end if the quantity being invoiced here is greater than the current stock on hand

							/*Now to update the stock cost with the new weighted average */
		
							/*Need to consider what to do if the cost has been changed manually between receiving the stock and entering the invoice - this code assumes there has been no cost updates made manually and all the price variance is posted to stock.
		
							A nicety or important?? */
		
		
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost could not be updated because');
							$DbgMsg = _('The following SQL to update the cost was used');
		
							if ($TotalQuantityOnHand>0) {
		
								$CostIncrement = ($PurchPriceVar - $WriteOffToVariances) / $TotalQuantityOnHand;
		
								$sql = "UPDATE stockmaster 
										SET lastcost=materialcost+overheadcost+labourcost,
										materialcost=materialcost+" . $CostIncrement . " 
										WHERE stockid='" . $EnteredGRN->ItemCode . "'";
								$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, True);
							} else { 
								/* if stock is negative then update the cost to this cost */
								$sql = "UPDATE stockmaster 
										SET lastcost=materialcost+overheadcost+labourcost,
											materialcost='" . ($EnteredGRN->ChgPrice /  $_SESSION['SuppTrans']->ExRate) . "'
										WHERE stockid='" . $EnteredGRN->ItemCode . "'";
								$Result = DB_query($sql, $db, $ErrMsg, $DbgMsg, True);
							}
						} /* End if it is weighted average costing we are working with */
					} /*Its a stock item */
				} /* There was a price variance */
			}
			if ($EnteredGRN->AssetID!=0) { //then it is an asset

				if ($PurchPriceVar !=0) {
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
													20,
													'" . $InvoiceNo . "',
													'" . $SQLInvoiceDate . "',
													'" . $PeriodNo . "',
													'" . Date('Y-m-d') . "',
													'cost',
													'" . ($PurchPriceVar) . "')";
					$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE The fixed asset transaction could not be inserted because');
					$DbgMsg = _('The following SQL to insert the fixed asset transaction record was used');
					$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
	
					/*Now update the asset cost in fixedassets table */
					$SQL = "UPDATE fixedassets SET cost = cost + " . ($PurchPriceVar)  . " 
							WHERE assetid = '" . $EnteredGRN->AssetID . "'";
							
					$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE. The fixed asset cost could not be updated because:');
					$DbgMsg = _('The following SQL was used to attempt the update of the asset cost:');
					$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
				} //end if there was a difference in the cost
			} //the item was an asset received on a purchase order
		} /* end of the GRN loop to do the updates for the quantity of order items the supplier has invoiced */

		/*Add shipment charges records as necessary */
 		foreach ($_SESSION['SuppTrans']->Shipts as $ShiptChg){

			$SQL = "INSERT INTO shipmentcharges (shiptref,
												transtype,
												transno,
												value)
									VALUES ('" . $ShiptChg->ShiptRef . "',
												'20',
											'" . $InvoiceNo . "',
											'" . $ShiptChg->Amount/ $_SESSION['SuppTrans']->ExRate . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The shipment charge record for the shipment') .
			' ' . $ShiptChg->ShiptRef . ' ' . _('could not be added because');

			$DbgMsg = _('The following SQL to insert the Shipment charge record was used');

			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);

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
									VALUES ('" . $Contract->ContractRef . "',
										'20',
										'" . $InvoiceNo . "',
										'" . $Contract->Amount/ $_SESSION['SuppTrans']->ExRate . "',
										'" . $Contract->Narrative . "',
										'" . $Anticipated . "')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The contract charge record for contract') . ' ' . $Contract->ContractRef . ' ' . _('could not be added because');
			$DbgMsg = _('The following SQL to insert the contract charge record was used');
			$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, True);
		}

		foreach ($_SESSION['SuppTrans']->Assets as $AssetAddition){

			/*Asset additions need to have
			 * 	1. A fixed asset transaction inserted for the cost
			 * 	2. A general ledger transaction to fixed asset cost account if creditors linked
			 * 	3. The fixedasset table cost updated by the addition
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
											20,
											'" . $InvoiceNo . "',
											'" . $SQLInvoiceDate . "',
											'" . $PeriodNo . "',
											'" . Date('Y-m-d') . "',
											'" . _('cost') . "',
											'" . ($AssetAddition->Amount / $_SESSION['SuppTrans']->ExRate)  . "')";
			$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE The fixed asset transaction could not be inserted because');
			$DbgMsg = _('The following SQL to insert the fixed asset transaction record was used');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

			/*Now update the asset cost in fixedassets table */
			$result = DB_query("SELECT datepurchased
								FROM fixedassets
								WHERE assetid='" . $AssetAddition->AssetID . "'",$db);
			$AssetRow = DB_fetch_array($result);

			$SQL = "UPDATE fixedassets SET cost = cost + " . ($AssetAddition->Amount  / $_SESSION['SuppTrans']->ExRate) ;
			if ($AssetRow['datepurchased']=='0000-00-00'){
				$SQL .= ", datepurchased='" . $SQLInvoiceDate . "'";
			}
			$SQL .= " WHERE assetid = '" . $AssetAddition->AssetID . "'";
			$ErrMsg = _('CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE. The fixed asset cost and date purchased was not able to be updated because:');
			$DbgMsg = _('The following SQL was used to attempt the update of the cost and the date the asset was purchased');
			$Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);
		} //end of non-gl fixed asset stuff

		$Result = DB_Txn_Commit($db);

		prnMsg(_('Supplier invoice number') . ' ' . $InvoiceNo . ' ' . _('has been processed'),'success');
		echo '<br />
				<div class="centre">
					<a href="' . $rootpath . '/SupplierInvoice.php?&SupplierID=' .$_SESSION['SuppTrans']->SupplierID . '">' . _('Enter another Invoice for this Supplier') . '</a>
				</div>';
		unset( $_SESSION['SuppTrans']->GRNs);
		unset( $_SESSION['SuppTrans']->Shipts);
		unset( $_SESSION['SuppTrans']->GLCodes);
		unset( $_SESSION['SuppTrans']->Contracts);
		unset( $_SESSION['SuppTrans']);
	}

} /*end of process invoice */

echo '</form>';
include('includes/footer.inc');
?>