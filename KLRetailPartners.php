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
			<td class="noprint"><a href="%sSelectedPartner=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this retail partner?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['partnercode'],
			$myrow['partnername'],
			locale_number_format($myrow['ppn'],0) . "%",
			locale_number_format($myrow['cashsalesreported'],0) . "%",
			locale_number_format($myrow['hppcompensation'],0) . "%",
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
		$_POST['ComissionCCMandiri'] = $myrow['comissionccmandiri'];
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
			<td><input type="text" name="PartnerAddress" value="' . $_POST['PartnerAddress'] . '" size="51" maxlength="100" /></td>
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
			<td>' . _('% HPP Compensation') . ':</td>
			<td><input type="text" name="HPPCompensation" class="number" title="' . _('HPP Compensation') . '" value="' . $_POST['HPPCompensation'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('HPP Compensation GL Account') . ':' . '</td>
		<td><select name="AccountHPPCompensation">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountHPPCompensation']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
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
		<td>' . _('Credit Card Comission GL Account') . ':' . '</td>
		<td><select name="AccountComissionCreditCard">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountComissionCreditCard']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
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
			<td>' . _('% Credit Card Comission Bank Mandiri') . ':</td>
			<td><input type="text" name="ComissionCCMandiri" class="number" value="' . $_POST['ComissionCCMandiri'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% Credit Card Comission Bank BCA') . ':</td>
			<td><input type="text" name="ComissionCCBCA" class="number" value="' . $_POST['ComissionCCBCA'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% AMEX Comission Bank BCA') . ':</td>
			<td><input type="text" name="ComissionAmexBCA" class="number" value="' . $_POST['ComissionAmexBCA'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% Consignment to PT ADU') . ':</td>
			<td><input type="text" name="PercentConsignmentTADU" class="number" value="' . $_POST['PercentConsignmentTADU'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('Consignment Sales PT. ADU GL Account') . ':' . '</td>
		<td><select name="AccountConsignmentSalesPTADU">';
	$GLAccount = DB_query("SELECT accountcode, accountname 
							FROM chartmaster 
							WHERE group_ = 'Clustering' 
							ORDER BY accountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountConsignmentSalesPTADU']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';
		
	echo '<tr>
		<td>' . _('Consignment COGS Partner GL Account') . ':' . '</td>
		<td><select name="AccountConsignmentCOGSPartner">';
	$GLAccount = DB_query("SELECT accountcode, accountname 
							FROM chartmaster 
							WHERE group_ = 'Clustering' 
							ORDER BY accountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountConsignmentCOGSPartner']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

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
