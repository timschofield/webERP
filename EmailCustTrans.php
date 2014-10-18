<?php

/* $Id: EmailCustTrans.php 6310 2013-08-29 10:42:50Z daintree $*/

include ('includes/session.inc');
include ('includes/SQL_CommonFunctions.inc');

if ($_GET['InvOrCredit']=='Invoice'){
	$TransactionType = _('Invoice');
	$TypeCode = 10;
} else {
	$TransactionType = _('Credit Note');
	$TypeCode =11;
}
$Title=_('Email') . ' ' . $TransactionType . ' ' . _('Number') . ' ' . $_GET['FromTransNo'];

if (isset($_POST['DoIt']) AND IsEmailAddress($_POST['EmailAddr'])){

	if ($_SESSION['InvoicePortraitFormat']==0){
		echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $_POST['TransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">';

		prnMsg(_('The transaction should have been emailed off') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ')' . '<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $_POST['FromTransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">' . _('click here') . '</a> ' . _('to email the customer transaction'),'success');
	} else {
		echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PrintCustTransPortrait.php?FromTransNo=' . $_POST['TransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">';

		prnMsg(_('The transaction should have been emailed off. If this does not happen (perhaps the browser does not support META Refresh)') . '<a href="' . $RootPath . '/PrintCustTransPortrait.php?FromTransNo=' . $_POST['FromTransNo'] . '&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">' . _('click here') . '</a> ' . _('to email the customer transaction'),'success');
	}
	exit;
} elseif (isset($_POST['DoIt'])) {
	$_GET['InvOrCredit'] = $_POST['InvOrCredit'];
	$_GET['FromTransNo'] = $_POST['FromTransNo'];
	prnMsg(_('The email address does not appear to be a valid email address. The transaction was not emailed'),'warn');
}

include ('includes/header.inc');


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<input type="hidden" name="TransNo" value="' . $_GET['FromTransNo'] . '" />';
echo '<input type="hidden" name="InvOrCredit" value="' . $_GET['InvOrCredit'] . '" />';

echo '<br /><table>';

$SQL = "SELECT email
		FROM custbranch INNER JOIN debtortrans
			ON custbranch.debtorno= debtortrans.debtorno
			AND custbranch.branchcode=debtortrans.branchcode
		WHERE debtortrans.type='" . $TypeCode . "'
		AND debtortrans.transno='" .$_GET['FromTransNo'] . "'";

$ErrMsg = _('There was a problem retrieving the contact details for the customer');
$ContactResult=DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($ContactResult)>0){
	$EmailAddrRow = DB_fetch_row($ContactResult);
	$EmailAddress = $EmailAddrRow[0];
} else {
	$EmailAddress ='';
}

echo '<tr><td>' . _('Email') . ' ' . $_GET['InvOrCredit'] . ' ' . _('number') . ' ' . $_GET['FromTransNo'] . ' ' . _('to') . ':</td>
	<td><input type="email" name="EmailAddr" autofocus="autofocus" maxlength="60" size="60" value="' . $EmailAddress . '" /></td>
    </tr>
	</table>';

echo '<br /><div class="centre"><input type="submit" name="DoIt" value="' . _('OK') . '" />';
echo '</div>
      </div>
      </form>';
include ('includes/footer.inc');
?>
