<?php
/* $Revision: 1.11 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Customer EDI Set Up');
include('includes/header.inc');

echo "<a href='" . $rootpath . '/SelectCustomer.php?' . SID . "'>" . _('Back to Customers') . '</a><br>';

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();	
$i=0;
if (isset($_POST['submit'])) {

    //initialise no input errors assumed initially before we test
    $InputError = 0;

    /* actions to take once the user has clicked the submit button
    ie the page has called itself with some user input */

    //first off validate inputs sensible

    if (strstr($_POST['EDIReference'],"'")
    		OR strstr($_POST['EDIReference'],'+')
		OR strstr($_POST['EDIReference'],"\"")
		OR strstr($_POST['EDIReference'],'&')
		OR strstr($_POST['EDIReference'],' ')) {
        $InputError = 1;
        prnMsg(_('The customers EDI reference code cannot contain any of the following characters') .' - \' & + \" ' . _('or a space'),'warn');
    }
    if (strlen($_POST['EDIReference'])<4 AND ($_POST['EDIInvoices']==1 OR $_POST['EDIOrders']==1)){
        $InputError = 1;
        prnMsg(_('The customers EDI reference code must be set when EDI Invoices or EDI orders are activated'),'warn');
		$Errors[$i] = 'EDIReference';
		$i++;		
    }
    if (strlen($_POST['EDIAddress'])<4 AND $_POST['EDIInvoices']==1){
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
        $sql = 'UPDATE debtorsmaster SET ediinvoices =' . $_POST['EDIInvoices'] . ',
					ediorders =' . $_POST['EDIOrders'] . ",
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

echo "<form method='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID ."'>";
echo '<table>';

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

echo '<tr><td>'._('Customer Code').':</td>
	<td>' . $_SESSION['CustomerID'] . '</td></tr>';
echo '<tr><td>'._('Customer Name').':</td>
	<td>' . $myrow['name'] . '</td></tr>';
echo '<tr><td>'._('Enable Sending of EDI Invoices').':</td>
	<td><select TABINDEX=1 name="EDIInvoices">';

if ($myrow['ediinvoices']==0){

    echo '<option selected VALUE=0>'._('Disabled');
    echo '<option VALUE=1>'._('Enabled');
} else {
    echo '<option VALUE=0>'._('Disabled');
    echo '<option selected VALUE=1>'._('Enabled');
}

echo "</select><a href='$rootpath/EDIMessageFormat.php?" . SID . "&MessageType=INVOIC&PartnerCode=" . $_SESSION['CustomerID'] . "'>"._('Create') . '/' . _('Edit Invoice Message Format').'</a></td></tr>';

echo '<tr><td>'._('Enable Receiving of EDI Orders').":</td>
	<td><select TABINDEX=2 name='EDIOrders'>";

if ($myrow['ediorders']==0){

    echo '<option selected VALUE=0>'._('Disabled');
    echo '<option VALUE=1>'._('Enabled');
} else {
    echo '<option VALUE=0>'._('Disabled');
    echo '<option selected VALUE=1>'._('Enabled');
}

echo '</select></td></tr>';

echo '<tr><td>'._('Customer EDI Reference').":</td>
	<td><input " . (in_array('EDIReference',$Errors) ?  'class="inputerror"' : '' ) .
		" TABINDEX=3 type='Text' name='EDIReference' size=20 maxlength=20 value='" . $myrow['edireference'] . "'></td></tr>";

echo '<tr><td>'._('EDI Communication Method').":</td>
	<td><select TABINDEX=4 name='EDITransport'>";

if ($myrow['editransport']=='email'){
    echo "<option selected VALUE='email'>"._('Email Attachments');
    echo "<option VALUE='ftp'>"._('File Transfer Protocol (FTP)');
} else {
    echo "<option VALUE='email'>"._('Email Attachments');
    echo "<option selected VALUE='ftp'>"._('File Transfer Protocol (FTP)');
}

echo '</select></td></tr>';

echo '<tr><td>'._('FTP Server or Email Address').":</td>
	<td><input " . (in_array('EDIAddress',$Errors) ?  'class="inputerror"' : '' ) .
		" TABINDEX=5 type='Text' name='EDIAddress' size=42 maxlength=40 value='" . $myrow['ediaddress'] . "'></td></tr>";

if ($myrow['editransport']=='ftp'){

    echo '<tr><td>'._('FTP Server User Name').":</td>
    		<td><input TABINDEX=6 type='Text' name='EDIServerUser' size=20 maxlength=20 value=" . $myrow['ediserveruser'] . "></td></tr>";
    echo '<tr><td>'._('FTP Server Password').":</td>
    		<td><input TABINDEX=7 type='Text' name='EDIServerPwd' size=20 maxlength=20 value='" . $myrow['ediserverpwd'] . "'></td></tr>";
}

echo "</table><div class='centre'><input TABINDEX=8 type='Submit' name='submit' value='"._('Update EDI Configuration')."'></div></form>";

include('includes/footer.inc');
?>
