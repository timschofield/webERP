<?php
/* $Revision: 1.13 $ */

$PageSecurity = 2;

include('includes/session.inc');

$title = _('Search Inventory Items');

include('includes/header.inc');

$msg='';

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

// Always show the search facilities

$SQL='SELECT categoryid,
		categorydescription
	FROM stockcategory
	ORDER BY categorydescription';

$result1 = DB_query($SQL,$db);
if (DB_num_rows($result1)==0){
	echo '<P><FONT SIZE=4 COLOR=RED>' . _('Problem Report') . ':</FONT><BR>' . _('There are no stock categories currently defined please use the link below to set them up');
	echo '<BR><A HREF="' . $rootpath . '/StockCategories.php?' . SID .'">' . _('Define Stock Categories') . '</A>';
	exit;
}

?>
<CENTER>
<FORM ACTION="<?php echo $_SERVER['PHP_SELF'] . '?' . SID; ?>" METHOD=POST>
<B><?php echo $msg; ?></B>
<TABLE>
<TR>
<TD><?php echo _('In Stock Category'); ?>:
<SELECT NAME="StockCat">
<?php
	if (!isset($_POST['StockCat'])){
		$_POST['StockCat']="";
	}
	if ($_POST['StockCat']=="All"){
		echo '<OPTION SELECTED VALUE="All">' . _('All');
	} else {
		echo '<OPTION VALUE="All">' . _('All');
	}
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['categoryid']==$_POST['StockCat']){
			echo '<OPTION SELECTED VALUE="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
		} else {
			echo '<OPTION VALUE="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
		}
	}
?>

</SELECT>
<TD><?php echo _('Text in the'); ?> <B><?php echo _('description'); ?></B>:</TD>
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
<TD><FONT SIZE 3><B><?php echo _('OR'); ?> </B></FONT><?php echo _('Text in the'); ?> <B><?php echo _('Stock Code'); ?></B>:</TD>
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
<INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>"></CENTER>
<HR>


<?php

// end of showing search facilities

// query for list of record(s)

if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){

	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])){
		// if Search then set to first page
    $_POST['PageOffset'] = 1;
	}

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);
		$i=0;
		$SearchString = '%';
		while (strpos($_POST['Keywords'], ' ', $i)) {
			$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
			$i=strpos($_POST['Keywords'],' ',$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

		if ($_POST['StockCat'] == 'All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.mbflag
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.description " . LIKE . " '$SearchString'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.mbflag
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND description " .  LIKE . " '$SearchString'
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";
		}
	} elseif (isset($_POST['StockCode'])){

		$_POST['StockCode'] = strtoupper($_POST['StockCode']);
		if ($_POST['StockCat'] == 'All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";

		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					sum(locstock.quantity) as qoh,
					stockmaster.units
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";
		}

	} elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		if ($_POST['StockCat'] == 'All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description, 
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";
		}
	}

	$ErrMsg = _('No stock items were returned by the SQL because');
	$Dbgmsg = _('The SQL that returned an error was');
	$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
	
	if (DB_num_rows($result)==0){
		prnMsg(_('No stock items were returned by this search please re-enter alternative criteria to try again'),'info');
	} elseif (DB_num_rows($result)==1){ /*autoselect it to avoid user hitting another keystroke */
		$myrow = DB_fetch_row($result);
		$_POST['Select'] = $myrow[0];
	}
	unset($_POST['Search']);
}

// end query for list of records

// display list if there is more than one record

If (isset($result) AND !isset($_POST['Select']) ) {
  // If the user hit the search button and there is more than one item to show
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

  if ($_POST['PageOffset']>$ListPageMax){
  	$_POST['PageOffset'] = $ListPageMax;
  }
  echo '<CENTER><BR>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
  <INPUT TYPE=SUBMIT NAME="Go" VALUE="<?php echo _('Go'); ?>">
  <INPUT TYPE=SUBMIT NAME="Previous" VALUE="<?php echo _('Previous'); ?>">
  <INPUT TYPE=SUBMIT NAME="Next" VALUE="<?php echo _('Next'); ?>">
<?php

  echo '<br><br>';

	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>';
	$tableheader = '<TR>
				<TD class="tableheader">' . _('Code') . '</TD>
				<TD class="tableheader">' . _('Description') . '</TD>
				<TD class="tableheader">' . _('Total Qty On Hand') . '</TD>
				<TD class="tableheader">' . _('Units') . '</TD>
			</TR>';
	echo $tableheader;

	$j = 1;

	$k = 0; //row counter to determine background colour

  $RowIndex = 0;

  if (DB_num_rows($result)<>0){
 	DB_data_seek($result, ($_POST['PageOffset']-1)*$_SESSION['DisplayRecordsMax']);
  }

	while (($myrow=DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k++;
		}

		if ($myrow['mbflag']=='D') {
			$qoh = 'N/A';
		} else {
			$qoh = number_format($myrow["qoh"],1);
		}

		printf("<td><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</td>
            		<td>%s</td>
            		<td ALIGN=RIGHT>%s</td>
            		<td>%s</td>
            		</tr>", 
            		$myrow['stockid'], 
            		$myrow['description'], 
            		$qoh, 
            		$myrow['units']);

		$j++;
		If ($j == 12 AND ($RowIndex+1 != $_SESSION['DisplayRecordsMax'])){
			$j=1;
			echo $tableheader;

		}
    $RowIndex = $RowIndex + 1;
//end of page full new headings if
	}
//end of while loop

	echo '</TABLE>';

}
// end display list if there is more than one record

// displays item options if there is one and only one selected

If (!isset($_POST['Search']) AND (isset($_POST['Select']) OR isset($_SESSION['SelectedStockItem']))) {

	if (isset($_POST['Select'])){
		$_SESSION['SelectedStockItem']= $_POST['Select'];
		$StockID = $_POST['Select'];
		unset($_POST['Select']);
	} else {
		$StockID = $_SESSION['SelectedStockItem'];
	}

	$result = DB_query("SELECT stockmaster.description, stockmaster.mbflag FROM stockmaster WHERE stockid='" . $StockID . "'",$db);
	$myrow = DB_fetch_row($result);

	$Its_A_Kitset_Assembly_Or_Dummy=False;
	$Its_A_Dummy=False;
	$Its_A_Kitset=False;

	echo '<br><FONT SIZE=3>' . _('Stock code') . ' <B>' . $StockID . ' - ' . $myrow[0] . ' </B> ' . _('is currently selected') . '. <br>' . _('Select one of the links below to operate using this item') . '.</FONT><BR><BR>';
	if ($myrow[1]=='A' OR $myrow[1]=='K' OR $myrow[1]=='D'){
		$Its_A_Kitset_Assembly_Or_Dummy=True;
	}
	if ($myrow[1]=='K'){
		$Its_A_Kitset=True;
	}
	if ($myrow[1]=='D'){
		$Its_A_Dummy=True;
	}

  // options (links) to pages. This requires stock id also to be passed.
	echo '<CENTER><TABLE WIDTH=90% COLSPAN=2 BORDER=2 CELLPADDING=4>';
	echo '<TR>
		<TD WIDTH=33% class="tableheader">' . _('Item Inquiries') . '</TD>
		<TD WIDTH=33% class="tableheader">' . _('Item Maintenance') . '</TD>
		<TD WIDTH=33% class="tableheader">' . _('Item Transactions') . '</TD>
	</TR>';
	echo '<TR><TD>';

	/*Stock Inquiry Options */

        echo '<A HREF="' . $rootpath . '/StockMovements.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Stock Movements') . '</A><BR>';

	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
        echo '<A HREF="' . $rootpath . '/StockStatus.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Stock Status') . '</A><BR>';
        echo '<A HREF="' . $rootpath . '/StockUsage.php?' . SID . '&StockID=' . $StockID . '">' . _('Show Stock Usage') . '</A><BR>';
	}
        echo '<A HREF="' . $rootpath . '/SelectSalesOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Sales Orders') . '</A><BR>';
        echo '<A HREF="' . $rootpath . '/SelectCompletedOrder.php?' .SID . '&SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</A><BR>';
	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
		echo '<A HREF="' . $rootpath . '/PO_SelectOSPurchOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Purchase Orders') . '</A><BR>';
		echo '<A HREF="' . $rootpath . '/PO_SelectPurchOrder.php?' . SID . '&SelectedStockItem=' . $StockID . '">' . _('Search All Purchase Orders') . '</A><BR>';
		echo '<A HREF="' . $rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg?' . SID . '">' . _('Show Part Picture (if available)') . '</A><BR>';
	}

	if ($Its_A_Dummy==False){
		echo '<A HREF="' . $rootpath . '/BOMInquiry.php?' . SID . '&StockID=' . $StockID . '">' . _('View Costed Bill Of Material') . '</A><BR>';
		echo '<A HREF="' . $rootpath . '/WhereUsedInquiry.php?' . SID . '&StockID=' . $StockID . '">' . _('Where This Item Is Used') . '</A><BR>';
	}
	echo '</TD><TD>';

	/*Stock Maintenance Options */

        echo '<A HREF="' . $rootpath . '/Stocks.php?' . SID . '&StockID=' . $StockID . '">' . _('Modify Stock Item Details') . '</A><BR>';
	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
		echo '<A HREF="' . $rootpath . '/StockReorderLevel.php?' . SID . '&StockID=' . $StockID . '">' . _('Maintain Reorder Levels') . '</A><BR>';
        	echo '<A HREF="' . $rootpath . '/StockCostUpdate.php?' . SID . '&StockID=' . $StockID . '">' . _('Maintain Standard Cost') . '</A><BR>';
        	echo '<A HREF="' . $rootpath . '/PurchData.php?' . SID . '&StockID=' . $StockID . '">' . _('Maintain Purchasing Data') . '</A><BR>';
	}
	if (! $Its_A_Kitset){
		echo '<A HREF="' . $rootpath . '/Prices.php?' . SID . '&Item=' . $StockID . '">' . _('Maintain Pricing') . '</A><BR>';
        	if (isset($_SESSION['CustomerID']) AND $_SESSION['CustomerID']!="" AND Strlen($_SESSION['CustomerID'])>0){
			echo '<A HREF="' . $rootpath . '/Prices_Customer.php?' . SID . '&Item=' . $StockID . '">' . _('Special Prices for customer') . ' - ' . $_SESSION['CustomerID'] . '</A><BR>';
        	}
	}
	echo '</TD><TD>';

	/* Stock Transactions */
	if ($Its_A_Kitset_Assembly_Or_Dummy==False){
		echo '<A HREF="' . $rootpath . '/StockAdjustments.php?' . SID . '&StockID=' . $StockID . '">' . _('Quantity Adjustments') . '</A><BR>';
        	echo '<A HREF="' . $rootpath . '/StockTransfers.php?' . SID . '&StockID=' . $StockID . '">' . _('Location Transfers') . '</A><BR>';
	}


	echo '</TD></TR></TABLE>';

} //end of if

// end displaying item options if there is one and only one record

?>
</CENTER>
</FORM>
<script language="JavaScript" type="text/javascript">
    //<![CDATA[
            <!--
            document.forms[0].StockCode.select();
            document.forms[0].StockCode.focus();
            //-->
    //]]>
</script>

<?php
include('includes/footer.inc');
?>