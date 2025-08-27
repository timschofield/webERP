<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Serial Item Research');
$ViewTopic = 'Inventory';
$BookMark = '';
include('includes/header.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . __('Inventory') . '" alt="" /><b>' . $Title. '</b>
	  </p>';

//validate the submission
if (isset($_POST['serialno'])) {
	$SerialNo = trim($_POST['serialno']);
} elseif(isset($_GET['serialno'])) {
	$SerialNo = trim($_GET['serialno']);
} else {
	$SerialNo = '';
}

echo '<form id="SerialNoResearch" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', __('Serial Number Lookup'), '</legend>';

echo  '<field>
		<label for="serialno">', __('Serial Number') .':</label>
		<input id="serialno" type="text" name="serialno" size="21" maxlength="20" value="'. $SerialNo . '" />
	</field>
	</fieldset>
	<div class="centre">
		<input type="submit" name="submit" value="' . __('Search') . '" />
	</div>
</form>';

echo '<script>
		document.getElementById("serialno").focus();
	</script>';


if ($SerialNo!='') {
	//the point here is to allow a semi fuzzy search, but still keep someone from killing the db server
	if (mb_strstr($SerialNo,'%')){
		while(mb_strstr($SerialNo,'%%'))	{
			$SerialNo = str_replace('%%','%',$SerialNo);
		}
		if (mb_strlen($SerialNo) < 11){
			$SerialNo = str_replace('%','',$SerialNo);
			prnMsg('You can not use LIKE with short numbers. It has been removed.','warn');
		}
	}
	$SQL = "SELECT ssi.serialno,
			ssi.stockid, ssi.quantity CurInvQty,
			ssm.moveqty,
			sm.type, st.typename,
			sm.transno, sm.loccode, l.locationname, sm.trandate, sm.debtorno, sm.branchcode, sm.reference, sm.qty TotalMoveQty
			FROM stockserialitems ssi INNER JOIN stockserialmoves ssm
				ON ssi.serialno = ssm.serialno AND ssi.stockid=ssm.stockid
			INNER JOIN stockmoves sm
				ON ssm.stockmoveno = sm.stkmoveno and ssi.loccode=sm.loccode
			INNER JOIN systypes st
				ON sm.type=st.typeid
			INNER JOIN locations l
				on sm.loccode = l.loccode
			INNER JOIN locationusers ON locationusers.loccode=l.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			WHERE ssi.serialno " . LIKE . " '" . $SerialNo . "'
			ORDER BY stkmoveno";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) == 0){
		prnMsg( __('No History found for Serial Number'). ': <b>' . $SerialNo . '</b>' , 'warn');
	} else {
		echo '<h4>' .  __('Details for Serial Item').': <b>' . $SerialNo . '</b><br />' .  __('Length').'='.mb_strlen($SerialNo) . '</h4>';
		echo '<table class="selection">';
		echo '<tr>
				<th>' . __('StockID') . '</th>
				<th>' . __('CurInvQty') . '</th>
				<th>' . __('Move Qty') . '</th>
				<th>' . __('Move Type') . '</th>
				<th>' . __('Trans #') . '</th>
				<th>' . __('Location') . '</th>
				<th>' . __('Date') . '</th>
				<th>' . __('DebtorNo') . '</th>
				<th>' . __('Branch') . '</th>
				<th>' . __('Move Ref') . '</th>
				<th>' . __('Total Move Qty') . '</th>
			</tr>';
		while ($MyRow=DB_fetch_row($Result)) {
			echo '<tr>
					<td>', $MyRow[1], '<br />', $MyRow[0], '</td>
					<td class="number">', $MyRow[2], '</td>
					<td class="number">', $MyRow[3], '</td>
					<td>', $MyRow[5], ' (', $MyRow[4], ')</td>
					<td class="number">', $MyRow[6], '</td>
					<td>', $MyRow[7], ' - ', $MyRow[8], '</td>
					<td>', $MyRow[9], ' &nbsp;</td>
					<td>', $MyRow[10], ' &nbsp;</td>
					<td>', $MyRow[11], ' &nbsp;</td>
					<td>', $MyRow[12], ' &nbsp;</td>
					<td class="number">', $MyRow[13], '</td>
				</tr>';
		} //END WHILE LIST LOOP
		echo '</table>';
	} // ELSE THERE WHERE ROWS
}//END OF POST IS SET
echo '</div>';

include('includes/footer.php');
