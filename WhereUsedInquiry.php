<?php


include('includes/session.php');
$Title = _('Where Used Inquiry');
$ViewTopic = 'Manufacturing';
$BookMark = '';
include('includes/header.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}

echo '<a href="' . $RootPath . '/SelectProduct.php" class="toplink">' . _('Back to Items') . '</a>
	<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '
	</p>';
if (isset($StockID)){
	$Result = DB_query("SELECT description,
								units,
								mbflag
						FROM stockmaster
						WHERE stockid='".$StockID."'");
	$MyRow = DB_fetch_row($Result);
	if (DB_num_rows($Result)==0){
		prnMsg(_('The item code entered') . ' - ' . $StockID . ' ' . _('is not set up as an item in the system') . '. ' . _('Re-enter a valid item code or select from the Select Item link above'),'error');
		include('includes/footer.php');
		exit;
	}
	echo '<div class="centre"><h3>' . $StockID . ' - ' . $MyRow[0] . '  (' . _('in units of') . ' ' . $MyRow[1] . ')</h3></div>';
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<fieldset>
		<legend>', _('Item Search'), '</legend>';
if (isset($StockID)) {
	echo '<field>
			<label for="StockID">', _('Enter an Item Code') . ':</label>
			<input type="text" required="required" data-type="no-illegal-chars" title="" name="StockID" autofocus="autofocus" size="21" maxlength="20" value="' . $StockID . '" placeholder="'._('No illegal characters allowed').'" />
			<fieldhelp>'._('Illegal characters and blank is not allowed').'</fieldhelp>
		</field>';
} else {
	echo '<field>
			<label for="StockID">', _('Enter an Item Code') . ':</label>
			<input type="text" required="required" data-type="no-illegal-chars"  title="" name="StockID" autofocus="autofocus" size="21" maxlength="20" placeholder="'._('No illegal characters allowed').'" />
			<fieldhelp>'._('Illegal characters and blank is not allowed').'</fieldhelp>
		</field>';
}
echo '</fieldset>';
echo '<div class="centre">
		<input type="submit" name="ShowWhereUsed" value="' . _('Show Where Used') . '" />
	</div>';

if (isset($StockID)) {

	$SQL = "SELECT bom.*,
				stockmaster.description,
				stockmaster.discontinued
			FROM bom INNER JOIN stockmaster
			ON bom.parent = stockmaster.stockid
			INNER JOIN locationusers ON locationusers.loccode=bom.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
			WHERE component='" . $StockID . "'
                AND bom.effectiveafter <= '" . date('Y-m-d') . "'
                AND bom.effectiveto > '" . date('Y-m-d') . "'
			ORDER BY stockmaster.discontinued, bom.parent";

	$ErrMsg = _('The parents for the selected part could not be retrieved because');;
	$Result = DB_query($SQL,$ErrMsg);
	if (DB_num_rows($Result)==0){
		prnMsg(_('The selected item') . ' ' . $StockID . ' ' . _('is not used as a component of any other parts'),'error');
	} else {

		echo '<table width="97%" class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Used By') . '</th>
					<th class="SortedColumn">' . _('Status') . '</th>
					<th class="SortedColumn">' . _('Work Centre') . '</th>
					<th class="SortedColumn">' . _('Location') . '</th>
					<th class="SortedColumn">' . _('Quantity Required') . '</th>
					<th class="SortedColumn">' . _('Effective After') . '</th>
					<th class="SortedColumn">' . _('Effective To') . '</th>
				</tr>
			</thead>
			<tbody>';

		while ($MyRow=DB_fetch_array($Result)) {

			if ($MyRow['discontinued'] == 1){
				$Status = _('Obsolete');
			}else{
				$Status = _('Current');
			}
			echo '<tr class="striped_row">
					<td><a target="_blank" href="' . $RootPath . '/BOMInquiry.php?StockID=' . $MyRow['parent'] . '" alt="' . _('Show Bill Of Material') . '">' . $MyRow['parent']. ' - ' . $MyRow['description']. '</a></td>
					<td>' . $Status. '</td>
					<td>' . $MyRow['workcentreadded']. '</td>
					<td>' . $MyRow['loccode']. '</td>
					<td class="number">' . locale_number_format($MyRow['quantity'],'Variable') . '</td>
					<td class="date">' . ConvertSQLDate($MyRow['effectiveafter']) . '</td>
					<td class="date">' . ConvertSQLDate($MyRow['effectiveto']) . '</td>
                </tr>';

			//end of page full new headings if
		}
		echo '</tbody></table>';
	}
} // StockID is set
echo '</form>';
include('includes/footer.php');
