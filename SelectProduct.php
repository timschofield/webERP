<?php
/* $Revision: 1.6 $ */
$title = "Search Inventory Items";

$PageSecurity = 2;

include("includes/session.inc");
include("includes/header.inc");

$msg="";

if (isset($_GET['StockID'])){  //The page is called with a StockID
	$_POST['Select'] = $_GET['StockID'];
}

if (isset($_GET['NewSearch'])){
	unset($StockID);
	unset($_SESSION['SelectedStockItem']);
	unset($_POST['Select']);
}

if (!isset($_POST['PageOffset'])) {
  $_POST['PageOffset'] = 1;
} else {
  if ($_POST['PageOffset']==0) {
    $_POST['PageOffset'] = 1;
  }
}

/*Always show the search facilities */

$SQL="SELECT CategoryID, CategoryDescription FROM StockCategory ORDER BY CategoryDescription";
$result1 = DB_query($SQL,$db);
if (DB_num_rows($result1)==0){
	echo "<P><FONT SIZE=4 COLOR=RED>Problem Report:</FONT><BR>There are no stock categories currently defined please use the link below to set them up.";
	echo "<BR><A HREF='$rootpath/StockCategories.php?" . SID ."'>Define Stock Categories</A>";
	exit;
}

?>

<FORM ACTION="<?php echo $_SERVER['PHP_SELF'] . "?" . SID; ?>" METHOD=POST>
<B><?php echo $msg; ?></B>
<TABLE>
<TR>
<TD>In Stock Category:
<SELECT NAME="StockCat">
<?php
	if (!isset($_POST['StockCat'])){
		$_POST['StockCat']="";
	}
	if ($_POST['StockCat']=="All"){
		echo "<OPTION SELECTED VALUE='All'>All";
	} else {
		echo "<OPTION VALUE='All'>All";
	}
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['CategoryID']==$_POST['StockCat']){
			echo "<OPTION SELECTED VALUE='". $myrow1["CategoryID"] . "'>" . $myrow1["CategoryDescription"];
		} else {
			echo "<OPTION VALUE='". $myrow1["CategoryID"] . "'>" . $myrow1["CategoryDescription"];
		}
	}
?>

</SELECT>
<TD>Text in the <B>description</B>:</TD>
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
</TR>
<TR><TD></TD>
<TD><FONT SIZE 3><B>OR </B></FONT>Text in the <B>Stock Code</B>:</TD>
<TD>
<?php
if (isset($_POST['StockCode'])) {
?>
<INPUT TYPE="Text" NAME="StockCode" value="<?php echo $_POST['StockCode']?>" SIZE=15 MAXLENGTH=18>
<?php
} else {
?>
<INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18>
<?php
}
?>
</TD>
</TR>
</TABLE>
<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="Search Now"></CENTER>
<HR>

<?php
if (!isset($_POST['Search'])){
	$_POST['Search'] ="";
}
if ($_POST['Search']=="Search Now"){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg="Stock description keywords have been used in preference to the Stock code extract entered.";
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST["Keywords"] = strtoupper($_POST["Keywords"]);
		$i=0;
		$SearchString = "%";
		while (strpos($_POST['Keywords'], " ", $i)) {
			$wrdlen=strpos($_POST['Keywords']," ",$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . "%";
			$i=strpos($_POST['Keywords']," ",$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i)."%";

		if ($_POST['StockCat'] == 'All'){
			$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH, Units, MBflag FROM StockMaster, LocStock WHERE StockMaster.StockID=LocStock.StockID AND Description LIKE '$SearchString' GROUP BY StockMaster.StockID, Description ORDER BY StockMaster.StockID";
		} else {
			$SQL = "SELECT StockMaster.StockID, Description, Sum(LocStock.Quantity) AS QOH, Units, MBflag FROM StockMaster, LocStock WHERE StockMaster.StockID=LocStock.StockID AND Description LIKE '$SearchString' AND CategoryID='" . $_POST['StockCat'] . "' GROUP BY StockMaster.StockID, Description ORDER BY StockMaster.StockID";
		}
	} elseif (isset($_POST['StockCode'])){

		$_POST["StockCode"] = strtoupper($_POST["StockCode"]);
		if ($_POST['StockCat'] == 'All'){
			$SQL = "SELECT StockMaster.StockID, Description, MBflag, Sum(LocStock.Quantity) AS QOH, Units FROM StockMaster, LocStock WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.StockID like '%" . $_POST['StockCode'] . "%' GROUP BY StockMaster.StockID, Description ORDER BY StockMaster.StockID";

		} else {
			$SQL = "SELECT StockMaster.StockID, Description, MBflag, Sum(LocStock.Quantity) AS QOH, Units FROM StockMaster, LocStock WHERE StockMaster.StockID=LocStock.StockID AND StockMaster.StockID like '%" . $_POST['StockCode'] . "%' AND CategoryID='" . $_POST['StockCat'] . "' GROUP BY StockMaster.StockID, Description ORDER BY StockMaster.StockID";
		}

	} elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		if ($_POST['StockCat'] == 'All'){
			$SQL = "SELECT StockMaster.StockID, Description, MBflag, Sum(LocStock.Quantity) AS QOH, Units FROM StockMaster, LocStock WHERE StockMaster.StockID=LocStock.StockID GROUP BY StockMaster.StockID, Description ORDER BY StockMaster.StockID";
		} else {
			$SQL = "SELECT StockMaster.StockID, Description, MBflag, Sum(LocStock.Quantity) AS QOH, Units FROM StockMaster, LocStock WHERE StockMaster.StockID=LocStock.StockID AND CategoryID='" . $_POST['StockCat'] . "' GROUP BY StockMaster.StockID, Description ORDER BY StockMaster.StockID";
		}
	}
	$SQL=$SQL . " LIMIT " . $Maximum_Number_Of_Parts_To_Show;

	$result = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		echo "<BR>No stock items were returned by the SQL because - " . DB_error_msg($db);
		if ($debug==1) {
			echo "<BR>The SQL that returned an error was: <BR>$SQL";
		}
	} elseif (DB_num_rows($result)==0){
		echo "<BR>No stock items were returned by this search please re-enter alternative criteria to try again.";
	} elseif (DB_num_rows($result)==1){ /*autoselect it to avoid user hitting another keystroke */
		$_POST['Search']=""; /*to enable the display of the selecteditem options */
		$myrow = DB_fetch_row($result);
		$_POST['Select'] = $myrow[0];
	}
}

If (isset($result) AND !isset($_POST['Select']) ) {
/*If the user hit the search button and there is more than one items to show */
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

	echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>";
	$tableheader = "<TR><TD class='tableheader'>Code</TD><TD class='tableheader'>Description</TD><TD class='tableheader'>Total Qty On Hand</TD><TD class='tableheader'>Units</TD></TR>";
	echo $tableheader;

	$j = 1;

	$k = 0; //row counter to determine background colour

  $RowIndex = 0;
	
  if (DB_num_rows($result)<>0){
    mysql_data_seek($result, ($_POST['PageOffset']-1)*$_SESSION['DisplayRecordsMax']);
	}
	
	while (($myrow=DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		if ($myrow["MBflag"]=='D') {
			$qoh = "N/A";
		} else {
			$qoh = number_format($myrow["QOH"],1);
		}

		printf("<td><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</td><td>%s</td><td ALIGN=RIGHT>%s</td><td>%s</td></tr>", $myrow["StockID"], $myrow["Description"], $qoh, $myrow["Units"]);

		$j++;
		If ($j == 12){
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

if (!isset($_POST['Search'])){
	$_POST['Search']="";
}


If ($_POST['Search']!='Search Now' AND (isset($_POST['Select']) OR isset($_SESSION['SelectedStockItem']))) {

	if (isset($_POST['Select'])){
		$_SESSION['SelectedStockItem']= $_POST['Select'];
		$StockID = $_POST['Select'];
		unset($_POST['Select']);
	} else {
		$StockID = $_SESSION['SelectedStockItem'];
	}

	$result = DB_query("SELECT Description, MBflag FROM StockMaster WHERE StockID='" . $StockID . "'",$db);
	$myrow = DB_fetch_row($result);

	$Its_A_Kitset_Assembly_Or_Dummy=False;
	$Its_A_Dummy=False;
	$Its_A_Kitset=False;

	echo "<FONT SIZE=3>Stock code <B>" . $StockID . " - " . $myrow[0] . " </B> is currently selected. <br>Select one of the links below to operate using this item.</FONT><BR><BR>";
	if ($myrow[1]=="A" OR $myrow[1]=="K" OR $myrow[1]=="D"){
		$Its_A_Kitset_Assembly_Or_Dummy=True;
	}
	if ($myrow[1]=="K"){
		$Its_A_Kitset=True;
	}
	if ($myrow[1]=="D"){
		$Its_A_Dummy=True;
	}

//LINKS TO PAGES REQUIRING STOCK ID TO BE PASSED
	echo "<CENTER><TABLE WIDTH=90% COLSPAN=2 BORDER=2 CELLPADDING=4>";
	echo "<TR><TD WIDTH=33% class='tableheader'>Item Inquiries</TD><TD WIDTH=33% class='tableheader'>Item Maintenance</TD><TD WIDTH=33% class='tableheader'>Item Transactions</TD></TR>";
	echo "<TR><TD>";

	/*Stock Inquiry Options */

        echo "<A HREF='$rootpath/StockMovements.php?" . SID . "StockID=$StockID'>Show Stock Movements</A><BR>";

	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
        echo "<A HREF='$rootpath/StockStatus.php?" . SID . "StockID=$StockID'>Show Stock Status</A><BR>";
        echo "<A HREF='$rootpath/StockUsage.php?" .SID . "StockID=$StockID'>Show Stock Usage</A><BR>";
	}
        echo "<A HREF='$rootpath/SelectSalesOrder.php?" . SID . "SelectedStockItem=$StockID'>Search Outstanding Sales Orders</A><BR>";
        echo "<A HREF='$rootpath/SelectCompletedOrder.php?" .SID . "SelectedStockItem=$StockID'>Search Completed Sales Orders</A><BR>";
	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
		echo "<A HREF='$rootpath/PO_SelectOSPurchOrder.php?" .SID . "SelectedStockItem=$StockID'>Search Outstanding Purchase Orders</A><BR>";
		echo "<A HREF='$rootpath/PO_SelectPurchOrder.php?" .SID . "SelectedStockItem=$StockID'>Search All Purchase Orders</A><BR>";
		echo "<A HREF='$rootpath/$part_pics_dir/$StockID.jpg?" . SID ."'>Show Part Picture (if available)</A><BR>";
	}

	if ($Its_A_Dummy==False){
		echo "<A HREF='$rootpath/BOMInquiry.php?" .SID . "StockID=$StockID'>View Costed Bill Of Material</A><BR>";
		echo "<A HREF='$rootpath/WhereUsedInquiry.php?" .SID . "StockID=$StockID'>Where This Item Is Used</A><BR>";
	}
	echo "</TD><TD>";

	/*Stock Maintenance Options */

        echo "<A HREF='$rootpath/Stocks.php?" . SID . "StockID=$StockID'>Modify Stock Item Details</A><BR>";
	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
		echo "<A HREF='$rootpath/StockReorderLevel.php?" . SID . "StockID=$StockID'>Maintain Reorder Levels</A><BR>";
        	echo "<A HREF='$rootpath/StockCostUpdate.php?" . SID . "StockID=$StockID'>Maintain Standard Cost</A><BR>";
        	echo "<A HREF='$rootpath/PurchData.php?" . SID . "StockID=$StockID'>Maintain Purchasing Data</A><BR>";
	}
	if (! $Its_A_Kitset){
		echo "<A HREF='$rootpath/Prices.php?" . SID ."Item=$StockID'>Maintain Pricing</A><BR>";
        	if (isset($_SESSION['CustomerID']) AND $_SESSION['CustomerID']!="" AND Strlen($_SESSION['CustomerID'])>0){
			echo "<A HREF='$rootpath/Prices_Customer.php?" . SID . "Item=$StockID'>Special Prices for customer - " . $_SESSION['CustomerID'] . "</A><BR>";
        	}
	}
	echo "</TD><TD>";

	/* Stock Transactions */
	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
		echo "<A HREF='$rootpath/StockAdjustments.php?" . SID . "StockID=$StockID'>Quantity Adjustments</A><BR>";
        	echo "<A HREF='$rootpath/StockTransfers.php?" . SID . "StockID=$StockID'>Location Transfers</A><BR>";
	}


	echo "</TD></TR></TABLE>";

} //end of if

?>

</FORM>

<?php
include("includes/footer.inc");
?>

