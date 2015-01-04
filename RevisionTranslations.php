<?php
/* $Id: RevisionTranslations.php 7040 2014-12-27 15:15:29Z tehonu $*/
/* This script is to review the item description translations. */

include('includes/session.inc');

$Title = _('Review Translated Descriptions');// Screen identificator.
$ViewTopic= 'Inventory';// Filename's id in ManualContents.php's TOC.
$BookMark = 'ReviewTranslatedDescriptions';// Anchor's id in the manual's html document.
include('includes/header.inc');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/maintenance.png" title="' .// Title icon.
	_('Review Translated Descriptions') . '" />' .// Icon title.
	_('Review Translated Descriptions') . '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');

//update database if update pressed
if(isset($_POST['Submit'])) {
	for ($i=1;$i<count($_POST);$i++) { //loop through the returned translations

		if(isset($_POST['Revised' . $i]) AND ($_POST['Revised' . $i] == '1')) {
			$sqlUpdate="UPDATE stockdescriptiontranslations
						SET needsrevision = '0',
							descriptiontranslation = '". $_POST['DescriptionTranslation' .$i] ."',
							longdescriptiontranslation = '". $_POST['LongDescriptionTranslation' .$i] ."'
						WHERE stockid = '". $_POST['StockID' .$i] ."'
							AND language_id = '". $_POST['LanguageID' .$i] ."'";
			$ResultUpdate = DB_Query($sqlUpdate,'', '', true);
			prnMsg($_POST['StockID' .$i] . ' ' . _('descriptions') . ' ' .  _('in') . ' ' . $_POST['LanguageID' .$i] . ' ' . _('have been updated'),'success');
		}
	}
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">
		<tr>
			<th colspan="7">' . _('Translations to revise') .'</th>
		</tr>';

$sql = "SELECT stockdescriptiontranslations.stockid,
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

$result = DB_query($sql);

echo '<tr>
	<th>' . _('Code') . '</th>
	<th>' . _('Language') . '</th>
	<th>' . _('Description') . '</th>
	<th>' . _('Long Description') . '</th>
	<th>' . _('Revised?') . '</th>
</tr>';

$k=0; //row colour counter
$i=1;
while($myrow=DB_fetch_array($result)) {

	if($k==1) {
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	echo '<td>' . $myrow['stockid'] . '</td>
		<td>'. $_SESSION['Language']. '</td>
		<td>' . $myrow['description'] . '</td>
		<td>' . $myrow['longdescription'] . '</td>
		</tr>';

	if($k==1) {
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	echo '<td></td>
		<td>' . $myrow['language_id'] . '</td>';

	echo '<td>
			<input type="text" class="text" name="DescriptionTranslation' . $i .'" maxlength="50" size="52" value="'. $myrow['descriptiontranslation'] .'" />
		</td>
		<td>
			<textarea name="LongDescriptionTranslation' . $i .'" cols="70" rows="5">'. $myrow['longdescriptiontranslation'] .'" </textarea></td>
		</td>';

	echo '<td><input type="checkbox" name="Revised' . $i.'" value="1" />
		</td>
		</tr>';
	echo '<input type="hidden" value="' . $myrow['stockid'] . '" name="StockID' . $i . '" />
		<input type="hidden" value="' . $myrow['language_id'] . '" name="LanguageID' . $i . '" />';
	$i++;

} //end of looping

echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="Submit" value="' . _('Update') . '" /></div>
		</div>
	</form>';

include('includes/footer.inc');
?>