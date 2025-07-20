<?php

include ('includes/session.php');
$Title = _('KL Set Related Items');
include ('includes/header.php');
include ('includes/KLDefines.php');
include ('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$UpdateDB = TRUE;

$begintime = time_start();

// let's start from an emprty table
/*$SQL = "TRUNCATE relateditems";
$Result = DB_query($SQL);
*/
// Select items and classify them
$SQL = "SELECT stockmaster.stockid,
			stockmaster.categoryid
		FROM stockmaster
		WHERE discontinued = 0
		ORDER BY stockmaster.stockid";
$Result = DB_query($SQL);
if (DB_num_rows($Result) != 0){
	$TableTitleText = _('Find Related Items For Online Shop');
	ShowTableTitle($TableTitleText);
	echo '<div>';
	echo '<table class="selection">';
	echo '<thead>';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Related') . '</th>
					</tr>';
	echo $TableHeader;
	echo '</thead>';
	echo '<tbody>';
	$i = 0;
	while ($MyRow = DB_fetch_array($Result)) {
		
/*		// Exception for DRAR items
		if (substr($MyRow['stockid'], 0,4) == "DRAR"){
			$CodePreffix = substr($MyRow['stockid'], 0,4);
		}else{
			$CodePreffix = substr($MyRow['stockid'], 0,6);
		}

		$SQLRelated = "SELECT stockmaster.stockid
						FROM stockmaster
						WHERE stockmaster.stockid LIKE '" . $CodePreffix. "%'
							AND stockmaster.discontinued = 0
							AND stockmaster.stockid != '" . $MyRow['stockid'] . "'
						ORDER BY stockmaster.stockid";
		$Resultrelated = DB_query($SQLRelated);
		
		while ($MyRelated = DB_fetch_array($Resultrelated)) {
			$SQLExists = "SELECT *
							FROM relateditems
							WHERE relateditems.stockid = '" . $MyRow['stockid'] . "'
								AND relateditems.related = '" . $MyRelated['stockid'] . "'";
			$ResultExists = DB_query($SQLExists);
			
			if (DB_num_rows($ResultExists) == 0){
				$SQLinsert = "INSERT INTO relateditems (
							stockid,
							related)
						VALUES (
							UPPER('" . $MyRow['stockid'] . "'),
							UPPER('" . $MyRelated['stockid'] . "'))";
				$ErrMsg =_('Could not insert the related items because');
				$Resultinsert = DB_query($SQLinsert,$ErrMsg);

				$i++;
				$k = StartEvenOrOddRow($k);
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$MyRow['stockid'], 
						$MyRelated['stockid']
						);
			}
		}
*/		
		if (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_DISC_20_50)){
			$SQLExists = "SELECT *
							FROM relateditems
							WHERE relateditems.stockid = '" . $MyRow['stockid'] . "'
								AND relateditems.related = 'WKPC01'";
			$ResultExists = DB_query($SQLExists);
			
			if (DB_num_rows($ResultExists) == 0){
				$SQLinsert = "INSERT INTO relateditems (
							stockid,
							related)
						VALUES (
							UPPER('" . $MyRow['stockid'] . "'),
							'WKPC01')";
				$ErrMsg =_('Could not insert the related items because');
				$Resultinsert = DB_query($SQLinsert,$ErrMsg);

				$i++;
				echo '<tr class="striped_row">
						<td class="number">'.$i.'</td>
						<td>'.$MyRow['stockid'].'</td>
						<td>WKPC01</td>
						</tr>';
			}
		}
	}
	echo '</tbody>';
	echo '</table>
			</div>';
	prnMsg("Number of related items pairs in website catalog: " . locale_number_format($i));
}
include ('includes/footer.php');
