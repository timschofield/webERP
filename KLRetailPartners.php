<?php

/* Defines the KL retail Partners */

include('includes/session.php');
$Title = _('KL Retail Partners Maintenance');// Screen identification.
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

$ExtraSpace = '<tr><td></td><td></td></tr><tr><td></td><td></td><td></td><td></td></tr><tr><td></td><td></td></tr>';


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
					partnernameinvoice ='" . $_POST['PartnerNameInvoice'] . "',
					partneraddress ='" . $_POST['PartnerAddress'] . "',
					partneraddressjalan ='" . $_POST['partneraddressjalan'] . "',
					partneraddressblok ='" . $_POST['partneraddressblok'] . "',
					partneraddressnomor ='" . $_POST['partneraddressnomor'] . "',
					partneraddressrt ='" . $_POST['partneraddressrt'] . "',
					partneraddressrw ='" . $_POST['partneraddressrw'] . "',
					partneraddresskecamatan ='" . $_POST['partneraddresskecamatan'] . "',
					partneraddresskelurahan ='" . $_POST['partneraddresskelurahan'] . "',
					partneraddresskabupaten ='" . $_POST['partneraddresskabupaten'] . "',
					partneraddresspropinsi ='" . $_POST['partneraddresspropinsi'] . "',
					partneraddresskodepos ='" . $_POST['partneraddresskodepos'] . "',
					partnertelepon ='" . $_POST['partnertelepon'] . "',
					partnernpwp ='" . $_POST['PartnerNPWP'] . "',
					partnernpwpinvoice ='" . $_POST['PartnerNPWPInvoice'] . "',
					ppn ='" . $_POST['PPN'] . "',
					accountppn ='" . $_POST['AccountPPN'] . "',
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
					percentconsignmentptadu = '" . $_POST['PercentConsignmentPTADU'] . "',
					accountconsignmentsalesptadu = '" . $_POST['AccountConsignmentSalesPTADU'] . "',
					accountconsignmentcogspartner = '" . $_POST['AccountConsignmentCOGSPartner'] . "',
					accountwechat='" . $_POST['AccountWeChat'] . "',
					comissionwechat = '" . $_POST['ComissionWeChat'] . "',
					accountcomissionwechat ='" . $_POST['AccountComissionWeChat'] . "',
					accountqris='" . $_POST['AccountQRIS'] . "',
					comissionqris = '" . $_POST['ComissionQRIS'] . "',
					accountcomissionqris ='" . $_POST['AccountComissionQRIS'] . "',
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
		unset($_POST['PartnerNameInvoice']);
		unset($_POST['PartnerAddress']);
		unset($_POST['partneraddressjalan']);
		unset($_POST['partneraddressblok']);
		unset($_POST['partneraddressnomor']);
		unset($_POST['partneraddressrt']);
		unset($_POST['partneraddressrw']);
		unset($_POST['partneraddresskecamatan']);
		unset($_POST['partneraddresskelurahan']);
		unset($_POST['partneraddresskabupaten']);
		unset($_POST['partneraddresspropinsi']);
		unset($_POST['partneraddresskodepos']);
		unset($_POST['partnertelepon']);
		unset($_POST['PartnerNPWP']);
		unset($_POST['PartnerNPWPInvoice']);
		unset($_POST['PPN']);
		unset($_POST['AccountPPN']);
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
		unset($_POST['PercentConsignmentPTADU']);
		unset($_POST['AccountConsignmentSalesPTADU']);
		unset($_POST['AccountConsignmentCOGSPartner']);
		unset($_POST['AccountWeChat']);
		unset($_POST['ComissionWeChat']);
		unset($_POST['AccountComissionWeChat']);
		unset($_POST['AccountQRIS']);
		unset($_POST['ComissionQRIS']);
		unset($_POST['AccountComissionQRIS']);
		unset($_POST['CounterInvoiceA']);
		unset($_POST['CounterInvoiceB']);
		unset($_POST['CounterInvoiceC']);

	} elseif($InputError !=1) {

		/*SelectedPartner is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$sql = "INSERT INTO klretailpartners 
								(partnercode,
								partnername,
								partnernameinvoice,
								partneraddress,
								partneraddressjalan,
								partneraddressblok,
								partneraddressnomor,
								partneraddressrt,
								partneraddressrw,
								partneraddresskecamatan,
								partneraddresskelurahan,
								partneraddresskabupaten,
								partneraddresspropinsi,
								partneraddresskodepos,
								partnertelepon,
								partnernpwp,
								partnernpwpinvoice,
								ppn,
								accountppn,
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
								accountwechat,
								comissionwechat,
								accountcomissionwechat,
								accountqris,
								comissionqris,
								accountcomissionqris,
								counterinvoicea,
								counterinvoiceb,
								counterinvoicec)
						VALUES ('" . $_POST['PartnerCode'] . "',
								'" . $_POST['PartnerName'] . "',
								'" . $_POST['PartnerNameInvoice'] . "',
								'" . $_POST['PartnerAddress'] ."',
								'" . $_POST['partneraddressjalan'] ."',
								'" . $_POST['partneraddressblok'] ."',
								'" . $_POST['partneraddressnomor'] ."',
								'" . $_POST['partneraddressrt'] ."',
								'" . $_POST['partneraddressrw'] ."',
								'" . $_POST['partneraddresskecamatan'] ."',
								'" . $_POST['partneraddresskelurahan'] ."',
								'" . $_POST['partneraddresskabupaten'] ."',
								'" . $_POST['partneraddresspropinsi'] ."',
								'" . $_POST['partneraddresskodepos'] ."',
								'" . $_POST['partnertelepon'] ."',
								'" . $_POST['PartnerNPWP'] ."',
								'" . $_POST['PartnerNPWPInvoice'] ."',
								'" . $_POST['PPN'] . "',
								'" . $_POST['AccountPPN'] . "',
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
								'" . $_POST['PercentConsignmentPTADU'] . "',
								'" . $_POST['AccountConsignmentSalesPTADU'] . "',
								'" . $_POST['AccountConsignmentCOGSPartner'] . "',
								'" . $_POST['AccountWeChat'] . "',
								'" . $_POST['ComissionWeChat'] . "',
								'" . $_POST['AccountComissionWeChat'] . "',
								'" . $_POST['AccountQRIS'] . "',
								'" . $_POST['ComissionQRIS'] . "',
								'" . $_POST['AccountComissionQRIS'] . "',
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
		unset($_POST['PartnerNameInvoice']);
		unset($_POST['PartnerAddress']);
		unset($_POST['partneraddressjalan']);
		unset($_POST['partneraddressblok']);
		unset($_POST['partneraddressnomor']);
		unset($_POST['partneraddressrt']);
		unset($_POST['partneraddressrw']);
		unset($_POST['partneraddresskecamatan']);
		unset($_POST['partneraddresskelurahan']);
		unset($_POST['partneraddresskabupaten']);
		unset($_POST['partneraddresspropinsi']);
		unset($_POST['partneraddresskodepos']);
		unset($_POST['partnertelepon']);
		unset($_POST['PartnerNPWP']);
		unset($_POST['PartnerNPWPInvoice']);
		unset($_POST['PPN']);
		unset($_POST['AccountPPN']);
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
		unset($_POST['PercentConsignmentPTADU']);
		unset($_POST['AccountConsignmentSalesPTADU']);
		unset($_POST['AccountConsignmentCOGSPartner']);
		unset($_POST['AccountWeChat']);
		unset($_POST['ComissionWeChat']);
		unset($_POST['AccountComissionWeChat']);
		unset($_POST['AccountQRIS']);
		unset($_POST['ComissionQRIS']);
		unset($_POST['AccountComissionQRIS']);
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
				comissionccdanamon,
				comissionamexdanamon,
				comissionccmandiri,
				comissionqris,
				comissionccbca,
				comissionamexbca,
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
			<th class="ascending">', _('EDC Danamon'), '</th>
			<th class="ascending">', _('AMEX Danamon'), '</th>
			<th class="ascending">', _('EDC Mandiri'), '</th>
			<th class="ascending">', _('QRIS Mandiri'), '</th>
			<th class="ascending">', _('EDC BCA'), '</th>
			<th class="ascending">', _('AMEX BCA'), '</th>
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
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			<td class="noprint"><a href="%sSelectedPartner=%s">' . _('Edit') . '</a></td>
			<td class="noprint"><a href="%sSelectedPartner=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this retail partner?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['partnercode'],
			$myrow['partnername'],
			locale_number_format($myrow['ppn'],0) . "%",
			locale_number_format($myrow['cashsalesreported'],0) . "%",
			locale_number_format($myrow['comissionccdanamon'],2) . "%",
			locale_number_format($myrow['comissionamexdanamon'],2) . "%",
			locale_number_format($myrow['comissionccmandiri'],2) . "%",
			locale_number_format($myrow['comissionqris'],2) . "%",
			locale_number_format($myrow['comissionccbca'],2) . "%",
			locale_number_format($myrow['comissionamexbca'],2) . "%",
			locale_number_format($myrow['percentconsignmentptadu'],0) . "%",
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['partnercode'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['partnercode']);
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
					partnernameinvoice,
					partneraddress,
					partneraddressjalan,
					partneraddressblok,
					partneraddressnomor,
					partneraddressrt,
					partneraddressrw,
					partneraddresskecamatan,
					partneraddresskelurahan,
					partneraddresskabupaten,
					partneraddresspropinsi,
					partneraddresskodepos,
					partnertelepon,
					partnernpwp,
					partnernpwpinvoice,
					ppn,
					accountppn,
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
					accountwechat,
					comissionwechat,
					accountcomissionwechat,
					accountqris,
					comissionqris,
					accountcomissionqris,
					counterinvoicea,
					counterinvoiceb,
					counterinvoicec
				FROM klretailpartners
				WHERE partnercode='" . $SelectedPartner . "'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['PartnerCode'] = $myrow['partnercode'];
		$_POST['PartnerName'] = $myrow['partnername'];
		$_POST['PartnerNameInvoice'] = $myrow['partnernameinvoice'];
		$_POST['PartnerAddress'] = $myrow['partneraddress'];
		$_POST['partneraddressjalan'] = $myrow['partneraddressjalan'];
		$_POST['partneraddressblok'] = $myrow['partneraddressblok'];
		$_POST['partneraddressnomor'] = $myrow['partneraddressnomor'];
		$_POST['partneraddressrt'] = $myrow['partneraddressrtº'];
		$_POST['partneraddressrw'] = $myrow['partneraddressrw'];
		$_POST['partneraddresskecamatan'] = $myrow['partneraddresskecamatan'];
		$_POST['partneraddresskelurahan'] = $myrow['partneraddresskelurahan'];
		$_POST['partneraddresskabupaten'] = $myrow['partneraddresskabupaten'];
		$_POST['partneraddresspropinsi'] = $myrow['partneraddresspropinsi'];
		$_POST['partneraddresskodepos'] = $myrow['partneraddresskodepos'];
		$_POST['partnertelepon'] = $myrow['partnertelepon'];
		$_POST['PartnerNPWP'] = $myrow['partnernpwp'];
		$_POST['PartnerNPWPInvoice'] = $myrow['partnernpwpinvoice'];
		$_POST['PPN'] = $myrow['ppn'];
		$_POST['AccountPPN'] = $myrow['accountppn'];
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
		$_POST['ComissionCCMandiri'] = $myrow['comissionccmandiri'];
		$_POST['ComissionCCBCA'] = $myrow['comissionccbca'];
		$_POST['ComissionAmexBCA'] = $myrow['comissionamexbca'];
		$_POST['PercentConsignmentPTADU'] = $myrow['percentconsignmentptadu'];
		$_POST['AccountConsignmentSalesPTADU'] = $myrow['accountconsignmentsalesptadu'];
		$_POST['AccountConsignmentCOGSPartner'] = $myrow['accountconsignmentcogspartner'];
		$_POST['AccountWeChat'] = $myrow['accountwechat'];
		$_POST['AccountComissionWeChat'] = $myrow['accountcomissionwechat'];
		$_POST['ComissionWeChat'] = $myrow['comissionwechat'];
		$_POST['AccountQRIS'] = $myrow['accountqris'];
		$_POST['AccountComissionQRIS'] = $myrow['accountcomissionqris'];
		$_POST['ComissionQRIS'] = $myrow['comissionqris'];
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
	if(!isset($_POST['PartnerNameInvoice'])) {
		$_POST['PartnerNameInvoice'] = '';
	}
	if(!isset($_POST['PartnerAddress'])) {
		$_POST['PartnerAddress'] = '';
	}
	if(!isset($_POST['partneraddressjalan'])) {
		$_POST['partneraddressjalan'] = '';
	}
	if(!isset($_POST['partneraddressblok'])) {
		$_POST['partneraddressblok'] = '';
	}
	if(!isset($_POST['partneraddressnomor'])) {
		$_POST['partneraddressnomor'] = '';
	}
	if(!isset($_POST['partneraddressrt'])) {
		$_POST['partneraddressrt'] = '';
	}
	if(!isset($_POST['partneraddressrw'])) {
		$_POST['partneraddressrw'] = '';
	}
	if(!isset($_POST['partneraddresskecamatan'])) {
		$_POST['partneraddresskecamatan'] = '';
	}
	if(!isset($_POST['partneraddresskelurahan'])) {
		$_POST['partneraddresskelurahan'] = '';
	}
	if(!isset($_POST['partneraddresskabupaten'])) {
		$_POST['partneraddresskabupaten'] = '';
	}
	if(!isset($_POST['partneraddresspropinsi'])) {
		$_POST['partneraddresspropinsi'] = '';
	}
	if(!isset($_POST['partneraddresskodepos'])) {
		$_POST['partneraddresskodepos'] = '';
	}
	if(!isset($_POST['partnertelepon'])) {
		$_POST['partnertelepon'] = '';
	}
	if(!isset($_POST['PartnerNPWP'])) {
		$_POST['PartnerNPWP'] = '';
	}
	if(!isset($_POST['PartnerNPWPInvoice'])) {
		$_POST['PartnerNPWPInvoice'] = '';
	}
	if(!isset($_POST['PPN'])) {
		$_POST['PPN'] = 0;
	}
	if(!isset($_POST['AccountPPN'])) {
		$_POST['AccountPPN'] = '';
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
	if(!isset($_POST['PercentConsignmentPTADU'])) {
		$_POST['PercentConsignmentPTADU'] = 0;
	}
	if(!isset($_POST['AccountConsignmentSalesPTADU'])) {
		$_POST['AccountConsignmentSalesPTADU'] = '';
	}
	if(!isset($_POST['AccountConsignmentCOGSPartner'])) {
		$_POST['AccountConsignmentCOGSPartner'] = '';
	}
	if(!isset($_POST['AccountWeChat'])) {
		$_POST['AccountWeChat'] = '';
	}
	if(!isset($_POST['ComissionWeChat'])) {
		$_POST['ComissionWeChat'] = 0;
	}
	if(!isset($_POST['AccountComissionWeChat'])) {
		$_POST['AccountComissionWeChat'] = '';
	}
	if(!isset($_POST['AccountQRIS'])) {
		$_POST['AccountQRIS'] = '';
	}
	if(!isset($_POST['ComissionQRIS'])) {
		$_POST['ComissionQRIS'] = 0;
	}
	if(!isset($_POST['AccountComissionQRIS'])) {
		$_POST['AccountComissionQRIS'] = '';
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
			<td>' . _('Partner Name in POS Slip') . ':' . '</td>
			<td><input type="text" name="PartnerName" required="required" value="'. $_POST['PartnerName'] . '" title="' . _('Enter the retail partner name') . '" namesize="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Address in POS Slip') . ':' . '</td>
			<td><input type="text" name="PartnerAddress" value="' . $_POST['PartnerAddress'] . '" size="51" maxlength="100" /></td>
		</tr>
		<tr>
			<td>' . _('NPWP in POS Slip') . ':' . '</td>
			<td><input type="text" name="PartnerNPWP" value="' . $_POST['PartnerNPWP'] . '" size="21" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('Partner Name in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="PartnerNameInvoice" required="required" value="'. $_POST['PartnerNameInvoice'] . '" title="' . _('Enter the retail partner name') . '" namesize="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Jalan in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partneraddressjalan" value="' . $_POST['partneraddressjalan'] . '" size="51" maxlength="100" /></td>
		</tr>
		<tr>
			<td>' . _('Blok in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partneraddressblok" value="' . $_POST['partneraddressblok'] . '" size="21" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('Nomor in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partneraddressnomor" value="' . $_POST['partneraddressnomor'] . '" size="21" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('RT in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partneraddressrt" value="' . $_POST['partneraddressrt'] . '" size="21" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('RW in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partneraddressrw" value="' . $_POST['partneraddressrw'] . '" size="21" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('Kecamatan in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partneraddresskecamatan" value="' . $_POST['partneraddresskecamatan'] . '" size="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Kelurahan in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partneraddresskelurahan" value="' . $_POST['partneraddresskelurahan'] . '" size="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Kabupaten in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partneraddresskabupaten" value="' . $_POST['partneraddresskabupaten'] . '" size="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Propinsi in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partneraddresspropinsi" value="' . $_POST['partneraddresspropinsi'] . '" size="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Kode Pos in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partneraddresskodepos" value="' . $_POST['partneraddresskodepos'] . '" size="11" maxlength="10" /></td>
		</tr>
		<tr>
			<td>' . _('Telepon in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="partnertelepon" value="' . $_POST['partnertelepon'] . '" size="21" maxlength="20" /></td>
		</tr>
		<tr>
			<td>' . _('NPWP in Consignment Invoice/FP') . ':' . '</td>
			<td><input type="text" name="PartnerNPWPInvoice" value="' . $_POST['PartnerNPWPInvoice'] . '" size="21" maxlength="20" /></td>
		</tr>';
		
	echo $ExtraSpace;

	echo '<tr>
			<td>' . _('% PPN') . ':</td>
			<td><input type="text" name="PPN" class="number" title="' . _('PPN to apply') . '" value="' . $_POST['PPN'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('PPN GL Account') . ':' . '</td>
		<td><select name="AccountPPN">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPPN']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo $ExtraSpace;

	echo '<tr>
		<td>' . _('Credit Card Sales Area') . ':' . '</td>
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

	echo '<tr>
		<td>' . _('Cash Sales Area') . ':' . '</td>
		<td><select name="AreaSalesCash">';
	$AreaSales = DB_query("SELECT areacode, areadescription FROM areas ORDER BY areadescription");
	while ($myrow=DB_fetch_array($AreaSales)) {
		if($_POST['AreaSalesCash']==$myrow['areacode']) {
			echo '<option selected="selected" value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . _('Other Cash Sales Area') . ':' . '</td>
		<td><select name="AreaSalesCashOthers">';
	$AreaSales = DB_query("SELECT areacode, areadescription FROM areas ORDER BY areadescription");
	while ($myrow=DB_fetch_array($AreaSales)) {
		if($_POST['AreaSalesCashOthers']==$myrow['areacode']) {
			echo '<option selected="selected" value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('% cash Sales Reported') . ':</td>
			<td><input type="text" name="CashSalesReported" class="number"  value="' . $_POST['CashSalesReported'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('Counter Invoices A') . ':' . '</td>
		<td><select name="CounterInvoiceA">';
	$InvoiceSysType = DB_query("SELECT typeid, typename FROM systypes ORDER BY typename");
	while ($myrow=DB_fetch_array($InvoiceSysType)) {
		if($_POST['CounterInvoiceA']==$myrow['typeid']) {
			echo '<option selected="selected" value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		} else {
			echo '<option value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		}
	}
	echo '</select></td></tr>';
		
	echo '<tr>
		<td>' . _('Counter Invoices B') . ':' . '</td>
		<td><select name="CounterInvoiceB">';
	$InvoiceSysType = DB_query("SELECT typeid, typename FROM systypes ORDER BY typename");
	while ($myrow=DB_fetch_array($InvoiceSysType)) {
		if($_POST['CounterInvoiceB']==$myrow['typeid']) {
			echo '<option selected="selected" value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		} else {
			echo '<option value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . _('Counter Invoices C') . ':' . '</td>
		<td><select name="CounterInvoiceC">';
	$InvoiceSysType = DB_query("SELECT typeid, typename FROM systypes ORDER BY typename");
	while ($myrow=DB_fetch_array($InvoiceSysType)) {
		if($_POST['CounterInvoiceC']==$myrow['typeid']) {
			echo '<option selected="selected" value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		} else {
			echo '<option value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo $ExtraSpace;

	echo '<tr>
		<td>' . _('Credit Card Comission GL Account') . ':' . '</td>
		<td><select name="AccountComissionCreditCard">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountComissionCreditCard']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . _('Bank Danamon GL Account') . ':' . '</td>
		<td><select name="AccountBankDanamon">';
	$GLAccount = DB_query("SELECT accountcode, bankaccountname FROM bankaccounts ORDER BY bankaccountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountBankDanamon']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('% Credit Card Comission Bank Danamon') . ':</td>
			<td><input type="text" name="ComissionCCDanamon" class="number" value="' . $_POST['ComissionCCDanamon'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% AMEX Comission Bank Danamon') . ':</td>
			<td><input type="text" name="ComissionAmexDanamon" class="number" value="' . $_POST['ComissionAmexDanamon'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('Bank Mandiri GL Account') . ':' . '</td>
		<td><select name="AccountBankMandiri">';
	$GLAccount = DB_query("SELECT accountcode, bankaccountname FROM bankaccounts ORDER BY bankaccountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountBankMandiri']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('% Credit Card Comission Bank Mandiri') . ':</td>
			<td><input type="text" name="ComissionCCMandiri" class="number" value="' . $_POST['ComissionCCMandiri'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('Bank BCA GL Account') . ':' . '</td>
		<td><select name="AccountBankBCA">';
	$GLAccount = DB_query("SELECT accountcode, bankaccountname FROM bankaccounts ORDER BY bankaccountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountBankBCA']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('% Credit Card Comission Bank BCA') . ':</td>
			<td><input type="text" name="ComissionCCBCA" class="number" value="' . $_POST['ComissionCCBCA'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% AMEX Comission Bank BCA') . ':</td>
			<td><input type="text" name="ComissionAmexBCA" class="number" value="' . $_POST['ComissionAmexBCA'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo $ExtraSpace;

	echo '<tr>
		<td>' . _('WeChat GL Account') . ':' . '</td>
		<td><select name="AccountWeChat">';
	$GLAccount = DB_query("SELECT accountcode, bankaccountname FROM bankaccounts ORDER BY bankaccountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountWeChat']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('% Comission WeChat') . ':</td>
			<td><input type="text" name="ComissionWeChat" class="number" value="' . $_POST['ComissionWeChat'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('WeChat Comission GL Account') . ':' . '</td>
		<td><select name="AccountComissionWeChat">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountComissionWeChat']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo $ExtraSpace;

	echo '<tr>
		<td>' . _('QRIS Mandiri GL Account') . ':' . '</td>
		<td><select name="AccountQRIS">';
	$GLAccount = DB_query("SELECT accountcode, bankaccountname FROM bankaccounts ORDER BY bankaccountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountQRIS']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('% Comission QRIS Mandiri') . ':</td>
			<td><input type="text" name="ComissionQRIS" class="number" value="' . $_POST['ComissionQRIS'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('QRIS Mandiri Comission GL Account') . ':' . '</td>
		<td><select name="AccountComissionQRIS">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountComissionQRIS']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo $ExtraSpace;

	echo '<tr>
			<td>' . _('% Consignment to PT ADU') . ':</td>
			<td><input type="text" name="PercentConsignmentPTADU" class="number" value="' . $_POST['PercentConsignmentPTADU'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('Consignment Sales PT. ADU GL Account') . ':' . '</td>
		<td><select name="AccountConsignmentSalesPTADU">';
	$GLAccount = DB_query("SELECT accountcode, accountname 
							FROM chartmaster 
							WHERE group_ = 'Clustering' 
							ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountConsignmentSalesPTADU']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
		
	echo '<tr>
		<td>' . _('Consignment COGS Partner GL Account') . ':' . '</td>
		<td><select name="AccountConsignmentCOGSPartner">';
	$GLAccount = DB_query("SELECT accountcode, accountname 
							FROM chartmaster 
							WHERE group_ = 'Clustering' 
							ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountConsignmentCOGSPartner']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo $ExtraSpace;

	echo '<tr>
			<td>' . _('% HPP Compensation') . ':</td>
			<td><input type="text" name="HPPCompensation" class="number" title="' . _('HPP Compensation') . '" value="' . $_POST['HPPCompensation'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('HPP Compensation GL Account') . ':' . '</td>
		<td><select name="AccountHPPCompensation">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountHPPCompensation']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
		</div>
		</form>';

}//end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
