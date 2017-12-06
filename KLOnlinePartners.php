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
					paypalusername ='" . $_POST['PayPalUsername'] . "',
					paypalpassword ='" . $_POST['PayPalPassword'] . "',
					paypalsignature ='" . $_POST['PayPalSignature'] . "',
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
		unset($_POST['PayPalUsername']);
		unset($_POST['PayPalPassword']);
		unset($_POST['PayPalSignature']);
		unset($_POST['AccountPayPalAUD']);
		unset($_POST['AccountPayPalComissionAUD']);
		unset($_POST['AccountPayPalUSD']);
		unset($_POST['AccountPayPalComissionUSD']);
		unset($_POST['AccountPayPalEUR']);
		unset($_POST['AccountPayPalComissionEUR']);
		unset($_POST['ForeignCurrencySurchargeFactor']);

	} elseif($InputError !=1) {

		/*SelectedPartner is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Location form */

		$sql = "INSERT INTO klonlinepartners 
								(onlinepartnercode,
								onlinepartnername,
								paypalaccount,
								paypalusername,
								paypalpassword,
								paypalsignature,
								accountpaypalaud,
								accountpaypalcomissionaud,
								accountpaypalusd,
								accountpaypalcomissionusd,
								accountpaypaleur,
								accountpaypalcomissioneur,
								foreigncurrencysurchargefactor)
						VALUES ('" . $_POST['OnlinePartnerCode'] . "',
								'" . $_POST['OnlinePartnerName'] . "',
								'" . $_POST['PayPalAccount'] ."',
								'" . $_POST['PayPalUsername'] ."',
								'" . $_POST['PayPalPassword'] . "',
								'" . $_POST['PayPalSignature'] . "',
								'" . $_POST['AccountPayPalAUD'] . "',
								'" . $_POST['AccountPayPalComissionAUD'] . "',
								'" . $_POST['AccountPayPalUSD'] . "',
								'" . $_POST['AccountPayPalComissionUSD'] . "',
								'" . $_POST['AccountPayPalEUR'] . "',
								'" . $_POST['AccountPayPalComissionEUR'] . "',
								'" . $_POST['ForeignCurrencySurchargeFactor'] . "')";

		$ErrMsg = _('An error occurred inserting the new online partner record because');
		$DbgMsg = _('The SQL used to insert the online partner record was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		prnMsg(_('The new online partner record has been added'),'success');

		unset($SelectedPartner);
		unset($_POST['OnlinePartnerCode']);
		unset($_POST['OnlinePartnerName']);
		unset($_POST['PayPalAccount']);
		unset($_POST['PayPalUsername']);
		unset($_POST['PayPalPassword']);
		unset($_POST['PayPalSignature']);
		unset($_POST['AccountPayPalAUD']);
		unset($_POST['AccountPayPalComissionAUD']);
		unset($_POST['AccountPayPalUSD']);
		unset($_POST['AccountPayPalComissionUSD']);
		unset($_POST['AccountPayPalEUR']);
		unset($_POST['AccountPayPalComissionEUR']);
		unset($_POST['ForeignCurrencySurchargeFactor']);
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

	printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td class="number">%s</td>
			<td class="noprint"><a href="%sSelectedPartner=%s">' . _('Edit') . '</a></td>
			<td class="noprint"><a href="%sSelectedPartner=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this online partner?') . '\');">' . _('Delete') . '</a></td>
			</tr>',
			$myrow['onlinepartnercode'],
			$myrow['onlinepartnername'],
			$myrow['paypalaccount'],
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
					paypalusername,
					paypalpassword,
					paypalsignature,
					accountpaypalaud,
					accountpaypalcomissionaud,
					accountpaypalusd,
					accountpaypalcomissionusd,
					accountpaypaleur,
					accountpaypalcomissioneur,
					foreigncurrencysurchargefactor
				FROM klonlinepartners
				WHERE onlinepartnercode='" . $SelectedPartner . "'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['OnlinePartnerCode'] = $myrow['onlinepartnercode'];
		$_POST['OnlinePartnerName'] = $myrow['onlinepartnername'];
		$_POST['PayPalAccount'] = $myrow['paypalaccount'];
		$_POST['PayPalUsername'] = $myrow['paypalusername'];
		$_POST['PayPalPassword'] = $myrow['paypalpassword'];
		$_POST['PayPalSignature'] = $myrow['paypalsignature'];
		$_POST['AccountPayPalAUD'] = $myrow['accountpaypalaud'];
		$_POST['AccountPayPalComissionAUD'] = $myrow['accountpaypalcomissionaud'];
		$_POST['AccountPayPalUSD'] = $myrow['accountpaypalusd'];
		$_POST['AccountPayPalComissionUSD'] = $myrow['accountpaypalcomissionusd'];
		$_POST['AccountPayPalEUR'] = $myrow['accountpaypaleur'];
		$_POST['AccountPayPalComissionEUR'] = $myrow['accountpaypalcomissioneur'];
		$_POST['ForeignCurrencySurchargeFactor'] = $myrow['foreigncurrencysurchargefactor'];

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
	if(!isset($_POST['PayPalUsername'])) {
		$_POST['PayPalUsername'] = '';
	}
	if(!isset($_POST['PayPalPassword'])) {
		$_POST['PayPalPassword'] = '';
	}
	if(!isset($_POST['PayPalSignature'])) {
		$_POST['PayPalSignature'] = '';
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

	echo '<tr>
			<td>' . _('Partner Name') . ':' . '</td>
			<td><input type="text" name="OnlinePartnerName" required="required" value="'. $_POST['OnlinePartnerName'] . '" title="' . _('Enter the online partner name') . '" namesize="51" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('PayPal Account') . ':' . '</td>
			<td><input type="text" name="PayPalAccount" value="' . $_POST['PayPalAccount'] . '" size="51" maxlength="50" /></td>
		</tr>
		<tr>
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
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalAUD']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . _('Comission PayPal AUD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalComissionAUD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalComissionAUD']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	
	echo '<tr>
		<td>' . _('PayPal USD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalUSD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalUSD']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . _('Comission PayPal USD GL Account') . ':' . '</td>
		<td><select name="AccountPayPalComissionUSD">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalComissionUSD']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
		<td>' . _('PayPal EUR GL Account') . ':' . '</td>
		<td><select name="AccountPayPalEUR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalEUR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
		<td>' . _('Comission PayPal EUR GL Account') . ':' . '</td>
		<td><select name="AccountPayPalComissionEUR">';
	$GLAccount = DB_query("SELECT accountcode, accountname FROM chartmaster ORDER BY accountname");
	while ($myrow=DB_fetch_array($GLAccount)) {
		if($_POST['AccountPayPalComissionEUR']==$myrow['accountcode']) {
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountname'] . '</option>';
		}
	}
	echo '</select></td></tr>';

	echo '<tr>
			<td>' . _('Foreign Currency Surcharge Factor') . ':</td>
			<td><input type="text" name="ForeignCurrencySurchargeFactor" class="number"  value="' . $_POST['ForeignCurrencySurchargeFactor'] . '" size="5" maxlength="5" /></td>
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
