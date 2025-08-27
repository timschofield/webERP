<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Translate Item Descriptions');
$ViewTopic = 'SpecialUtilities'; // Filename in ManualContents.php's TOC.
$BookMark = 'Z_TranslateItemDescriptions'; // Anchor's id in the manual's html document.
include('includes/header.php');

if (!function_exists("curl_init")) {
	prnMsg("This script requires that the PHP curl module be available to use the Google API. Unfortunately this installation does not have the curl module available","error");
	include('includes/footer.php');
	exit();
}

include('includes/GoogleTranslator.php');

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
$Result = DB_query($SQL);

if(DB_num_rows($Result) != 0) {
	echo '<p class="page_title_text"><strong>' . __('Description Automatic Translation for empty translations') . '</strong></p>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . __('#') . '</th>
						<th>' . __('Code') . '</th>
						<th>' . __('Description') . '</th>
						<th>' . __('To') . '</th>
						<th>' . __('Translated') . '</th>
					</tr>';
	echo $TableHeader;
	$i = 0;
	while ($MyRow = DB_fetch_array($Result)) {

		if ($MyRow['descriptiontranslation'] == ''){
			$TargetLanguage=mb_substr($MyRow['language_id'],0,2);
			$TranslatedText = translate_via_google_translator($MyRow['description'],$TargetLanguage,$SourceLanguage);

			$SQL = "UPDATE stockdescriptiontranslations " .
					"SET descriptiontranslation='" . $TranslatedText . "', " .
						"needsrevision= '1' " .
					"WHERE stockid='" . $MyRow['stockid'] . "' AND (language_id='" . $MyRow['language_id'] . "')";
			$Update = DB_query($SQL, $ErrMsg, '', true);

			$i++;
			echo '<tr class="striped_row">
					<td class="number">', $i, '</td>
					<td>', $MyRow['stockid'], '</td>
					<td>', $MyRow['description'], '</td>
					<td>', $MyRow['language_id'], '</td>
					<td>', $TranslatedText, '</td>
				</tr>';
		}
		if ($MyRow['longdescriptiontranslation'] == ''){
			$TargetLanguage=mb_substr($MyRow['language_id'],0,2);
			$TranslatedText = translate_via_google_translator($MyRow['longdescription'],$TargetLanguage,$SourceLanguage);

			$SQL = "UPDATE stockdescriptiontranslations " .
					"SET longdescriptiontranslation='" . $TranslatedText . "', " .
						"needsrevision= '1' " .
					"WHERE stockid='" . $MyRow['stockid'] . "' AND (language_id='" . $MyRow['language_id'] . "')";
					echo $SQL;
			$Update = DB_query($SQL, $ErrMsg, '', true);

			$i++;
			echo '<tr class="striped_row">
					<td class="number">', $i, '</td>
					<td>', $MyRow['stockid'], '</td>
					<td>', $MyRow['longdescription'], '</td>
					<td>', $MyRow['language_id'], '</td>
					<td>', $TranslatedText, '</td>
				</tr>';
		}
	}
	echo '</table>';
	prnMsg(__('Number of translated descriptions via Google API') . ': ' . locale_number_format($i));
} else {

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('No item descriptions were automatically translated') . '" />' . ' ' .
		__('No item descriptions were automatically translated') . '</p>';

// Add error message for "Google Translator API Key" empty.

}

include('includes/footer.php');
