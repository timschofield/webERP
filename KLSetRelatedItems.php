<?php

define("VERSIONFILE", "1.00"); 

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('Kapal-Laut Set Related Items '. VERSIONFILE);
include ('includes/header.php');
include ('includes/KLDefines.php');
include ('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');

$UpdateDB = TRUE;

$begintime = time_start();

// let's start from an emprty table
/*$SQL = "TRUNCATE relateditems";
$result = DB_query($SQL);
*/
// Select items and classify them
$SQL = "SELECT stockmaster.stockid,
			stockmaster.categoryid
		FROM stockmaster
		WHERE discontinued = 0
		ORDER BY stockmaster.stockid";
$result = DB_query($SQL);
if (DB_num_rows($result) != 0){
	echo '<p class="page_title_text" align="center"><strong>' . _('Find Related Items For Online Shop') . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Related') . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 0;
	while ($myrow = DB_fetch_array($result)) {
		
/*		// Exception for DRAR items
		if (substr($myrow['stockid'], 0,4) == "DRAR"){
			$CodePreffix = substr($myrow['stockid'], 0,4);
		}else{
			$CodePreffix = substr($myrow['stockid'], 0,6);
		}

		$SQLRelated = "SELECT stockmaster.stockid
						FROM stockmaster
						WHERE stockmaster.stockid LIKE '" . $CodePreffix. "%'
							AND stockmaster.discontinued = 0
							AND stockmaster.stockid != '" . $myrow['stockid'] . "'
						ORDER BY stockmaster.stockid";
		$resultrelated = DB_query($SQLRelated);
		
		while ($myrelated = DB_fetch_array($resultrelated)) {
			$SQLExists = "SELECT *
							FROM relateditems
							WHERE relateditems.stockid = '" . $myrow['stockid'] . "'
								AND relateditems.related = '" . $myrelated['stockid'] . "'";
			$resultExists = DB_query($SQLExists);
			
			if (DB_num_rows($resultExists) == 0){
				$sqlinsert = "INSERT INTO relateditems (
							stockid,
							related)
						VALUES (
							UPPER('" . $myrow['stockid'] . "'),
							UPPER('" . $myrelated['stockid'] . "'))";
				$ErrMsg =_('Could not insert the related items because');
				$resultinsert = DB_query($sqlinsert,$ErrMsg);

				$i++;
				$k = StartEvenOrOddRow($k);
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$myrow['stockid'], 
						$myrelated['stockid']
						);
			}
		}
*/		
		if (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_KAPAL_LAUT_INCLUDING_DISC_20_50)){
			$SQLExists = "SELECT *
							FROM relateditems
							WHERE relateditems.stockid = '" . $myrow['stockid'] . "'
								AND relateditems.related = 'WKPC01'";
			$resultExists = DB_query($SQLExists);
			
			if (DB_num_rows($resultExists) == 0){
				$sqlinsert = "INSERT INTO relateditems (
							stockid,
							related)
						VALUES (
							UPPER('" . $myrow['stockid'] . "'),
							'WKPC01')";
				$ErrMsg =_('Could not insert the related items because');
				$resultinsert = DB_query($sqlinsert,$ErrMsg);

				$i++;
				$k = StartEvenOrOddRow($k);
				printf('<td class="number">%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>', 
						$i, 
						$myrow['stockid'], 
						'WKPC01'
						);
			}
		}
	}
	echo '</table>
			</div>';
	prnMsg("Number of related items pairs in website catalog: " . locale_number_format($i));
}
include ('includes/footer.php');
?>