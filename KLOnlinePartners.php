<?php

/* Defines the KL online Partners */

require(__DIR__ . '/includes/session.php');

$Title = __('KL Online Partners Maintenance');// Screen identification.
$ViewTopic = '';// Filename's id in ManualContents.php's TOC.
$BookMark = '';// Anchor's id in the manual's html document.
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/CountriesArray.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/supplier.png" title="',// Icon image.
	__('Setup'), '" /> ',// Icon title.
	__('KL Online Partners Maintenance'), '</p>';// Page title.

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

	$_POST['OnlinePartnerCode']=mb_strtoupper($_POST['OnlinePartnerCode']);
	if (trim($_POST['OnlinePartnerCode']) == '') {
		$InputError = 1;
		prnMsg(__('The Partner Code code may not be empty'), 'error');
	}

	if (isset($SelectedPartner) AND $InputError !=1) {

		$SQL = "UPDATE klonlinepartners 
				SET onlinepartnername ='" . $_POST['OnlinePartnerName'] . "',
					paypalaccount ='" . $_POST['PayPalAccount'] . "',
					paypaltest ='" . $_POST['PayPalTest'] . "',
					paypalusername ='" . $_POST['PayPalUsername'] . "',
					paypalpassword ='" . $_POST['PayPalPassword'] . "',
					paypalsignature ='" . $_POST['PayPalSignature'] . "',
					accounttransfermandiri ='" . $_POST['AccountTransferMandiri'] . "',
					accounttransferbca ='" . $_POST['AccountTransferBCA'] . "',
					accounttransferdanamon ='" . $_POST['AccountTransferDanamon'] . "',
					accountdokuidr ='" . $_POST['AccountDokuIDR'] . "',
					accountdokucomissionidr = '" . $_POST['AccountDokuComissionIDR'] . "',
					comissionflatdoku = '" . $_POST['ComissionFlatDoku'] . "',
					comissionccdoku = '" . $_POST['ComissionCCDoku'] . "',
					accountxenditidr ='" . $_POST['AccountXenditIDR'] . "',
					accountxenditcomissionidr = '" . $_POST['AccountXenditComissionIDR'] . "',
					comissionxenditflattransfer = '" . $_POST['ComissionXenditFlatTransfer'] . "',
					comissionxenditflatcc = '" . $_POST['ComissionXenditFlatCC'] . "',
					comissionxenditpercentcc = '" . $_POST['ComissionXenditPercentCC'] . "',
					accountmidtransidr ='" . $_POST['AccountMidtransIDR'] . "',
					accounttokopediaidr ='" . $_POST['AccountTokopediaIDR'] . "',
					accounttokopediacomissionidr = '" . $_POST['AccountTokopediaComissionIDR'] . "',
					comissiontokopediapercent = '" . $_POST['ComissionTokopediaPercent'] . "',
					comissiontokopediaflatfee = '" . $_POST['ComissionTokopediaFlatFee'] . "',
					accountshopeeidr ='" . $_POST['AccountShopeeIDR'] . "',
					accountshopeecomissionidr = '" . $_POST['AccountShopeeComissionIDR'] . "',
					comissionshopeepercent = '" . $_POST['ComissionShopeePercent'] . "',
					comissionshopeeflatfee = '" . $_POST['ComissionShopeeFlatFee'] . "',
					accountlazadaidr ='" . $_POST['AccountLazadaIDR'] . "',
					accountlazadacomissionidr = '" . $_POST['AccountLazadaComissionIDR'] . "',
					comissionlazadapercent = '" . $_POST['ComissionLazadaPercent'] . "',
					accountcomissionppn ='" . $_POST['AccountComissionPPN'] . "',
					accountpaypalaud ='" . $_POST['AccountPayPalAUD'] . "',
					accountpaypalcomissionaud = '" . $_POST['AccountPayPalComissionAUD'] . "',
					accountpaypalusd ='" . $_POST['AccountPayPalUSD'] . "',
					accountpaypalcomissionusd = '" . $_POST['AccountPayPalComissionUSD'] . "',
					accountpaypaleur ='" . $_POST['AccountPayPalEUR'] . "',
					accountpaypalcomissioneur = '" . $_POST['AccountPayPalComissionEUR'] . "',
					foreigncurrencysurchargefactor ='" . $_POST['ForeignCurrencySurchargeFactor'] . "'
				WHERE onlinepartnercode = '" . $SelectedPartner . "'";

		$ErrMsg = __('An error occurred updating the') . ' ' . $SelectedPartner . ' ' . __('online partner record because');
		$Result = DB_query($SQL,$ErrMsg);

		prnMsg(__('The online partner record has been updated'),'success');

		unset($SelectedPartner);
		unset($_POST['OnlinePartnerCode']);
		unset($_POST['OnlinePartnerName']);
		unset($_POST['PayPalAccount']);
		unset($_POST['PayPalTest']);
		unset($_POST['PayPalUsername']);
		unset($_POST['PayPalPassword']);
		unset($_POST['PayPalSignature']);
		unset($_POST['AccountTransferMandiri']);
		unset($_POST['AccountTransferBCA']);
		unset($_POST['AccountTransferDanamon']);
		unset($_POST['AccountDokuIDR']);
		unset($_POST['AccountDokuComissionIDR']);
		unset($_POST['ComissionFlatDoku']);
		unset($_POST['ComissionCCDoku']);
		unset($_POST['AccountPayPalAUD']);
		unset($_POST['AccountPayPalComissionAUD']);
		unset($_POST['AccountPayPalUSD']);
		unset($_POST['AccountPayPalComissionUSD']);
		unset($_POST['AccountPayPalEUR']);
		unset($_POST['AccountPayPalComissionEUR']);
		unset($_POST['ForeignCurrencySurchargeFactor']);
		unset($_POST['AccountXenditIDR']);
		unset($_POST['AccountXenditComissionIDR']);
		unset($_POST['ComissionXenditFlatTransfer']);
		unset($_POST['ComissionXenditFlatCC']);
		unset($_POST['ComissionXenditPercentCC']);
		unset($_POST['AccountMidtransIDR']);
		unset($_POST['AccountTokopediaIDR']);
		unset($_POST['AccountTokopediaComissionIDR']);
		unset($_POST['AccountShopeeIDR']);
		unset($_POST['AccountShopeeComissionIDR']);
		unset($_POST['AccountLazadaIDR']);
		unset($_POST['AccountLazadaComissionIDR']);
		unset($_POST['AccountComissionPPN']);
		unset($_POST['ComissionTokopediaPercent']);
		unset($_POST['ComissionTokopediaFlatFee']);
		unset($_POST['ComissionShopeePercent']);
		unset($_POST['ComissionShopeeFlatFee']);
		unset($_POST['ComissionLazadaPercent']);

	} elseif ($InputError !=1) {

		/*SelectedPartner is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$SQL = "INSERT INTO klonlinepartners 
								(onlinepartnercode,
								onlinepartnername,
								paypalaccount,
								paypaltest,
								paypalusername,
								paypalpassword,
								paypalsignature,
								accounttransfermandiri,
								accounttransferbca,
								accounttransferdanamon,
								accountdokuidr,
								accountdokucomissionidr,
								comissionflatdoku,
								comissionccdoku,
								accountxenditidr,
								accountxenditcomissionidr,
								comissionxenditflattransfer,
								comissionxenditflatcc,
								comissionxenditpercentcc,
								accountmidtransidr,
								accounttokopediaidr,
								accounttokopediacomissionidr,
								accountshopeeidr,
								accountshopeecomissionidr,
								accountlazadaidr,
								accountlazadacomissionidr,
								accountcomissionppn,
								accountpaypalaud,
								accountpaypalcomissionaud,
								accountpaypalusd,
								accountpaypalcomissionusd,
								accountpaypaleur,
								accountpaypalcomissioneur,
								comissiontokopediapercent,
								comissiontokopediaflatfee,
								comissionshopeepercent,
								comissionshopeeflatfee,
								comissionlazadapercent,
								foreigncurrencysurchargefactor)
						VALUES ('" . $_POST['OnlinePartnerCode'] . "',
								'" . $_POST['OnlinePartnerName'] . "',
								'" . $_POST['PayPalAccount'] ."',
								'" . $_POST['PayPalTest'] ."',
								'" . $_POST['PayPalUsername'] ."',
								'" . $_POST['PayPalPassword'] . "',
								'" . $_POST['PayPalSignature'] . "',
								'" . $_POST['AccountTransferMandiri'] . "',
								'" . $_POST['AccountTransferBCA'] . "',
								'" . $_POST['AccountTransferDanamon'] . "',
								'" . $_POST['AccountDokuIDR'] . "',
								'" . $_POST['AccountDokuComissionIDR'] . "',
								'" . $_POST['ComissionFlatDoku'] . "',
								'" . $_POST['ComissionCCDoku'] . "',
								'" . $_POST['AccountXenditIDR'] . "',
								'" . $_POST['AccountXenditComissionIDR'] . "',
								'" . $_POST['ComissionXenditFlatTransfer'] . "',
								'" . $_POST['ComissionXenditFlatCC'] . "',
								'" . $_POST['ComissionXenditPercentCC'] . "',
								'" . $_POST['AccountMidtransIDR'] . "',
								'" . $_POST['AccountTokopediaIDR'] . "',
								'" . $_POST['AccountTokopediaComissionIDR'] . "',
								'" . $_POST['AccountShopeeIDR'] . "',
								'" . $_POST['AccountShopeeComissionIDR'] . "',
								'" . $_POST['AccountLazadaIDR'] . "',
								'" . $_POST['AccountLazadaComissionIDR'] . "',
								'" . $_POST['AccountComissionPPN'] . "',
								'" . $_POST['AccountPayPalAUD'] . "',
								'" . $_POST['AccountPayPalComissionAUD'] . "',
								'" . $_POST['AccountPayPalUSD'] . "',
								'" . $_POST['AccountPayPalComissionUSD'] . "',
								'" . $_POST['AccountPayPalEUR'] . "',
								'" . $_POST['AccountPayPalComissionEUR'] . "',
								'" . $_POST['ComissionTokopediaPercent'] . "',
								'" . $_POST['ComissionTokopediaFlatFee'] . "',
								'" . $_POST['ComissionShopeePercent'] . "',
								'" . $_POST['ComissionShopeeFlatFee'] . "',
								'" . $_POST['ComissionLazadaPercent'] . "',
								'" . $_POST['ForeignCurrencySurchargeFactor'] . "')";

		$ErrMsg = __('An error occurred inserting the new online partner record because');
		$Result = DB_query($SQL,$ErrMsg);

		prnMsg(__('The new online partner record has been added'),'success');

		unset($SelectedPartner);
		unset($_POST['OnlinePartnerCode']);
		unset($_POST['OnlinePartnerName']);
		unset($_POST['PayPalAccount']);
		unset($_POST['PayPalTest']);
		unset($_POST['PayPalUsername']);
		unset($_POST['PayPalPassword']);
		unset($_POST['PayPalSignature']);
		unset($_POST['AccountTransferMandiri']);
		unset($_POST['AccountTransferBCA']);
		unset($_POST['AccountTransferDanamon']);
		unset($_POST['AccountDokuIDR']);
		unset($_POST['AccountDokuComissionIDR']);
		unset($_POST['ComissionFlatDoku']);
		unset($_POST['ComissionCCDoku']);
		unset($_POST['AccountPayPalAUD']);
		unset($_POST['AccountPayPalComissionAUD']);
		unset($_POST['AccountPayPalUSD']);
		unset($_POST['AccountPayPalComissionUSD']);
		unset($_POST['AccountPayPalEUR']);
		unset($_POST['AccountPayPalComissionEUR']);
		unset($_POST['ForeignCurrencySurchargeFactor']);
		unset($_POST['AccountXenditIDR']);
		unset($_POST['AccountXenditComissionIDR']);
		unset($_POST['ComissionXenditFlatTransfer']);
		unset($_POST['ComissionXenditFlatCC']);
		unset($_POST['ComissionXenditPercentCC']);
		unset($_POST['AccountMidtransIDR']);
		unset($_POST['AccountTokopediaIDR']);
		unset($_POST['AccountTokopediaComissionIDR']);
		unset($_POST['AccountShopeeIDR']);
		unset($_POST['AccountShopeeComissionIDR']);
		unset($_POST['AccountLazadaIDR']);
		unset($_POST['AccountLazadaComissionIDR']);
		unset($_POST['AccountComissionPPN']);
		unset($_POST['ComissionTokopediaPercent']);
		unset($_POST['ComissionTokopediaFlatFee']);
		unset($_POST['ComissionShopeePercent']);
		unset($_POST['ComissionShopeeFlatFee']);
		unset($_POST['ComissionLazadaPercent']);
	}

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$CancelDelete = 0;
	// PREVENT DELETES IF DEPENDENT RECORDS
	$SQL= "SELECT COUNT(*) FROM locations WHERE onlinepartnercode='". $SelectedPartner . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		$CancelDelete = 1;
		prnMsg(__('Cannot delete this online partner because there are locations related to it.'),'warn');
		echo __('There are') . ' ' . $MyRow[0] . ' ' . __('locations using this online partner');
	}
	if (! $CancelDelete) {
		$Result = DB_query("DELETE FROM klonlinepartners WHERE onlinepartnercode='" . $SelectedPartner . "'");
		prnMsg(__('Online Partner') . ' ' . $SelectedPartner . ' ' . __('has been deleted') . '!', 'success');
		unset ($SelectedPartner);
	}//end if Delete Online Partner
	unset($SelectedPartner);
	unset($_GET['delete']);
}

if (!isset($SelectedPartner)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPartner will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Locations will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT onlinepartnercode,
				onlinepartnername,
				paypalaccount,
				paypaltest,
				foreigncurrencysurchargefactor
			FROM klonlinepartners
			ORDER BY onlinepartnername";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result)==0) {
		prnMsg(__('There are no online partners'),'error');
	}

	echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">', __('Code'), '</th>
				<th class="SortedColumn">', __('Name'), '</th>
				<th class="SortedColumn">', __('PayPal Account'), '</th>
				<th class="SortedColumn">', __('Test?'), '</th>
				<th class="SortedColumn">', __('Foreign Factor'), '</th>
				<th class="noprint" colspan="2">&nbsp;</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow = DB_fetch_array($Result)) {
		echo '<tr class="striped_row">
				<td>' . $MyRow['onlinepartnercode'] . '</td>
				<td>' . $MyRow['onlinepartnername'] . '</td>
				<td>' . $MyRow['paypalaccount'] . '</td>
				<td>' . $MyRow['paypaltest'] . '</td>
				<td class="number">' . locale_number_format($MyRow['foreigncurrencysurchargefactor'],2) . '</td>
				<td class="noprint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedPartner=' . $MyRow['onlinepartnercode'] . '">' . __('Edit') . '</a></td>
				<td class="noprint"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedPartner=' . $MyRow['onlinepartnercode'] . '&amp;delete=1" onclick="return confirm(\'' . __('Are you sure you wish to delete this online partner?') . '\');">' . __('Delete') . '</a></td>
				</tr>';
	}
	//END WHILE LIST LOOP
	echo '</tbody></table>';
}

//end of ifs and buts!

echo '<br />';
if (isset($SelectedPartner)) {
	echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Review Records') . '</a>';
}
echo '<br />';

if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedPartner)) {
		//editing an existing Location

		$SQL = "SELECT onlinepartnercode,
					onlinepartnername,
					paypalaccount,
					paypaltest,
					paypalusername,
					paypalpassword,
					paypalsignature,
					accounttransfermandiri,
					accounttransferbca,
					accounttransferdanamon,
					accountdokuidr,
					accountdokucomissionidr,
					comissionflatdoku,
					comissionccdoku,
					accountxenditidr,
					accountxenditcomissionidr,
					comissionxenditflattransfer,
					comissionxenditflatcc,
					comissionxenditpercentcc,
					accountmidtransidr,
					accounttokopediaidr,
					accounttokopediacomissionidr,
					accountshopeeidr,
					accountshopeecomissionidr,
					accountlazadaidr,
					accountlazadacomissionidr,
					accountcomissionppn,
					accountpaypalaud,
					accountpaypalcomissionaud,
					accountpaypalusd,
					accountpaypalcomissionusd,
					accountpaypaleur,
					accountpaypalcomissioneur,
					comissiontokopediapercent,
					comissiontokopediaflatfee,
					comissionshopeepercent,
					comissionshopeeflatfee,
					comissionlazadapercent,
					foreigncurrencysurchargefactor
				FROM klonlinepartners
				WHERE onlinepartnercode='" . $SelectedPartner . "'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['OnlinePartnerCode'] = $MyRow['onlinepartnercode'];
		$_POST['OnlinePartnerName'] = $MyRow['onlinepartnername'];
		$_POST['PayPalAccount'] = $MyRow['paypalaccount'];
		$_POST['PayPalTest'] = $MyRow['paypaltest'];
		$_POST['PayPalUsername'] = $MyRow['paypalusername'];
		$_POST['PayPalPassword'] = $MyRow['paypalpassword'];
		$_POST['PayPalSignature'] = $MyRow['paypalsignature'];
		$_POST['AccountTransferMandiri'] = $MyRow['accounttransfermandiri'];
		$_POST['AccountTransferBCA'] = $MyRow['accounttransferbca'];
		$_POST['AccountTransferDanamon'] = $MyRow['accounttransferdanamon'];
		$_POST['AccountDokuIDR'] = $MyRow['accountdokuidr'];
		$_POST['AccountDokuComissionIDR'] = $MyRow['accountdokucomissionidr'];
		$_POST['ComissionFlatDoku'] = $MyRow['comissionflatdoku'];
		$_POST['ComissionCCDoku'] = $MyRow['comissionccdoku'];
		$_POST['AccountPayPalAUD'] = $MyRow['accountpaypalaud'];
		$_POST['AccountPayPalComissionAUD'] = $MyRow['accountpaypalcomissionaud'];
		$_POST['AccountPayPalUSD'] = $MyRow['accountpaypalusd'];
		$_POST['AccountPayPalComissionUSD'] = $MyRow['accountpaypalcomissionusd'];
		$_POST['AccountPayPalEUR'] = $MyRow['accountpaypaleur'];
		$_POST['AccountPayPalComissionEUR'] = $MyRow['accountpaypalcomissioneur'];
		$_POST['ForeignCurrencySurchargeFactor'] = $MyRow['foreigncurrencysurchargefactor'];
		$_POST['AccountXenditIDR'] = $MyRow['accountxenditidr'];
		$_POST['AccountXenditComissionIDR'] = $MyRow['accountxenditcomissionidr'];
		$_POST['ComissionXenditFlatTransfer'] = $MyRow['comissionxenditflattransfer'];
		$_POST['ComissionXenditFlatCC'] = $MyRow['comissionxenditflatcc'];
		$_POST['ComissionXenditPercentCC'] = $MyRow['comissionxenditpercentcc'];
		$_POST['AccountMidtransIDR'] = $MyRow['accountmidtransidr'];
		$_POST['AccountTokopediaIDR'] = $MyRow['accounttokopediaidr'];
		$_POST['AccountTokopediaComissionIDR'] = $MyRow['accounttokopediacomissionidr'];
		$_POST['AccountShopeeIDR'] = $MyRow['accountshopeeidr'];
		$_POST['AccountShopeeComissionIDR'] = $MyRow['accountshopeecomissionidr'];
		$_POST['AccountLazadaIDR'] = $MyRow['accountlazadaidr'];
		$_POST['AccountLazadaComissionIDR'] = $MyRow['accountlazadacomissionidr'];
		$_POST['AccountComissionPPN'] = $MyRow['accountcomissionppn'];
		$_POST['ComissionTokopediaPercent'] = $MyRow['comissiontokopediapercent'];
		$_POST['ComissionTokopediaFlatFee'] = $MyRow['comissiontokopediaflatfee'];
		$_POST['ComissionShopeePercent'] = $MyRow['comissionshopeepercent'];
		$_POST['ComissionShopeeFlatFee'] = $MyRow['comissionshopeeflatfee'];
		$_POST['ComissionLazadaPercent'] = $MyRow['comissionlazadapercent'];

		echo '<input type="hidden" name="SelectedPartner" value="' . $SelectedPartner . '" />';
		echo '<input type="hidden" name="OnlinePartnerCode" value="' . $_POST['OnlinePartnerCode'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . __('Amend Online Partner details') . '</th>
			</tr>';
		echo '<tr>
				<td>' . __('Online Partner Code') . ':</td>
				<td>' . $_POST['OnlinePartnerCode'] . '</td>
			</tr>';
	} else {//end of if $SelectedPartner only do the else when a new record is being entered
		if (!isset($_POST['OnlinePartnerCode'])) {
			$_POST['OnlinePartnerCode'] = '';
		}
		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . __('New Online Partner details') . '</h3></th>
				</tr>';
		echo '<tr>
				<td>' . __('Partner Code') . ':</td>
				<td><input type="text" autofocus="autofocus" required="required" title="' . __('Enter up to 10 characters for the online partner code') . '" data-type="no-illegal-chars" name="OnlinePartnerCode" value="' . $_POST['OnlinePartnerCode'] . '" size="11" maxlength="10" /></td>
			</tr>';
	}
	if (!isset($_POST['OnlinePartnerName'])) {
		$_POST['OnlinePartnerName'] = '';
	}
	if (!isset($_POST['PayPalAccount'])) {
		$_POST['PayPalAccount'] = ' ';
	}
	if (!isset($_POST['PayPalTest'])) {
		$_POST['PayPalTest'] = 1;
	}
	if (!isset($_POST['PayPalUsername'])) {
		$_POST['PayPalUsername'] = '';
	}
	if (!isset($_POST['PayPalPassword'])) {
		$_POST['PayPalPassword'] = '';
	}
	if (!isset($_POST['PayPalSignature'])) {
		$_POST['PayPalSignature'] = '';
	}
	if (!isset($_POST['AccountTransferMandiri'])) {
		$_POST['AccountTransferMandiri'] = '';
	}
	if (!isset($_POST['AccountDokuIDR'])) {
		$_POST['AccountDokuIDR'] = '';
	}
	if (!isset($_POST['AccountDokuComissionIDR'])) {
		$_POST['AccountDokuComissionIDR'] = '';
	}
	if (!isset($_POST['ComissionFlatDoku'])) {
		$_POST['ComissionFlatDoku'] = 0;
	}
	if (!isset($_POST['ComissionCCDoku'])) {
		$_POST['ComissionCCDoku'] = 0;
	}
	if (!isset($_POST['AccountPayPalAUD'])) {
		$_POST['AccountPayPalAUD'] = '';
	}
	if (!isset($_POST['AccountPayPalComissionAUD'])) {
		$_POST['AccountPayPalComissionAUD'] = '';
	}
	if (!isset($_POST['AccountPayPalUSD'])) {
		$_POST['AccountPayPalUSD'] = '';
	}
	if (!isset($_POST['AccountPayPalComissionUSD'])) {
		$_POST['AccountPayPalComissionUSD'] = '';
	}
	if (!isset($_POST['AccountPayPalEUR'])) {
		$_POST['AccountPayPalEUR'] = '';
	}
	if (!isset($_POST['AccountPayPalComissionEUR'])) {
		$_POST['AccountPayPalComissionEUR'] = '';
	}
	if (!isset($_POST['ForeignCurrencySurchargeFactor'])) {
		$_POST['ForeignCurrencySurchargeFactor'] = 0;
	}
	if (!isset($_POST['AccountXenditIDR'])) {
		$_POST['AccountXenditIDR'] = '';
	}
	if (!isset($_POST['AccountXenditComissionIDR'])) {
		$_POST['AccountXenditComissionIDR'] = '';
	}
	if (!isset($_POST['ComissionXenditFlatTransfer'])) {
		$_POST['ComissionXenditFlatTransfer'] = 0;
	}
	if (!isset($_POST['ComissionXenditFlatCC'])) {
		$_POST['ComissionXenditFlatCC'] = 0;
	}
	if (!isset($_POST['ComissionXenditPercentCC'])) {
		$_POST['ComissionXenditPercentCC'] = 0;
	}
	if (!isset($_POST['AccountXenditIDR'])) {
		$_POST['AccountXenditIDR'] = '';
	}
	if (!isset($_POST['AccountXenditComissionIDR'])) {
		$_POST['AccountXenditComissionIDR'] = '';
	}
	if (!isset($_POST['AccountMidtransIDR'])) {
		$_POST['AccountMidtransIDR'] = '';
	}
	if (!isset($_POST['AccountTokopediaIDR'])) {
		$_POST['AccountTokopediaIDR'] = '';
	}
	if (!isset($_POST['AccountTokopediaComissionIDR'])) {
		$_POST['AccountTokopediaComissionIDR'] = '';
	}
	if (!isset($_POST['AccountShopeeIDR'])) {
		$_POST['AccountShopeeIDR'] = '';
	}
	if (!isset($_POST['AccountShopeeComissionIDR'])) {
		$_POST['AccountShopeeComissionIDR'] = '';
	}
	if (!isset($_POST['AccountLazadaIDR'])) {
		$_POST['AccountLazadaIDR'] = '';
	}
	if (!isset($_POST['AccountLazadaComissionIDR'])) {
		$_POST['AccountLazadaComissionIDR'] = '';
	}
	if (!isset($_POST['AccountComissionPPN'])) {
		$_POST['AccountComissionPPN'] = 0;
	}
	if (!isset($_POST['ComissionTokopediaPercent'])) {
		$_POST['ComissionTokopediaPercent'] = 0;
	}
	if (!isset($_POST['ComissionTokopediaFlatFee'])) {
		$_POST['ComissionTokopediaFlatFee'] = 0;
	}
	if (!isset($_POST['ComissionShopeePercent'])) {
		$_POST['ComissionShopeePercent'] = 0;
	}
	if (!isset($_POST['ComissionShopeeFlatFee'])) {
		$_POST['ComissionShopeeFlatFee'] = 0;
	}
	if (!isset($_POST['ComissionLazadaPercent'])) {
		$_POST['ComissionLazadaPercent'] = 0;
	}

	echo '<tr>
			<td>' . __('Partner Name') . ':' . '</td>
			<td><input type="text" name="OnlinePartnerName" required="required" value="'. $_POST['OnlinePartnerName'] . '" title="' . __('Enter the online partner name') . '" namesize="51" maxlength="50" /></td>
		</tr>
		<tr>';

	echo '<tr>
			<td>' . __('Foreign Currency Surcharge Factor') . ':</td>
			<td><input type="text" name="ForeignCurrencySurchargeFactor" class="number"  value="' . $_POST['ForeignCurrencySurchargeFactor'] . '" size="5" maxlength="5" /></td>
		</tr>';
	
	echo '<tr>
		<td>' . __('PPN Paid in Commissions GL Account') . ':' . '</td>
		<td><select name="AccountComissionPPN">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountComissionPPN']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<th colspan="2">' . 'Direct Bank Transfer Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . __('Mandiri Bank Transfer IDR GL Account') . ':' . '</td>
		<td><select name="AccountTransferMandiri">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountTransferMandiri']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . __('BCA Bank Transfer IDR GL Account') . ':' . '</td>
		<td><select name="AccountTransferBCA">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountTransferBCA']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . __('Danamon Bank Transfer IDR GL Account') . ':' . '</td>
		<td><select name="AccountTransferDanamon">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountTransferDanamon']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<th colspan="2">' . 'PayPal Details' . '</th>
		</tr>';

	echo '<td>' . __('PayPal Account') . ':' . '</td>
			<td><input type="text" name="PayPalAccount" value="' . $_POST['PayPalAccount'] . '" size="51" maxlength="50" /></td>
		</tr>';
	echo '<tr>
			<td>' . __('Test Account?') . ':</td>
			<td><select name="PayPalTest">';
	if ($_POST['PayPalTest']==1) {
		echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
	} else {
		echo '<option value="1">' . __('Yes') . '</option>';
	}
	if ($_POST['PayPalTest']==0) {
		echo '<option selected="selected" value="0">' . __('No') . '</option>';
	} else {
		echo '<option value="0">' . __('No') . '</option>';
	}
	echo '</select></td></tr>';
	echo '<tr>
			<td>' . __('PayPal Username') . ':' . '</td>
			<td><input type="text" name="PayPalUsername" value="' . $_POST['PayPalUsername'] . '" size="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . __('PayPal Password') . ':' . '</td>
			<td><input type="text" name="PayPalPassword" value="' . $_POST['PayPalPassword'] . '" size="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . __('PayPal Signature') . ':' . '</td>
			<td><input type="text" name="PayPalSignature" value="' . $_POST['PayPalSignature'] . '" size="51" maxlength="100" /></td>
		</tr>';

	echo '<tr>
		<td>' . __('PayPal AUD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalAUD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountPayPalAUD']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . __('Comission PayPal AUD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalComissionAUD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountPayPalComissionAUD']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	echo '<tr>
		<td>' . __('PayPal USD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalUSD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountPayPalUSD']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . __('Comission PayPal USD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalComissionUSD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountPayPalComissionUSD']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . __('PayPal EUR GL Account') . ':' . '</td>
		<td><select name="AccountPayPalEUR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountPayPalEUR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . __('Comission PayPal EUR GL Account') . ':' . '</td>
		<td><select name="AccountPayPalComissionEUR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountPayPalComissionEUR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<th colspan="2">' . 'MIDTRANS Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . __('Midtrans IDR GL Account') . ':' . '</td>
		<td><select name="AccountMidtransIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountMidtransIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . __('% Fee Comission Midtrans') . ':</td>
			<td>' . __('MidTrans has commissions but we cannot integrate them. We account full order, later manually we process commissions') . ':</td>
		</tr>';


	echo '<tr>
			<th colspan="2">' . 'TOKOPEDIA Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . __('Tokopedia IDR GL Account') . ':' . '</td>
		<td><select name="AccountTokopediaIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountTokopediaIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . __('Comission Tokopedia IDR GL Account') . ':' . '</td>
		<td><select name="AccountTokopediaComissionIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountTokopediaComissionIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . __('% Fee Comission Tokopedia') . ':</td>
			<td><input type="text" name="ComissionTokopediaPercent" class="number"  value="' . $_POST['ComissionTokopediaPercent'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . __('Flat Order Processing Fee') . ':</td>
			<td><input type="text" name="ComissionTokopediaFlatFee" class="number"  value="' . $_POST['ComissionTokopediaFlatFee'] . '" size="10" maxlength="10" /></td>
		</tr>';

	echo '<tr>
			<th colspan="2">' . 'SHOPEE Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . __('Shopee IDR GL Account') . ':' . '</td>
		<td><select name="AccountShopeeIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountShopeeIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . __('Comission Shopee IDR GL Account') . ':' . '</td>
		<td><select name="AccountShopeeComissionIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountShopeeComissionIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';	

	echo '<tr>
			<td>' . __('% Fee Comission Shopee') . ':</td>
			<td><input type="text" name="ComissionShopeePercent" class="number"  value="' . $_POST['ComissionShopeePercent'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . __('Flat Order Processing Fee') . ':</td>
			<td><input type="text" name="ComissionShopeeFlatFee" class="number"  value="' . $_POST['ComissionShopeeFlatFee'] . '" size="10" maxlength="10" /></td>
		</tr>';

	echo '<tr>
			<th colspan="2">' . 'LAZADA Details (NOT USED YET)' . '</th>
		</tr>';

	echo '<tr>
		<td>' . __('Lazada IDR GL Account') . ':' . '</td>
		<td><select name="AccountLazadaIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountLazadaIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . __('Comission Lazada IDR GL Account') . ':' . '</td>
		<td><select name="AccountLazadaComissionIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountLazadaComissionIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . __('% Fee Comission Lazada') . ':</td>
			<td><input type="text" name="ComissionLazadaPercent" class="number"  value="' . $_POST['ComissionLazadaPercent'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<th colspan="2">' . 'DOKU Details (NOT USED YET)' . '</th>
		</tr>';

	echo '<tr>
		<td>' . __('Doku IDR GL Account') . ':' . '</td>
		<td><select name="AccountDokuIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountDokuIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . __('Comission Doku IDR GL Account') . ':' . '</td>
		<td><select name="AccountDokuComissionIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountDokuComissionIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . __('Flat Fee Commission DOKU for all tx (in IDR)') . ':</td>
			<td><input type="text" name="ComissionFlatDoku" class="number"  value="' . $_POST['ComissionFlatDoku'] . '" size="20" maxlength="20" /></td>
		</tr>';

	echo '<tr>
			<td>' . __('% Fee Commission DOKU for CC only (%))') . ':</td>
			<td><input type="text" name="ComissionCCDoku" class="number"  value="' . $_POST['ComissionCCDoku'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<th colspan="2">' . 'XENDIT Details (NOT USED YET)' . '</th>
		</tr>';

	echo '<tr>
		<td>' . __('Xendit IDR GL Account') . ':' . '</td>
		<td><select name="AccountXenditIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountXenditIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . __('Comission Xendit IDR GL Account') . ':' . '</td>
		<td><select name="AccountXenditComissionIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($MyRow=DB_fetch_array($GLAccount)) {
		if ($_POST['AccountXenditComissionIDR']==$MyRow['accountcode']) {
			echo '<option selected="selected" value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $MyRow['accountcode'] .  '">' . $MyRow['accountcode'] . ' - ' . htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . __('Flat Fee Commission Xendit for Bank Transfers (in IDR)') . ':</td>
			<td><input type="text" name="ComissionXenditFlatTransfer" class="number"  value="' . $_POST['ComissionXenditFlatTransfer'] . '" size="20" maxlength="20" /></td>
		</tr>';

	echo '<tr>
			<td>' . __('Flat Fee Commission Xendit for CC (in IDR)') . ':</td>
			<td><input type="text" name="ComissionXenditFlatCC" class="number"  value="' . $_POST['ComissionXenditFlatCC'] . '" size="20" maxlength="20" /></td>
		</tr>';

	echo '<tr>
			<td>' . __('% Fee Commission Xendit for CC only (%)') . ':</td>
			<td><input type="text" name="ComissionXenditPercentCC" class="number"  value="' . $_POST['ComissionXenditPercentCC'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '</tbody></table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . __('Enter Information') . '" />
		</div>
		</div>
		</form>';

}//end if record deleted no point displaying form to add record

include(__DIR__ . '/includes/footer.php');

