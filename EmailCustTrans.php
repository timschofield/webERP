<?php
/* $Revision: 1.6 $ */

$PageSecurity = 2;

include ('includes/session.inc');

if ($_GET['InvOrCredit']=='Invoice'){
	$TransactionType = _('Invoice');
	$TypeCode = 10;
} else {
	$TransactionType = _('Credit Note');
	$TypeCode =11;
}
$title=_('Email') . ' ' . $TransactionType . ' ' . _('Number') . ' ' . $_GET['FromTransNo'];

if (isset($_POST['DoIt']) AND strlen($_POST['EmailAddr'])>3){

	if ($_SESSION['InvoicePortraitFormat']==0){	
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . '/PrintCustTrans.php?' . SID . '&FromTransNo=' . $_POST['TransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . "'>";

		prnMsg(_('The transaction should have been emailed off') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ')' . "<a href='" . $rootpath . '/PrintCustTrans.php?' . SID . '&FromTransNo=' . $_POST['FromTransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . "'>" . _('click here') . '</a> ' . _('to email the customer tranaction'),'success');
	} else {
		echo "<META HTTP-EQUIV='Refresh' CONTENT='0; URL=" . $rootpath . '/PrintCustTransPortrait.php?' . SID . '&FromTransNo=' . $_POST['TransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . "'>";

		prnMsg(_('The transaction should have been emailed off') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ')' . "<a href='" . $rootpath . '/PrintCustTransPortrait.php?' . SID . '&FromTransNo=' . $_POST['FromTransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . "'>" . _('click here') . '</a> ' . _('to email the customer tranaction'),'success');
	}
	exit;
} elseif (isset($_POST['DoIt'])) {
	$_GET['InvOrCredit'] = $_POST['InvOrCredit'];
	$_GET['FromTransNo'] = $_POST['FromTransNo'];
	prnMsg(_('The email address entered is too short to be a valid email address') . '. ' . _('The transaction was not emailed'),'warn');
}

include ('includes/header.inc');




echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "' METHOD=POST>";

echo "<INPUT TYPE=HIDDEN NAME='TransNo' VALUE=" . $_GET['FromTransNo'] . ">";
echo "<INPUT TYPE=HIDDEN NAME='InvOrCredit' VALUE=" . $_GET['InvOrCredit'] . '>';

echo '<CENTER><P><TABLE>';


$SQL = "SELECT email
		FROM custbranch INNER JOIN debtortrans
			ON custbranch.debtorno= debtortrans.debtorno
			AND custbranch.branchcode=debtortrans.branchcode
	WHERE debtortrans.type=$TypeCode
	AND debtortrans.transno=" .$_GET['FromTransNo'];

$ErrMsg = _('There was a problem retrieving the contact details for the customer');
$ContactResult=DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($ContactResult)>0){
	$EmailAddrRow = DB_fetch_row($ContactResult);
	$EmailAddress = $EmailAddrRow[0];
} else {
	$EmailAddress ='';
}

echo '<TR><TD>' . _('Email') . ' ' . $_GET['InvOrCredit'] . ' ' . _('number') . ' ' . $_GET['FromTransNo'] . ' ' . _('to') . ":</TD>
	<TD><INPUT TYPE=TEXT NAME='EmailAddr' MAXLENGTH=60 SIZE=60 VALUE='" . $EmailAddress . "'</TD>
	</TABLE>";

echo "<BR><INPUT TYPE=SUBMIT NAME='DoIt' VALUE='" . _('OK') . "'>";
echo '</CENTER></FORM>';
include ('includes/footer.inc');
?>
