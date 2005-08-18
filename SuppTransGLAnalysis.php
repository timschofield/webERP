<?php
/* $Revision: 1.8 $ */
/*The supplier transaction uses the SuppTrans class to hold the information about the invoice or credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing/crediting and also
an array of GLCodes objects - only used if the AP - GL link is effective */

include('includes/DefineSuppTransClass.php');

$PageSecurity = 5;

/* Session started in header.inc for password checking and authorisation level check */
include('includes/session.inc');

$title = _('Supplier Transaction General Ledger Analysis');

include('includes/header.inc');

if (!isset($_SESSION['SuppTrans'])){
	prnMsg(_('To enter a supplier invoice or credit note the supplier must first be selected from the supplier selection screen') . ', ' . _('then the link to enter a supplier invoice or supplier credit note must be clicked on'),'info');
	echo "<BR><A HREF='$rootpath/SelectSupplier.php?" . SID ."'>" . _('Select A Supplier') . '</A>';
	include('includes/footer.inc');
	exit;
	/*It all stops here if there aint no supplier selected and transaction initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to transaction button then process this first before showing  all GL codes on the transaction otherwise it wouldnt show the latest addition*/

if ($_POST['AddGLCodeToTrans'] == _('Enter GL Line')){

	$InputError = False;
	if ($_POST['GLCode'] == ''){
		$_POST['GLCode'] = $_POST['AcctSelection'];
	}

	$sql = 'SELECT accountcode,
			accountname
		FROM chartmaster
		WHERE accountcode=' . $_POST['GLCode'];
	$result = DB_query($sql, $db);
	if (DB_num_rows($result) == 0){
		prnMsg(_('The account code entered is not a valid code') . '. ' . _('This line cannot be added to the transaction') . '.<BR>' . _('You can use the selection box to select the account you want'),'error');
		$InputError = True;
	} else {
		$myrow = DB_fetch_row($result);
		$GLActName = $myrow[1];
		if (!is_numeric($_POST['Amount'])){
			prnMsg( _('The amount entered is not numeric') . '. ' . _('This line cannot be added to the transaction'),'error');
			$InputError = True;
		} elseif ($_POST['JobRef'] != ''){
			$sql = "SELECT contractref FROM contracts WHERE contactref='" . $_POST['JobRef'] . "'";
			$result = DB_query($sql, $db);
			if (DB_num_rows($result) == 0){
				prnMsg( _('The contract reference entered is not a valid contract, this line cannot be added to the transaction'),'error');
				$InputError = True;
			}
		}
	}

	if ($InputError == False){
		$_SESSION['SuppTrans']->Add_GLCodes_To_Trans($_POST['GLCode'],
								$GLActName,
								$_POST['Amount'],
								$_POST['JobRef'],
								$_POST['Narrative']);
		unset($_POST['GLCode']);
		unset($_POST['Amount']);
		unset($_POST['JobRef']);
		unset($_POST['Narrative']);
		unset($_POST['AcctSelection']);
	}
}

if (isset($_GET['Delete'])){

	$_SESSION['SuppTrans']->Remove_GLCodes_From_Trans($_GET['Delete']);

}

/*Show all the selected GLCodes so far from the SESSION['SuppInv']->GLCodes array */
if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo '<CENTER><FONT SIZE=4 COLOR=BLUE>' . _('General Ledger Analysis of Invoice From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
} else {
	echo '<CENTER><FONT SIZE=4 COLOR=RED>' . _('General Ledger Analysis of Credit Note From') . ' ' . $_SESSION['SuppTrans']->SupplierName;
}
echo '<TABLE CELLPADDING=2>';

$TableHeader = "<TR>
		<TD CLASS='tableheader'>" . _('Account') . "</TD>
		<TD CLASS='tableheader'>" . _('Name') . "</TD>
		<TD CLASS='tableheader'>" . _('Amount') . '<BR>' . _('in') . ' ' . $_SESSION['SuppTrans']->CurrCode . "</TD>
		<TD CLASS='tableheader'>" . _('Job') . "</TD>
		<TD CLASS='tableheader'>" . _('Narrative') . '</TD>
		</TR>';
echo $TableHeader;
$TotalGLValue=0;

foreach ( $_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

	echo '<TR>
		<TD>' . $EnteredGLCode->GLCode . '</TD>
		<TD>' . $EnteredGLCode->GLActName . '</TD>
		<TD ALIGN=RIGHT>' . number_format($EnteredGLCode->Amount,2) . '</TD>
		<TD>' .$EnteredGLCode->JobRef . '</TD>
		<TD>' . $EnteredGLCode->Narrative . "</TD>
		<TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $EnteredGLCode->Counter . "'>" . _('Delete') . '</A></TD>
		</TR>';

	$TotalGLValue = $TotalGLValue + $EnteredGLCode->Amount;

	$i++;
	if ($i>15){
		$i = 0;
		echo $TableHeader;
	}
}

echo '<TR>
	<TD COLSPAN=2 ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE>' . _('Total') . ':</FONT></TD>
	<TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>' . number_format($TotalGLValue,2) . '</U></FONT></TD>
	</TR>
	</TABLE>';


if ($_SESSION['SuppTrans']->InvoiceOrCredit == 'Invoice'){
	echo "<BR><A HREF='$rootpath/SupplierInvoice.php?" . SID . "'>" . _('Back to Invoice Entry') . '</A><HR>';
} else {
	echo "<BR><A HREF='$rootpath/SupplierCredit.php?" . SID . "'>" . _('Back to Credit Note Entry') . '</A><HR>';
}

/*Set up a form to allow input of new GL entries */
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo '<TABLE>';
echo '<TR>
	<TD>' . _('Account Code') . ":</TD>
	<TD><INPUT TYPE='Text' NAME='GLCode' SIZE=12 MAXLENGTH=11 VALUE=" .  $_POST['GLCode'] . '></TD>
	</TR>';
echo '<TR>
	<TD>' . _('Account Selection') . ':<BR><FONT SIZE=1>' . _('If you know the code enter it above') . '<BR>' . _('otherwise select the account from the list') . "</FONT></TD>
	<TD><SELECT NAME='AcctSelection'>";

$sql = "SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode";

$result = DB_query($sql, $db);

while ($myrow = DB_fetch_array($result)) {
	if ($myrow['accountcode'] == $_POST['AcctSelection']) {
		echo '<OPTION SELECTED VALUE=';
	} else {
		echo '<OPTION VALUE=';
	}
	echo $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' - ' . $myrow['accountname'];
	echo '</OPTION>';
}

echo '</SELECT>
	</TD>
	</TR>';

echo '<TR>
	<TD>' . _('Amount') . ":</TD>
	<TD><INPUT TYPE='Text' NAME='Amount' SIZE=12 MAXLENGTH=11 VALUE=" .  $_POST['Amount'] . '></TD>
	</TR>';
echo '<TR>
	<TD>' . _('Contract Ref') . ":</TD>
	<TD><INPUT TYPE='Text' NAME='JobRef' SIZE=21 MAXLENGTH=20 VALUE=" . $_POST['JobRef'] . ">";
	 
	 /* Once the contract stuff is written then it would be appropriate to have:
	  <A TARGET='_blank' HREF='$rootpath/ContractsList.php?" . SID . "'>" . _('View Open Contracts/Jobs') . '</A> */
	  
echo ' </TD>
	</TR>';
echo '<TR>
	<TD>' . _('Narrative') . ":</TD>
	<TD><TEXTAREA NAME='Narrative' COLS=40 ROWS=2>" .  $_POST['Narrative'] . '</TEXTAREA></TD>
	</TR>
	</TABLE>';

echo "<INPUT TYPE='Submit' NAME='AddGLCodeToTrans' VALUE='" . _('Enter GL Line') . "'>";

echo '</FORM>';
include('includes/footer.inc');
?>