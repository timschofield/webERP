<?php
/* $Revision: 1.10 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Customer EDI Set Up');
include('includes/header.inc');

echo "<A HREF='" . $rootpath . '/SelectCustomer.php?' . SID . "'>" . _('Back to Customers') . '</A><BR>';

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

echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . '?' . SID ."'>";
echo '<CENTER><TABLE>';

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

echo '<TR><TD>'._('Customer Code').':</TD>
	<TD>' . $_SESSION['CustomerID'] . '</TD></TR>';
echo '<TR><TD>'._('Customer Name').':</TD>
	<TD>' . $myrow['name'] . '</TD></TR>';
echo '<TR><TD>'._('Enable Sending of EDI Invoices').':</TD>
	<TD><SELECT TABINDEX=1 name="EDIInvoices">';

if ($myrow['ediinvoices']==0){

    echo '<OPTION SELECTED VALUE=0>'._('Disabled');
    echo '<OPTION VALUE=1>'._('Enabled');
} else {
    echo '<OPTION VALUE=0>'._('Disabled');
    echo '<OPTION SELECTED VALUE=1>'._('Enabled');
}

echo "</SELECT><A HREF='$rootpath/EDIMessageFormat.php?" . SID . "&MessageType=INVOIC&PartnerCode=" . $_SESSION['CustomerID'] . "'>"._('Create') . '/' . _('Edit Invoice Message Format').'</A></TD></TR>';

echo '<TR><TD>'._('Enable Receiving of EDI Orders').":</TD>
	<TD><SELECT TABINDEX=2 name='EDIOrders'>";

if ($myrow['ediorders']==0){

    echo '<OPTION SELECTED VALUE=0>'._('Disabled');
    echo '<OPTION VALUE=1>'._('Enabled');
} else {
    echo '<OPTION VALUE=0>'._('Disabled');
    echo '<OPTION SELECTED VALUE=1>'._('Enabled');
}

echo '</SELECT></TD></TR>';

echo '<TR><TD>'._('Customer EDI Reference').":</TD>
	<TD><input " . (in_array('EDIReference',$Errors) ?  'class="inputerror"' : '' ) .
		" TABINDEX=3 type='Text' name='EDIReference' SIZE=20 MAXLENGTH=20 value='" . $myrow['edireference'] . "'></TD></TR>";

echo '<TR><TD>'._('EDI Communication Method').":</TD>
	<TD><SELECT TABINDEX=4 name='EDITransport'>";

if ($myrow['editransport']=='email'){
    echo "<OPTION SELECTED VALUE='email'>"._('Email Attachments');
    echo "<OPTION VALUE='ftp'>"._('File Transfer Protocol (FTP)');
} else {
    echo "<OPTION VALUE='email'>"._('Email Attachments');
    echo "<OPTION SELECTED VALUE='ftp'>"._('File Transfer Protocol (FTP)');
}

echo '</SELECT></TD></TR>';

echo '<TR><TD>'._('FTP Server or Email Address').":</TD>
	<TD><input " . (in_array('EDIAddress',$Errors) ?  'class="inputerror"' : '' ) .
		" TABINDEX=5 type='Text' name='EDIAddress' SIZE=42 MAXLENGTH=40 value='" . $myrow['ediaddress'] . "'></TD></TR>";

if ($myrow['editransport']=='ftp'){

    echo '<TR><TD>'._('FTP Server User Name').":</TD>
    		<TD><input TABINDEX=6 type='Text' name='EDIServerUser' SIZE=20 MAXLENGTH=20 value=" . $myrow['ediserveruser'] . "></TD></TR>";
    echo '<TR><TD>'._('FTP Server Password').":</TD>
    		<TD><input TABINDEX=7 type='Text' name='EDIServerPwd' SIZE=20 MAXLENGTH=20 value='" . $myrow['ediserverpwd'] . "'></TD></TR>";
}

echo "</TABLE><CENTER><input TABINDEX=8 type='Submit' name='submit' value='"._('Update EDI Configuration')."'></FORM>";

include('includes/footer.inc');
?>
