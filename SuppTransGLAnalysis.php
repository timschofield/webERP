<?php
/* $Revision: 1.3 $ */
/*The supplier transaction uses the SuppTrans class to hold the information about the invoice or credit note
the SuppTrans class contains an array of GRNs objects - containing details of GRNs for invoicing/crediting and also
an array of GLCodes objects - only used if the AP - GL link is effective */

$title = "Supplier Transaction General Ledger Analysis";

include("includes/DefineSuppTransClass.php");
$PageSecurity=5;
/* Session started in header.inc for password checking and authorisation level check */
include("includes/session.inc");
include("includes/header.inc");


if (!isset($_SESSION['SuppTrans'])){
	echo "<P>To enter a supplier invoice or credit note the supplier must first be selected from the supplier selection screen, then the link to enter a supplier invoice or supplier credit note must be clicked on.";
	echo "<BR><A HREF='$rootpath/SelectSupplier.php?" . SID ."'>Select A Supplier</A>";
	exit;
	/*It all stops here if there aint no supplier selected and transaction initiated ie $_SESSION['SuppTrans'] started off*/
}

/*If the user hit the Add to transaction button then process this first before showing  all GL codes on the transaction otherwise it wouldnt show the latest addition*/

if ($_POST['AddGLCodeToTrans']=="Enter GL Line" ){

	$InputError=False;
	if ($_POST['GLCode']==""){
		$_POST['GLCode'] = $_POST['AcctSelection'];
	}

	$sql = "SELECT AccountCode, AccountName From ChartMaster WHERE AccountCode=" . $_POST['GLCode'];
	$result = DB_query($sql,$db);
	if (DB_num_rows($result)==0){
		echo "<BR>The account code entered is not a valid code, this line cannot be added to the transaction.<BR> You can use the selection box to select the account you want.";
		$InputError=True;
	} else {
		$myrow = DB_fetch_row($result);
		$GLActName = $myrow[1];
		if (!is_numeric($_POST['Amount'])){
			echo "<BR>The amount entered is not numeric. This line cannot be added to the transaction.";
			$InputError=True;
		} elseif ($_POST['JobRef']!=""){
			$sql = "SELECT ContractRef From Contracts WHERE ContactRef='" . $_POST['JobRef'] . "'";
			$result = DB_query($sql,$db);
			if (DB_num_rows($result)==0){
				echo "<BR>The contract reference entered is not a valid contract, this line cannot be added to the transaction.";
				$InputError=True;
			}
		}
	}

	if ($InputError==False){
		$_SESSION['SuppTrans']->Add_GLCodes_To_Trans($_POST['GLCode'], $GLActName, $_POST['Amount'], $_POST['JobRef'], $_POST['Narrative']);
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
if ($_SESSION['SuppTrans']->InvoiceOrCredit == "Invoice"){
	echo "<CENTER><FONT SIZE=4 COLOR=BLUE>General Ledger Analysis of Invoice From " . $_SESSION['SuppTrans']->SupplierName;
} else {
	echo "<CENTER><FONT SIZE=4 COLOR=RED>General Ledger Analysis of Credit Note From " . $_SESSION['SuppTrans']->SupplierName;
}
echo "<TABLE CELLPADDING=2>";

$TableHeader = "<TR>
		<TD class='tableheader'>Account</TD>
		<TD class='tableheader'>Name</TD>
		<TD class='tableheader'>Amount<BR>in " . $_SESSION['SuppTrans']->CurrCode . "</TD>
		<TD class='tableheader'>Job</TD>
		<TD class='tableheader'>Narrative</TD>
		</TR>";
echo $TableHeader;
$TotalGLValue=0;

foreach ( $_SESSION['SuppTrans']->GLCodes as $EnteredGLCode){

	echo "<TR>
		<TD>" . $EnteredGLCode->GLCode . "</TD>
		<TD>" . $EnteredGLCode->GLActName . "</TD>
		<TD ALIGN=RIGHT>" . number_format($EnteredGLCode->Amount,2) . "</TD>
		<TD>" .$EnteredGLCode->JobRef . "</TD>
		<TD>" . $EnteredGLCode->Narrative . "</TD>
		<TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $EnteredGLCode->Counter . "'>Delete</A></TD>
		</TR>";

	$TotalGLValue = $TotalGLValue + $EnteredGLCode->Amount;

	$i++;
	if ($i>15){
		$i=0;
		echo $TableHeader;
	}
}

echo "<TR>
	<TD COLSPAN=2 ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE>Total:</FONT></TD>
	<TD ALIGN=RIGHT><FONT SIZE=4 COLOR=BLUE><U>" . number_format($TotalGLValue,2) . "</U></FONT></TD>
	</TR>
	</TABLE>";


if ($_SESSION['SuppTrans']->InvoiceOrCredit=="Invoice"){
	echo "<BR><A HREF='$rootpath/SupplierInvoice.php?" . SID ."'>Back to Invoice Entry</A><HR>";
} else {
	echo "<BR><A HREF='$rootpath/SupplierCredit.php?" . SID ."'>Back to Credit Note Entry</A><HR>";
}



/*Set up a form to allow input of new GL entries */
echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<TABLE>";
echo "<TR>
	<TD>Account Code:</TD>
	<TD><input type='Text' name='GLCode' SIZE=12 MAXLENGTH=11 value=" .  $_POST['GLCode'] . "></TD>
	</TR>";
echo "<TR>
	<TD>Account Selection:<BR><FONT SIZE=1>If you know the code enter it above<BR>otherwise select the account from the list</FONT></TD>
	<TD><SELECT Name='AcctSelection'>";

$sql = "SELECT AccountCode, AccountName FROM ChartMaster ORDER BY AccountCode";

$result = DB_query($sql,$db);

while ($myrow = DB_fetch_array($result)) {
	if ($myrow["AccountCode"]==$_POST['AcctSelection']) {
		echo "<OPTION SELECTED VALUE=";
	} else {
		echo "<OPTION VALUE=";
	}
	echo $myrow["AccountCode"] . ">" . $myrow["AccountCode"] . " - " . $myrow["AccountName"];
	echo "</OPTION>";
}

echo "</SELECT>
	</TD>
	</TR>";

echo "<TR>
	<TD>Amount:</TD>
	<TD><input type='Text' name='Amount' SIZE=12 MAXLENGTH=11 value=" .  $_POST['Amount'] . "></TD>
	</TR>";
echo "<TR>
	<TD>Contract Ref:</TD>
	<TD><input type='Text' name='JobRef' SIZE=21 MAXLENGTH=20 value=" . $_POST['JobRef'] . "> <a target='_blank' href='$rootpath/ContractsList.php?" . SID . "'>View Open Contracts/Jobs</a></TD>
	</TR>";
echo "<TR>
	<TD>Narrative:</TD>
	<TD><TEXTAREA NAME=Narrative COLS=40 ROWS=2>" .  $_POST['Narrative'] . "</TEXTAREA></TD>
	</TR>
	</TABLE>";

echo "<INPUT TYPE='Submit' Name='AddGLCodeToTrans' Value='Enter GL Line'>";

echo "</FORM>";
include("includes/footer.inc");
?>