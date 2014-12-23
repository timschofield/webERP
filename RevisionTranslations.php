<?php
/* $Id: RevisionTranslations.php 6951 2014-10-29 06:29:22Z daintree $*/

include('includes/session.inc');
$Title = _('Revision of Description Translations');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

/*
if (isset($_POST['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])){
	$SelectedTabs = mb_strtoupper($_GET['SelectedTabs']);
}

if (isset($_POST['SelectedIndex'])){
	$SelectedIndex = $_POST['SelectedIndex'];
} elseif (isset($_GET['SelectedIndex'])){
	$SelectedIndex = $_GET['SelectedIndex'];
}

if (isset($_POST['Days'])){
	$Days = filter_number_format($_POST['Days']);
} elseif (isset($_GET['Days'])){
	$Days = filter_number_format($_GET['Days']);
}

if (isset($_POST['Process'])) {
	if ($SelectedTabs=='') {
		prnMsg(_('You Must First Select a Petty Cash Tab To Authorise'),'error');
		unset($SelectedTabs);
	}
}

if (isset($_POST['Go'])) {
	if ($Days<=0) {
		prnMsg(_('The number of days must be a positive number'),'error');
		$Days=30;
	}
}

if (isset($SelectedTabs)) {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Petty Cash') .
		'" alt="" />' . _('Authorisation Of Petty Cash Expenses') . ' '.$SelectedTabs . '</p>';
} else {
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Petty Cash') .
		'" alt="" />' . _('Authorisation Of Petty Cash Expenses') . '</p>';
}

*/
if (isset($_POST['Submit']) or isset($_POST['update']) OR isset($SelectedTabs) OR isset ($_POST['GO'])) {

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
		<th>' . _('Description') . '</th>
		<th>' . _('Long Description') . '</th>
		<th>' . _('Language') . '</th>
		<th>' . _('Translated Description') . '</th>
		<th>' . _('Translated long Description') . '</th>
		<th>' . _('Revised?') . '</th>
	</tr>';

	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($result))	{
 
	//update database if update pressed
		if (isset($_POST['Submit'])
			AND $_POST['Submit']==_('Update')
			AND isset($_POST[$myrow['counterindex']])){

			$sqlUpdate="UPDATE stockdescriptiontranslations 
						SET needsrevision = '0',
							descriptiontranslation = '". $myrow['descriptiontranslation'] ."',
							longdescriptiontranslation = '". $myrow['longdescriptiontranslation'] ."'
						WHERE stockid = '". $myrow['stockid'] ."'
							AND language_id = '". $myrow['language_id'] ."'";
			$ResultUpdate = DB_Query($sqlUpdate,'', '', true);

		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		echo'<td>' . $myrow['stockid'] . '</td>
			<td>' . $myrow['description'] . '</td>
			<td>' . $myrow['longdescription'] . '</td>
			<td>' . $myrow['language_id'] . '</td>
			<td>' . $myrow['descriptiontranslation'] . '</td>
			<td>' . $myrow['longdescriptiontranslation'] . '</td>';

		echo '<td align="right"><input type="checkbox" name="'.$myrow['counterindex'].'" />';
	
		echo '<input type="hidden" name="SelectedIndex" value="' . $myrow['counterindex']. '" />';
		echo '</td></tr>';


	} //end of looping

	echo '</table>
			<br />
			<div class="centre">
				<input type="submit" name="Submit" value="' . _('Update') . '" /></div>
			</div>
		</form>';


}

include('includes/footer.inc');
?>
