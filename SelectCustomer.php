<?php
/* $Revision: 1.8 $ */
$title = "Search Customers";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");

$msg="";
if (!isset($_SESSION['CustomerID'])){ //initialise if not already done
	$_SESSION['CustomerID']="";
}

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

if ($_POST['Search']=="Search Now" OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
	if ($_POST['Search']=="Search Now"){
		$_POST['PageOffset'] = 1;
	}
	If ($_POST['Keywords'] AND $_POST['CustCode']) {
		$msg="Customer name keywords have been used in preference to the customer code extract entered.";
		$_POST["Keywords"] = strtoupper($_POST["Keywords"]);
	}
	If ($_POST['Keywords']=="" AND $_POST['CustCode']=="") {
		$msg="At least one customer name keyword OR an extract of a customer code must be entered for the search";
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
			$SearchString = $SearchString . substr($_POST['Keywords'],$i)."%";
			$SQL = "SELECT DebtorsMaster.DebtorNo, DebtorsMaster.Name, CustBranch.BrName, CustBranch.ContactName, CustBranch.PhoneNo, CustBranch.FaxNo FROM DebtorsMaster LEFT JOIN CustBranch ON DebtorsMaster.DebtorNo = CustBranch.DebtorNo WHERE DebtorsMaster.Name LIKE '$SearchString'";

		} elseif (strlen($_POST['CustCode'])>0){

			$_POST["CustCode"] = strtoupper($_POST["CustCode"]);

			$SQL = "SELECT DebtorsMaster.DebtorNo, DebtorsMaster.Name, CustBranch.BrName, CustBranch.ContactName, CustBranch.PhoneNo, CustBranch.FaxNo FROM DebtorsMaster LEFT JOIN CustBranch ON DebtorsMaster.DebtorNo = CustBranch.DebtorNo WHERE DebtorsMaster.DebtorNo LIKE '%" . $_POST['CustCode'] . "%'";
		}

		$result = DB_query($SQL,$db);
		if (DB_error_no($db) !=0) {
			echo "The searched customer records requested cannot be retrieved because - " . DB_error_msg($db) . "<BR>SQL used to retrieve the customer details was:<BR>$sql";
		} elseif (DB_num_rows($result)==1){
			$myrow=DB_fetch_array($result);
			$_POST['Select'] = $myrow["DebtorNo"];
			unset($result);
		} elseif (DB_num_rows($result)==0){
			echo "<P>No customer records contain the selected text - please alter your search criteria and try again.";
		}

	} //one of keywords or custcode was more than a zero length string
} //end of if search


If (!isset($_POST['Select'])){
	$_POST['Select']="";
}

If ($_POST['Select']!="" OR ($_SESSION['CustomerID']!="" AND !isset($_POST['Keywords']) AND !isset($_POST['CustCode']))) {

	If ($_POST['Select']!=""){
		$SQL = "Select Name FROM DebtorsMaster WHERE DebtorNo='" . $_POST['Select'] . "'";
		$_SESSION['CustomerID'] = $_POST['Select'];
	} else {
		$SQL = "Select Name FROM DebtorsMaster WHERE DebtorNo='" . $_SESSION['CustomerID'] . "'";
	}
	$result = DB_query($SQL,$db);
	if (DB_error_no($db) !=0) {
		echo "The customer name requested cannot be retrieved because - " . DB_error_msg($db) . "<BR>SQL used to retrieve the customer name was:<BR>$sql";
	}
	if ($myrow=DB_fetch_row($result)){
		$CustomerName = $myrow[0];
	}
	unset($result);
	echo "<BR><BR><FONT SIZE=3>Customer :<B> " . $_SESSION['CustomerID'] . " - $CustomerName</B> has been selected.<BR>Select a menu option to operate using this customer.</FONT><BR>";

	$_POST['Select'] = NULL;

	echo "<BR><a href='$rootpath/CustomerInquiry.php?CustomerID=" . $_SESSION['CustomerID'] . "'>Customer Transaction Inquiries</a><BR>";
	echo "<a href='$rootpath/Customers.php?DebtorNo=" . $_SESSION['CustomerID'] . "'>Modify Customer Details</a><BR>";
	echo "<a href='$rootpath/CustomerBranches.php?DebtorNo=" . $_SESSION['CustomerID'] . "'>Add/Edit/Delete Customer Branch records</a><BR>";
	echo "<a href='$rootpath/SelectSalesOrder.php?SelectedCustomer=" . $_SESSION['CustomerID'] . "'>Modify Outstanding Sales Orders</a><BR>";
	echo "<a href='$rootpath/SelectCompletedOrder.php?SelectedCustomer=" . $_SESSION['CustomerID'] . "'>Order Inquiries</a><BR>";
	echo "<a href='$rootpath/SelectProduct.php'>Special Customer Prices</a><BR>";
	echo "<a href='$rootpath/CustEDISetup.php'>Customer EDI Configuration</a><BR>";

}

?>

<FORM ACTION="<?php echo $_SERVER['PHP_SELF'] . "?" . SID; ?>" METHOD=POST>

<B><?php echo $msg; ?></B>
<TABLE CELLPADDING=3 COLSPAN=4>
<TR>
<TD>Text in the <B>name</B>:</TD>
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
<TD><FONT SIZE=3><B>OR</B></FONT></TD>
<TD>Text extract in the customer <B>code</B>:</TD>
<TD>
<?php
if (isset($_POST['CustCode'])) {
?>
<INPUT TYPE="Text" NAME="CustCode" value="<?php echo $_POST['CustCode'] ?>" SIZE=15 MAXLENGTH=18>
<?php
} else {
?>
<INPUT TYPE="Text" NAME="CustCode" SIZE=15 MAXLENGTH=18>
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

<?php

  echo "<br><br>";

	echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>";
	$TableHeader = "<TR>
				<TD Class='tableheader'>Code</TD>
				<TD Class='tableheader'>Customer Name</TD>
				<TD Class='tableheader'>Branch</TD>
				<TD Class='tableheader'>Contact</TD>
				<TD Class='tableheader'>Phone</TD>
				<TD Class='tableheader'>Fax</TD>
			</TR>";

	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
  $RowIndex = 0;

  if (DB_num_rows($result)<>0){
  	DB_data_seek($result, ($_POST['PageOffset']-1)*$_SESSION['DisplayRecordsMax']);
  }

	while (($myrow=DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		printf("<td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td>
			<td><FONT SIZE=1>%s</FONT></td></tr>",
			$myrow["DebtorNo"],
			$myrow["Name"],
			$myrow["BrName"],
			$myrow["ContactName"],
			$myrow["PhoneNo"],
			$myrow["FaxNo"]);

		$j++;
		If ($j == 11 AND ($RowIndex+1 != $_SESSION['DisplayRecordsMax'])){
			$j=1;
			echo $TableHeader;
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
