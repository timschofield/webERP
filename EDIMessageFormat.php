<?php
/* $Revision: 1.10 $ */

$PageSecurity = 10;

include('includes/session.inc');
$title = _('EDI Message Format');
include('includes/header.inc');

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
	$sql = "INSERT INTO edimessageformat (partnercode,
						messagetype,
						sequenceno,
						section,
						linetext)
		SELECT '$PartnerCode',
			'INVOIC',
			sequenceno,
			section,
			linetext
		FROM edimessageformat
		WHERE partnercode='DEFAULT'
			AND messagetype='INVOIC'";

	$ErrMsg = _('There was an error inserting the default template invoice message records for') . ' ' . $PartnerCode . ' ' . _('because');
	$result = DB_query($sql,$db,$ErrMsg);
}


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	/*
	 if (($_POST['PandL']!=1 AND $_POST['PandL']!=0 AND $_POST['PandL']!=-1)) {
		$InputError = 1;
		echo "The profit and loss account flag must be either 1 or 0. 1 indicates that the account is a profit and loss account";
	}
	*/

	if ($InputError !=1) {

		/*SelectedMessageLine could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		if (!isset($SelectedMessageLine)) {$SelectedMessageLine='';}
		$sql = "UPDATE edimessageformat
				SET
					partnercode='" . $PartnerCode . "',
					messagetype='" . $MessageType . "',
					section='" . $_POST['Section'] . "',
					sequenceno=" . $_POST['SequenceNo'] . ",
					linetext='" . $_POST['LineText'] . "'
				WHERE id = '" . $SelectedMessageLine . "'";

		$msg = _('Message line updated');

	} elseif ($InputError !=1) {

	/*Selected group is null cos no item selected on first time round so must be adding a record must be submitting new entries in the new message line form */

		$sql = "INSERT INTO edimessageformat (
					partnercode,
					messagetype,
					section,
					sequenceno,
					linetext)
				VALUES (
					'" . $PartnerCode . "',
					'" . $MessageType . "',
					'" . $_POST['Section'] . "',
					" . $_POST['SequenceNo'] . ",
					'" . $_POST['LineText'] . "'
					)";
		$msg = _('Message line added');
	}
	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	prnMsg($msg,'success');
	unset ($SelectedMessageLine);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button


	$sql='DELETE FROM edimessageformat WHERE id=' . $_GET['delete'];
	$result = DB_query($sql,$db);
	prnMsg(_('The selected message line has been deleted'),'success');

}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

if (!isset($SelectedMessageLine)) {

/* A message line could be posted when one has been edited and is being updated or GOT when selected for modification SelectedMessageLine will exist because it was sent with the page in a GET .
 If its the first time the page has been displayed with no parameters
then none of the above are true and the list of message lines will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	echo '<font size=4>' . _('Definition of') . ' ' . $MessageType . ' ' . _('for') . ' ' . $PartnerCode;

	$sql = "SELECT id,
			section,
			sequenceno,
			linetext
		FROM edimessageformat
		WHERE partnercode='" . $PartnerCode . "'
		AND messagetype='" . $MessageType . "'
		ORDER BY sequenceno";

	$result = DB_query($sql,$db);

	echo '<table>';
	$TableHeader = "<tr>
			<th>" . _('Section') . "</th>
			<th>" . _('Sequence') . "</th>
			<th>" . _('Format String') . "</th>
			</tr>";
	echo $TableHeader;

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}


		printf("<td>%s</td>
			<td class=number>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedMessageLine=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&delete=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow[1],
			$myrow[2],
			$myrow[3],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0],
			$_SERVER['PHP_SELF'] . '?' . SID,
			$myrow[0]);

	} //END WHILE LIST LOOP
	echo '</table><p>';
	if (DB_num_rows($result)==0){
		echo "<div class='centre'><input tabindex=1 type=submit name='NewEDIInvMsg' VALUE='" . _('Create New EDI Invoice Message From Default Template') . "'></div>";
	}
} //end of ifs SelectedLine is not set




if (isset($SelectedMessageLine)) {
	//editing an existing message line

	$sql = 'SELECT messagetype,
			partnercode,
			section,
			sequenceno,
			linetext
		FROM edimessageformat
		WHERE id=' . $SelectedMessageLine;

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);


	$_POST['Section']  = $myrow['section'];
	$_POST['SequenceNo']  = $myrow['sequenceno'];
	$_POST['LineText']  = $myrow['linetext'];

	echo '<font size=4>' . _('Definition of') . ' ' . $myrow['messagetype'] . ' ' . _('for') . ' ' . $myrow['partnercode'];

	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . 'MessageType=INVOIC&PartnerCode=' . $myrow['partnercode'] . "'>" . _('Review Message Lines') . '</a></div>';

	echo "<input type=hidden name='SelectedMessageLine' VALUE='" . $SelectedMessageLine . "'>";
	echo "<input type=hidden name='MessageType' VALUE='" . $myrow['messagetype'] . "'>";
	echo "<input type=hidden name='PartnerCode' VALUE='" . $myrow['partnercode'] . "'>";
} else { //end of if $SelectedMessageLine only do the else when a new record is being entered
	echo "<input type=hidden name='MessageType' VALUE='" . $MessageType . "'>";
	echo "<input type=hidden name='PartnerCode' VALUE='" . $PartnerCode . "'>";
}

echo '<table>';

echo '<tr><td>Section:</td>';
echo '<td>';
echo '<select tabindex=2 name="Section">';

if ($_POST['Section']=='Heading') {
	echo "<option selected VALUE='Heading'>" . _('Heading');
} else {
	echo "<option value='Heading'>" . _('Heading');
}

if (isset($_POST['Section']) and $_POST['Section']=='Detail') {
	echo "<option selected VALUE='Detail'>" . _('Detail');
} else {
	echo "<option value='Detail'>" . _('Detail');
}
if (isset($_POST['Section']) and $_POST['Section']=='Summary') {
	echo "<option selected VALUE='Summary'>" . _('Summary');
} else {
	echo "<option value='Summary'>" . _('Summary');
}

echo '</select>';

if (!isset($_POST['SequenceNo'])) {$_POST['SequenceNo']='';}
if (!isset($_POST['LineText'])) {$_POST['LineText']='';}

?>

</td></tr>

<tr><td>Sequence Number:</td>
<td><input tabindex=3 type=TEXT name=SequenceNo size=3 maxlength=3 VALUE=<?php echo $_POST['SequenceNo'] ?>>
</td></tr>
<tr><td><?php echo _('Line Text') . ':'; ?></td>
<td>
<input tabindex=4 type="Text" name="LineText" size=50 maxlength=50 VALUE=<?php echo  $_POST['LineText']; ?>>
</td></tr>
</table>
<div class="centre"><input TABINDEX=5 type="Submit" name="submit" value="<?php echo _('Enter Information'); ?>"></div>

</form>

<?php
include('includes/footer.inc');
?>