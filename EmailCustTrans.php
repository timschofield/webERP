<?php
/* $Revision: 1.3 $ */

$PageSecurity = 2;

include ("includes/session.inc");

if ($_GET['InvOrCredit']=='Invoice'){
	$TransactionType = _('Invoice');
	$TypeCode = 10;
} else {
	$TransactionType = _('Credit Note');
	$TypeCode =11;
}
$title=_('Email') . ' ' . $TransactionType . ' ' . _('Number') . ' ' . $_GET['FromTransNo'];

if (isset($_POST['DoIt']) AND strlen($_POST['EmailAddr'])>3){
	echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . "/PrintCustTrans.php?" . SID . "FromTransNo=" . $_POST['TransNo'] . "&PrintPDF=Yes&InvOrCredit=" . $_POST['InvOrCredit'] ."&Email=" . $_POST['EmailAddr'] . "'>";
	echo '<P>' . _('The transaction should be emailed off. If this does not happen (if the browser does not support META Refresh)') . "<a href='" . $rootpath . "/PrintCustTrans.php?" . SID . "FromTransNo=" . $_POST['FromTransNo'] . "&PrintPDF=Yes&InvOrCredit=" . $_POST['InvOrCredit'] ."&Email=" . $_POST['EmailAddr'] . "'>" . _('click here') . '</a> ' . _('to email the customer tranaction') . '<BR>';
	exit;
} elseif (isset($_POST['DoIt'])) {
	$_GET['InvOrCredit'] = $_POST['InvOrCredit'];
	$_GET['FromTransNo'] = $_POST['FromTransNo'];
	$ErrorMessage = '<BR>' . _('The email address entered is too short to be a valid email address. The transaction was not emailed');
}

include ("includes/header.inc");


echo $ErrorMessage;

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN NAME='TransNo' VALUE=" . $_GET['FromTransNo'] . ">";
echo "<INPUT TYPE=HIDDEN NAME='InvOrCredit' VALUE=" . $_GET['InvOrCredit'] . ">";

echo "<CENTER><TABLE>";


$SQL = "SELECT Email
		FROM CustBranch INNER JOIN DebtorTrans ON CustBranch.DebtorNo= DebtorTrans.DebtorNo AND CustBranch.BranchCode=DebtorTrans.BranchCode
		WHERE DebtorTrans.Type=$TypeCode
		AND DebtorTrans.TransNo=" .$_GET['FromTransNo'];

$ErrMsg = _('There was a problem retrieving the contact details for the customer');
$ContactResult=DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($ContactResult)>0){
	$EmailAddrRow = DB_fetch_row($ContactResult);
	$EmailAddress = $EmailAddrRow[0];
} else {
	$EmailAddress ="";
}

echo '<TR><TD>' . _('Email to') . ":</TD>
	<TD><INPUT TYPE=TEXT NAME='EmailAddr' MAXLENGTH=60 SIZE=60 VALUE='" . $EmailAddress . "'</TD>
	</TABLE>";

echo "<BR><INPUT TYPE=SUBMIT NAME='DoIt' VALUE='" . _('OK') . "'>";
echo '</CENTER></FORM>';
include ('includes/footer.inc');
?>
