<?php
/* $Revision: 1.8 $ */

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
		echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/PrintCustTrans.php?' . SID . '&FromTransNo=' . $_POST['TransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . "'>";

		prnMsg(_('The transaction should have been emailed off') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ')' . "<a href='" . $rootpath . '/PrintCustTrans.php?' . SID . '&FromTransNo=' . $_POST['FromTransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . "'>" . _('click here') . '</a> ' . _('to email the customer transaction'),'success');
	} else {
		echo "<meta http-equiv='Refresh' content='0; url=" . $rootpath . '/PrintCustTransPortrait.php?' . SID . '&FromTransNo=' . $_POST['TransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . "'>";

		prnMsg(_('The transaction should have been emailed off') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ')' . "<a href='" . $rootpath . '/PrintCustTransPortrait.php?' . SID . '&FromTransNo=' . $_POST['FromTransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . "'>" . _('click here') . '</a> ' . _('to email the customer transaction'),'success');
	}
	exit;
} elseif (isset($_POST['DoIt'])) {
	$_GET['InvOrCredit'] = $_POST['InvOrCredit'];
	$_GET['FromTransNo'] = $_POST['FromTransNo'];
	prnMsg(_('The email address entered is too short to be a valid email address') . '. ' . _('The transaction was not emailed'),'warn');
}

include ('includes/header.inc');




echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method=post>";

echo "<input type=hidden name='TransNo' VALUE=" . $_GET['FromTransNo'] . ">";
echo "<input type=hidden name='InvOrCredit' VALUE=" . $_GET['InvOrCredit'] . '>';

echo '<p><table>';


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

echo '<tr><td>' . _('Email') . ' ' . $_GET['InvOrCredit'] . ' ' . _('number') . ' ' . $_GET['FromTransNo'] . ' ' . _('to') . ":</td>
	<td><input type=TEXT name='EmailAddr' maxlength=60 size=60 VALUE='" . $EmailAddress . "'</td>
	</table>";

echo "<br><div class='centre'><input type=submit name='DoIt' VALUE='" . _('OK') . "'>";
echo '</div></form>';
include ('includes/footer.inc');
?>