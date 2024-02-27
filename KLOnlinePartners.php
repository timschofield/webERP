<?php

/* Defines the KL online Partners */

include('includes/session.php');
$Title = _('KL Online Partners Maintenance');// Screen identification.
$ViewTopic = '';// Filename's id in ManualContents.php's TOC.
$BookMark = '';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/supplier.png" title="',// Icon image.
	_('Setup'), '" /> ',// Icon title.
	_('KL Online Partners Maintenance'), '</p>';// Page title.

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

	$_POST['OnlinePartnerCode']=mb_strtoupper($_POST['OnlinePartnerCode']);
	if(trim($_POST['OnlinePartnerCode']) == '') {
		$InputError = 1;
		prnMsg(_('The Partner Code code may not be empty'), 'error');
	}

	if(isset($SelectedPartner) AND $InputError !=1) {

		$sql = "UPDATE klonlinepartners 
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
					comissiontokopediafreeshippingperitempercent = '" . $_POST['ComissionTokopediaFreeShippingPerItem'] . "',
					comissiontokopediafreeshippingperitemmaximum = '" . $_POST['ComissionTokopediaFreeShippingMaximum'] . "',
					accountshopeeidr ='" . $_POST['AccountShopeeIDR'] . "',
					accountshopeecomissionidr = '" . $_POST['AccountShopeeComissionIDR'] . "',
					comissionshopeepercent = '" . $_POST['ComissionShopeePercent'] . "',
					comissionshopeefreeshippingperitempercent = '" . $_POST['ComissionShopeeFreeShippingPerItem'] . "',
					comissionshopeefreeshippingperitemmaximum = '" . $_POST['ComissionShopeeFreeShippingMaximum'] . "',
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

		$ErrMsg = _('An error occurred updating the') . ' ' . $SelectedPartner . ' ' . _('online partner record because');
		$DbgMsg = _('The SQL used to update the online partner record was');

		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		prnMsg(_('The online partner record has been updated'),'success');

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
		unset($_POST['ComissionTokopediaFreeShippingPerItem']);
		unset($_POST['ComissionTokopediaFreeShippingMaximum']);
		unset($_POST['ComissionShopeePercent']);
		unset($_POST['ComissionShopeeFreeShippingPerItem']);
		unset($_POST['ComissionShopeeFreeShippingMaximum']);
		unset($_POST['ComissionLazadaPercent']);

	} elseif($InputError !=1) {

		/*SelectedPartner is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$sql = "INSERT INTO klonlinepartners 
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
								comissiontokopediafreeshippingperitempercent,
								comissiontokopediafreeshippingperitemmaximum,
								comissionshopeepercent,
								comissionshopeefreeshippingperitempercent,
								comissionshopeefreeshippingperitemmaximum,
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
								'" . $_POST['ComissionTokopediaFreeShippingPerItem'] . "',
								'" . $_POST['ComissionTokopediaFreeShippingMaximum'] . "',
								'" . $_POST['ComissionShopeePercent'] . "',
								'" . $_POST['ComissionShopeeFreeShippingPerItem'] . "',
								'" . $_POST['ComissionShopeeFreeShippingMaximum'] . "',
								'" . $_POST['ComissionLazadaPercent'] . "',
								'" . $_POST['ForeignCurrencySurchargeFactor'] . "')";

		$ErrMsg = _('An error occurred inserting the new online partner record because');
		$DbgMsg = _('The SQL used to insert the online partner record was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		prnMsg(_('The new online partner record has been added'),'success');

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
		unset($_POST['ComissionTokopediaFreeShippingPerItem']);
		unset($_POST['ComissionTokopediaFreeShippingMaximum']);
		unset($_POST['ComissionShopeePercent']);
		unset($_POST['ComissionShopeeFreeShippingPerItem']);
		unset($_POST['ComissionShopeeFreeShippingMaximum']);
		unset($_POST['ComissionLazadaPercent']);
	}

} elseif(isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	$CancelDelete = 0;
	// PREVENT DELETES IF DEPENDENT RECORDS
	$sql= "SELECT COUNT(*) FROM locations WHERE onlinepartnercode='". $SelectedPartner . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this online partner because there are locations related to it.'),'warn');
		echo _('There are') . ' ' . $myrow[0] . ' ' . _('locations using this online partner');
	}
	if(! $CancelDelete) {
		$result = DB_query("DELETE FROM klonlinepartners WHERE onlinepartnercode='" . $SelectedPartner . "'");
		prnMsg(_('Online Partner') . ' ' . $SelectedPartner . ' ' . _('has been deleted') . '!', 'success');
		unset ($SelectedPartner);
	}//end if Delete Online Partner
	unset($SelectedPartner);
	unset($_GET['delete']);
}

if(!isset($SelectedPartner)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPartner will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Locations will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT onlinepartnercode,
				onlinepartnername,
				paypalaccount,
				paypaltest,
				foreigncurrencysurchargefactor
			FROM klonlinepartners
			ORDER BY onlinepartnername";
	$result = DB_query($sql);

	if(DB_num_rows($result)==0) {
		prnMsg(_('There are no online partners'),'error');
	}

	echo '<table class="selection">
		<tr>
			<th class="ascending">', _('Code'), '</th>
			<th class="ascending">', _('Name'), '</th>
			<th class="ascending">', _('PayPal Account'), '</th>
			<th class="ascending">', _('Test?'), '</th>
			<th class="ascending">', _('Foreign Factor'), '</th>
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
	if($myrow['paypaltest'] == 1) {
		$PayPalTest = _('Yes');
	} else {
		$PayPalTest = _('No');
	}

	printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="noprint"><a href="%sSelectedPartner=%s">' . _('Edit') . '</a></td>
			<td class="noprint"><a href="%sSelectedPartner=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this online partner?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['onlinepartnercode'],
			$myrow['onlinepartnername'],
			$myrow['paypalaccount'],
			$PayPalTest,
			locale_number_format($myrow['foreigncurrencysurchargefactor'],2),
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['onlinepartnercode'],
			htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?', $myrow['onlinepartnercode']);
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

		$sql = "SELECT onlinepartnercode,
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
					comissiontokopediafreeshippingperitempercent,
					comissiontokopediafreeshippingperitemmaximum,
					comissionshopeepercent,
					comissionshopeefreeshippingperitempercent,
					comissionshopeefreeshippingperitemmaximum,
					comissionlazadapercent,
					foreigncurrencysurchargefactor
				FROM klonlinepartners
				WHERE onlinepartnercode='" . $SelectedPartner . "'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['OnlinePartnerCode'] = $myrow['onlinepartnercode'];
		$_POST['OnlinePartnerName'] = $myrow['onlinepartnername'];
		$_POST['PayPalAccount'] = $myrow['paypalaccount'];
		$_POST['PayPalTest'] = $myrow['paypaltest'];
		$_POST['PayPalUsername'] = $myrow['paypalusername'];
		$_POST['PayPalPassword'] = $myrow['paypalpassword'];
		$_POST['PayPalSignature'] = $myrow['paypalsignature'];
		$_POST['AccountTransferMandiri'] = $myrow['accounttransfermandiri'];
		$_POST['AccountTransferBCA'] = $myrow['accounttransferbca'];
		$_POST['AccountTransferDanamon'] = $myrow['accounttransferdanamon'];
		$_POST['AccountDokuIDR'] = $myrow['accountdokuidr'];
		$_POST['AccountDokuComissionIDR'] = $myrow['accountdokucomissionidr'];
		$_POST['ComissionFlatDoku'] = $myrow['comissionflatdoku'];
		$_POST['ComissionCCDoku'] = $myrow['comissionccdoku'];
		$_POST['AccountPayPalAUD'] = $myrow['accountpaypalaud'];
		$_POST['AccountPayPalComissionAUD'] = $myrow['accountpaypalcomissionaud'];
		$_POST['AccountPayPalUSD'] = $myrow['accountpaypalusd'];
		$_POST['AccountPayPalComissionUSD'] = $myrow['accountpaypalcomissionusd'];
		$_POST['AccountPayPalEUR'] = $myrow['accountpaypaleur'];
		$_POST['AccountPayPalComissionEUR'] = $myrow['accountpaypalcomissioneur'];
		$_POST['ForeignCurrencySurchargeFactor'] = $myrow['foreigncurrencysurchargefactor'];
		$_POST['AccountXenditIDR'] = $myrow['accountxenditidr'];
		$_POST['AccountXenditComissionIDR'] = $myrow['accountxenditcomissionidr'];
		$_POST['ComissionXenditFlatTransfer'] = $myrow['comissionxenditflattransfer'];
		$_POST['ComissionXenditFlatCC'] = $myrow['comissionxenditflatcc'];
		$_POST['ComissionXenditPercentCC'] = $myrow['comissionxenditpercentcc'];
		$_POST['AccountMidtransIDR'] = $myrow['accountmidtransidr'];
		$_POST['AccountTokopediaIDR'] = $myrow['accounttokopediaidr'];
		$_POST['AccountTokopediaComissionIDR'] = $myrow['accounttokopediacomissionidr'];
		$_POST['AccountShopeeIDR'] = $myrow['accountshopeeidr'];
		$_POST['AccountShopeeComissionIDR'] = $myrow['accountshopeecomissionidr'];
		$_POST['AccountLazadaIDR'] = $myrow['accountlazadaidr'];
		$_POST['AccountLazadaComissionIDR'] = $myrow['accountlazadacomissionidr'];
		$_POST['AccountComissionPPN'] = $myrow['accountcomissionppn'];
		$_POST['ComissionTokopediaPercent'] = $myrow['comissiontokopediapercent'];
		$_POST['ComissionTokopediaFreeShippingPerItem'] = $myrow['comissiontokopediafreeshippingperitempercent'];
		$_POST['ComissionTokopediaFreeShippingMaximum'] = $myrow['comissiontokopediafreeshippingperitemmaximum'];
		$_POST['ComissionShopeePercent'] = $myrow['comissionshopeepercent'];
		$_POST['ComissionShopeeFreeShippingPerItem'] = $myrow['comissionshopeefreeshippingperitempercent'];
		$_POST['ComissionShopeeFreeShippingMaximum'] = $myrow['comissionshopeefreeshippingperitemmaximum'];
		$_POST['ComissionLazadaPercent'] = $myrow['comissionlazadapercent'];

		echo '<input type="hidden" name="SelectedPartner" value="' . $SelectedPartner . '" />';
		echo '<input type="hidden" name="OnlinePartnerCode" value="' . $_POST['OnlinePartnerCode'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<th colspan="2">' . _('Amend Online Partner details') . '</th>
			</tr>';
		echo '<tr>
				<td>' . _('Online Partner Code') . ':</td>
				<td>' . $_POST['OnlinePartnerCode'] . '</td>
			</tr>';
	} else {//end of if $SelectedPartner only do the else when a new record is being entered
		if(!isset($_POST['OnlinePartnerCode'])) {
			$_POST['OnlinePartnerCode'] = '';
		}
		echo '<table class="selection">
				<tr>
					<th colspan="2"><h3>' . _('New Online Partner details') . '</h3></th>
				</tr>';
		echo '<tr>
				<td>' . _('Partner Code') . ':</td>
				<td><input type="text" autofocus="autofocus" required="required" title="' . _('Enter up to 10 characters for the online partner code') . '" data-type="no-illegal-chars" name="OnlinePartnerCode" value="' . $_POST['OnlinePartnerCode'] . '" size="11" maxlength="10" /></td>
			</tr>';
	}
	if(!isset($_POST['OnlinePartnerName'])) {
		$_POST['OnlinePartnerName'] = '';
	}
	if(!isset($_POST['PayPalAccount'])) {
		$_POST['PayPalAccount'] = ' ';
	}
	if(!isset($_POST['PayPalTest'])) {
		$_POST['PayPalTest'] = 1;
	}
	if(!isset($_POST['PayPalUsername'])) {
		$_POST['PayPalUsername'] = '';
	}
	if(!isset($_POST['PayPalPassword'])) {
		$_POST['PayPalPassword'] = '';
	}
	if(!isset($_POST['PayPalSignature'])) {
		$_POST['PayPalSignature'] = '';
	}
	if(!isset($_POST['AccountTransferMandiri'])) {
		$_POST['AccountTransferMandiri'] = '';
	}
	if(!isset($_POST['AccountDokuIDR'])) {
		$_POST['AccountDokuIDR'] = '';
	}
	if(!isset($_POST['AccountDokuComissionIDR'])) {
		$_POST['AccountDokuComissionIDR'] = '';
	}
	if(!isset($_POST['ComissionFlatDoku'])) {
		$_POST['ComissionFlatDoku'] = 0;
	}
	if(!isset($_POST['ComissionCCDoku'])) {
		$_POST['ComissionCCDoku'] = 0;
	}
	if(!isset($_POST['AccountPayPalAUD'])) {
		$_POST['AccountPayPalAUD'] = '';
	}
	if(!isset($_POST['AccountPayPalComissionAUD'])) {
		$_POST['AccountPayPalComissionAUD'] = '';
	}
	if(!isset($_POST['AccountPayPalUSD'])) {
		$_POST['AccountPayPalUSD'] = '';
	}
	if(!isset($_POST['AccountPayPalComissionUSD'])) {
		$_POST['AccountPayPalComissionUSD'] = '';
	}
	if(!isset($_POST['AccountPayPalEUR'])) {
		$_POST['AccountPayPalEUR'] = '';
	}
	if(!isset($_POST['AccountPayPalComissionEUR'])) {
		$_POST['AccountPayPalComissionEUR'] = '';
	}
	if(!isset($_POST['ForeignCurrencySurchargeFactor'])) {
		$_POST['ForeignCurrencySurchargeFactor'] = 0;
	}
	if(!isset($_POST['AccountXenditIDR'])) {
		$_POST['AccountXenditIDR'] = '';
	}
	if(!isset($_POST['AccountXenditComissionIDR'])) {
		$_POST['AccountXenditComissionIDR'] = '';
	}
	if(!isset($_POST['ComissionXenditFlatTransfer'])) {
		$_POST['ComissionXenditFlatTransfer'] = 0;
	}
	if(!isset($_POST['ComissionXenditFlatCC'])) {
		$_POST['ComissionXenditFlatCC'] = 0;
	}
	if(!isset($_POST['ComissionXenditPercentCC'])) {
		$_POST['ComissionXenditPercentCC'] = 0;
	}
	if(!isset($_POST['AccountXenditIDR'])) {
		$_POST['AccountXenditIDR'] = '';
	}
	if(!isset($_POST['AccountXenditComissionIDR'])) {
		$_POST['AccountXenditComissionIDR'] = '';
	}
	if(!isset($_POST['AccountMidtransIDR'])) {
		$_POST['AccountMidtransIDR'] = '';
	}
	if(!isset($_POST['AccountTokopediaIDR'])) {
		$_POST['AccountTokopediaIDR'] = '';
	}
	if(!isset($_POST['AccountTokopediaComissionIDR'])) {
		$_POST['AccountTokopediaComissionIDR'] = '';
	}
	if(!isset($_POST['AccountShopeeIDR'])) {
		$_POST['AccountShopeeIDR'] = '';
	}
	if(!isset($_POST['AccountShopeeComissionIDR'])) {
		$_POST['AccountShopeeComissionIDR'] = '';
	}
	if(!isset($_POST['AccountLazadaIDR'])) {
		$_POST['AccountLazadaIDR'] = '';
	}
	if(!isset($_POST['AccountLazadaComissionIDR'])) {
		$_POST['AccountLazadaComissionIDR'] = '';
	}
	if(!isset($_POST['AccountComissionPPN'])) {
		$_POST['AccountComissionPPN'] = 0;
	}
	if(!isset($_POST['ComissionTokopediaPercent'])) {
		$_POST['ComissionTokopediaPercent'] = 0;
	}
	if(!isset($_POST['ComissionTokopediaFreeShippingPerItem'])) {
		$_POST['ComissionTokopediaFreeShippingPerItem'] = 0;
	}
	if(!isset($_POST['ComissionTokopediaFreeShippingMaximum'])) {
		$_POST['ComissionTokopediaFreeShippingMaximum'] = 0;
	}
	if(!isset($_POST['ComissionShopeePercent'])) {
		$_POST['ComissionShopeePercent'] = 0;
	}
	if(!isset($_POST['ComissionShopeeFreeShippingPerItem'])) {
		$_POST['ComissionShopeeFreeShippingPerItem'] = 0;
	}
	if(!isset($_POST['ComissionShopeeFreeShippingMaximum'])) {
		$_POST['ComissionShopeeFreeShippingMaximum'] = 0;
	}
	if(!isset($_POST['ComissionLazadaPercent'])) {
		$_POST['ComissionLazadaPercent'] = 0;
	}

	echo '<tr>
			<td>' . _('Partner Name') . ':' . '</td>
			<td><input type="text" name="OnlinePartnerName" required="required" value="'. $_POST['OnlinePartnerName'] . '" title="' . _('Enter the online partner name') . '" namesize="51" maxlength="50" /></td>
		</tr>
		<tr>';

	echo '<tr>
			<td>' . _('Foreign Currency Surcharge Factor') . ':</td>
			<td><input type="text" name="ForeignCurrencySurchargeFactor" class="number"  value="' . $_POST['ForeignCurrencySurchargeFactor'] . '" size="5" maxlength="5" /></td>
		</tr>';
	
	echo '<tr>
		<td>' . _('PPN Paid in Commissions GL Account') . ':' . '</td>
		<td><select name="AccountComissionPPN">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountComissionPPN']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<th colspan="2">' . 'Direct Bank Transfer Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . _('Mandiri Bank Transfer IDR GL Account') . ':' . '</td>
		<td><select name="AccountTransferMandiri">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountTransferMandiri']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . _('BCA Bank Transfer IDR GL Account') . ':' . '</td>
		<td><select name="AccountTransferBCA">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountTransferBCA']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . _('Danamon Bank Transfer IDR GL Account') . ':' . '</td>
		<td><select name="AccountTransferDanamon">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountTransferDanamon']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<th colspan="2">' . 'PayPal Details' . '</th>
		</tr>';

	echo '<td>' . _('PayPal Account') . ':' . '</td>
			<td><input type="text" name="PayPalAccount" value="' . $_POST['PayPalAccount'] . '" size="51" maxlength="50" /></td>
		</tr>';
	echo '<tr>
			<td>' . _('Test Account?') . ':</td>
			<td><select name="PayPalTest">';
	if($_POST['PayPalTest']==1) {
		echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	} else {
		echo '<option value="1">' . _('Yes') . '</option>';
	}
	if($_POST['PayPalTest']==0) {
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
	} else {
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td></tr>';
	echo '<tr>
			<td>' . _('PayPal Username') . ':' . '</td>
			<td><input type="text" name="PayPalUsername" value="' . $_POST['PayPalUsername'] . '" size="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('PayPal Password') . ':' . '</td>
			<td><input type="text" name="PayPalPassword" value="' . $_POST['PayPalPassword'] . '" size="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('PayPal Signature') . ':' . '</td>
			<td><input type="text" name="PayPalSignature" value="' . $_POST['PayPalSignature'] . '" size="51" maxlength="100" /></td>
		</tr>';

	echo '<tr>
		<td>' . _('PayPal AUD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalAUD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalAUD']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . _('Comission PayPal AUD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalComissionAUD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalComissionAUD']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	echo '<tr>
		<td>' . _('PayPal USD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalUSD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalUSD']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . _('Comission PayPal USD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalComissionUSD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalComissionUSD']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . _('PayPal EUR GL Account') . ':' . '</td>
		<td><select name="AccountPayPalEUR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalEUR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . _('Comission PayPal EUR GL Account') . ':' . '</td>
		<td><select name="AccountPayPalComissionEUR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalComissionEUR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<th colspan="2">' . 'MIDTRANS Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . _('Midtrans IDR GL Account') . ':' . '</td>
		<td><select name="AccountMidtransIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountMidtransIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('% Fee Comission Midtrans') . ':</td>
			<td>' . _('MidTrans has commissions but we cannot integrate them. We account full order, later manually we process commissions') . ':</td>
		</tr>';


	echo '<tr>
			<th colspan="2">' . 'TOKOPEDIA Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . _('Tokopedia IDR GL Account') . ':' . '</td>
		<td><select name="AccountTokopediaIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountTokopediaIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . _('Comission Tokopedia IDR GL Account') . ':' . '</td>
		<td><select name="AccountTokopediaComissionIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountTokopediaComissionIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	echo '<tr>
			<td>' . _('% Fee Comission Tokopedia') . ':</td>
			<td><input type="text" name="ComissionTokopediaPercent" class="number"  value="' . $_POST['ComissionTokopediaPercent'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% Fee Comission Tokopedia When Free Shipping per Item') . ':</td>
			<td><input type="text" name="ComissionTokopediaFreeShippingPerItem" class="number"  value="' . $_POST['ComissionTokopediaFreeShippingPerItem'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% Fee Comission Tokopedia When Free Shipping Maximum') . ':</td>
			<td><input type="text" name="ComissionTokopediaFreeShippingMaximum" class="number"  value="' . $_POST['ComissionTokopediaFreeShippingMaximum'] . '" size="10" maxlength="10" /></td>
		</tr>';

	echo '<tr>
			<th colspan="2">' . 'SHOPEE Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . _('Shopee IDR GL Account') . ':' . '</td>
		<td><select name="AccountShopeeIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountShopeeIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . _('Comission Shopee IDR GL Account') . ':' . '</td>
		<td><select name="AccountShopeeComissionIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountShopeeComissionIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';	

	echo '<tr>
			<td>' . _('% Fee Comission Shopee') . ':</td>
			<td><input type="text" name="ComissionShopeePercent" class="number"  value="' . $_POST['ComissionShopeePercent'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% Fee Comission Shopee When Free Shipping per Item') . ':</td>
			<td><input type="text" name="ComissionShopeeFreeShippingPerItem" class="number"  value="' . $_POST['ComissionShopeeFreeShippingPerItem'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% Fee Comission Shopee When Free Shipping Maximum') . ':</td>
			<td><input type="text" name="ComissionShopeeFreeShippingMaximum" class="number"  value="' . $_POST['ComissionShopeeFreeShippingMaximum'] . '" size="10" maxlength="10" /></td>
		</tr>';

	echo '<tr>
			<th colspan="2">' . 'LAZADA Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . _('Lazada IDR GL Account') . ':' . '</td>
		<td><select name="AccountLazadaIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountLazadaIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . _('Comission Lazada IDR GL Account') . ':' . '</td>
		<td><select name="AccountLazadaComissionIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountLazadaComissionIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('% Fee Comission Lazada') . ':</td>
			<td><input type="text" name="ComissionLazadaPercent" class="number"  value="' . $_POST['ComissionLazadaPercent'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<th colspan="2">' . 'DOKU Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . _('Doku IDR GL Account') . ':' . '</td>
		<td><select name="AccountDokuIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountDokuIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . _('Comission Doku IDR GL Account') . ':' . '</td>
		<td><select name="AccountDokuComissionIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountDokuComissionIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Flat Fee Commission DOKU for all tx (in IDR)') . ':</td>
			<td><input type="text" name="ComissionFlatDoku" class="number"  value="' . $_POST['ComissionFlatDoku'] . '" size="20" maxlength="20" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% Fee Commission DOKU for CC only (%))') . ':</td>
			<td><input type="text" name="ComissionCCDoku" class="number"  value="' . $_POST['ComissionCCDoku'] . '" size="5" maxlength="5" /></td>
		</tr>';

	echo '<tr>
			<th colspan="2">' . 'XENDIT Details' . '</th>
		</tr>';

	echo '<tr>
		<td>' . _('Xendit IDR GL Account') . ':' . '</td>
		<td><select name="AccountXenditIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountXenditIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . _('Comission Xendit IDR GL Account') . ':' . '</td>
		<td><select name="AccountXenditComissionIDR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountXenditComissionIDR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] .  '">' . $myrow['accountcode'] . ' - ' . htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false) . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Flat Fee Commission Xendit for Bank Transfers (in IDR)') . ':</td>
			<td><input type="text" name="ComissionXenditFlatTransfer" class="number"  value="' . $_POST['ComissionXenditFlatTransfer'] . '" size="20" maxlength="20" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('Flat Fee Commission Xendit for CC (in IDR)') . ':</td>
			<td><input type="text" name="ComissionXenditFlatCC" class="number"  value="' . $_POST['ComissionXenditFlatCC'] . '" size="20" maxlength="20" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('% Fee Commission Xendit for CC only (%))') . ':</td>
			<td><input type="text" name="ComissionXenditPercentCC" class="number"  value="' . $_POST['ComissionXenditPercentCC'] . '" size="5" maxlength="5" /></td>
		</tr>';

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
