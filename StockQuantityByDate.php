<?php

/* $Id: StockQuantityByDate.php 7751 2017-04-13 16:34:26Z rchacon $ */

include('includes/session.php');
$Title = _('Stock On Hand By Date');
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') .
'" alt="" /><b>' . $Title. '</b>
	</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$sql = "SELECT categoryid, categorydescription FROM stockcategory";
$resultStkLocs = DB_query($sql);

echo '<table class="selection">
	<tr>
		<td>' . _('For Stock Category') . ':</td>
		<td><select name="StockCategory">
			<option value="All">' . _('All') . '</option>';

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockCategory']) AND $_POST['StockCategory']!='All'){
		if ($myrow['categoryid'] == $_POST['StockCategory']){
		     echo '<option selected="selected" value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
		}
	}else {
		 echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categorydescription'] . '</option>';
	}
}
echo '</select></td>';

$sql = "SELECT locations.loccode, locationname FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
$resultStkLocs = DB_query($sql);

echo '<td>' . _('For Stock Location') . ':</td>
	<td><select name="StockLocation"> ';

while ($myrow=DB_fetch_array($resultStkLocs)){
	if (isset($_POST['StockLocation']) AND $_POST['StockLocation']!='All'){
		if ($myrow['loccode'] == $_POST['StockLocation']){
		     echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
		     echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
	} elseif ($myrow['loccode']==$_SESSION['UserStockLocation']){
		 echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		 $_POST['StockLocation']=$myrow['loccode'];
	} else {
		 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
}
echo '</select></td>';

if (!isset($_POST['OnHandDate'])){
	$_POST['OnHandDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m'),0,Date('y')));
}

echo '<td>' . _('On-Hand On Date') . ':</td>
	<td><input type="text" class="date" name="OnHandDate" size="11" maxlength="10" value="' . $_POST['OnHandDate'] . '" /></td>
	</tr>';
echo '<tr>
		<td colspan="6">
		<div class="centre">
		<input type="submit" name="ShowStatus" value="' . _('Show Stock Status') .'" />
		</div></td>
	</tr>
	</table>
    </div>
	</form>';

$TotalQuantity = 0;

if(isset($_POST['ShowStatus']) AND Is_Date($_POST['OnHandDate'])) {
        if ($_POST['StockCategory']=='All') {
                 $sql = "SELECT stockid,
                                 controlled,
                                 description,
                                 decimalplaces
                         FROM stockmaster
                         WHERE (mbflag='M' OR mbflag='B')";
         } else {
                 $sql = "SELECT stockid,
                                 controlled,
                                 description,
                                 decimalplaces
                         FROM stockmaster
                         WHERE categoryid = '" . $_POST['StockCategory'] . "'
                         AND (mbflag='M' OR mbflag='B')";
         }

	$ErrMsg = _('The stock items in the category selected cannot be retrieved because');
	$DbgMsg = _('The SQL that failed was');

	$StockResult = DB_query($sql, $ErrMsg, $DbgMsg);

	$SQLOnHandDate = FormatDateForSQL($_POST['OnHandDate']);

	echo '<br />
		<table class="selection">';

	$tableheader = '<tr>
						<th>' . _('Item Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Quantity On Hand') . '</th>
						<th>' . _('Controlled') . '</th>
					</tr>';
	echo $tableheader;

	while ($myrows=DB_fetch_array($StockResult)) {

		$sql = "SELECT stockid,
				newqoh
				FROM stockmoves
				WHERE stockmoves.trandate <= '". $SQLOnHandDate . "'
				AND stockid = '" . $myrows['stockid'] . "'
				AND loccode = '" . $_POST['StockLocation'] ."'
				AND newqoh > 0
				ORDER BY stkmoveno DESC LIMIT 1";

		$ErrMsg =  _('The stock held as at') . ' ' . $_POST['OnHandDate'] . ' ' . _('could not be retrieved because');

		$LocStockResult = DB_query($sql, $ErrMsg);

		$NumRows = DB_num_rows($LocStockResult);
		$j = 1;

		while ($LocQtyRow=DB_fetch_array($LocStockResult)) {

			if ($myrows['controlled']==1){
				$Controlled="Yes";
			} else {
				$Controlled="No";
			}

			if($NumRows == 0){
				printf('<tr class="striped_row">
						<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?%s">%s</a></td>
						<td>%s</td>
						<td class="number">%s</td>
						</tr>',
						'StockID=' . mb_strtoupper($myrows['stockid']),
						mb_strtoupper($myrows['stockid']),
						$myrows['description'],
						0);
			} else {
				printf('<tr class="striped_row">
					<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?%s">%s</a></td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>',
					'StockID=' . mb_strtoupper($myrows['stockid']),
					mb_strtoupper($myrows['stockid']),
					$myrows['description'],
					locale_number_format($LocQtyRow['newqoh'],$myrows['decimalplaces']),
					$Controlled);

				$TotalQuantity += $LocQtyRow['newqoh'];
			}
			$j++;
			if ($j == 12){
				$j=1;
				echo $tableheader;
			}
		//end of page full new headings if
		}

	}//end of while loop
	echo '<tr>
			<td><b>' . _('Total') . '</b></td>
			<td></td>
			<td class="number"><b>' . locale_number_format($TotalQuantity,1) . '</b></td>
			<td></td>
		</tr>
		</table>';
}

include('includes/footer.php');
?>