<?php

require(__DIR__ . '/includes/session.php');

$Title = __('EDI Message Format');
$ViewTopic = 'EDI';
$BookMark = '';
include('includes/header.php');

if (isset($_GET['PartnerCode'])){
	$PartnerCode = $_GET['PartnerCode'];
} elseif (isset($_POST['PartnerCode'])){
	$PartnerCode = $_POST['PartnerCode'];
}

if (isset($_GET['MessageType'])){
	$MessageType = $_GET['MessageType'];
} elseif (isset($_POST['MessageType'])){
	$MessageType = $_POST['MessageType'];
}

if (isset($_GET['SelectedMessageLine'])){
	$SelectedMessageLine = $_GET['SelectedMessageLine'];
}elseif (isset($_POST['SelectedMessageLine'])){
	$SelectedMessageLine = $_POST['SelectedMessageLine'];
}

if (isset($_POST['NewEDIInvMsg'])){
	$SQL = "INSERT INTO edimessageformat (partnercode,
						messagetype,
						sequenceno,
						section,
						linetext)
			SELECT '".$PartnerCode."',
				'INVOIC',
				sequenceno,
				section,
				linetext
			FROM edimessageformat
			WHERE partnercode='DEFAULT'
			AND messagetype='INVOIC'";

	$ErrMsg = __('There was an error inserting the default template invoice message records for') . ' ' . $PartnerCode . ' ' . __('because');
	$Result = DB_query($SQL, $ErrMsg);
}

$InputError = 0;
if ($InputError !=1 AND isset($_POST['update'])) {

	/*SelectedMessageLine could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
	if (!isset($SelectedMessageLine)) {
		$SelectedMessageLine='';
	}
	$SQL = "UPDATE edimessageformat
			SET partnercode='" . $PartnerCode . "',
				messagetype='" . $MessageType . "',
				section='" . $_POST['Section'] . "',
				sequenceno='" . $_POST['SequenceNo'] . "',
				linetext='" . $_POST['LineText'] . "'
			WHERE id = '" . $SelectedMessageLine . "'";
	$Result = DB_query($SQL);
	$Msg = __('Message line updated');
	unset ($SelectedMessageLine);

} elseif ($InputError !=1 AND isset($_POST['submit'])) {

/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new message line form */

	$SQL = "INSERT INTO edimessageformat (
				partnercode,
				messagetype,
				section,
				sequenceno,
				linetext)
			VALUES (
				'" . $PartnerCode . "',
				'" . $MessageType . "',
				'" . $_POST['Section'] . "',
				'" . $_POST['SequenceNo'] . "',
				'" . $_POST['LineText'] . "'
				)";
		$Msg = __('Message line added');
	//run the SQL from either of the above possibilites
	$Result = DB_query($SQL);
	unset ($SelectedMessageLine);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button


	$SQL="DELETE FROM edimessageformat WHERE id='" . $_GET['delete']."'";
	$Result = DB_query($SQL);
	$Msg=__('The selected message line has been deleted');

}
if (isset($Msg)) {
	prnMsg($Msg,'success');
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
	<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (!isset($SelectedMessageLine)) {

/* A message line could be posted when one has been edited and is being updated or GOT when selected for modification SelectedMessageLine will exist because it was sent with the page in a GET .
 If its the first time the page has been displayed with no parameters
then none of the above are true and the list of message lines will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT id,
				section,
				sequenceno,
				linetext
			FROM edimessageformat
			WHERE partnercode='" . $PartnerCode . "'
			AND messagetype='" . $MessageType . "'
			ORDER BY sequenceno";

	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<tr><th colspan="5"><h3>' . __('Definition of') . ' ' . $MessageType . ' ' . __('for') . ' ' . $PartnerCode . '</h3></th></tr>';
	$TableHeader = '<tr>
					<th>' . __('Section') . '</th>
					<th>' . __('Sequence') . '</th>
					<th>' . __('Format String') . '</th>
					</tr>';
	echo $TableHeader;

	while ($MyRow = DB_fetch_row($Result)) {

		echo '<tr class="striped_row">
				<td>', $MyRow[1], '</td>
				<td class="number">', $MyRow[2], '</td>
				<td>', $MyRow[3], '</td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '?SelectedMessageLine=', $MyRow[0], '">' . __('Edit') . '</a></td>
				<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '?delete=', $MyRow[0], '">' . __('Delete') . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
	if (DB_num_rows($Result)==0){
		echo '<div class="centre">
				<input tabindex="1" type="submit" name="NewEDIInvMsg" value="' . __('Create New EDI Invoice Message From Default Template') . '" />
			</div>';
	}
} //end of ifs SelectedLine is not set

if (isset($SelectedMessageLine)) {
	//editing an existing message line

	$SQL = "SELECT messagetype,
			partnercode,
			section,
			sequenceno,
			linetext
		FROM edimessageformat
		WHERE id='" . $SelectedMessageLine . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['Section']  = $MyRow['section'];
	$_POST['SequenceNo']  = $MyRow['sequenceno'];
	$_POST['LineText']  = $MyRow['linetext'];

	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?MessageType=INVOIC&amp;PartnerCode=' . $MyRow['partnercode'] . '">' . __('Review Message Lines') . '</a></div>';

	echo '<input type="hidden" name="SelectedMessageLine" value="' . $SelectedMessageLine . '" />';
	echo '<input type="hidden" name="MessageType" value="' . $MyRow['messagetype'] . '" />';
	echo '<input type="hidden" name="PartnerCode" value="' . $MyRow['partnercode'] . '" />';
} else { //end of if $SelectedMessageLine only do the else when a new record is being entered
	echo '<input type="hidden" name="MessageType" value="' . $MessageType . '" />';
	echo '<input type="hidden" name="PartnerCode" value="' . $PartnerCode . '" />';
}

echo '<fieldset>
		<legend>', __('Message Format Details'), '</legend>';

if (isset($MyRow['messagetype']) and $MyRow['messagetype']!='') {
	echo '<field><th colspan="2">' . __('Definition of') . ' ' . $MyRow['messagetype'] . ' ' . __('for') . ' ' . $MyRow['partnercode'] . '</th></field>';
}

echo '<field>
		<label for="Section">' . __('Section') . ':</label>';
echo '<select tabindex="2" name="Section">';

if ($_POST['Section']=='Heading') {
	echo '<option selected="selected" value="Heading">' . __('Heading') . '</option>';
} else {
	echo '<option value="Heading">' . __('Heading') . '</option>';
}

if (isset($_POST['Section']) AND $_POST['Section']=='Detail') {
	echo '<option selected="selected" value="Detail">' . __('Detail') . '</option>';
} else {
	echo '<option value="Detail">' . __('Detail') . '</option>';
}
if (isset($_POST['Section']) AND $_POST['Section']=='Summary') {
	echo '<option selected="selected" value="Summary">' . __('Summary') . '</option>';
} else {
	echo '<option value="Summary">' . __('Summary') . '</option>';
}

echo '</select>';

if (!isset($_POST['SequenceNo'])) {$_POST['SequenceNo']='';}
if (!isset($_POST['LineText'])) {$_POST['LineText']='';}


echo '</field>';

echo '<field>
		<label for="SequenceNo">Sequence Number:</label>
		<input tabindex="3" type="text" name="SequenceNo" size="3" maxlength="3" value="'.$_POST['SequenceNo'].'" />
	</field>';

echo '<field>
		<label for="LineText">' .  __('Line Text') . ':'  . '</label>
		<input tabindex="4" type="text" name="LineText" size="50" maxlength="50" value="'.$_POST['LineText'] .'" />
	</field>';

echo '</fieldset>';

if (isset($_GET['SelectedMessageLine'])) {
	echo '<div class="centre">
			<input tabindex="5" type="submit" name="update" value="'.  __('Update Information'). '" />
		</div>';
} else {
	echo '<div class="centre">
			<input tabindex="5" type="submit" name="submit" value="'.  __('Enter Information'). '" />
		</div>';
}
echo '</form>';

include('includes/footer.php');
