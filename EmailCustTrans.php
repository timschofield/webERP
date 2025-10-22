<?php

require(__DIR__ . '/includes/session.php');

$ViewTopic = 'Sales';
$BookMark = '';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_POST['DoIt'])) {
	$_GET['InvOrCredit'] = $_POST['InvOrCredit'];
	$_GET['FromTransNo'] = $_POST['FromTransNo'];
}

if ((isset($_POST['InvOrCredit']) and $_POST['InvOrCredit']=='Invoice') or $_GET['InvOrCredit']=='Invoice'){
	$TransactionType = __('Invoice');
	$TypeCode = 10;
} else {
	$TransactionType = __('Credit Note');
	$TypeCode =11;
}
$Title=__('Email') . ' ' . $TransactionType . ' ' . __('Number') . ' ' . $_GET['FromTransNo'];

if (isset($_POST['DoIt']) AND IsEmailAddress($_POST['EmailAddr'])){

	if ($_SESSION['InvoicePortraitFormat']==0){
		echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $_POST['TransNo'] . '&orientation=landscape&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">';

		prnMsg(__('The transaction should have been emailed off. If this does not happen (perhaps the browser does not support META Refresh)') . '<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $_POST['FromTransNo'] . '&orientation=landscape&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">' . __('click here') . '</a> ' . __('to email the customer transaction'),'success');
	} else {
		echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $_POST['TransNo'] . '&orientation=portrait&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">';

		prnMsg(__('The transaction should have been emailed off. If this does not happen (perhaps the browser does not support META Refresh)') . '<a href="' . $RootPath . '/PrintCustTrans.php?FromTransNo=' . $_POST['FromTransNo'] . '&orientation=portrait&PrintPDF=Yes&InvOrCredit=' . $_POST['InvOrCredit'] .'&Email=' . $_POST['EmailAddr'] . '">' . __('click here') . '</a> ' . __('to email the customer transaction'),'success');
	}
	exit();
} elseif (isset($_POST['DoIt'])) {
	$_GET['InvOrCredit'] = $_POST['InvOrCredit'];
	$_GET['FromTransNo'] = $_POST['FromTransNo'];
	prnMsg(__('The email address does not appear to be a valid email address. The transaction was not emailed'),'warn');
}

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

$ErrMsg = __('There was a problem retrieving the contact details for the customer');
$ContactResult = DB_query($SQL, $ErrMsg);

if (DB_num_rows($ContactResult)>0){
	$EmailAddrRow = DB_fetch_row($ContactResult);
	$EmailAddress = $EmailAddrRow[0];
} else {
	$EmailAddress ='';
}

echo '<tr><td>' . __('Email') . ' ' . $_GET['InvOrCredit'] . ' ' . __('number') . ' ' . $_GET['FromTransNo'] . ' ' . __('to') . ':</td>
	<td><input type="email" name="EmailAddr" autofocus="autofocus" maxlength="60" size="60" value="' . $EmailAddress . '" /></td>
    </tr>
	</table>';

echo '<br /><div class="centre"><input type="submit" name="DoIt" value="' . __('OK') . '" />';
echo '</div>
      </div>
      </form>';
include('includes/footer.php');
