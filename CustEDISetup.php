<?php

/* $Id: CustEDISetup.php 6310 2013-08-29 10:42:50Z daintree $*/

include('includes/session.inc');
$Title = _('Customer EDI Set Up');
include('includes/header.inc');

echo '<a href="' . $RootPath . '/SelectCustomer.php">' . _('Back to Customers') . '</a><br />';

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();
$i=0;
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (ContainsIllegalCharacters($_POST['EDIReference'])
		OR mb_strstr($_POST['EDIReference'],' ')) {
		$InputError = 1;
		prnMsg(_('The customers EDI reference code cannot contain any of the following characters') .' - \' &amp; + \" ' . _('or a space'),'warn');
	}
	if (mb_strlen($_POST['EDIReference'])<4 AND ($_POST['EDIInvoices']==1 OR $_POST['EDIOrders']==1)){
		$InputError = 1;
		prnMsg(_('The customers EDI reference code must be set when EDI Invoices or EDI orders are activated'),'warn');
		$Errors[$i] = 'EDIReference';
		$i++;
	}
	if (mb_strlen($_POST['EDIAddress'])<4 AND $_POST['EDIInvoices']==1){
		$InputError = 1;
		prnMsg(_('The customers EDI email address or FTP server address must be entered if EDI Invoices are to be sent'),'warn');
		$Errors[$i] = 'EDIAddress';
		$i++;
	}


	If ($InputError==0){ //ie no input errors

		if (!isset($_POST['EDIServerUser'])){
			$_POST['EDIServerUser']='';
		}
		if (!isset($_POST['EDIServerPwd'])){
			$_POST['EDIServerPwd']='';
		}
		$sql = "UPDATE debtorsmaster SET ediinvoices ='" . $_POST['EDIInvoices'] . "',
					ediorders ='" . $_POST['EDIOrders'] . "',
					edireference='" . $_POST['EDIReference'] . "',
					editransport='" . $_POST['EDITransport'] . "',
					ediaddress='" . $_POST['EDIAddress'] . "',
					ediserveruser='" . $_POST['EDIServerUser'] . "',
					ediserverpwd='" . $_POST['EDIServerPwd'] . "'
			WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";

		$ErrMsg = _('The customer EDI setup data could not be updated because');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('Customer EDI configuration updated'),'success');
	} else {
		prnMsg(_('Customer EDI configuration failed'),'error');
	}
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br /><table class="selection">';

$sql = "SELECT debtorno,
		name,
		ediinvoices,
		ediorders,
		edireference,
		editransport,
		ediaddress,
		ediserveruser,
		ediserverpwd
	FROM debtorsmaster
	WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";

$ErrMsg = _('The customer EDI configuration details could not be retrieved because');
$result = DB_query($sql, $db,$ErrMsg);

$myrow = DB_fetch_array($result);

echo '<tr><td>' . _('Customer Code').':</td>
		<td>' . $_SESSION['CustomerID'] . '</td>
		</tr>';
echo '<tr><td>' . _('Customer Name').':</td>
		<td>' . $myrow['name'] . '</td>
		</tr>';
echo '<tr><td>' . _('Enable Sending of EDI Invoices').':</td>
		<td><select tabindex="1" name="EDIInvoices">';

if ($myrow['ediinvoices']==0){

	echo '<option selected="selected" value="0">' . _('Disabled') . '</option>';
	echo '<option value="1">' . _('Enabled'). '</option>';
} else {
	echo '<option value="0">' . _('Disabled') . '</option>';
	echo '<option selected="selected" value="1">' . _('Enabled') . '</option>';
}

echo '</select><a href="' . $RootPath . '/EDIMessageFormat.php?MessageType=INVOIC&amp;PartnerCode=' . $_SESSION['CustomerID'] . '">' . _('Create') . '/' . _('Edit Invoice Message Format') . '</a></td>
	</tr>';

echo '<tr><td>' . _('Enable Receiving of EDI Orders') . ':</td>
	<td><select tabindex="2" name="EDIOrders">';

if ($myrow['ediorders']==0){

	echo '<option selected="selected" value="0">' . _('Disabled') . '</option>';
	echo '<option value="1">' . _('Enabled') . '</option>';
} else {
	echo '<option value="0">' . _('Disabled') . '</option>';
	echo '<option selected="selected" value="1">' . _('Enabled') . '</option>';
}

echo '</select></td>
	</tr>';

echo '<tr><td>' . _('Customer EDI Reference') . ':</td>
	<td><input ' . (in_array('EDIReference',$Errors) ?  'class="inputerror"' : '' ) .
        ' tabindex="3" type="text" name="EDIReference" size="20" maxlength="20" value="' . $myrow['edireference'] . '" /></td></tr>';

echo '<tr><td>' . _('EDI Communication Method') . ':</td>
	<td><select tabindex="4" name="EDITransport" >';

if ($myrow['editransport']=='email'){
	echo '<option selected="selected" value="email">' . _('Email Attachments') . '</option>';
	echo '<option value="ftp">' . _('File Transfer Protocol (FTP)') . '</option>';
} else {
	echo '<option value="email">' . _('Email Attachments') . '</option>';
	echo '<option selected="selected" value="ftp">' . _('File Transfer Protocol (FTP)') . '</option>';
}

echo '</select></td></tr>';

echo '<tr><td>' . _('FTP Server or Email Address') . ':</td>
	<td><input ' . (in_array('EDIAddress',$Errors) ?  'class="inputerror"' : '' ) .
        ' tabindex="5" type="text" name="EDIAddress" required="required" size="42" maxlength="40" value="' . $myrow['ediaddress'] . '" /></td></tr>';

if ($myrow['editransport']=='ftp'){

	echo '<tr><td>' . _('FTP Server User Name') . ':</td>
			<td><input tabindex="6" type="text" name="EDIServerUser" required="required" size="20" maxlength="20" value="' . $myrow['ediserveruser'] . '" /></td></tr>';
	echo '<tr><td>' . _('FTP Server Password') . ':</td>
			<td><input tabindex="7" type="text" name="EDIServerPwd" required="required" size="20" maxlength="20" value="' . $myrow['ediserverpwd'] . '" /></td></tr>';
}

echo '</table>
		<br /><div class="centre"><input tabindex="8" type="submit" name="submit" value="' ._('Update EDI Configuration'). '" /></div>
    </div>
	</form>';

include('includes/footer.inc');
?>