<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('List of Items without picture');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php'); ;
include ('includes/header.php');

$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			stockcategory.categorydescription
		FROM stockmaster, stockcategory
		WHERE stockmaster.categoryid = stockcategory.categoryid
			AND stockmaster.discontinued = 0
			AND stockcategory.stocktype != 'D'
		ORDER BY stockcategory.categorydescription, stockmaster.stockid";
$Result = DB_query($SQL);
$PrintHeader = TRUE;

if (DB_num_rows($Result) != 0){
	echo '<p class="page_title_text"><strong>' . _('Current Items without picture in webERP') . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$i = 1;
	$SupportedImgExt = array('png','jpg','jpeg');
	while ($MyRow = DB_fetch_array($Result)) {
        $Glob = (glob($_SESSION['part_pics_dir'] . '/' . $MyRow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE));
		$ImageFile = reset($Glob);
		if(!file_exists($ImageFile) ) {
			if($PrintHeader){
				$TableHeader = '<tr>
								<th>' . '#' . '</th>
								<th>' . _('Category') . '</th>
								<th>' . _('Item Code') . '</th>
								<th>' . _('Description') . '</th>
								</tr>';
				echo $TableHeader;
				$PrintHeader = FALSE;
			}

			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $MyRow['stockid'] . '" target="_blank">' . $MyRow['stockid'] . '</a>';
			echo '<tr class="striped_row">
					<td class="number">', $i, '</td>
					<td>', $MyRow['categorydescription'], '</td>
					<td>', $CodeLink, '</td>
					<td>', $MyRow['description'], '</td>
				</tr>';
			$i++;
		}
	}
	echo '</table>
			</div>
			</form>';
}

include ('includes/footer.php');

?>