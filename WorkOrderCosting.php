<?php
/* $Revision: 1.1 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Work Order Costing');
include('includes/header.inc');


echo '<A HREF="'. $rootpath . '/SelectWorkOrder.php?' . SID . '">' . _('Back to Work Orders'). '</A><BR>';

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';

if (!isset($_REQUEST['WO'])) {
	/* This page can only be called with a work order number */
	echo '<CENTER><A HREF="' . $rootpath . '/SelectWorkOrder.php?' . SID . '">'.
		_('Select a work order').'</A></CENTER>';
	prnMsg(_('This page can only be opened if a work order has been selected.'),'info');
	include ('includes/footer.inc');
	exit;
} else {
	echo '<input type="hidden" name="WO" value=' .$_REQUEST['WO'] . '>';
	$_POST['WO']=$_REQUEST['WO'];
}



$ErrMsg = _('Could not retrieve the details of the selected work order');
$WOResult = DB_query("SELECT workorders.loccode,
						 locations.locationname,
						 workorders.requiredby,
						 workorders.startdate,
						 workorders.closed,
						FROM workorders INNER JOIN locations
						ON workorders.loccode=locations.loccode
						WHERE workorders.wo=" . DB_escape_string($_POST['WO']),
						$db,
						$ErrMsg);

if (DB_num_rows($WOResult)==0){
	prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
	include('includes/footer.inc');
	exit;
}
$WORow = DB_fetch_array($WOResult);


echo '<center><table cellpadding=2 border=0>
	<tr><td class="label">' . _('Work order') . ':</td><td>' . $_POST['WO'] .'</td></tr>
	 <tr><td class="label">' . _('Manufactured at') . ':</td><td>' . $WORow['locationname'] . '</td><td class="label">' . _('Required By') . ':</td><td>' . ConvertSQLDate($WORow['requiredby']) . '</td></tr>';


$WOItemsResult = DB_query("SELECT woitems.stockid,
									stockmaster.description,
						  			stockmaster.decimalplaces,
						 			stockmaster.units,
						 			woitems.qtyreqd,
						 			woitems.qtyrecd
						 	FROM woitems INNER JOIN stockmaster
						 	ON woitems.stockid=stockmaster.stockid
						 	WHERE woitems.wo=". DB_escape_string($_POST['WO']),
						$db,
						$ErrMsg);

echo  '<tr><td>' . _('Item') . '</td>
		<td>' . _('Description') . '</td>
		<td>' . _('Quantity Required') . '</td>
		<td>' . _('Units') . '</td>
		<td>' . _('Quantity Received') . '</td></tr>';

while ($WORow = DB_fetch_array($WOItemsResult)){

	 echo '<tr><td>' . $WORow['stockid'] . '</td>
	 			<td>' . $WORow['description'] . '</td>
	 			<td align=right>' . number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td>
	 			<td>' . $WORow['units'] . '</td>
	 			<td align=right>' . number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td>
	 			</tr>';

}
echo '</table>
	<hr>
	<table>';


echo '<tr><td "tableheader">' . _('Item') . '</td>
			<td "tableheader">' . _('Description') . '</td>
			<td "tableheader">' . _('Qty Reqd') . '</td>
			<td "tableheader">' . _('Cost Reqd') . '</td>
			<td "tableheader">' . _('Date Issued') . '</td>
			<td "tableheader">' . _('Issued Qty') . '</td>
			<td "tableheader">' . _('Issued Cost') . '</td>
			<td "tableheader">' . _('Usage Variance') . '</td>
			<td "tableheader">' . _('Cost Variance') . '</td>
			</tr>';

$RequirementsResult = DB_query("SELECT worequirements.stockid,
										stockmaster.description,
										stockmaster.decimalplaces,
										SUM(qtypu*woitems.qtyrecd) AS requiredqty,
										SUM(stdcost*woitems.qtyrecd*qtypu) AS expectedcost
									FROM worequirements INNER JOIN stockmaster
									ON worequirements.stockid=stockmaster.stockid
									INNER JOIN woitems ON woitems.stockid=worequirements.parentstockid
									WHERE wo=" . $_POST['WO'] . "
									GROUP BY worequirements.stockid,
											stockmaster.description,
											stockmaster.decimalplaces",
									$db);
$k=0;
$TotalUsageVar =0;
$TotalCostVar =0;
$RequiredItems =array();

while ($RequirementsRow = DB_fetch_array($RequirmentsResult)){
	$RequiredItems[] = $RequirementsRow['stockid'];
	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
		$k=0;
	} else {
		echo '<tr bgcolor="#EEEEEE">';
		$k++;
	}

	echo '<td>' .  $RequirementsRow['stockid'] . '</td>
			<td>' .  $RequirementsRow['description'] . '</td>';

	$IssuesResult = DB_query("SELECT trandate,
										qty,
										standardcost
								FROM stockmoves
								WHERE stockmoves.type=28
								AND reference = '" . DB_escape_string($_POST['WO']) . "'
								AND stockid = '" . $RequirementsRow['stockid'] . "'",
								$db,
								_('Could not retrieve the issues of the item because:'));
	$IssueQty =0;
	$IssueCost=0;

	if (DB_num_rows($IssuesResult)>0){
		while ($IssuesRow = DB_fetch_array($IssuesResult)){
			if ($k==1){
				echo '<tr bgcolor="#CCCCCC">';
			} else {
				echo '<tr bgcolor="#EEEEEE">';
			}
			echo '<td colspan=4></td><td>' . ConvertSQLDate($IssuesRow['trandate']) . '</td>
									<td align="right">' . number_format(-$IssuesRow['qty'],$RequirementsRow['decimalplaces']) . '</td>
									<td align="right">' . number_format(-($IssuesRow['qty']*$IssuesRow['standardcost']),2) . '</td></tr>';
			$IssueQty -= $IssuesRow['qty'];// because qty for the stock movement will be negative
			$IssueCost -= ($IssuesRow['qty']*$IssuesRow['standardcost']);

		}
		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
		} else {
			echo '<tr bgcolor="#EEEEEE">';
		}
		echo '<td colspan="9"><hr></td></tr>';
	}
	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
	} else {
		echo '<tr bgcolor="#EEEEEE">';
	}

	if ($IssueQty != 0){
		$CostVar = $IssueQty *(($RequirementsRow['expectedcost']/$RequirementsRow['requiredqty']) -($IssueCost/$IssueQty));
	} else {
		$CostVar = 0;
	}

	$UsageVar =($RequirementsRow['requiredqty']-$IssueQty)*($RequirementsRow['expectedcost']/$RequirementsRow['requiredqty']);

	echo '<td colspan="2"></td><td align="right">'  . number_format($RequirementsRow['requiredqty'],$RequirementsRow['decimalplaces']) . '</td>
								<td align="right">' . number_format($RequirementsRow['expectedcost'],2) . '</td>
								<td></td>
								<td align="right">' . number_format($IssueQty,$RequirementsRow['decimalplaces']) . '</td>
								<td align="right">' . number_format($IssueCost,2) . '</td>
								<td align="right">' . number_format($UsageVar,2) . '</td>
								<td align="right">' . number_format($CostVar,2) . '</td></tr>';
	$TotalCostVar += $CostVar;
	$TotalUsageVar += $UsageVar;
	if ($k==1){
		echo '<tr bgcolor="#CCCCCC">';
	} else {
		echo '<tr bgcolor="#EEEEEE">';
	}
	echo '<td colspan="9"><hr></td>';
}


//Now need to run through the issues to the work order that weren't in the requirements






echo '</table>';


echo '<hr>';

	$SQL="SELECT categoryid,
			categorydescription
			FROM stockcategory
			WHERE stocktype='F' OR stocktype='D'
			ORDER BY categorydescription";
		$result1 = DB_query($SQL,$db);

	echo '<table><tr><td><font size=2>' . _('Select a stock category') . ':</FONT><SELECT NAME="StockCat">';

	if (!isset($_POST['StockCat'])){
		echo "<OPTION SELECTED VALUE='All'>" . _('All');
		$_POST['StockCat'] ='All';
	} else {
		echo "<OPTION VALUE='All'>" . _('All');
	}

	while ($myrow1 = DB_fetch_array($result1)) {

		if ($_POST['StockCat']==$myrow1['categoryid']){
			echo '<OPTION SELECTED VALUE=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
		} else {
			echo '<OPTION VALUE='. $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
		}
	}
	?>

	</SELECT>
	<TD><FONT SIZE=2><?php echo _('Enter text extracts in the'); ?> <B><?php echo _('description'); ?></B>:</FONT></TD>
	<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25 VALUE="<?php if (isset($_POST['Keywords'])) echo $_POST['Keywords']; ?>"></TD></TR>
	<TR><TD></TD>
			<TD><FONT SIZE 3><B><?php echo _('OR'); ?> </B></FONT><FONT SIZE=2><?php echo _('Enter extract of the'); ?> <B><?php echo _('Stock Code'); ?></B>:</FONT></TD>
		<TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18 VALUE="<?php if (isset($_POST['StockCode'])) echo $_POST['StockCode']; ?>"></TD>
			</TR>
			</TABLE>
			<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>">

	<script language='JavaScript' type='text/javascript'>

		document.forms[0].StockCode.select();
		document.forms[0].StockCode.focus();

	</script>

	<?php
	echo '</CENTER>';

	if (isset($SearchResult)) {

		if (DB_num_rows($SearchResult)>1){

			echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>';
			$TableHeader = '<TR><TD class="tableheader">' . _('Code') . '</TD>
						<TD class="tableheader">' . _('Description') . '</TD>
						<TD class="tableheader">' . _('Units') . '</TD></TR>';
			echo $TableHeader;
			$j = 1;
			$k=0; //row colour counter
			$ItemCodes = array();
			for ($i=1;$i<=$NumberOfOutputs;$i++){
				$ItemCodes[] =$_POST['OutputItem'.$i];
			}

			while ($myrow=DB_fetch_array($SearchResult)) {

				if (!in_array($myrow['stockid'],$ItemCodes)){
					if (function_exists('imagecreatefrompng') ){
						$ImageSource = '<IMG SRC="GetStockImage.php?SID&automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . urlencode($myrow['stockid']). '&text=&width=64&height=64">';
					} else {
						if(file_exists($_SERVER['DOCUMENT_ROOT'] . $rootpath. '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg')) {
							$ImageSource = '<IMG SRC="' .$_SERVER['DOCUMENT_ROOT'] . $rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg">';
						} else {
							$ImageSource = _('No Image');
						}
					}

					if ($k==1){
						echo '<tr bgcolor="#CCCCCC">';
						$k=0;
					} else {
						echo '<tr bgcolor="#EEEEEE">';
						$k=1;
					}

					$IssueLink = $_SERVER['PHP_SELF'] . '?' . SID . '&WO=' . $_POST['WO'] . '&StockID=' . $_POST['StockID'] . '&IssueItem=' . $myrow['stockid'] . '&FromLocation=' . $_POST['FromLocation'];
					printf("<TD><FONT SIZE=1>%s</FONT></TD>
							<TD><FONT SIZE=1>%s</FONT></TD>
							<TD><FONT SIZE=1>%s</FONT></TD>
							<TD>%s</TD>
							<TD><FONT SIZE=1><A HREF='%s'>"
							. _('Add to Work Order') . '</A></FONT></TD>
							</TR>',
							$myrow['stockid'],
							$myrow['description'],
							$myrow['units'],
							$ImageSource,
							$IssueLink);

					$j++;
					If ($j == 25){
						$j=1;
						echo $TableHeader;
					} //end of page full new headings if
				} //end if not already on work order
			}//end of while loop
		} //end if more than 1 row to show
		echo '</TABLE>';
	}#end if SearchResults to show
} else{ //There is an item selected to issue

	echo '<hr>';
	//need to get some details about the item to issue
	$sql = "SELECT description,
			decimalplaces,
			units,
			controlled,
			serialised
		FROM stockmaster
		WHERE stockid='" . $_POST['IssueItem'] . "'";
	$ErrMsg = _('Could not get the detail of the item being issued because');
	$IssueItemResult = DB_query($sql,$db,$ErrMsg);
	$IssueItemRow = DB_fetch_array($IssueItemResult);

	echo '<table>
		<tr><td class="label">' . _('Issuing') . ':</td>
			<td>' . $_POST['IssueItem'] . ' - ' . $IssueItemRow['description'] .'</td>
			<td class="label">' . _('Units') . ':</td><td>' . $IssueItemRow['units'] .'</td></tr>
		</table>';

	echo '<table>';

	//Now Setup the form for entering quantites of the item to be issued to the WO
	if ($IssueItemRow['controlled']==1){ //controlled

		if ($IssueItemRow['serialised']==1){ //serialised
			echo '<tr><td class="tableheader">' . _('Serial Numbers Issued') . '</td></tr>';


			$SerialNoResult = DB_query("SELECT serialno
							FROM stockserialitems
							WHERE stockid='" . $_POST['StockID'] . "'
							AND loccode='" . $_POST['FromLocation'] . "'",
						$db,_('Could not retrieve the serial numbers available at the location specified because'));
			if (DB_num_rows($SerialNoResult)==0){
				echo '<tr><td>' . _('There are no serial numbers at this location to issue') . '</td></tr>';
				echo '<tr><td align="center"><input type=submit name="Retry" value="' . _('Reselect Location or Issued Item') . '"></td></tr>';
			} else {
				echo '<tr><td><select name="SerialNos"[] multiple>';
				while ($SerialNoRow = DB_fetch_array($SerialNoResult)){
					if (in_array($SerialNoRow['serialno'],$_POST['SerialNos'])){
						echo '<option selected value="' . $SerialNoRow['serialno'] . '">' . $SerialNoRow['serialno'] . '</option>';
					} else {
						echo '<option value="' . $SerialNoRow['serialno'] . '">' . $SerialNoRow['serialno'] . '</option>';
					}
				}
				echo '</select></td></tr>';
				echo '<input type="hidden" name="IssueItem" value="' . $_POST['IssueItem'] . '">';
				echo '<tr><td align="center"><input type=submit name="Process" value="' . _('Process Items Issued') . '"></td></tr>';
			}
		} else { //controlled but not serialised - just lot/batch control
			echo '<tr><td colspan="2" class="tableheader">' . _('Batch/Lots Issued') . '</td></tr>';
			for ($i=0;$i<15;$i++){
				echo '<tr><td><input type="textbox" name="BatchRef' . $i .'" ';
				echo '></td>
				      <td><input type="textbox" name="Qty' . $i .'"></td></tr>';
			}
			echo '<input type="hidden" name="IssueItem" value="' . $_POST['IssueItem'] . '">';
			echo '<tr><td align="center" colspan=2><input type=submit name="Process" value="' . _('Process Items Issued') . '"></td></tr>';
		} //end of lot/batch control
	} else { //not controlled - an easy one!
		echo '<input type="hidden" name="IssueItem" value="' . $_POST['IssueItem'] . '">';
		echo '<tr><td>' . _('Quantity Issued') . ':</td>
			  <td><input type="textbox" name="Qty"></tr>';
		echo '<tr><td align="center"><input type=submit name="Process" value="' . _('Process Items Issued') . '"></td></tr>';
	}
} //end if selecting new item to issue or entering the issued item quantites
echo '</table>';
echo '</FORM>';

include('includes/footer.inc');
?>