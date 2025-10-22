<?php

require(__DIR__ . '/includes/session.php');

$UpdateSecurity = 10;

$Title = __('Stock Cost Update');
$ViewTopic = 'Inventory';
$BookMark = '';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID =trim(mb_strtoupper($_POST['StockID']));
}

echo '<a href="' . $RootPath . '/SelectProduct.php" class="toplink">' . __('Back to Items') . '</a><br />';

echo '<p class="page_title_text">
	 <img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Inventory Adjustment') . '" alt="" />
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
	$ErrMsg = __('The entered item code does not exist');
	$OldResult = DB_query($SQL, $ErrMsg);
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
		prnMsg(__('The entered item code does not exist'),'error',__('Non-existent Item'));
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

		$ErrMsg = __('The cost details for the stock item could not be updated because');
		$Result = DB_query($SQL, $ErrMsg, '', true);

		DB_Txn_Commit();
		UpdateCost($StockID); //Update any affected BOMs

	}
}

$ErrMsg = __('The cost details for the stock item could not be retrieved because');

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
							$ErrMsg);


$MyRow = DB_fetch_array($Result);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<fieldset>
		<legend>' . $StockID . ' - ' . $MyRow['description'] . '</legend>
		<field>
			<label for="StockID">' . __('Item Code') . ':</label>
			<input type="text" name="StockID" value="' . $StockID . '"  maxlength="20" />
			<input type="submit" name="Show" value="' . __('Show Cost Details') . '" />
		</field>
		<field>
			<label>' .  __('Total Quantity On Hand') . ':</label>
			<fieldtext>' . $MyRow['totalqoh'] . ' ' . $MyRow['units']  . '</fieldtext>
		</field>
		<field>
			<label>' .  __('Last Cost update on') . ':</label>
			<fieldtext>' . ConvertSQLDate($MyRow['lastcostupdate'])  . '</fieldtext>
		</field>';

if (($MyRow['mbflag']=='D' AND $MyRow['stocktype'] != 'L')
							OR $MyRow['mbflag']=='A'
							OR $MyRow['mbflag']=='K'){
	echo '</div>
		  </form>'; // Close the form
   if ($MyRow['mbflag']=='D'){
		echo '<br />' . $StockID .' ' . __('is a service item');
   } else if ($MyRow['mbflag']=='A'){
		echo '<br />' . $StockID  .' '  . __('is an assembly part');
   } else if ($MyRow['mbflag']=='K'){
		echo '<br />' . $StockID . ' ' . __('is a kit set part');
   }
   prnMsg(__('Cost information cannot be modified for kits assemblies or service items') . '. ' . __('Please select a different part'),'warn');
   include('includes/footer.php');
   exit();
}

echo '<field>';
echo '<input type="hidden" name="OldMaterialCost" value="' . $MyRow['materialcost'] .'" />';
echo '<input type="hidden" name="OldLabourCost" value="' . $MyRow['labourcost'] .'" />';
echo '<input type="hidden" name="OldOverheadCost" value="' . $MyRow['overheadcost'] .'" />';
echo '<input type="hidden" name="QOH" value="' . $MyRow['totalqoh'] .'" />';

echo '<label>', __('Last Cost') .':</label>
		<fieldtext>' . locale_number_format($MyRow['lastcost'],$_SESSION['StandardCostDecimalPlaces']) . '</fieldtext>
	</field>';
if (! in_array($_SESSION['PageSecurityArray']['CostUpdate'],$_SESSION['AllowedPageSecurityTokens'])){
	echo '<field>
			<td>' . __('Cost') . ':</td>
			<td class="number">' . locale_number_format($MyRow['materialcost']+$MyRow['labourcost']+$MyRow['overheadcost'],$_SESSION['StandardCostDecimalPlaces']) . '</td>
		</field>
		</table>';
} else {

	if ($MyRow['mbflag']=='M'){
		echo '<field>
				<label for="MaterialCost">' . __('Standard Material Cost Per Unit') .':</label>
				<input type="text" class="number" name="MaterialCost" value="' . locale_number_format($MyRow['materialcost'],$_SESSION['StandardCostDecimalPlaces']) . '" />
			</field>
			<field>
				<label for="LabourCost">' . __('Standard Labour Cost Per Unit') . ':</label>
				<input type="text" class="number" name="LabourCost" value="' . locale_number_format($MyRow['labourcost'],$_SESSION['StandardCostDecimalPlaces']) . '" />
			</field>
			<field>
				<label for="OverheadCost">' . __('Standard Overhead Cost Per Unit') . ':</label>
				<input type="text" class="number" name="OverheadCost" value="' . locale_number_format($MyRow['overheadcost'],$_SESSION['StandardCostDecimalPlaces']) . '" />
			</field>';
	} elseif ($MyRow['mbflag']=='B' OR  $MyRow['mbflag']=='D') {
		echo '<field>
				<td>' . __('Standard Cost') .':</td>
				<td class="number"><input type="text" class="number" name="MaterialCost" value="' . locale_number_format($MyRow['materialcost'],$_SESSION['StandardCostDecimalPlaces']) . '" /></td>
			</field>';
	} else 	{
		echo '<field><td><input type="hidden" name="LabourCost" value="0" />';
		echo '<input type="hidden" name="OverheadCost" value="0" /></td></field>';
	}
	echo '</fieldset>
   		  <div class="centre">
				  <input type="submit" name="UpdateData" value="' . __('Update') . '" />
			 </div>';
}
if ($MyRow['mbflag']!='D'){
	echo '<div class="centre"><a href="' . $RootPath . '/StockStatus.php?StockID=' . $StockID . '">' . __('Show Stock Status') . '</a>';
	echo '<br /><a href="' . $RootPath . '/StockMovements.php?StockID=' . $StockID . '">' . __('Show Stock Movements') . '</a>';
	echo '<br /><a href="' . $RootPath . '/StockUsage.php?StockID=' . $StockID . '">' . __('Show Stock Usage')   . '</a>';
	echo '<br /><a href="' . $RootPath . '/SelectSalesOrder.php?SelectedStockItem=' . $StockID . '">' . __('Search Outstanding Sales Orders') . '</a>';
	echo '<br /><a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedStockItem=' . $StockID . '">' . __('Search Completed Sales Orders') . '</a></div>';
}
echo '</div>
	  </form>';
include('includes/footer.php');
