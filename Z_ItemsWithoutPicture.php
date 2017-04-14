<?php

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include ('includes/session.php');
$Title = _('List of Items without picture');
include ('includes/header.php');

$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			stockcategory.categorydescription
		FROM stockmaster, stockcategory
		WHERE stockmaster.categoryid = stockcategory.categoryid
			AND stockmaster.discontinued = 0
			AND stockcategory.stocktype != 'D'
		ORDER BY stockcategory.categorydescription, stockmaster.stockid";
$result = DB_query($SQL);
$PrintHeader = TRUE;

if (DB_num_rows($result) != 0){
	echo '<p class="page_title_text" align="center"><strong>' . _('Current Items without picture in webERP') . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$k = 0; //row colour counter
	$i = 1;
	$SupportedImgExt = array('png','jpg','jpeg');
	while ($myrow = DB_fetch_array($result)) {
		$imagefile = reset((glob($_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE)));
		if(!file_exists($imagefile) ) {
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
			$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '" target="_blank">' . $myrow['stockid'] . '</a>';
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

include ('includes/footer.php');

?>