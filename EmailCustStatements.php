<?php

/* $Id: EmailCustTrans.php 6310 2013-08-29 10:42:50Z daintree $*/

include ('includes/session.inc');
include ('includes/SQL_CommonFunctions.inc');
if (!isset($_GET['FromCust'])) {
	$_GET['FromCust'] = $_SESSION['CustomerID'];
}
$Title=_('Email Customer Statement For Customer No.') . ' ' . $_GET['FromCust'];

if (isset($_POST['DoIt']) AND IsEmailAddress($_POST['EmailAddr'])){
		echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/PrintCustStatements.php?FromCust=' . $_SESSION['CustomerID'] . '&ToCust='.$_SESSION['CustomerID'] . '&PrintPDF=Yes&Email=' . $_POST['EmailAddr'] . '">';
		prnMsg(_('The customer statement should have been emailed off') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ')' . '<a href="' . $RootPath . '/PrintCustStatements.php?FromCust=' . $_SESSION['CustomerID'] . '&PrintPDF=Yes&Email=' . $_POST['EmailAddr'] . '">' . _('click here') . '</a> ' . _('to email the customer statement'),'success');
	exit;
} elseif (isset($_POST['DoIt'])) {
	prnMsg(_('The email address does not appear to be a valid email address. The statement was not emailed'),'warn');
}
include ('includes/header.inc');

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br /><table>';

$SQL = "SELECT custbranch.email as email
		FROM custbranch INNER JOIN debtorsmaster
			ON custbranch.debtorno= debtorsmaster.debtorno
		WHERE debtorsmaster.debtorno='" .$_SESSION['CustomerID'] . "' LIMIT 1";

$ErrMsg = _('There was a problem retrieving the contact details for the customer');
$ContactResult=DB_query($SQL,$db,$ErrMsg);

if (DB_num_rows($ContactResult)>0){
	$EmailAddrRow = DB_fetch_row($ContactResult);
	$EmailAddress = $EmailAddrRow[0];
} else {
	$EmailAddress ='';
}

echo '<tr><td>' . _('Email to') . ':</td>
	<td><input type="email" name="EmailAddr" autofocus="autofocus" maxlength="60" size="60" value="' . $EmailAddress . '" /></td>
    </tr>
	</table>';

echo '<br /><div class="centre"><input type="submit" name="DoIt" value="' . _('OK') . '" />';
echo '</div>
      </div>
      </form>';
include ('includes/footer.inc');
?>
