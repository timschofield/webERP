<?php

include ('includes/session.inc');
$Title = _('Automatic Translation - Descriptions');
include ('includes/header.inc');
include ('includes/GoogleTranslator.php');

$SourceLanguage="en";
$TargetLanguage="id";

// Select items and classify them
$SQL = "SELECT stockid,
				description
		FROM stockmaster
		WHERE stockmaster.discontinued = 0
		ORDER BY stockmaster.stockid
		LIMIT 0, 5";
$result = DB_query($SQL);

if (DB_num_rows($result) != 0){
	echo '<p class="page_title_text" align="center"><strong>' . _('Description Automatic Translation') . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('To') . '</th>
						<th>' . _('Translated') . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 0;
	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		$i++;
		
		$TranslatedText = translate_via_google_translator($myrow['description'],$TargetLanguage,$SourceLanguage);
		
		printf('<td class="number">%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				</tr>', 
				$i, 
				$myrow['stockid'], 
				$myrow['description'],
				$TargetLanguage,
				$TranslatedText
				);
	}
	echo '</table>
			</div>';
	prnMsg("Number of translated descriptions via Google API: " . locale_number_format($i));
}

include ('includes/footer.inc');
?>