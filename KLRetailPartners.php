<?php

/* Defines the KL retail Partners */

include('includes/session.php');

$Title = __('KL Retail Partners Maintenance'); // Screen identification.
$ViewTopic = ''; // Filename's id in ManualContents.php's TOC.
$BookMark = ''; // Anchor's id in the manual's html document.
include('includes/header.php');

include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/CountriesArray.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/supplier.png" title="', // Icon image.
	__('Setup'), '" /> ', // Icon title.
	__('KL Retail Partners Maintenance'), '</p>'; // Page title.

if (isset($_GET['SelectedPartner'])) {
	$SelectedPartner = $_GET['SelectedPartner'];
} elseif (isset($_POST['SelectedPartner'])) {
	$SelectedPartner = $_POST['SelectedPartner'];
}

if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	$_POST['PartnerCode'] = mb_strtoupper($_POST['PartnerCode']);
	if (trim($_POST['PartnerCode']) == '') {
		$InputError = 1;
		prnMsg(__('The Partner Code code may not be empty'), 'error');
	}

	if (isset($SelectedPartner) AND $InputError != 1) {

		$SQL = "UPDATE klretailpartners
				SET partnername = '" . $_POST['PartnerName'] . "',
					partneremail = '" . $_POST['PartnerEmail'] . "',
					partnernameinvoice = '" . $_POST['PartnerNameInvoice'] . "',
					partneraddress = '" . $_POST['PartnerAddress'] . "',
					partneraddressjalan = '" . $_POST['partneraddressjalan'] . "',
					partneraddressblok = '" . $_POST['partneraddressblok'] . "',
					partneraddressnomor = '" . $_POST['partneraddressnomor'] . "',
					partneraddressrt = '" . $_POST['partneraddressrt'] . "',
					partneraddressrw = '" . $_POST['partneraddressrw'] . "',
					partneraddresskecamatan = '" . $_POST['partneraddresskecamatan'] . "',
					partneraddresskelurahan = '" . $_POST['partneraddresskelurahan'] . "',
					partneraddresskabupaten = '" . $_POST['partneraddresskabupaten'] . "',
					partneraddresspropinsi = '" . $_POST['partneraddresspropinsi'] . "',
					partneraddresskodepos = '" . $_POST['partneraddresskodepos'] . "',
					partnertelepon = '" . $_POST['partnertelepon'] . "',
					partnernpwp = '" . $_POST['PartnerNPWP'] . "',
					partnernpwpinvoice = '" . $_POST['PartnerNPWPInvoice'] . "',
					ppn = '" . $_POST['PPN'] . "',
					accountppn = '" . $_POST['AccountPPN'] . "',
					areasalescreditcard = '" . $_POST['AreaSalesCreditCard'] . "',
					areasalescash = '" . $_POST['AreaSalesCash'] . "',
					areasalescashothers = '" . $_POST['AreaSalesCashOthers'] . "',
					cashsalesreported = '" . $_POST['CashSalesReported'] . "',
					hppcompensation = '" . $_POST['HPPCompensation'] . "',
					accounthppcompensation = '" . $_POST['AccountHPPCompensation'] . "',
					accountbankdanamon = '" . $_POST['AccountBankDanamon'] . "',
					settlementdelaydanamon = '" . filter_number_format($_POST['SettlementDelayDanamon']) . "',
					accountbankbni = '" . $_POST['AccountBankBNI'] . "',
					settlementdelaybni = '" . filter_number_format($_POST['SettlementDelayBNI']) . "',
					accountbankmandiri = '" . $_POST['AccountBankMandiri'] . "',
					settlementdelaymandiri = '" . filter_number_format($_POST['SettlementDelayMandiri']) . "',
					accountbankbca = '" . $_POST['AccountBankBCA'] . "',
					settlementdelaybca = '" . filter_number_format($_POST['SettlementDelayBCA']) . "',
					accountcomissioncreditcard = '" . $_POST['AccountComissionCreditCard'] . "',
					comissionccdanamon = '" . $_POST['ComissionCCDanamon'] . "',
					comissionamexdanamon = '" . $_POST['ComissionAmexDanamon'] . "',
					comissionccbni = '" . $_POST['ComissionCCBNI'] . "',
					comissionamexbni = '" . $_POST['ComissionAmexBNI'] . "',
					comissionccmandiri = '" . $_POST['ComissionCCMandiri'] . "',
					comissionccbca = '" . $_POST['ComissionCCBCA'] . "',
					comissionamexbca = '" . $_POST['ComissionAmexBCA'] . "',
					percentconsignmentptadu = '" . $_POST['PercentConsignmentPTADU'] . "',
					accountconsignmentsalesptadu = '" . $_POST['AccountConsignmentSalesPTADU'] . "',
					accountconsignmentcogspartner = '" . $_POST['AccountConsignmentCOGSPartner'] . "',
					accountwechat = '" . $_POST['AccountWeChat'] . "',
					comissionwechat = '" . $_POST['ComissionWeChat'] . "',
					accountcomissionwechat = '" . $_POST['AccountComissionWeChat'] . "',
					accountqris = '" . $_POST['AccountQRIS'] . "',
					comissionqris = '" . $_POST['ComissionQRIS'] . "',
					accountcomissionqris = '" . $_POST['AccountComissionQRIS'] . "',
					accountposreceivable = '" . $_POST['AccountPOSReceivable'] . "',
					counterinvoicea = '" . $_POST['CounterInvoiceA'] . "',
					counterinvoiceb = '" . $_POST['CounterInvoiceB'] . "',
					counterinvoicec = '" . $_POST['CounterInvoiceC'] . "',
					settlementdelaywechat = '" . filter_number_format($_POST['SettlementDelayWeChat']) . "',
					settlementdelayqris = '" . filter_number_format($_POST['SettlementDelayQRIS']) . "'
				WHERE partnercode = '" . $SelectedPartner . "'";

		$ErrMsg = __('An error occurred updating the') . ' ' . $SelectedPartner . ' ' . __('retail partner record because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('The retail partner record has been updated'), 'success');

		unset($SelectedPartner);
		unset($_POST['PartnerCode']);
		unset($_POST['PartnerName']);
		unset($_POST['PartnerEmail']);
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
		unset($_POST['SettlementDelayDanamon']);
		unset($_POST['AccountBankBNI']);
		unset($_POST['SettlementDelayBNI']);
		unset($_POST['AccountBankMandiri']);
		unset($_POST['SettlementDelayMandiri']);
		unset($_POST['AccountBankBCA']);
		unset($_POST['SettlementDelayBCA']);
		unset($_POST['AccountComissionCreditCard']);
		unset($_POST['ComissionCCDanamon']);
		unset($_POST['ComissionAmexDanamon']);
		unset($_POST['ComissionCCBNI']);
		unset($_POST['ComissionAmexBNI']);
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
		unset($_POST['AccountPOSReceivable']);
		unset($_POST['CounterInvoiceA']);
		unset($_POST['CounterInvoiceB']);
		unset($_POST['CounterInvoiceC']);
		unset($_POST['SettlementDelayWeChat']);
		unset($_POST['SettlementDelayQRIS']);

	} elseif ($InputError != 1) {

		/*SelectedPartner is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$SQL = "INSERT INTO klretailpartners
								(partnercode,
								partnername,
								partneremail,
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
								settlementdelaydanamon,
								accountbankbni,
								settlementdelaybni,
								accountbankmandiri,
								settlementdelaymandiri,
								accountbankbca,
								settlementdelaybca,
								accountcomissioncreditcard,
								comissionccdanamon,
								comissionamexdanamon,
								comissionccbni,
								comissionamexbni,
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
								accountposreceivable,
								counterinvoicea,
								counterinvoiceb,
								counterinvoicec,
								settlementdelaywechat,
								settlementdelayqris)
						VALUES ('" . $_POST['PartnerCode'] . "',
								'" . $_POST['PartnerName'] . "',
								'" . $_POST['PartnerEmail'] . "',
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
								'" . filter_number_format($_POST['SettlementDelayDanamon']) . "',
								'" . $_POST['AccountBankBNI'] . "',
								'" . filter_number_format($_POST['SettlementDelayBNI']) . "',
								'" . $_POST['AccountBankMandiri'] . "',
								'" . filter_number_format($_POST['SettlementDelayMandiri']) . "',
								'" . $_POST['AccountBankBCA'] . "',
								'" . filter_number_format($_POST['SettlementDelayBCA']) . "',
								'" . $_POST['AccountComissionCreditCard'] . "',
								'" . $_POST['ComissionCCDanamon'] . "',
								'" . $_POST['ComissionAmexDanamon'] . "',
								'" . $_POST['ComissionCCBNI'] . "',
								'" . $_POST['ComissionAmexBNI'] . "',
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
								'" . $_POST['AccountPOSReceivable'] . "',
								'" . $_POST['CounterInvoiceA'] . "',
								'" . $_POST['CounterInvoiceB'] . "',
								'" . $_POST['CounterInvoiceC'] . "',
								'" . filter_number_format($_POST['SettlementDelayWeChat']) . "',
								'" . filter_number_format($_POST['SettlementDelayQRIS']) . "')";

		$ErrMsg = __('An error occurred inserting the new retail partner record because');
		$Result = DB_query($SQL, $ErrMsg);

		prnMsg(__('The new retail partner record has been added'), 'success');

		unset($SelectedPartner);
		unset($_POST['PartnerCode']);
		unset($_POST['PartnerName']);
		unset($_POST['PartnerEmail']);
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
		unset($_POST['AccountBankDanamon']);
		unset($_POST['SettlementDelayDanamon']);
		unset($_POST['AccountBankBNI']);
		unset($_POST['SettlementDelayBNI']);
		unset($_POST['ComissionCCDanamon']);
		unset($_POST['ComissionAmexDanamon']);
		unset($_POST['ComissionCCBNI']);
		unset($_POST['ComissionAmexBNI']);
		unset($_POST['ComissionCCBCA']);
		unset($_POST['SettlementDelayBCA']);
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
		unset($_POST['AccountPOSReceivable']);
		unset($_POST['CounterInvoiceA']);
		unset($_POST['CounterInvoiceB']);
		unset($_POST['CounterInvoiceC']);
		unset($_POST['SettlementDelayWeChat']);
		unset($_POST['SettlementDelayQRIS']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$CancelDelete = 0;
	// PREVENT DELETES IF DEPENDENT RECORDS
	$SQL = "SELECT COUNT(*) FROM locations WHERE partnercode = '" . $SelectedPartner . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0] > 0) {
		$CancelDelete = 1;
		prnMsg(__('Cannot delete this retal partner because there are locations related to it.'), 'warn');
		echo __('There are') . ' ' . $MyRow[0] . ' ' . __('locations using this retail partner');
	}
	if (!$CancelDelete) {
		$Result = DB_query("DELETE FROM klretailpartners WHERE partnercode = '" . $SelectedPartner . "'");
		prnMsg(__('Retail Partner') . ' ' . $SelectedPartner . ' ' . __('has been deleted') . '!', 'success');
		unset($SelectedPartner);
	} //end if Delete Retail Partner
	unset($SelectedPartner);
	unset($_GET['delete']);
}

if (!isset($SelectedPartner)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPartner will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Locations will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT partnercode,
				partnername,
				partneremail,
				ppn,
				cashsalesreported,
				comissionccdanamon,
				comissionamexdanamon,
				comissionccbni,
				comissionamexbni,
				comissionccmandiri,
				comissionqris,
				comissionccbca,
				comissionamexbca,
				percentconsignmentptadu
			FROM klretailpartners
			ORDER BY partnername";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0) {
		prnMsg(__('There are no retail partners'), 'error');
	}

	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">', __('Code'), '</th>
				<th class="SortedColumn">', __('Name'), '</th>
				<th class="SortedColumn">', __('PPN'), '</th>
				<th class="SortedColumn">', __('Cash Reported'), '</th>
				<th class="SortedColumn">', __('EDC Danamon'), '</th>
				<th class="SortedColumn">', __('EDC BNI'), '</th>
				<th class="SortedColumn">', __('AMEX BNI'), '</th>
				<th class="SortedColumn">', __('EDC Mandiri'), '</th>
				<th class="SortedColumn">', __('QRIS Mandiri'), '</th>
				<th class="SortedColumn">', __('EDC BCA'), '</th>
				<th class="SortedColumn">', __('AMEX BCA'), '</th>
				<th class="SortedColumn">', __('Consign PTADU'), '</th>
				<th class="noprint" colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">';
		echo '<td>' . $MyRow['partnercode'] . '</td>
				<td>' . $MyRow['partnername'] . '</td>
				<td class="number">' . locale_number_format($MyRow['ppn'], 0) . "%" . '</td>
				<td class="number">' . locale_number_format($MyRow['cashsalesreported'], 0) . "%" . '</td>
				<td class="number">' . locale_number_format($MyRow['comissionccdanamon'], 2) . "%" . '</td>
				<td class="number">' . locale_number_format($MyRow['comissionccbni'], 2) . "%" . '</td>
				<td class="number">' . locale_number_format($MyRow['comissionamexbni'], 2) . "%" . '</td>
				<td class="number">' . locale_number_format($MyRow['comissionccmandiri'], 2) . "%" . '</td>
				<td class="number">' . locale_number_format($MyRow['comissionqris'], 2) . "%" . '</td>
				<td class="number">' . locale_number_format($MyRow['comissionccbca'], 2) . "%" . '</td>
				<td class="number">' . locale_number_format($MyRow['comissionamexbca'], 2) . "%" . '</td>
				<td class="number">' . locale_number_format($MyRow['percentconsignmentptadu'], 0) . "%" . '</td>
				<td class="noprint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .
					'?SelectedPartner=' . $MyRow['partnercode'] . '">' . __('Edit') . '</a></td>
				<td class="noprint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .
					'?SelectedPartner=' . $MyRow['partnercode'] . '&amp;delete=1" onclick="return confirm(\'' .
					__('Are you sure you wish to delete this retail partner?') . '\');">' . __('Delete') . '</a></td>
				</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody></table>';
}

//end of ifs and buts!

echo '<br />';
if (isset($SelectedPartner)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . __('Review Records') . '</a>';
}
echo '<br />';

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedPartner)) {
		//editing an existing Partner

		$SQL = "SELECT partnercode,
					partnername,
					partneremail,
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
					settlementdelaydanamon,
					accountbankbni,
					settlementdelaybni,
					hppcompensation,
					cashsalesreported,
					accounthppcompensation,
					accountbankmandiri,
					settlementdelaymandiri,
					accountbankbca,
					settlementdelaybca,
					accountcomissioncreditcard,
					comissionccdanamon,
					comissionamexdanamon,
					comissionccbni,
					comissionamexbni,
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
					counterinvoicec,
					accountposreceivable,
					settlementdelaywechat,
					settlementdelayqris
				FROM klretailpartners
				WHERE partnercode = '" . $SelectedPartner . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['PartnerCode'] = $MyRow['partnercode'];
		$_POST['PartnerName'] = $MyRow['partnername'];
		$_POST['PartnerEmail'] = $MyRow['partneremail'];
		$_POST['PartnerNameInvoice'] = $MyRow['partnernameinvoice'];
		$_POST['PartnerAddress'] = $MyRow['partneraddress'];
		$_POST['partneraddressjalan'] = $MyRow['partneraddressjalan'];
		$_POST['partneraddressblok'] = $MyRow['partneraddressblok'];
		$_POST['partneraddressnomor'] = $MyRow['partneraddressnomor'];
		$_POST['partneraddressrt'] = $MyRow['partneraddressrt'];
		$_POST['partneraddressrw'] = $MyRow['partneraddressrw'];
		$_POST['partneraddresskecamatan'] = $MyRow['partneraddresskecamatan'];
		$_POST['partneraddresskelurahan'] = $MyRow['partneraddresskelurahan'];
		$_POST['partneraddresskabupaten'] = $MyRow['partneraddresskabupaten'];
		$_POST['partneraddresspropinsi'] = $MyRow['partneraddresspropinsi'];
		$_POST['partneraddresskodepos'] = $MyRow['partneraddresskodepos'];
		$_POST['partnertelepon'] = $MyRow['partnertelepon'];
		$_POST['PartnerNPWP'] = $MyRow['partnernpwp'];
		$_POST['PartnerNPWPInvoice'] = $MyRow['partnernpwpinvoice'];
		$_POST['PPN'] = $MyRow['ppn'];
		$_POST['AccountPPN'] = $MyRow['accountppn'];
		$_POST['AreaSalesCreditCard'] = $MyRow['areasalescreditcard'];
		$_POST['AreaSalesCash'] = $MyRow['areasalescash'];
		$_POST['AreaSalesCashOthers'] = $MyRow['areasalescashothers'];
		$_POST['AccountBankDanamon'] = $MyRow['accountbankdanamon'];
		$_POST['SettlementDelayDanamon'] = $MyRow['settlementdelaydanamon'];
		$_POST['AccountBankBNI'] = $MyRow['accountbankbni'];
		$_POST['SettlementDelayBNI'] = $MyRow['settlementdelaybni'];
		$_POST['CashSalesReported'] = $MyRow['cashsalesreported'];
		$_POST['HPPCompensation'] = $MyRow['hppcompensation'];
		$_POST['AccountHPPCompensation'] = $MyRow['accounthppcompensation'];
		$_POST['AccountBankMandiri'] = $MyRow['accountbankmandiri'];
		$_POST['SettlementDelayMandiri'] = $MyRow['settlementdelaymandiri'];
		$_POST['AccountBankBCA'] = $MyRow['accountbankbca'];
		$_POST['SettlementDelayBCA'] = $MyRow['settlementdelaybca'];
		$_POST['AccountComissionCreditCard'] = $MyRow['accountcomissioncreditcard'];
		$_POST['ComissionCCDanamon'] = $MyRow['comissionccdanamon'];
		$_POST['ComissionAmexDanamon'] = $MyRow['comissionamexdanamon'];
		$_POST['ComissionCCBNI'] = $MyRow['comissionccbni'];
		$_POST['ComissionAmexBNI'] = $MyRow['comissionamexbni'];
		$_POST['ComissionCCMandiri'] = $MyRow['comissionccmandiri'];
		$_POST['ComissionCCBCA'] = $MyRow['comissionccbca'];
		$_POST['ComissionAmexBCA'] = $MyRow['comissionamexbca'];
		$_POST['PercentConsignmentPTADU'] = $MyRow['percentconsignmentptadu'];
		$_POST['AccountConsignmentSalesPTADU'] = $MyRow['accountconsignmentsalesptadu'];
		$_POST['AccountConsignmentCOGSPartner'] = $MyRow['accountconsignmentcogspartner'];
		$_POST['AccountWeChat'] = $MyRow['accountwechat'];
		$_POST['AccountComissionWeChat'] = $MyRow['accountcomissionwechat'];
		$_POST['ComissionWeChat'] = $MyRow['comissionwechat'];
		$_POST['AccountQRIS'] = $MyRow['accountqris'];
		$_POST['AccountComissionQRIS'] = $MyRow['accountcomissionqris'];
		$_POST['ComissionQRIS'] = $MyRow['comissionqris'];
		$_POST['CounterInvoiceA'] = $MyRow['counterinvoicea'];
		$_POST['CounterInvoiceB'] = $MyRow['counterinvoiceb'];
		$_POST['CounterInvoiceC'] = $MyRow['counterinvoicec'];
		$_POST['AccountPOSReceivable'] = $MyRow['accountposreceivable'];
		$_POST['SettlementDelayWeChat'] = $MyRow['settlementdelaywechat'];
		$_POST['SettlementDelayQRIS'] = $MyRow['settlementdelayqris'];

		echo '<input type="hidden" name="SelectedPartner" value="' . $SelectedPartner . '" />';
		echo '<input type="hidden" name="PartnerCode" value="' . $_POST['PartnerCode'] . '" />';

		echo '<fieldset><legend>' . __('Edit Retail Partner details') . '</legend>';
		echo '<field>' . __('Retail Partner Code') . ': ' . $_POST['PartnerCode'] . '</field>';
		echo '</fieldset>';

	} else { //end of if $SelectedPartner only do the else when a new record is being entered
		if (!isset($_POST['PartnerCode'])) {
			$_POST['PartnerCode'] = '';
		}
		echo '<fieldset><legend>' . __('New Retail Partner details') . '</legend>';
		echo FieldToSelectOneText('PartnerCode', $_POST['PartnerCode'], 20, 20, __('Partner Code'), '', 'no-illegal-chars', '', true, true);
		echo '</fieldset>';
	}
	if (!isset($_POST['PartnerName'])) {
		$_POST['PartnerName'] = '';
	}
	if (!isset($_POST['PartnerEmail'])) {
		$_POST['PartnerEmail'] = '';
	}
	if (!isset($_POST['PartnerNameInvoice'])) {
		$_POST['PartnerNameInvoice'] = '';
	}
	if (!isset($_POST['PartnerAddress'])) {
		$_POST['PartnerAddress'] = '';
	}
	if (!isset($_POST['partneraddressjalan'])) {
		$_POST['partneraddressjalan'] = '';
	}
	if (!isset($_POST['partneraddressblok'])) {
		$_POST['partneraddressblok'] = '';
	}
	if (!isset($_POST['partneraddressnomor'])) {
		$_POST['partneraddressnomor'] = '';
	}
	if (!isset($_POST['partneraddressrt'])) {
		$_POST['partneraddressrt'] = '';
	}
	if (!isset($_POST['partneraddressrw'])) {
		$_POST['partneraddressrw'] = '';
	}
	if (!isset($_POST['partneraddresskecamatan'])) {
		$_POST['partneraddresskecamatan'] = '';
	}
	if (!isset($_POST['partneraddresskelurahan'])) {
		$_POST['partneraddresskelurahan'] = '';
	}
	if (!isset($_POST['partneraddresskabupaten'])) {
		$_POST['partneraddresskabupaten'] = '';
	}
	if (!isset($_POST['partneraddresspropinsi'])) {
		$_POST['partneraddresspropinsi'] = '';
	}
	if (!isset($_POST['partneraddresskodepos'])) {
		$_POST['partneraddresskodepos'] = '';
	}
	if (!isset($_POST['partnertelepon'])) {
		$_POST['partnertelepon'] = '';
	}
	if (!isset($_POST['PartnerNPWP'])) {
		$_POST['PartnerNPWP'] = '';
	}
	if (!isset($_POST['PartnerNPWPInvoice'])) {
		$_POST['PartnerNPWPInvoice'] = '';
	}
	if (!isset($_POST['PPN'])) {
		$_POST['PPN'] = 0;
	}
	if (!isset($_POST['AccountPPN'])) {
		$_POST['AccountPPN'] = '';
	}
	if (!isset($_POST['AreaSalesCreditCard'])) {
		$_POST['AreaSalesCreditCard'] = '';
	}
	if (!isset($_POST['AreaSalesCash'])) {
		$_POST['AreaSalesCash'] = '';
	}
	if (!isset($_POST['AreaSalesCashOthers'])) {
		$_POST['AreaSalesCashOthers'] = '';
	}
	if (!isset($_POST['CashSalesReported'])) {
		$_POST['CashSalesReported'] = 0;
	}
	if (!isset($_POST['HPPCompensation'])) {
		$_POST['HPPCompensation'] = 100;
	}
	if (!isset($_POST['AccountHPPCompensation'])) {
		$_POST['AccountHPPCompensation'] = '510010050';
	}
	if (!isset($_POST['AccountBankDanamon'])) {
		$_POST['AccountBankDanamon'] = '';
	}
	if (!isset($_POST['SettlementDelayDanamon'])) {
		$_POST['SettlementDelayDanamon'] = 1;
	}
	if (!isset($_POST['AccountBankBNI'])) {
		$_POST['AccountBankBNI'] = '';
	}
	if (!isset($_POST['SettlementDelayBNI'])) {
		$_POST['SettlementDelayBNI'] = 1;
	}
	if (!isset($_POST['AccountBankMandiri'])) {
		$_POST['AccountBankMandiri'] = '';
	}
	if (!isset($_POST['SettlementDelayMandiri'])) {
		$_POST['SettlementDelayMandiri'] = 1;
	}
	if (!isset($_POST['AccountBankBCA'])) {
		$_POST['AccountBankBCA'] = '';
	}
	if (!isset($_POST['SettlementDelayBCA'])) {
		$_POST['SettlementDelayBCA'] = 1;
	}
	if (!isset($_POST['AccountComissionCreditCard'])) {
		$_POST['AccountComissionCreditCard'] = '';
	}
	if (!isset($_POST['ComissionCCDanamon'])) {
		$_POST['ComissionCCDanamon'] = 0;
	}
	if (!isset($_POST['ComissionAmexDanamon'])) {
		$_POST['ComissionAmexDanamon'] = 0;
	}
	if (!isset($_POST['ComissionCCBNI'])) {
		$_POST['ComissionCCBNI'] = 0;
	}
	if (!isset($_POST['ComissionAmexBNI'])) {
		$_POST['ComissionAmexBNI'] = 0;
	}
	if (!isset($_POST['ComissionCCMandiri'])) {
		$_POST['ComissionCCMandiri'] = 0;
	}
	if (!isset($_POST['ComissionCCBCA'])) {
		$_POST['ComissionCCBCA'] = 0;
	}
	if (!isset($_POST['ComissionAmexBCA'])) {
		$_POST['ComissionAmexBCA'] = 0;
	}
	if (!isset($_POST['PercentConsignmentPTADU'])) {
		$_POST['PercentConsignmentPTADU'] = 0;
	}
	if (!isset($_POST['AccountConsignmentSalesPTADU'])) {
		$_POST['AccountConsignmentSalesPTADU'] = '';
	}
	if (!isset($_POST['AccountConsignmentCOGSPartner'])) {
		$_POST['AccountConsignmentCOGSPartner'] = '';
	}
	if (!isset($_POST['AccountWeChat'])) {
		$_POST['AccountWeChat'] = '';
	}
	if (!isset($_POST['ComissionWeChat'])) {
		$_POST['ComissionWeChat'] = 0;
	}
	if (!isset($_POST['AccountComissionWeChat'])) {
		$_POST['AccountComissionWeChat'] = '';
	}
	if (!isset($_POST['AccountQRIS'])) {
		$_POST['AccountQRIS'] = '';
	}
	if (!isset($_POST['ComissionQRIS'])) {
		$_POST['ComissionQRIS'] = 0;
	}
	if (!isset($_POST['AccountComissionQRIS'])) {
		$_POST['AccountComissionQRIS'] = '';
	}
	if (!isset($_POST['CounterInvoiceA'])) {
		$_POST['CounterInvoiceA'] = 0;
	}
	if (!isset($_POST['CounterInvoiceB'])) {
		$_POST['CounterInvoiceB'] = 0;
	}
	if (!isset($_POST['CounterInvoiceC'])) {
		$_POST['CounterInvoiceC'] = 0;
	}
	if (!isset($_POST['AccountPOSReceivable'])) {
		$_POST['AccountPOSReceivable'] = '';
	}
	if (!isset($_POST['SettlementDelayWeChat'])) {
		$_POST['SettlementDelayWeChat'] = 1;
	}
	if (!isset($_POST['SettlementDelayQRIS'])) {
		$_POST['SettlementDelayQRIS'] = 1;
	}

	echo '<fieldset><legend>' . __('Partner POS Parameters') . '</legend>';
	echo FieldToSelectOneText('PartnerName', $_POST['PartnerName'], 51, 50, __('Partner Name in POS Slip'), '');
	echo FieldToSelectOneText('PartnerAddress', $_POST['PartnerAddress'], 51, 100, __('Address in POS Slip'));
	echo FieldToSelectOneText('PartnerNPWP', $_POST['PartnerNPWP'], 21, 20, __('NPWP in POS Slip'));
	echo FieldToSelectOneSalesArea('AreaSalesCreditCard', $_POST['AreaSalesCreditCard'], __('Credit Card Sales Area'));
	echo FieldToSelectOneSalesArea('AreaSalesCash', $_POST['AreaSalesCash'], __('Cash Sales Area'));
	echo FieldToSelectOneSalesArea('AreaSalesCashOthers', $_POST['AreaSalesCashOthers'], __('Other Cash Sales Area'));
	echo FieldToSelectOneText('CashSalesReported', $_POST['CashSalesReported'], 5, 5, __('% cash Sales Reported'));
	echo FieldToSelectOneGLAccount('AccountPOSReceivable', $_POST['AccountPOSReceivable'], __('POS Receivable GL Account'));
	echo FieldToSelectOneSysType('CounterInvoiceA', $_POST['CounterInvoiceA'], __('Counter Invoices Credit Card'));
	echo FieldToSelectOneSysType('CounterInvoiceB', $_POST['CounterInvoiceB'], __('Counter Invoices Cash'));
	echo FieldToSelectOneSysType('CounterInvoiceC', $_POST['CounterInvoiceC'], __('Counter Invoices Other Cash'));
	echo '</fieldset>';

	echo '<fieldset><legend>' . __('Invoice Details') . '</legend>';
	echo FieldToSelectOneText('PartnerNameInvoice', $_POST['PartnerNameInvoice'], 51, 50, __('Partner Name in Consignment Invoice/FP'), '');
	echo FieldToSelectOneText('partneraddressjalan', $_POST['partneraddressjalan'], 51, 100, __('Jalan in Consignment Invoice/FP'));
	echo FieldToSelectOneText('partneraddressblok', $_POST['partneraddressblok'], 21, 20, __('Blok in Consignment Invoice/FP'), '', '', '', false, false);
	echo FieldToSelectOneText('partneraddressnomor', $_POST['partneraddressnomor'], 21, 20, __('Nomor in Consignment Invoice/FP'));
	echo FieldToSelectOneText('partneraddressrt', $_POST['partneraddressrt'], 21, 20, __('RT in Consignment Invoice/FP'), '', '', '', false, false);
	echo FieldToSelectOneText('partneraddressrw', $_POST['partneraddressrw'], 21, 20, __('RW in Consignment Invoice/FP'), '', '', '', false, false);
	echo FieldToSelectOneText('partneraddresskecamatan', $_POST['partneraddresskecamatan'], 51, 50, __('Kecamatan in Consignment Invoice/FP'), '', '', '', false, false);
	echo FieldToSelectOneText('partneraddresskelurahan', $_POST['partneraddresskelurahan'], 51, 50, __('Kelurahan in Consignment Invoice/FP'), '', '', '', false, false);
	echo FieldToSelectOneText('partneraddresskabupaten', $_POST['partneraddresskabupaten'], 51, 50, __('Kabupaten in Consignment Invoice/FP'), '', '', '', false, false);
	echo FieldToSelectOneText('partneraddresspropinsi', $_POST['partneraddresspropinsi'], 51, 50, __('Propinsi in Consignment Invoice/FP'));
	echo FieldToSelectOneText('partneraddresskodepos', $_POST['partneraddresskodepos'], 11, 10, __('Kode Pos in Consignment Invoice/FP'));
	echo FieldToSelectOneText('partnertelepon', $_POST['partnertelepon'], 21, 20, __('Telepon in Consignment Invoice/FP'), '', '', '', false, false);
	echo FieldToSelectOneText('PartnerNPWPInvoice', $_POST['PartnerNPWPInvoice'], 21, 20, __('NPWP in Consignment Invoice/FP'));
	echo FieldToSelectOneEmail('PartnerEmail', $_POST['PartnerEmail'], 51, 50, __('Partner Email'), '');
	echo '</fieldset>';

	echo '<fieldset><legend>' . __('Tax Information') . '</legend>';
	echo FieldToSelectOneText('PPN', $_POST['PPN'], 5, 5, __('% PPN to apply'), '');
	echo FieldToSelectOneGLAccount('AccountPPN', $_POST['AccountPPN'], __('PPN GL Account'));
	echo '</fieldset>';

	echo '<fieldset><legend>' . __('PTADU Consignment Information') . '</legend>';
	echo FieldToSelectOneText('PercentConsignmentPTADU', $_POST['PercentConsignmentPTADU'], 5, 5, __('% Consignment to PT ADU'));
	echo FieldToSelectOneGLAccount('AccountConsignmentSalesPTADU', $_POST['AccountConsignmentSalesPTADU'], __('Consignment Sales PT. ADU GL Account'));
	echo FieldToSelectOneGLAccount('AccountConsignmentCOGSPartner', $_POST['AccountConsignmentCOGSPartner'], __('Consignment COGS Partner GL Account'));
	echo '</fieldset>';

	echo '<fieldset><legend>' . __('EDC Comission Information') . '</legend>';
	echo FieldToSelectOneGLAccount('AccountComissionCreditCard', $_POST['AccountComissionCreditCard'], __('Credit Card Comission GL Account'));
	echo FieldToSelectOneGLAccount('AccountBankDanamon', $_POST['AccountBankDanamon'], __('Bank Danamon GL Account'));
	echo FieldToSelectOneText('SettlementDelayDanamon', $_POST['SettlementDelayDanamon'], 3, 3, __('Settlement Delay Danamon (days)'), '', 'number');
	echo FieldToSelectOneText('ComissionCCDanamon', $_POST['ComissionCCDanamon'], 5, 5, __('% Credit Card Comission Bank Danamon'));
	echo FieldToSelectOneText('ComissionAmexDanamon', $_POST['ComissionAmexDanamon'], 5, 5, __('% AMEX Comission Bank Danamon'));
	echo FieldToSelectOneGLAccount('AccountBankBNI', $_POST['AccountBankBNI'], __('Bank BNI GL Account'));
	echo FieldToSelectOneText('SettlementDelayBNI', $_POST['SettlementDelayBNI'], 3, 3, __('Settlement Delay BNI (days)'), '', 'number');
	echo FieldToSelectOneText('ComissionCCBNI', $_POST['ComissionCCBNI'], 5, 5, __('% Credit Card Comission Bank BNI'));
	echo FieldToSelectOneText('ComissionAmexBNI', $_POST['ComissionAmexBNI'], 5, 5, __('% AMEX Comission Bank BNI'));
	echo FieldToSelectOneGLAccount('AccountBankMandiri', $_POST['AccountBankMandiri'], __('Bank Mandiri GL Account'));
	echo FieldToSelectOneText('SettlementDelayMandiri', $_POST['SettlementDelayMandiri'], 3, 3, __('Settlement Delay Mandiri (days)'), '', 'number');
	echo FieldToSelectOneText('ComissionCCMandiri', $_POST['ComissionCCMandiri'], 5, 5, __('% Credit Card Comission Bank Mandiri'));
	echo FieldToSelectOneGLAccount('AccountBankBCA', $_POST['AccountBankBCA'], __('Bank BCA GL Account'));
	echo FieldToSelectOneText('SettlementDelayBCA', $_POST['SettlementDelayBCA'], 3, 3, __('Settlement Delay BCA (days)'), '', 'number');
	echo FieldToSelectOneText('ComissionCCBCA', $_POST['ComissionCCBCA'], 5, 5, __('% Credit Card Comission Bank BCA'));
	echo FieldToSelectOneText('ComissionAmexBCA', $_POST['ComissionAmexBCA'], 5, 5, __('% AMEX Comission Bank BCA'));
	echo '</fieldset>';

	echo '<fieldset><legend>' . __('QRIS Information') . '</legend>';
	echo FieldToSelectOneGLAccount('AccountQRIS', $_POST['AccountQRIS'], __('QRIS Mandiri GL Account'));
	echo FieldToSelectOneText('SettlementDelayQRIS', $_POST['SettlementDelayQRIS'], 3, 3, __('Settlement Delay QRIS Mandiri (days)'), '', 'number');
	echo FieldToSelectOneText('ComissionQRIS', $_POST['ComissionQRIS'], 5, 5, __('% Comission QRIS Mandiri'));
	echo FieldToSelectOneGLAccount('AccountComissionQRIS', $_POST['AccountComissionQRIS'], __('QRIS Mandiri Comission GL Account'));
	echo '</fieldset>';

	echo '<fieldset><legend>' . __('AliPay/WeChat Information') . '</legend>';
	echo FieldToSelectOneGLAccount('AccountWeChat', $_POST['AccountWeChat'], __('AliPay/WeChat GL Account'));
	echo FieldToSelectOneText('SettlementDelayWeChat', $_POST['SettlementDelayWeChat'], 3, 3, __('Settlement Delay AliPay/WeChat (days)'), '', 'number');
	echo FieldToSelectOneText('ComissionWeChat', $_POST['ComissionWeChat'], 5, 5, __('% Comission AliPay/WeChat'));
	echo FieldToSelectOneGLAccount('AccountComissionWeChat', $_POST['AccountComissionWeChat'], __('AliPay/WeChat Comission GL Account'));
	echo '</fieldset>';

	echo '<fieldset><legend>' . __('HPP Compensation (Obsolete)') . '</legend>';
	echo FieldToSelectOneText('HPPCompensation', $_POST['HPPCompensation'], 5, 5, __('% HPP Compensation (Obsolete. Always 100)'));
	echo FieldToSelectOneGLAccount('AccountHPPCompensation', $_POST['AccountHPPCompensation'], __('HPP Compensation GL Account'));
	echo '</fieldset>';

	echo OneButtonCenteredForm('submit', __('Enter Information'));

	echo '</div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');

