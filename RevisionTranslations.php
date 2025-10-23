<?php

/* This script is to review the item description translations. */

require(__DIR__ . '/includes/session.php');

$Title = __('Review Translated Descriptions');// Screen identificator.
$ViewTopic = 'Inventory';// Filename's id in ManualContents.php's TOC.
$BookMark = 'ReviewTranslatedDescriptions';// Anchor's id in the manual's html document.
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/maintenance.png" title="' .// Title icon.
	__('Review Translated Descriptions') . '" />' .// Icon title.
	__('Review Translated Descriptions') . '</p>';// Page title.

//update database if update pressed
if(isset($_POST['Submit'])) {
	for ($i=1;$i<count($_POST);$i++) { //loop through the returned translations

		if(isset($_POST['Revised' . $i]) AND ($_POST['Revised' . $i] == '1')) {
			$SQLUpdate="UPDATE stockdescriptiontranslations
						SET needsrevision = '0',
							descriptiontranslation = '". $_POST['DescriptionTranslation' .$i] ."',
							longdescriptiontranslation = '". $_POST['LongDescriptionTranslation' .$i] ."'
						WHERE stockid = '". $_POST['StockID' .$i] ."'
							AND language_id = '". $_POST['LanguageID' .$i] ."'";
			$ResultUpdate = DB_Query($SQLUpdate,'', '', true);
			prnMsg($_POST['StockID' .$i] . ' ' . __('descriptions') . ' ' .  __('in') . ' ' . $_POST['LanguageID' .$i] . ' ' . __('have been updated'),'success');
		}
	}
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">
		<tr>
			<th colspan="7">' . __('Translations to revise') .'</th>
		</tr>';

$SQL = "SELECT stockdescriptiontranslations.stockid,
				stockmaster.description,
				stockmaster.longdescription,
				stockdescriptiontranslations.language_id,
				stockdescriptiontranslations.descriptiontranslation,
				stockdescriptiontranslations.longdescriptiontranslation
		FROM stockdescriptiontranslations, stockmaster
		WHERE stockdescriptiontranslations.stockid = stockmaster.stockid
			AND stockdescriptiontranslations.needsrevision = '1'
		ORDER BY stockdescriptiontranslations.stockid,
				stockdescriptiontranslations.language_id";

$Result = DB_query($SQL);

echo '<tr>
	<th>' . __('Code') . '</th>
	<th>' . __('Language') . '</th>
	<th>' . __('Part Description (short)') . '</th>
	<th>' . __('Part Description (long)') . '</th>
	<th>' . __('Revised?') . '</th>
</tr>';

$i=1;
while($MyRow=DB_fetch_array($Result)) {

	echo '<tr class="striped_row">
		<td>' . $MyRow['stockid'] . '</td>
		<td>' . $_SESSION['Language']. '</td>
		<td>' . $MyRow['description'] . '</td>
		<td>' . nl2br($MyRow['longdescription']) . '</td>
		<td>&nbsp;</td>
		</tr>';// nl2br: Inserts HTML line breaks before all newlines in a string.

	echo '<tr class="striped_row">
		<td>&nbsp;</td>
		<td>' . $MyRow['language_id'] . '</td>';

	echo '<td><input class="text" maxlength="50" name="DescriptionTranslation' . $i .'" size="52" type="text" value="'. $MyRow['descriptiontranslation'] .'" /></td>
		<td><textarea name="LongDescriptionTranslation' . $i .'" cols="70" rows="5">'. $MyRow['longdescriptiontranslation'] .'" </textarea></td>';

	echo '<td>
			<input name="Revised' . $i . '" type="checkbox" value="1" />
			<input name="StockID' . $i . '" type="hidden" value="' . $MyRow['stockid'] . '" />
			<input name="LanguageID' . $i . '" type="hidden" value="' . $MyRow['language_id'] . '" />
		</td>
		</tr>';
	$i++;

} //end of looping

echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="Submit" value="' . __('Update') . '" /></div>
		</div>
	</form>';

include('includes/footer.php');
