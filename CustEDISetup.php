<?php
/* $Revision: 1.2 $ */
$title = "Customer EDI Set Up";

$PageSecurity = 11;

include("includes/session.inc");
include("includes/header.inc");


if ($_POST['submit']=='Update EDI Configuration') {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strstr($_POST['EDIReference'],"'") OR strstr($_POST['EDIReference'],"+") OR strstr($_POST['EDIReference'],"\"") OR strstr($_POST['EDIReference'],"&") OR strstr($_POST['EDIReference']," ")) {
		$InputError = 1;
		echo "<BR>The customers EDI reference code cannot contain any of the following characters - ' & + \" or a space";
	}
	if (strlen($_POST['EDIReference'])<4 AND ($_POST['EDIInvoices']==1 OR $_POST['EDIOrders']==1)){
		$InputError = 1;
		echo "<BR>The customers EDI reference code must be set when EDI Invoices or EDI orders are activated.";
	}
	if (strlen($_POST['EDIAddress'])<4 AND $_POST['EDIInvoices']==1){
		$InputError = 1;
		echo "<BR>The customers EDI email address or FTP server address must be entered if EDI Invoices are to be sent";
	}
	

	If ($InputError==0){ //ie no input errors

		$sql = "UPDATE DebtorsMaster SET EDIInvoices =" . $_POST['EDIInvoices'] . ", EDIOrders =" . $_POST['EDIOrders'] . ", EDIReference='" . $_POST['EDIReference'] . "', EDITransport='" . $_POST['EDITransport'] . "', EDIAddress='" . $_POST['EDIAddress'] . "', EDIServerUser='" . $_POST['EDIServerUser'] . "', EDIServerPwd='" . $_POST['EDIServerPwd'] . "' WHERE DebtorNo = '" . $_SESSION['CustomerID'] . "'";

		$result = DB_query($sql,$db);
		if (DB_error_no($db) !=0) {
			echo "<BR>The customer EDI setup data could not be updated because - " . DB_error_msg($db);
		} else {
			echo "<BR>Customer EDI configuration updated";
		}


	} else {
		echo "<BR>Customer EDI configuration failed.";
	}
}

echo "<FORM METHOD='post' action='" . $_SERVER['PHP_SELF'] . "?" . SID ."'>";
echo "<CENTER><TABLE>";

$sql = "SELECT DebtorNo, Name, EDIInvoices, EDIOrders, EDIReference, EDITransport, EDIAddress, EDIServerUser, EDIServerPwd FROM DebtorsMaster WHERE DebtorNo = '" . $_SESSION['CustomerID'] . "'";
$result = DB_query($sql, $db);

if (DB_error_no($db) !=0) {
	echo "The customer EDI configuration details could not be retrieved because - " . DB_error_msg($db) . "the SQL that was used - and failed was: <BR>$sql";
	exit;
}

$myrow = DB_fetch_array($result);

echo "<TR><TD>Customer Code:</TD><TD>" . $_SESSION['CustomerID'] . "</TD></TR>";
echo "<TR><TD>Customer Name:</TD><TD>" . $myrow['Name'] . "</TD></TR>";
echo "<TR><TD>Enable Sending of EDI Invoices:</TD><TD><SELECT name='EDIInvoices'>";

if ($myrow['EDIInvoices']==0){

	echo "<OPTION SELECTED VALUE=0>Disabled";
	echo "<OPTION VALUE=1>Enabled";
} else {
	echo "<OPTION VALUE=0>Disabled";
	echo "<OPTION SELECTED VALUE=1>Enabled";
}

echo "</SELECT><A HREF='$rootpath/EDIMessageFormat.php?" . SID . "&MessageType=INVOIC&PartnerCode=" . $_SESSION['CustomerID'] . "'>Create/Edit Invoice Message Format</A></TD></TR>";

echo "<TR><TD>Enable Receiving of EDI Orders:</TD><TD><SELECT name='EDIOrders'>";

if ($myrow['EDIOrders']==0){

	echo "<OPTION SELECTED VALUE=0>Disabled";
	echo "<OPTION VALUE=1>Enabled";
} else {
	echo "<OPTION VALUE=0>Disabled";
	echo "<OPTION SELECTED VALUE=1>Enabled";
}

echo "</SELECT></TD></TR>";

echo "<TR><TD>Customer's EDI Reference:</TD><TD><input type='Text' name='EDIReference' SIZE=20 MAXLENGTH=20 value='" . $myrow['EDIReference'] . "'></TD></TR>";

echo "<TR><TD>EDI Communciation Method:</TD><TD><SELECT name='EDITransport'>";

if ($myrow['EDITransport']=='email'){
	echo "<OPTION SELECTED VALUE='email'>Email Attachments";
	echo "<OPTION VALUE='ftp'>File Transfer Protocol (FTP)";
} else {
	echo "<OPTION VALUE='email'>Email Attachments";
	echo "<OPTION SELECTED VALUE='ftp'>File Transfer Protocol (FTP)";
}

echo "</SELECT></TD></TR>";

echo "<TR><TD>FTP Server/Email Address:</TD><TD><input type='Text' name='EDIAddress' SIZE=42 MAXLENGTH=40 value='" . $myrow['EDIAddress'] . "'></TD></TR>";

if ($myrow['EDITransport']=='ftp'){

	echo "<TR><TD>FTP Server User Name:</TD><TD><input type='Text' name='EDIServerUser' SIZE=20 MAXLENGTH=20 value=" . $myrow['EDIServerUser'] . "></TD></TR>";
	echo "<TR><TD>FTP Server Password:</TD><TD><input type='Text' name='EDIServerPwd' SIZE=20 MAXLENGTH=20 value='" . $myrow['ServerPwd'] . "'></TD></TR>";
}

echo "</TABLE><CENTER><input type='Submit' name='submit' value='Update EDI Configuration'><BR></FORM>";

include("includes/footer.inc");
?>
