<?php
/* $Revision: 1.6 $ */
$title = "Search Suppliers";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");

$msg="";

if (!isset($_POST['Search'])){
	$_POST['Search']="";
}

if (!isset($_POST['PageOffset'])) {
  $_POST['PageOffset'] = 1;
} else {
  if ($_POST['PageOffset']==0) {
    $_POST['PageOffset'] = 1;
  }
}

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){

	if (isset($_POST['SearchNow'])){
		$_POST['PageOffset'] = 1;
	}

	If ($_POST['Keywords'] AND $_POST['SupplierCode']) {
		$msg="<BR>Supplier name keywords have been used in preference to the Supplier code extract entered.";
	}
	If ($_POST['Keywords']=="" AND $_POST['SupplierCode']=="") {
		$msg="<BR>At least one Supplier name keyword OR an extract of a Supplier code must be entered for the search";
	} else {
		If (strlen($_POST['Keywords'])>0) {

			$_POST["Keywords"] = strtoupper($_POST["Keywords"]);
			//insert wildcard characters in spaces

			$i=0;
			$SearchString = "%";
			while (strpos($_POST['Keywords'], " ", $i)) {
				$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
				$i=strpos($_POST['Keywords']," ",$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";

			$SQL = "SELECT SupplierID, SuppName, CurrCode, Address1, Address2, Address3, Address4 FROM Suppliers WHERE SuppName LIKE '$SearchString'";

		} elseif (strlen($_POST['SupplierCode'])>0){
			$_POST["SupplierCode"] = strtoupper($_POST["SupplierCode"]);
			$SQL = "SELECT SupplierID, SuppName, CurrCode, Address1, Address2, Address3, Address4 FROM Suppliers WHERE SupplierID LIKE '%" . $_POST['SupplierCode'] . "%'";

		}

		$result = DB_query($SQL,$db);
		if (DB_num_rows($result)==1){
		   $myrow = DB_fetch_row($result);
		   $_POST['Select'] = $myrow[0];
		}

	} //one of keywords or SupplierCode was more than a zero length string
} //end of if search

If (isset($_POST['Select'])) { /*User has hit the button selecting a supplier or there was only one supplier returned */
	$_SESSION['SupplierID'] = $_POST['Select'];
	$_POST['Select'] = NULL;
}

if (isset($_SESSION['SupplierID'])){

	// Sherifoz 23.06.03 Display the supplier name too, not just the code
	$SupplierName = "";
	$SQL = "SELECT Suppliers.SuppName FROM Suppliers WHERE Suppliers.SupplierID ='" . $_SESSION['SupplierID'] . "'";
	$SupplierNameResult = DB_query($SQL,$db);
	if (DB_num_rows($SupplierNameResult)==1){
	   $myrow = DB_fetch_row($SupplierNameResult);
	   $SupplierName = $myrow[0];
	}

	echo "<FONT SIZE=3><P>Supplier <B>" . $_SESSION['SupplierID']  . "-$SupplierName</B> is currently been selected. <BR>Select a menu option to operate using this supplier.<P></FONT>";

	echo "<CENTER><TABLE WIDTH=90% COLSPAN=2 BORDER=2 CELLPADDING=4>";
	echo "<TR><TD WIDTH=33% class='tableheader'>Supplier Inquiries</TD><TD WIDTH=33% class='tableheader'>Supplier Transactions</TD><TD WIDTH=33% class='tableheader'>Supplier Maintenance</TD></TR>";

	echo "<TR><TD VALIGN=TOP>";    /* Inquiry Options */

	echo "<A HREF=\"$rootpath/SupplierInquiry.php?" . SID . "SupplierID=" . $_SESSION['SupplierID'] . "\">Supplier Account Inquiry</A><BR>";

	echo "<BR>";

	echo "<BR><A HREF='$rootpath/PO_SelectOSPurchOrder.php?" . SID . "SelectedSupplier=" . $_SESSION['SupplierID'] . "'>Outstanding Purchase Orders</A>";
	echo "<BR><A HREF='$rootpath/PO_SelectPurchOrder.php?" . SID . "SelectedSupplier=" . $_SESSION['SupplierID'] . "'>View All Purchase Orders</A>";

	echo "<BR>";

	echo "<BR><A HREF='$rootpath/Shipt_Select.php?" . SID . "SelectedSupplier=" . $_SESSION['SupplierID'] . "'>Search Shipments</A>";

	echo "</TD><TD VALIGN=TOP>"; /* Supplier Transactions */

	echo "<A HREF=\"$rootpath/SupplierInvoice.php?" . SID . "SupplierID=" . $_SESSION['SupplierID'] . "\">Enter a Supplier's Invoice</A><BR>";
	echo "<A HREF=\"$rootpath/SupplierCredit.php?" . SID . "SupplierID=" . $_SESSION['SupplierID'] . "\">Enter a Supplier's Credit Note</A><BR>";
	echo "<A HREF=\"$rootpath/Payments.php?" . SID . "SupplierID=" . $_SESSION['SupplierID'] . "\">Enter a Payment to the Supplier</A><BR>";

	echo "<BR>";

	echo "<BR><A HREF='$rootpath/ReverseGRN.php?" . SID . "SupplierID=" . $_SESSION['SupplierID'] . "'>Reverse an Outstanding GRN</A>";

	echo "</TD><TD VALIGN=TOP>"; /* Supplier Maintenance */

	echo "<A HREF=\"$rootpath/Suppliers.php?" . SID . "SupplierID=" . $_SESSION['SupplierID'] . "\">Modify Or Delete Supplier Details</A>";
	echo "<BR><A HREF=\"$rootpath/SupplierContacts.php?" . SID . "SupplierID=" . $_SESSION['SupplierID'] . "\">Modify Or Delete Supplier Contact Details</A>";

	echo "<BR>";

	echo "<BR><A HREF='$rootpath/Shipments.php?" . SID . "NewShipment=Yes'>Set Up A New Shipment</A>";

	echo "</TD></TR></TABLE>";
}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";
echo "<B>" . $msg;

?>

</B>
<TABLE CELLPADDING=3 COLSPAN=4>
<TR>
<TD>Text in the <B>NAME</B>:</FONT></TD>
<TD>
<?php
if (isset($_POST['Keywords'])) {
?>
<INPUT TYPE="Text" NAME="Keywords" value="<?php echo $_POST['Keywords']?>" SIZE=20 MAXLENGTH=25>
<?php
} else {
?>
<INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25>
<?php
}
?>
</TD>
<TD><B>OR</B></FONT></TD>
<TD>Text in <B>CODE</B>:</FONT></TD>
<TD>
<?php
if (isset($_POST['SupplierCode'])) {
?>
<INPUT TYPE="Text" NAME="SupplierCode" value="<?php echo $_POST['SupplierCode']?>" SIZE=15 MAXLENGTH=18>
<?php
} else {
?>
<INPUT TYPE="Text" NAME="SupplierCode" SIZE=15 MAXLENGTH=18>
<?php
}
?>
</TD>
</TR>
</TABLE>
<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="Search Now">
<INPUT TYPE=SUBMIT ACTION=RESET VALUE="Reset"></CENTER>


<?php

If (isset($result)) {
  $ListCount=DB_num_rows($result);
  $ListPageMax=ceil($ListCount/$_SESSION['DisplayRecordsMax']);								

  if (isset($_POST['Next'])) {
    if ($_POST['PageOffset'] < $ListPageMax) {
	    $_POST['PageOffset'] = $_POST['PageOffset'] + 1;
    }
	}

  if (isset($_POST['Previous'])) {
    if ($_POST['PageOffset'] > 1) {
	    $_POST['PageOffset'] = $_POST['PageOffset'] - 1;
    }
  }
	
  echo "&nbsp;&nbsp;" . $_POST['PageOffset'] . " of " . $ListPageMax . " pages. Go to Page: ";
?>	

  <select name="PageOffset">

<?php	
  $ListPage=1;
  while($ListPage<=$ListPageMax) {
	  if ($ListPage==$_POST['PageOffset']) {
?>

  		<option value=<?php echo($ListPage); ?> selected><?php echo($ListPage); ?></option>

<?php	
	  } else {
?>

		  <option value=<?php echo($ListPage); ?>><?php echo($ListPage); ?></option>

<?php 
	  }
	  $ListPage=$ListPage+1;
  }
?>

  </select>
  <INPUT TYPE=SUBMIT NAME="Go" VALUE="Go">
  <INPUT TYPE=SUBMIT NAME="Previous" VALUE="Previous">
  <INPUT TYPE=SUBMIT NAME="Next" VALUE="Next">
  <INPUT TYPE=hidden NAME="Search" VALUE="Search Now">
<?php
  
  echo "<br><br>";

	echo "<BR><TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>";
	$tableheader = "<TR class='tableheader'><TD class='tableheader'>Code</TD><TD class='tableheader'>Supplier Name</TD><TD class='tableheader'>Currency</TD><TD class='tableheader'>Address 1</TD><TD class='tableheader'>Address 2</TD></B><TD class='tableheader'>Address 3</TD><TD class='tableheader'>Address 4</TD></TR>";
	echo $tableheader;

	$j = 1;

  $RowIndex = 0;
	
  if (DB_num_rows($result)<>0){
 	DB_data_seek($result, ($_POST['PageOffset']-1)*$_SESSION['DisplayRecordsMax']);
  }

	while (($myrow=DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

		printf("<tr>
			<td><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			</tr>",
			$myrow["SupplierID"],
			$myrow["SuppName"],
			$myrow["CurrCode"],
			$myrow["Address1"],
			$myrow["Address2"],
			$myrow["Address3"],
			$myrow["Address4"]);

		$j++;
		If ($j == 11){
			$j=1;
			echo $tableheader;
		}
    $RowIndex = $RowIndex + 1;
//end of page full new headings if
	}
//end of while loop

	echo "</TABLE>";

}
//end if results to show
echo "</form>";
include("includes/footer.inc");
?>


