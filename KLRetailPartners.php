<?php

/* Defines the KL retail Partners */

include('includes/session.php');
$Title = _('KL retail Partners Maintenance');// Screen identification.
$ViewTopic = '';// Filename's id in ManualContents.php's TOC.
$BookMark = '';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/supplier.png" title="',// Icon image.
	_('Setup'), '" /> ',// Icon title.
	_('KL Retail Partners Maintenance'), '</p>';// Page title.

include('includes/CountriesArray.php');

if(isset($_GET['SelectedPartner'])) {
	$SelectedPartner = $_GET['SelectedPartner'];
} elseif(isset($_POST['SelectedPartner'])) {
	$SelectedPartner = $_POST['SelectedPartner'];
}

if(isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	$_POST['PartnerCode']=mb_strtoupper($_POST['PartnerCode']);
	if(trim($_POST['PartnerCode']) == '') {
		$InputError = 1;
		prnMsg(_('The Partner Code code may not be empty'), 'error');
	}

	if(isset($SelectedPartner) AND $InputError !=1) {

		$sql = "UPDATE klretailpartners 
				SET partnername ='" . $_POST['PartnerName'] . "',
					partneraddress ='" . $_POST['PartnerAddress'] . "',
					partnernpwp ='" . $_POST['PartnerNPWP'] . "',
					ppn ='" . $_POST['PPN'] . "',
					areasalescreditcard ='" . $_POST['AreaSalesCreditCard'] . "',
					areasalescash ='" . $_POST['AreaSalesCash'] . "',
					areasalescashothers ='" . $_POST['AreaSalesCashOthers'] . "',
					cashsalesreported ='" . $_POST['CashSalesReported'] . "',
					hppcompensation ='" . $_POST['HPPCompensation'] . "',
					accounthppcompensation='" . $_POST['AccountHPPCompensation'] . "',
					accountbankdanamon='" . $_POST['AccountBankDanamon'] . "',
					accountbankmandiri = '" . $_POST['AccountBankMandiri'] . "',
					accountbankbca ='" . $_POST['AccountBankBCA'] . "',
					accountcomissioncreditcard ='" . $_POST['AccountComissionCreditCard'] . "',
					comissionccdanamon = '" . $_POST['ComissionCCDanamon'] . "',
					comissionamexdanamon = '" . $_POST['ComissionAmexDanamon'] . "',
					comissionccmandiri = '" . $_POST['ComissionCCMandiri'] . "',
					comissionccbca = '" . $_POST['ComissionCCBCA'] . "',
					comissionamexbca = '" . $_POST['ComissionAmexBCA'] . "',
					percentconsignmentptadu = '" . $_POST['PercentConsignmentTADU'] . "',
					accountconsignmentsalesptadu = '" . $_POST['AccountConsignmentSalesPTADU'] . "',
					accountconsignmentcogspartner = '" . $_POST['AccountConsignmentCOGSPartner'] . "',
					counterinvoicea = '" . $_POST['CounterInvoiceA'] . "',
					counterinvoiceb = '" . $_POST['CounterInvoiceB'] . "',
					counterinvoicec = '" . $_POST['CounterInvoiceC'] . "'
				WHERE partnercode = '" . $SelectedPartner . "'";

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedPartner . ' ' . _('retail partner record because');
		$DbgMsg = _('The SQL used to update the retail partner record was');

		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		prnMsg(_('The retail partner record has been updated'),'success');

		unset($SelectedPartner);
		unset($_POST['PartnerCode']);
		unset($_POST['PartnerName']);
		unset($_POST['PartnerAddress']);
		unset($_POST['PartnerNPWP']);
		unset($_POST['PPN']);
		unset($_POST['AreaSalesCreditCard']);
		unset($_POST['AreaSalesCash']);
		unset($_POST['AreaSalesCashOthers']);
		unset($_POST['CashSalesReported']);
		unset($_POST['HPPCompensation']);
		unset($_POST['AccountHPPCompensation']);
		unset($_POST['AccountBankDanamon']);
		unset($_POST['AccountBankMandiri']);
		unset($_POST['AccountBankBCA']);
		unset($_POST['AccountComissionCreditCard']);
		unset($_POST['ComissionCCDanamon']);
		unset($_POST['ComissionAmexDanamon']);
		unset($_POST['ComissionCCMandiri']);
		unset($_POST['ComissionCCBCA']);
		unset($_POST['ComissionAmexBCA']);
		unset($_POST['PercentConsignmentTADU']);
		unset($_POST['AccountConsignmentSalesPTADU']);
		unset($_POST['AccountConsignmentCOGSPartner']);
		unset($_POST['CounterInvoiceA']);
		unset($_POST['CounterInvoiceB']);
		unset($_POST['CounterInvoiceC']);

	} elseif($InputError !=1) {

		/*SelectedPartner is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$sql = "INSERT INTO klretailpartners 
								(partnercode,
								partnername,
								partneraddress,
								partnernpwp,
								ppn,
								areasalescreditcard,
								areasalescash,
								areasalescashothers,
								cashsalesreported,
								hppcompensation,
								accounthppcompensation,
								accountbankdanamon,
								accountbankmandiri,
								accountbankbca,
								accountcomissioncreditcard,
								comissionccdanamon,
								comissionamexdanamon,
								comissionccmandiri,
								comissionccbca,
								comissionamexbca,
								percentconsignmentptadu,
								accountconsignmentsalesptadu,
								accountconsignmentcogspartner,
								counterinvoicea,
								counterinvoiceb,
								counterinvoicec)
						VALUES ('" . $_POST['PartnerCode'] . "',
								'" . $_POST['PartnerName'] . "',
								'" . $_POST['PartnerAddress'] ."',
								'" . $_POST['PartnerNPWP'] ."',
								'" . $_POST['PPN'] . "',
								'" . $_POST['AreaSalesCreditCard'] . "',
								'" . $_POST['AreaSalesCash'] . "',
								'" . $_POST['AreaSalesCashOthers'] . "',
								'" . $_POST['CashSalesReported'] . "',
								'" . $_POST['HPPCompensation'] . "',
								'" . $_POST['AccountHPPCompensation'] . "',
								'" . $_POST['AccountBankDanamon'] . "',
								'" . $_POST['AccountBankMandiri'] . "',
								'" . $_POST['AccountBankBCA'] . "',
								'" . $_POST['AccountComissionCreditCard'] . "',
								'" . $_POST['ComissionCCDanamon'] . "',
								'" . $_POST['ComissionAmexDanamon'] . "',
								'" . $_POST['ComissionCCMandiri'] . "',
								'" . $_POST['ComissionCCBCA'] . "',
								'" . $_POST['ComissionAmexBCA'] . "',
								'" . $_POST['PercentConsignmentTADU'] . "',
								'" . $_POST['AccountConsignmentSalesPTADU'] . "',
								'" . $_POST['AccountConsignmentCOGSPartner'] . "',
								'" . $_POST['CounterInvoiceA'] . "',
								'" . $_POST['CounterInvoiceB'] . "',
								'" . $_POST['CounterInvoiceC'] . "')";

		$ErrMsg = _('An error occurred inserting the new retail partner record because');
		$DbgMsg = _('The SQL used to insert the retail partner record was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		prnMsg(_('The new retail partner record has been added'),'success');

		unset($SelectedPartner);
		unset($_POST['PartnerCode']);
		unset($_POST['PartnerName']);
		unset($_POST['PartnerAddress']);
		unset($_POST['PartnerNPWP']);
		unset($_POST['PPN']);
		unset($_POST['AreaSalesCreditCard']);
		unset($_POST['AreaSalesCash']);
		unset($_POST['AreaSalesCashOthers']);
		unset($_POST['CashSalesReported']);
		unset($_POST['HPPCompensation']);
		unset($_POST['AccountHPPCompensation']);
		unset($_POST['AccountBankMandiri']);
		unset($_POST['AccountBankBCA']);
		unset($_POST['AccountComissionCreditCard']);
		unset($_POST['ComissionCCDanamon']);
		unset($_POST['AccountBankDanamon']);
		unset($_POST['ComissionAmexDanamon']);
		unset($_POST['Priority']);
		unset($_POST['ComissionCCBCA']);
		unset($_POST['ComissionAmexBCA']);
		unset($_POST['PercentConsignmentTADU']);
		unset($_POST['AccountConsignmentSalesPTADU']);
		unset($_POST['AccountConsignmentCOGSPartner']);
		unset($_POST['CounterInvoiceA']);
		unset($_POST['CounterInvoiceB']);
		unset($_POST['CounterInvoiceC']);
	}

} elseif(isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$CancelDelete = 0;
	// PREVENT DELETES IF DEPENDENT RECORDS
	$sql= "SELECT COUNT(*) FROM locations WHERE partnercode='". $SelectedPartner . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this retal partner because there are locations related to it.'),'warn');
		echo _('There are') . ' ' . $myrow[0] . ' ' . _('locations using this retail partner');
	}
	if(! $CancelDelete) {
		$result = DB_query("DELETE FROM klretailpartners WHERE partnercode='" . $SelectedPartner . "'");
		prnMsg(_('Retail Partner') . ' ' . $SelectedPartner . ' ' . _('has been deleted') . '!', 'success');
		unset ($SelectedPartner);
	}//end if Delete Retail Partner
	unset($SelectedPartner);
	unset($_GET['delete']);
}

if(!isset($SelectedPartner)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPartner will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Locations will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT partnercode,
				partnername,
				ppn,
				cashsalesreported,
				hppcompensation,
				percentconsignmentptadu
			FROM klretailpartners
			ORDER BY partnername";
	$result = DB_query($sql);

	if(DB_num_rows($result)==0) {
		prnMsg(_('There are no retail partners'),'error');
	}

	echo '<table class="selection">
		<tr>
			<th class="ascending">', _('Code'), '</th>
			<th class="ascending">', _('Name'), '</th>
			<th class="ascending">', _('PPN'), '</th>
			<th class="ascending">', _('Cash Reported'), '</th>
			<th class="ascending">', _('HPP Compensation'), '</th>
			<th class="ascending">', _('Consign PTADU'), '</th>
			<th class="noprint" colspan="2">&nbsp;</th>
		</tr>';

$k=0;//row colour counter
while ($myrow = DB_fetch_array($result)) {
	if($k==1) {
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	printf('<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="noprint"><a href="%sSelectedPartner=%s">' . _('Edit') . '</a></td>
			<td class="noprint"><a href="%sSelectedPartner=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this inventory location?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['partnercode'],
			$myrow['partnername'],
			locale_number_format($myrow['ppn'],0) . "%",
			locale_number_format($myrow['cashsalesreported'],0) . "%",
			locale_number_format($myrow['hppcompensation'],0) . "%",
			locale_number_format($myrow['percentconsignmentptadu'],0) . "%",
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['PartnerCode'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['PartnerCode']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

echo '<br />';
if(isset($SelectedPartner)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Records') . '</a>';
}
echo '<br />';

if(!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(isset($SelectedPartner)) {
		//editing an existing Location

		$sql = "SELECT partnercode,
					partnername,
					partneraddress,
					partnernpwp,
					ppn,
					areasalescreditcard,
					areasalescash,
					areasalescashothers,
					accountbankdanamon,
					hppcompensation,
					cashsalesreported,
					accounthppcompensation,
					accountbankmandiri,
					accountbankbca,
					accountcomissioncreditcard,
					comissionccdanamon,
					comissionamexdanamon,
					comissionccmandiri,
					comissionccbca,
					comissionamexbca,
					percentconsignmentptadu,
					accountconsignmentsalesptadu,
					accountconsignmentcogspartner,
					counterinvoicea,
					counterinvoiceb,
					counterinvoicec
				FROM klretailpartners
				WHERE partnercode='" . $SelectedPartner . "'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['PartnerCode'] = $myrow['partnercode'];
		$_POST['PartnerName'] = $myrow['partnername'];
		$_POST['PartnerAddress'] = $myrow['partneraddress'];
		$_POST['PartnerNPWP'] = $myrow['partnernpwp'];
		$_POST['PPN'] = $myrow['ppn'];
		$_POST['AreaSalesCreditCard'] = $myrow['areasalescreditcard'];
		$_POST['AreaSalesCash'] = $myrow['areasalescash'];
		$_POST['AreaSalesCashOthers'] = $myrow['areasalescashothers'];
		$_POST['AccountBankDanamon'] = $myrow['accountbankdanamon'];
		$_POST['CashSalesReported'] = $myrow['cashsalesreported'];
		$_POST['HPPCompensation'] = $myrow['hppcompensation'];
		$_POST['AccountHPPCompensation'] = $myrow['accounthppcompensation'];
		$_POST['AccountBankMandiri'] = $myrow['accountbankmandiri'];
		$_POST['AccountBankBCA'] = $myrow['accountbankbca'];
		$_POST['AccountComissionCreditCard'] = $myrow['accountcomissioncreditcard'];
		$_POST['ComissionCCDanamon'] = $myrow['comissionccdanamon'];
		$_POST['ComissionAmexDanamon'] = $myrow['comissionamexdanamon'];
		$_POST['Priority'] = $myrow['comissionccmandiri'];
		$_POST['ComissionCCBCA'] = $myrow['comissionccbca'];
		$_POST['ComissionAmexBCA'] = $myrow['comissionamexbca'];
		$_POST['PercentConsignmentTADU'] = $myrow['percentconsignmentptadu'];
		$_POST['AccountConsignmentSalesPTADU'] = $myrow['accountconsignmentsalesptadu'];
		$_POST['AccountConsignmentCOGSPartner'] = $myrow['accountconsignmentcogspartner'];
		$_POST['CounterInvoiceA'] = $myrow['counterinvoicea'];
		$_POST['CounterInvoiceB'] = $myrow['counterinvoiceb'];
		$_POST['CounterInvoiceC'] = $myrow['counterinvoicec'];

		echo '<input type="hidden" name="SelectedPartner" value="' . $SelectedPartner . '" />';
		echo '<input type="hidden" name="PartnerCode" value="' . $_POST['PartnerCode'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . _('Amend Retail Partner details') . '</th>
			</tr>';
		echo '<tr>
				<td>' . _('Retail Partner Code') . ':</td>
				<td>' . $_POST['PartnerCode'] . '</td>
			</tr>';
	} else {//end of if $SelectedPartner only do the else when a new record is being entered
		if(!isset($_POST['PartnerCode'])) {
			$_POST['PartnerCode'] = '';
		}
		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('New Retail Partner details') . '</h3></th>
				</tr>';
		echo '<tr>
				<td>' . _('Partner Code') . ':</td>
				<td><input type="text" autofocus="autofocus" required="required" title="' . _('Enter up to 20 characters for the retail partner code') . '" data-type="no-illegal-chars" name="PartnerCode" value="' . $_POST['PartnerCode'] . '" size="20" maxlength="20" /></td>
			</tr>';
	}
	if(!isset($_POST['PartnerName'])) {
		$_POST['PartnerName'] = '';
	}
	if(!isset($_POST['PartnerAddress'])) {
		$_POST['PartnerAddress'] = ' ';
	}
	if(!isset($_POST['PartnerNPWP'])) {
		$_POST['PartnerNPWP'] = '';
	}
	if(!isset($_POST['PPN'])) {
		$_POST['PPN'] = 0;
	}
	if(!isset($_POST['AreaSalesCreditCard'])) {
		$_POST['AreaSalesCreditCard'] = '';
	}
	if(!isset($_POST['AreaSalesCash'])) {
		$_POST['AreaSalesCash'] = '';
	}
	if(!isset($_POST['AreaSalesCashOthers'])) {
		$_POST['AreaSalesCashOthers'] = '';
	}
	if(!isset($_POST['CashSalesReported'])) {
		$_POST['CashSalesReported'] = 0;
	}
	if(!isset($_POST['HPPCompensation'])) {
		$_POST['HPPCompensation'] = 0;
	}
	if(!isset($_POST['AccountHPPCompensation'])) {
		$_POST['AccountHPPCompensation'] = '';
	}
	if(!isset($_POST['AccountBankDanamon'])) {
		$_POST['AccountBankDanamon'] = '';
	}
	if(!isset($_POST['AccountBankMandiri'])) {
		$_POST['AccountBankMandiri'] = '';
	}
	if(!isset($_POST['AccountBankBCA'])) {
		$_POST['AccountBankBCA'] = '';
	}
	if(!isset($_POST['AccountComissionCreditCard'])) {
		$_POST['AccountComissionCreditCard'] = '';
	}
	if(!isset($_POST['ComissionCCDanamon'])) {
		$_POST['ComissionCCDanamon'] = 0;
	}
	if(!isset($_POST['ComissionAmexDanamon'])) {
		$_POST['ComissionAmexDanamon'] = 0;
	}
	if(!isset($_POST['ComissionCCMandiri'])) {
		$_POST['ComissionCCMandiri'] = 0;
	}
	if(!isset($_POST['ComissionCCBCA'])) {
		$_POST['ComissionCCBCA'] = 0;
	}
	if(!isset($_POST['ComissionAmexBCA'])) {
		$_POST['ComissionAmexBCA'] = 0;
	}
	if(!isset($_POST['PercentConsignmentTADU'])) {
		$_POST['PercentConsignmentTADU'] = 0;
	}
	if(!isset($_POST['AccountConsignmentSalesPTADU'])) {
		$_POST['AccountConsignmentSalesPTADU'] = '';
	}
	if(!isset($_POST['AccountConsignmentCOGSPartner'])) {
		$_POST['AccountConsignmentCOGSPartner'] = '';
	}
	if(!isset($_POST['CounterInvoiceA'])) {
		$_POST['CounterInvoiceA'] = 0;
	}
	if(!isset($_POST['CounterInvoiceB'])) {
		$_POST['CounterInvoiceB'] = 0;
	}
	if(!isset($_POST['CounterInvoiceC'])) {
		$_POST['CounterInvoiceC'] = 0;
	}

	echo '<tr>
			<td>' . _('Partner Name') . ':' . '</td>
			<td><input type="text" name="PartnerName" required="required" value="'. $_POST['PartnerName'] . '" title="' . _('Enter the retail partner name') . '" namesize="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Address') . ':' . '</td>
			<td><input type="text" name="PartnerAddress" value="' . $_POST['PartnerAddress'] . '" size="101" maxlength="100" /></td>
		</tr>
		<tr>
			<td>' . _('NPWP') . ':' . '</td>
			<td><input type="text" name="PartnerNPWP" value="' . $_POST['PartnerNPWP'] . '" size="21" maxlength="20" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('% PPN') . ':</td>
			<td><input type="text" name="PPN" class="number" title="' . _('PPN to apply') . '" value="' . $_POST['PPN'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('Credit Card Sales Area ') . ':' . '</td>
		<td><select name="AreaSalesCreditCard">';
	$AreaSales = DB_query("SELECT areacode, areadescription FROM areas ORDER BY areadescription");
	while ($myrow=DB_fetch_array($AreaSales)) {
		if($_POST['AreaSalesCreditCard']==$myrow['areacode']) {
			echo '<option selected="selected" value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';

/*	echo '<tr>
			<td>' . _('Delivery Address 3 (Suburb)') . ':' . '</td>
			<td><input type="text" name="PPN" value="' . $_POST['PPN'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 4 (City)') . ':' . '</td>
			<td><input type="text" name="AreaSalesCreditCard" value="' . $_POST['AreaSalesCreditCard'] . '" size="41" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Delivery Address 5 (Zip Code)') . ':' . '</td>
			<td><input type="text" name="AreaSalesCash" value="' . $_POST['AreaSalesCash'] . '" size="21" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('Country') . ':</td>
			<td><select name="AreaSalesCashOthers">';
	foreach ($CountriesArray as $CountryEntry => $CountryName) {
		if(isset($_POST['AreaSalesCashOthers']) AND (strtoupper($_POST['AreaSalesCashOthers']) == strtoupper($CountryName))) {
			echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
		} elseif(!isset($_POST['Address6']) AND $CountryName == "") {
			echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
		} else {
			echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
		}
	}
	echo '</select></td>
	</tr>
	<tr>
		<td>' . _('CashSalesReportedephone No') . ':' . '</td>
		<td><input name="CashSalesReported" type="cashsalesreported" pattern="[0-9+\-\s()]*" value="' . $_POST['CashSalesReported'] . '" size="31" maxlength="30" title="' . _('The phone number should consist of numbers, spaces, parentheses, or the + character') . '" /></td>
	</tr>
	<tr>
		<td>' . _('Facsimile No') . ':' . '</td>
		<td><input name="HPPCompensation" type="cashsalesreported" pattern="[0-9+\-\s()]*" value="' . $_POST['HPPCompensation'] . '" size="31" maxlength="30" title="' . _('The hppcompensation number should consist of numbers, parentheses, spaces or the + character') . '"/></td>
	</tr>';
	// AccountHPPCompensation address:
	echo '<tr title="', _('The accounthppcompensation address should be an accounthppcompensation format such as adm@weberp.org'), '">
		<td><label for="AccountHPPCompensation">', _('AccountHPPCompensation'), ':</label></td>
		<td><input id="AccountHPPCompensation" maxlength="55" name="AccountHPPCompensation" size="31" type="accounthppcompensation" value="', $_POST['AccountHPPCompensation'], '" /></td>
	</tr>';

	// Tax Province:
	echo '<tr>
		<td>' . _('Tax Province') . ':' . '</td>
		<td><select name="AccountBankMandiri">';

	$AccountBankMandiriResult = DB_query("SELECT accountbankmandiri, taxprovincename FROM taxprovinces");
	while ($myrow=DB_fetch_array($AccountBankMandiriResult)) {
		if($_POST['AccountBankMandiri']==$myrow['accountbankmandiri']) {
			echo '<option selected="selected" value="' . $myrow['accountbankmandiri'] . '">' . $myrow['taxprovincename'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountbankmandiri'] . '">' . $myrow['taxprovincename'] . '</option>';
		}
	}

	echo '</select></td>
		</tr>
		<tr>
			<td>' . _('Default Counter Sales Customer Code') . ':' . '</td>
			<td><input type="text" name="AccountBankBCA" data-type="no-illegal-chars" title="' . _('If counter sales are being used for this location then an existing customer account code needs to be entered here. All sales created from the counter sales will be recorded against this customer account') . '" value="' . $_POST['AccountBankBCA'] . '" size="11" maxlength="10" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Counter Sales Branch Code') . ':' . '</td>
			<td><input type="text" name="AccountComissionCreditCard" data-type="no-illegal-chars" title="' . _('If counter sales are being used for this location then an existing customer branch code for the customer account code entered above needs to be entered here. All sales created from the counter sales will be recorded against this branch') . '" value="' . $_POST['AccountComissionCreditCard'] . '" size="11" maxlength="10" /></td>
		</tr>';
	echo '
		<tr>
			<td>' . _('KL Priority for KL Smart Transfers') . ':' . '</td>
			<td><input type="text" name="Priority" class="number" title="' . _('Priority for KL Shops Smart Transfers 1-Max Priority 9-Min Priority') . '" value="' . $_POST['Priority'] . '" size="1" maxlength="1" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('KL Smart Transfers from') . ':</td>
			<td><input type="text" name="ComissionCCBCA" title="' . _('Enter the location code where KL Smart Transfers must pull stock to this location (usually KANTO)') . '" data-type="no-illegal-chars" name="PartnerCode" value="' . $_POST['ComissionCCBCA'] . '" size="5" maxlength="5" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('KL Smart Transfers # max models') . ':</td>
			<td><input type="text" name="ComissionAmexBCA" class="number" title="' . _('Enter the maximum number of models to be included in KL Smart Transfers') . '" name="MaxModels" value="' . $_POST['ComissionAmexBCA'] . '" size="5" maxlength="5" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('KL Smart Transfers # min models') . ':</td>
			<td><input type="text" name="AccountConsignmentSalesPTADU" class="number" title="' . _('Enter the minimum number of models to be included in KL Smart Transfers') . '" name="MinModels" value="' . $_POST['AccountConsignmentSalesPTADU'] . '" size="5" maxlength="5" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('KL Yearly Rent IDR (Shops Only)') . ':</td>
			<td><input type="text" name="AccountConsignmentCOGSPartner" class="number" title="' . _('Enter the yearly rent in IDR') . '" name="AccountConsignmentCOGSPartner" value="' . $_POST['AccountConsignmentCOGSPartner'] . '" size="12" maxlength="12" /></td>
		</tr>';
	// POS Cash GL Account
	echo '<tr title="', _('Enter the KL POS Cash GL account for this location, or leave it in blank if not needed'), '">
			<td><label for="GLAccountCode">', _('KL POS Cash GL Account'), ':</label></td>
			<td><input data-type="no-illegal-chars" id="CounterInvoiceA" maxlength="20" name="CounterInvoiceA" size="20" type="text" value="', $_POST['CounterInvoiceA'], '" /></td></tr>';
	// POS Tag
	echo '<tr title="', _('Enter the KL POS Tag code for this location, or leave it in blank if not needed'), '">
			<td><label for="CounterInvoiceB">', _('KL POS Tag'), ':</label></td>
			<td><input data-type="no-illegal-chars" id="CounterInvoiceB" maxlength="20" name="CounterInvoiceB" size="4" type="text" value="', $_POST['CounterInvoiceB'], '" /></td></tr>';

	// Location CounterInvoiceC:
	echo '<tr>
		<td>' . _('KL Location CounterInvoiceC') . ':' . '</td>
		<td><select name="CounterInvoiceC">';

	$CounterInvoiceCsResult = DB_query("SELECT code, description FROM locationcounterinvoicecs ORDER BY description");
	while ($myrow=DB_fetch_array($CounterInvoiceCsResult)) {
		if($_POST['CounterInvoiceC']==$myrow['code']) {
			echo '<option selected="selected" value="' . $myrow['code'] . '">' . $myrow['description'] . '</option>';
		} else {
			echo '<option value="' . $myrow['code'] . '">' . $myrow['description'] . '</option>';
		}
	}

	// Location Type:
	echo '<tr>
		<td>' . _('KL Location Type') . ':' . '</td>
		<td><select name="TypeLoc">';

	$TypesResult = DB_query("SELECT code, description FROM locationtypes ORDER BY description");
	while ($myrow=DB_fetch_array($TypesResult)) {
		if($_POST['TypeLoc']==$myrow['code']) {
			echo '<option selected="selected" value="' . $myrow['code'] . '">' . $myrow['description'] . '</option>';
		} else {
			echo '<option value="' . $myrow['code'] . '">' . $myrow['description'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	// Retail Partner:
	echo '<tr>
		<td>' . _('KL Retail Partner') . ':' . '</td>
		<td><select name="PartnerCode">';

	$TypesResult = DB_query("SELECT partnercode, partnername FROM klretailpartners ORDER BY partnername");
	while ($myrow=DB_fetch_array($TypesResult)) {
		if($_POST['PartnerCode']==$myrow['partnercode']) {
			echo '<option selected="selected" value="' . $myrow['partnercode'] . '">' . $myrow['partnername'] . '</option>';
		} else {
			echo '<option value="' . $myrow['partnercode'] . '">' . $myrow['partnername'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>';

	echo '<tr>
			<td>' . _('With Stock ready To Sell?') . ':</td>
			<td><select name="StockReadyToSell">';
	if($_POST['StockReadyToSell']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		echo '<option value="0">' . _('No') . '</option>';
	} else {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	echo '</select></td></tr>';
		
	echo '<tr>
			<td>' . _('With Stock Available for Shop Online?') . ':</td>
			<td><select name="StockAvailableForOnline">';
	if($_POST['StockAvailableForOnline']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		echo '<option value="0">' . _('No') . '</option>';
	} else {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('KL RL Factor for Packaging Transfers') . ':' . '</td>
			<td><input type="text" name="RLFactorForPackaging" class="number" title="' . _('Factor to Multiply Reorder Level for Packaging Transfers') . '" value="' . $_POST['RLFactorForPackaging'] . '" size="4" maxlength="4" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('KL RL Days for Packaging Transfers') . ':' . '</td>
			<td><input type="text" name="RLDaysForPackaging" class="number" title="' . _('Set Reorder Level as needs of pacjaking for a number of days') . '" value="' . $_POST['RLDaysForPackaging'] . '" size="2" maxlength="2" /></td>
		</tr>';

		echo '<tr>
			<td>' . _('Allow internal requests?') . ':</td>
			<td><select name="ComissionAmexDanamon">';
	if($_POST['ComissionAmexDanamon']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['ComissionAmexDanamon']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Use for Work Order Productions?') . ':</td>
			<td><select name="UsedForWO">';
	if($_POST['UsedForWO']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['UsedForWO']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';
	// Location's ledger account:
	echo '<tr title="', _('Enter the GL account for this location, or leave it in blank if not needed'), '">
			<td><label for="GLAccountCode">', _('GL Account Code'), ':</label></td>
			<td><input data-type="no-illegal-chars" id="GLAccountCode" maxlength="20" name="GLAccountCode" size="20" type="text" value="', $_POST['GLAccountCode'], '" /></td></tr>';
	// Allow or deny the invoicing of items in this location:
	echo '<tr title="', _('Use this parameter to indicate whether these inventory location allows or denies the invoicing of its items.'), '">
			<td><label for="AllowInvoicing">', _('Allow Invoicing'), ':</label></td>
			<td><select name="AllowInvoicing">
				<option', ($_POST['AllowInvoicing']==1 ? ' selected="selected"' : ''), ' value="1">', _('Yes'), '</option>
				<option', ($_POST['AllowInvoicing']==0 ? ' selected="selected"' : ''), ' value="0">', _('No'), '</option>
			</select></td>
		</tr>';

*/	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
		</div>
		</form>';

}//end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
