<?php
/* $Id: AutomaticTranslationDescriptions.php 7037 2014-12-22 14:45:20Z tehonu $ */

include ('includes/session.php');
$Title = _('Translate Item Descriptions');
$ViewTopic = 'SpecialUtilities'; // Filename in ManualContents.php's TOC.
$BookMark = 'Z_TranslateItemDescriptions'; // Anchor's id in the manual's html document.
include ('includes/header.php');

include ('includes/GoogleTranslator.php');

$SourceLanguage=mb_substr($_SESSION['Language'],0,2);

// Select items and classify them
$SQL = "SELECT stockmaster.stockid,
				description,
				longdescription,
				language_id,
				descriptiontranslation,
				longdescriptiontranslation
		FROM stockmaster, stockdescriptiontranslations
		WHERE stockmaster.stockid = stockdescriptiontranslations.stockid
			AND stockmaster.discontinued = 0
			AND (descriptiontranslation = ''
				OR longdescriptiontranslation = '')
		ORDER BY stockmaster.stockid,
				language_id";
$result = DB_query($SQL);

if(DB_num_rows($result) != 0) {
	echo '<p class="page_title_text" align="center"><strong>' . _('Description Automatic Translation for empty translations') . '</strong></p>';
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

		if ($myrow['descriptiontranslation'] == ''){
			$TargetLanguage=mb_substr($myrow['language_id'],0,2);
			$TranslatedText = translate_via_google_translator($myrow['description'],$TargetLanguage,$SourceLanguage);

			$sql = "UPDATE stockdescriptiontranslations " .
					"SET descriptiontranslation='" . $TranslatedText . "', " .
						"needsrevision= '1' " .
					"WHERE stockid='" . $myrow['stockid'] . "' AND (language_id='" . $myrow['language_id'] . "')";
			$update = DB_query($sql, $ErrMsg, $DbgMsg, true);

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			$i++;
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					$i,
					$myrow['stockid'],
					$myrow['description'],
					$myrow['language_id'],
					$TranslatedText
					);
		}
		if ($myrow['longdescriptiontranslation'] == ''){
			$TargetLanguage=mb_substr($myrow['language_id'],0,2);
			$TranslatedText = translate_via_google_translator($myrow['longdescription'],$TargetLanguage,$SourceLanguage);

			$sql = "UPDATE stockdescriptiontranslations " .
					"SET longdescriptiontranslation='" . $TranslatedText . "', " .
						"needsrevision= '1' " .
					"WHERE stockid='" . $myrow['stockid'] . "' AND (language_id='" . $myrow['language_id'] . "')";
			$update = DB_query($sql, $ErrMsg, $DbgMsg, true);

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			$i++;
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					</tr>',
					$i,
					$myrow['stockid'],
					$myrow['longdescription'],
					$myrow['language_id'],
					$TranslatedText
					);
		}
	}
	echo '</table>
			</div>';
	prnMsg("Number of translated descriptions via Google API: " . locale_number_format($i));
} else {

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		_('No item description was automatically translated') . '" />' . ' ' .
		_('No item description was automatically translated') . '</p>';

// Add error message for "Google Translator API Key" empty.

}

include ('includes/footer.php');
?>
