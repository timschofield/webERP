<?php

if ($_GET['InvOrCredit']=='Invoice'){
	$TransactionType = "Invoice";
	$TypeCode = 10;
} else {
	$TransactionType = "Credit Note";
	$TypeCode =11;
}

$title="Email $TransactionType Number " . $_GET['FromTransNo'];

$PageSecurity = 2;

include ("includes/session.inc");


if ($_POST['DoIt']=="OK" AND strlen($_POST['EmailAddr'])>3){
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/PrintCustTrans.php?" . SID . "FromTransNo=" . $_POST['TransNo'] . "&PrintPDF=Yes&InvOrCredit=" . $_POST['InvOrCredit'] ."&Email=" . $_POST['EmailAddr'] . "'>";
	echo "<P>The transaction should be emailed off. If this does not happen (if the browser doesn't support META Refresh) <a href='" . $rootpath . "/PrintCustTrans.php?" . SID . "FromTransNo=" . $_POST['FromTransNo'] . "&PrintPDF=Yes&InvOrCredit=" . $_POST['InvOrCredit'] ."&Email=" . $_POST['EmailAddr'] . "'>click here</a> to email the customer tranaction.<br>";
	exit;
} elseif ($_POST['DoIt']=="OK") {
	$_GET['InvOrCredit'] = $_POST['InvOrCredit'];
	$_GET['FromTransNo'] = $_POST['FromTransNo'];
	$ErrorMessage = "<BR>The email address entered is too short to be a valid email address. The transaction was not emailed.";
}

include ("includes/header.inc");


echo $ErrorMessage;

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN NAME='TransNo' VALUE=" . $_GET['FromTransNo'] . ">";
echo "<INPUT TYPE=HIDDEN NAME='InvOrCredit' VALUE=" . $_GET['InvOrCredit'] . ">";

echo "<CENTER><TABLE>";


$SQL = "SELECT Email FROM CustBranch INNER JOIN DebtorTrans ON CustBranch.DebtorNo= DebtorTrans.DebtorNo AND CustBranch.BranchCode=DebtorTrans.BranchCode WHERE DebtorTrans.Type=$TypeCode AND DebtorTrans.TransNo=" .$_GET['FromTransNo'];

$ContactResult=DB_query($SQL,$db);
if (DB_error_no($db)!=0) {
	echo "<BR>There was a problem retrieving the contact details for the customer.";
	if ($debug==1){
		echo "<BR>The SQL used to get this information (that failed) was:<BR>$SQL";
	}
	exit;
}
if (DB_num_rows($ContactResult)>0){
	$EmailAddrRow = DB_fetch_row($ContactResult);
	$EmailAddress = $EmailAddrRow[0];
} else {
	$EmailAddress ="";
}

echo "<TR><TD>Email to:</TD><TD><INPUT TYPE=TEXT NAME='EmailAddr' MAXLENGTH=60 SIZE=60 VALUE='" . $EmailAddress . "'</TD></TABLE>";

echo "<BR><INPUT TYPE=SUBMIT NAME='DoIt' VALUE='OK'>";
echo "</CENTER></FORM>";
include ("includes/footer.inc");
?>
