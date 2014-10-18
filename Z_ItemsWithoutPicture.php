<?php

/* Session started in session.inc for password checking and authorisation level check
config.php is in turn included in session.inc*/
include ('includes/session.inc');
$Title = _('List of Items without picture');
include ('includes/header.inc');

$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			stockcategory.categorydescription
		FROM stockmaster, stockcategory
		WHERE stockmaster.categoryid = stockcategory.categoryid
			AND stockmaster.discontinued = 0
			AND stockcategory.stocktype != 'D'
		ORDER BY stockcategory.categorydescription, stockmaster.stockid";
$result = DB_query($SQL, $db);
$PrintHeader = TRUE;

if (DB_num_rows($result) != 0){
	echo '<p class="page_title_text" align="center"><strong>' . _('Current Items without picture in webERP') . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$k = 0; //row colour counter
	$i = 1;
	while ($myrow = DB_fetch_array($result)) {
		if(!file_exists($_SESSION['part_pics_dir'] . '/' .$myrow['stockid'].'.jpg') ) {
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
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>', 
					$i, 
					$myrow['categorydescription'],
					$CodeLink, 
					$myrow['description']
					);
			$i++;
		}
	}
	echo '</table>
			</div>
			</form>';
}

include ('includes/footer.inc');

?>