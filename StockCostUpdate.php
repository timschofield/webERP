<?php


$UpdateSecurity =10;

include('includes/session.php');
$Title = _('Stock Cost Update');
$ViewTopic = 'Inventory';
$BookMark = '';
include('includes/header.php');
include('includes/SQL_CommonFunctions.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID =trim(mb_strtoupper($_POST['StockID']));
}

echo '<a href="' . $RootPath . '/SelectProduct.php" class="toplink">' . _('Back to Items') . '</a><br />';

echo '<p class="page_title_text">
	 <img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Inventory Adjustment') . '" alt="" />
	 ' . ' ' . $Title . '</p>';

if (isset($_POST['UpdateData'])){

	$SQL = "SELECT materialcost,
					labourcost,
					overheadcost,
					mbflag,
					sum(quantity) as totalqoh
			FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid=locstock.stockid
			WHERE stockmaster.stockid='".$StockID."'
			GROUP BY description,
					units,
					lastcost,
					actualcost,
					materialcost,
					labourcost,
					overheadcost,
					mbflag";
	$ErrMsg = _('The entered item code does not exist');
	$OldResult = DB_query($SQL,$ErrMsg);
	$OldRow = DB_fetch_array($OldResult);
	$_POST['QOH'] = $OldRow['totalqoh'];
	$_POST['OldMaterialCost'] = $OldRow['materialcost'];
	if ($OldRow['mbflag']=='M') {
		$_POST['OldLabourCost'] = $OldRow['labourcost'];
		$_POST['OldOverheadCost'] = $OldRow['overheadcost'];
	} else {
		$_POST['OldLabourCost'] = 0;
		$_POST['OldOverheadCost'] = 0;
		$_POST['LabourCost'] = 0;
		$_POST['OverheadCost'] = 0;
	}
	DB_free_result($OldResult);

 	$OldCost = $_POST['OldMaterialCost'] + $_POST['OldLabourCost'] + $_POST['OldOverheadCost'];
   	$NewCost = filter_number_format($_POST['MaterialCost']) + filter_number_format($_POST['LabourCost']) + filter_number_format($_POST['OverheadCost']);

	$Result = DB_query("SELECT * FROM stockmaster WHERE stockid='" . $StockID . "'");
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result)==0) {
		prnMsg (_('The entered item code does not exist'),'error',_('Non-existent Item'));
	} elseif (abs($NewCost - $OldCost) > pow(10,-($_SESSION['StandardCostDecimalPlaces']+1))){

		DB_Txn_Begin();
		ItemCostUpdateGL($StockID, $NewCost, $OldCost, $_POST['QOH']);

		$SQL = "UPDATE stockmaster 
				SET	materialcost='" . filter_number_format($_POST['MaterialCost']) . "',
					labourcost='" . filter_number_format($_POST['LabourCost']) . "',
					overheadcost='" . filter_number_format($_POST['OverheadCost']) . "',
					lastcost='" . $OldCost . "',
					lastcostupdate = CURRENT_DATE
				WHERE stockid='" . $StockID . "'";

		$ErrMsg = _('The cost details for the stock item could not be updated because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		DB_Txn_Commit();
		UpdateCost($StockID); //Update any affected BOMs

	}
}

$ErrMsg = _('The cost details for the stock item could not be retrieved because');
$DbgMsg = _('The SQL that failed was');

$Result = DB_query("SELECT description,
							units,
							lastcost,
							actualcost,
							materialcost,
							labourcost,
							overheadcost,
							mbflag,
							stocktype,
							lastcostupdate,
							sum(quantity) as totalqoh
						FROM stockmaster INNER JOIN locstock
							ON stockmaster.stockid=locstock.stockid
							INNER JOIN stockcategory
							ON stockmaster.categoryid = stockcategory.categoryid
						WHERE stockmaster.stockid='" . $StockID . "'
						GROUP BY description,
							units,
							lastcost,
							actualcost,
							materialcost,
							labourcost,
							overheadcost,
							mbflag,
							stocktype",
							$ErrMsg,
							$DbgMsg);


$MyRow = DB_fetch_array($Result);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<fieldset>
		<legend>' . $StockID . ' - ' . $MyRow['description'] . '</legend>
		<field>
			<label for="StockID">' . _('Item Code') . ':</label>
			<input type="text" name="StockID" value="' . $StockID . '"  maxlength="20" />
			<input type="submit" name="Show" value="' . _('Show Cost Details') . '" />
		</field>
		<field>
			<label>' .  _('Total Quantity On Hand') . ':</label>
			<fieldtext>' . $MyRow['totalqoh'] . ' ' . $MyRow['units']  . '</fieldtext>
		</field>
		<field>
			<label>' .  _('Last Cost update on') . ':</label>
			<fieldtext>' . ConvertSQLDate($MyRow['lastcostupdate'])  . '</fieldtext>
		</field>';

if (($MyRow['mbflag']=='D' AND $MyRow['stocktype'] != 'L')
							OR $MyRow['mbflag']=='A'
							OR $MyRow['mbflag']=='K'){
	echo '</div>
		  </form>'; // Close the form
   if ($MyRow['mbflag']=='D'){
		echo '<br />' . $StockID .' ' . _('is a service item');
   } else if ($MyRow['mbflag']=='A'){
		echo '<br />' . $StockID  .' '  . _('is an assembly part');
   } else if ($MyRow['mbflag']=='K'){
		echo '<br />' . $StockID . ' ' . _('is a kit set part');
   }
   prnMsg(_('Cost information cannot be modified for kits assemblies or service items') . '. ' . _('Please select a different part'),'warn');
   include('includes/footer.php');
   exit;
}

echo '<field>';
echo '<input type="hidden" name="OldMaterialCost" value="' . $MyRow['materialcost'] .'" />';
echo '<input type="hidden" name="OldLabourCost" value="' . $MyRow['labourcost'] .'" />';
echo '<input type="hidden" name="OldOverheadCost" value="' . $MyRow['overheadcost'] .'" />';
echo '<input type="hidden" name="QOH" value="' . $MyRow['totalqoh'] .'" />';

echo '<label>', _('Last Cost') .':</label>
		<fieldtext>' . locale_number_format($MyRow['lastcost'],$_SESSION['StandardCostDecimalPlaces']) . '</fieldtext>
	</field>';
if (! in_array($_SESSION['PageSecurityArray']['CostUpdate'],$_SESSION['AllowedPageSecurityTokens'])){
	echo '<field>
			<td>' . _('Cost') . ':</td>
			<td class="number">' . locale_number_format($MyRow['materialcost']+$MyRow['labourcost']+$MyRow['overheadcost'],$_SESSION['StandardCostDecimalPlaces']) . '</td>
		</field>
		</table>';
} else {

	if ($MyRow['mbflag']=='M'){
		echo '<field>
				<label for="MaterialCost">' . _('Standard Material Cost Per Unit') .':</label>
				<input type="text" class="number" name="MaterialCost" value="' . locale_number_format($MyRow['materialcost'],$_SESSION['StandardCostDecimalPlaces']) . '" />
			</field>
			<field>
				<label for="LabourCost">' . _('Standard Labour Cost Per Unit') . ':</label>
				<input type="text" class="number" name="LabourCost" value="' . locale_number_format($MyRow['labourcost'],$_SESSION['StandardCostDecimalPlaces']) . '" />
			</field>
			<field>
				<label for="OverheadCost">' . _('Standard Overhead Cost Per Unit') . ':</label>
				<input type="text" class="number" name="OverheadCost" value="' . locale_number_format($MyRow['overheadcost'],$_SESSION['StandardCostDecimalPlaces']) . '" />
			</field>';
	} elseif ($MyRow['mbflag']=='B' OR  $MyRow['mbflag']=='D') {
		echo '<field>
				<td>' . _('Standard Cost') .':</td>
				<td class="number"><input type="text" class="number" name="MaterialCost" value="' . locale_number_format($MyRow['materialcost'],$_SESSION['StandardCostDecimalPlaces']) . '" /></td>
			</field>';
	} else 	{
		echo '<field><td><input type="hidden" name="LabourCost" value="0" />';
		echo '<input type="hidden" name="OverheadCost" value="0" /></td></field>';
	}
	echo '</fieldset>
   		  <div class="centre">
				  <input type="submit" name="UpdateData" value="' . _('Update') . '" />
			 </div>';
}
if ($MyRow['mbflag']!='D'){
	echo '<div class="centre"><a href="' . $RootPath . '/StockStatus.php?StockID=' . $StockID . '">' . _('Show Stock Status') . '</a>';
	echo '<br /><a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '">' . _('Show Stock Movements') . '</a>';
	echo '<br /><a href="' . $RootPath . '/StockUsage.php?StockID=' . $StockID . '">' . _('Show Stock Usage')   . '</a>';
	echo '<br /><a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Outstanding Sales Orders') . '</a>';
	echo '<br /><a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . _('Search Completed Sales Orders') . '</a></div>';
}
echo '</div>
	  </form>';
include('includes/footer.php');
?>