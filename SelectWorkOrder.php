<?php

/* $Revision: 1.5 $ */

$PageSecurity = 2;

include('includes/session.inc');
$title = _('Search Work Orders');
include('includes/header.inc');

echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] .'?' .SID . ' METHOD=POST>';


If (isset($_POST['ResetPart'])){
     unset($_REQUEST['SelectedStockItem']);
}

If (isset($_REQUEST['WO']) AND $_REQUEST['WO']!='') {
	$_REQUEST['WO'] = trim($_REQUEST['WO']);
	if (!is_numeric($_REQUEST['WO'])){
		  prnMsg(_('The work order number entered MUST be numeric'),'warn');
		  unset ($_REQUEST['WO']);
		  include('includes/footer.inc');
		  exit;
	} else {
		echo _('Work Order Number') . ' - ' . $_REQUEST['WO'];
	}
} else {
	if (isset($_REQUEST['SelectedStockItem'])) {
		 echo _('for the item') . ': ' . $_REQUEST['SelectedStockItem'] . ' ' . _('and') . " <input type=hidden name='SelectedStockItem' value='" . $_REQUEST['SelectedStockItem'] . "'>";
	}
}

if (isset($_POST['SearchParts'])){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo _('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$i=0;
		$SearchString = '%';
		while (strpos($_POST['Keywords'], ' ', $i)) {
			$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
			$i=strpos($_POST['Keywords'],' ',$i) +1;
		}
		$SearchString = $SearchString . substr($_POST['Keywords'],$i).'%';

		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				SUM(locstock.quantity) AS qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.description " . LIKE . " '" . $SearchString . "'
			AND stockmaster.categoryid='" . $_POST['StockCat']. "'
			AND stockmaster.mbflag='M'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (isset($_POST['StockCode'])){
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				sum(locstock.quantity) as qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
			AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
			AND stockmaster.mbflag='M'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";

	 } elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		$SQL = "SELECT stockmaster.stockid,
				stockmaster.description,
				sum(locstock.quantity) as qoh,
				stockmaster.units
			FROM stockmaster,
				locstock
			WHERE stockmaster.stockid=locstock.stockid
			AND stockmaster.categoryid='" . $_POST['StockCat'] ."'
			AND stockmaster.mbflag='M'
			GROUP BY stockmaster.stockid,
				stockmaster.description,
				stockmaster.units
			ORDER BY stockmaster.stockid";
	 }

	$ErrMsg =  _('No items were returned by the SQL because');
	$DbgMsg = _('The SQL used to retrieve the searched parts was');
	$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
}

if (isset($_POST['StockID'])){
	$StockID = trim(strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID = trim(strtoupper($_GET['StockID']));
}

if (!isset($StockID)) {

     /* Not appropriate really to restrict search by date since may miss older
     ouststanding orders
	$OrdersAfterDate = Date('d/m/Y',Mktime(0,0,0,Date('m')-2,Date('d'),Date('Y')));
     */

	if ($_REQUEST['WO']=='' OR !$_REQUEST['WO']){

		echo _('Work Order number') . ": <INPUT type=text name='WO' MAXLENGTH =8 SIZE=9>&nbsp " . _('Processing at') . ":<SELECT name='StockLocation'> ";

		$sql = 'SELECT loccode, locationname FROM locations';

		$resultStkLocs = DB_query($sql,$db);

		while ($myrow=DB_fetch_array($resultStkLocs)){
			if (isset($_POST['StockLocation'])){
				if ($myrow['loccode'] == $_POST['StockLocation']){
				     echo "<OPTION SELECTED Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				} else {
				     echo "<OPTION Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
				}
			} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
				 echo "<OPTION SELECTED Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			} else {
				 echo "<OPTION Value='" . $myrow['loccode'] . "'>" . $myrow['locationname'];
			}
		}

		echo '</SELECT> &nbsp&nbsp';
		echo '<SELECT NAME="ClosedOrOpen">';

		if ($_GET['ClosedOrOpen']=='Closed_Only'){
			$_POST['ClosedOrOpen']='Closed_Only';
		}

		if ($_POST['ClosedOrOpen']=='Closed_Only'){
			echo '<OPTION SELECTED VALUE="Closed_Only">' . _('Closed Work Orders Only');
			echo '<OPTION VALUE="Open_Only">' . _('Open Work Orders Only');
		} else {
			echo '<OPTION VALUE="Closed_Only">' . _('Closed Work Orders Only');
			echo '<OPTION SELECTED VALUE="Open_Only">' . _('Open Work Orders Only');
		}

		echo '</SELECT> &nbsp&nbsp';
		echo "<INPUT TYPE=SUBMIT NAME='SearchOrders' VALUE='" . _('Search') . "'>";
    	echo '&nbsp;&nbsp;<a href="' . $rootpath . '/WorkOrderEntry.php?' . SID . '">' . _('New Work Order') . '</a>';
	}

	$SQL='SELECT categoryid,
			categorydescription
		FROM stockcategory
		ORDER BY categorydescription';

	$result1 = DB_query($SQL,$db);

	echo '<HR>
		<FONT SIZE=1>' . _('To search for work orders for a specific item use the item selection facilities below') . "</FONT>
		<INPUT TYPE=SUBMIT NAME='SearchParts' VALUE='" . _('Search Items Now') . "'>
		<INPUT TYPE=SUBMIT NAME='ResetPart' VALUE='" . _('Show All') . "'>
      <TABLE>
      	<TR>
      		<TD><FONT SIZE=1>" . _('Select a stock category') . ":</FONT>
      			<SELECT NAME='StockCat'>";

	while ($myrow1 = DB_fetch_array($result1)) {
		echo "<OPTION VALUE='". $myrow1['categoryid'] . "'>" . $myrow1['categorydescription'];
	}

      echo '</SELECT>
      		<TD><FONT SIZE=1>' . _('Enter text extract(s) in the description') . ":</FONT></TD>
      		<TD><INPUT TYPE='Text' NAME='Keywords' SIZE=20 MAXLENGTH=25></TD>
	</TR>
      	<TR><TD></TD>
      		<TD><FONT SIZE 3><B>" . _('OR') . ' </B></FONT><FONT SIZE=1>' . _('Enter extract of the Stock Code') . "</B>:</FONT></TD>
      		<TD><INPUT TYPE='Text' NAME='StockCode' SIZE=15 MAXLENGTH=18></TD>
      	</TR>
      </TABLE>
      <HR>";

If (isset($StockItemsResult)) {

	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>';
	$TableHeader = "<TR>
				<TD class='tableheader'>" . _('Code') . "</TD>
				<TD class='tableheader'>" . _('Description') . "</TD>
				<TD class='tableheader'>" . _('On Hand') . "</TD>
				<TD class='tableheader'>" . _('Units') . "</TD>
			</TR>";
	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		printf("<td><INPUT TYPE=SUBMIT NAME='SelectedStockItem' VALUE='%s'</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td>
			</tr>",
			$myrow['stockid'],
			$myrow['description'],
			$myrow['qoh'],
			$myrow['units']);

		$j++;
		If ($j == 12){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</TABLE>';

}
//end if stock search results to show
  else {

	//figure out the SQL required from the inputs available
	if ($_POST['ClosedOrOpen']=='Open_Only'){
		$ClosedOrOpen = 0;
	} else {
		$ClosedOrOpen = 1;
	}
	if (isset($_REQUEST['WO']) && $_REQUEST['WO'] !='') {
			$SQL = "SELECT workorders.wo,
					woitems.stockid,
					stockmaster.description,
					woitems.qtyreqd,
					woitems.qtyrecd,
					workorders.requiredby
					FROM workorders
					INNER JOIN woitems ON workorders.wo=woitems.wo
					INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
					WHERE workorders.closed=" . $ClosedOrOpen . "
					AND workorders.wo=". $_REQUEST['WO'] ."
					ORDER BY workorders.wo,
							 woitems.stockid";
	} else {
	      /* $DateAfterCriteria = FormatDateforSQL($OrdersAfterDate); */

			if (isset($_REQUEST['SelectedStockItem'])) {
				$SQL = "SELECT workorders.wo,
					woitems.stockid,
					stockmaster.description,
					woitems.qtyreqd,
					woitems.qtyrecd,
					workorders.requiredby
					FROM workorders
					INNER JOIN woitems ON workorders.wo=woitems.wo
					INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
					WHERE workorders.closed=" . $ClosedOrOpen . "
					AND woitems.stockid='". $_REQUEST['SelectedStockItem'] ."'
					AND workorders.loccode='" . $_POST['StockLocation'] . "'
					ORDER BY workorders.wo,
							 woitems.stockid";
			} else {
				$SQL = "SELECT workorders.wo,
					woitems.stockid,
					stockmaster.description,
					woitems.qtyreqd,
					woitems.qtyrecd,
					workorders.requiredby
					FROM workorders
					INNER JOIN woitems ON workorders.wo=woitems.wo
					INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
					WHERE workorders.closed=" . $ClosedOrOpen . "
					AND workorders.loccode='" . $_POST['StockLocation'] . "'
					ORDER BY workorders.wo,
							 woitems.stockid";
			}
	} //end not order number selected

	$ErrMsg = _('No works orders were returned by the SQL because');
	$WorkOrdersResult = DB_query($SQL,$db,$ErrMsg);

	/*show a table of the orders returned by the SQL */

	echo '<TABLE CELLPADDING=2 COLSPAN=7 WIDTH=100%>';


	$tableheader = "<TR>
				<TD class='tableheader'>" . _('Modify') . "</TD>
				<TD class='tableheader'>" . _('Status') . "</TD>
				<TD class='tableheader'>" . _('Receive') . "</TD>
				<TD class='tableheader'>" . _('Issue To') . "</TD>
				<TD class='tableheader'>" . _('Costing') . "</TD>
				<TD class='tableheader'>" . _('Item') . "</TD>
				<TD class='tableheader'>" . _('Quantity Required') . "</TD>
				<TD class='tableheader'>" . _('Quantity Received') . "</TD>
				<TD class='tableheader'>" . _('Quantity Outstanding') . "</TD>
				<TD class='tableheader'>" . _('Required Date') . "</TD>
				</TR>";

	echo $tableheader;

	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($WorkOrdersResult)) {

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		$ModifyPage = $rootpath . "/WorkOrderEntry.php?" . SID . '&WO=' . $myrow['wo'];
		$Status_WO = $rootpath . '/WorkOrderStatus.php?' . SID . '&WO=' .$myrow['wo'] . '&StockID=' . $myrow['stockid'];
		$Receive_WO = $rootpath . '/WorkOrderReceive.php?' . SID . '&WO=' .$myrow['wo'] . '&StockID=' . $myrow['stockid'];
		$Issue_WO = $rootpath . '/WorkOrderIssue.php?' . SID . '&WO=' .$myrow['wo'] . '&StockID=' . $myrow['stockid'];
		$Costing_WO =$rootpath . '/WorkOrderCosting.php?' . SID . '&WO=' .$myrow['wo'];

		$FormatedRequiredByDate = ConvertSQLDate($myrow['requiredby']);


		printf("<td><A HREF='%s'>%s</A></td>
				<td><A HREF='%s'>" . _('Status') . "</A></td>
				<td><A HREF='%s'>" . _('Receive') . "</A></td>
				<td><A HREF='%s'>" . _('Issue To') . "</A></td>
				<td><A HREF='%s'>" . _('Costing') . "</A></td>
				<td>%s - %s</td>
				<td align=right>%s</td>
				<td align=right>%s</td>
				<td align=right>%s</td>
				<td>%s</td>
				</tr>",
				$ModifyPage,
				$myrow['wo'],
				$Status_WO,
				$Receive_WO,
				$Issue_WO,
				$Costing_WO,
				$myrow['stockid'],
				$myrow['description'],
				$myrow['qtyreqd'],
				$myrow['qtyrecd'],
				$myrow['qtyreqd']-$myrow['qtyrecd'],
				$FormatedRequiredByDate);

		$j++;
		If ($j == 12){
			$j=1;
			echo $tableheader;
		}
	//end of page full new headings if
	}
	//end of while loop

	echo '</TABLE>';
}

?>
</FORM>

<?php }

include('includes/footer.inc');
?>