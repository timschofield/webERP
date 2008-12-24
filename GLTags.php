<?php

$PageSecurity = 10;
include('includes/session.inc');
$title = _('General Ledger Tags');

include('includes/header.inc');

if (isset($_GET['SelectedTag'])) {
	$sql='SELECT tagref, tagdescription FROM tags where tagref='.$_GET['SelectedTag'];
	$result= DB_query($sql,$db);
	$myrow = DB_fetch_array($result,$db);
	$ref=$myrow[0];
	$description=$myrow[1];
	$department=$myrow[2];
}

if (isset($_POST['submit'])) {
	$sql = 'insert into tags values(NULL, "'.$_POST['description'].'")';
	$result= DB_query($sql,$db);
}

if (isset($_POST['update'])) {
	$sql = 'update tags set tagdescription="'.$_POST['description'].
		'" where tagref="'.$_POST['reference'].'"';
	$result= DB_query($sql,$db);
}

echo "<center><FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
echo '<BR><table><tr>';


echo '<td>'. _('Description') . '</td>
		<td><input type="text" SIZE=30 MAXLENGTH=30 name="description" value="'.$description.'"></td><td>
		<input type="hidden" name="reference" value="'.$_GET['SelectedTag'].'">';

if ($_GET['Action']=='edit') {
	echo '<CENTER><input type=Submit name=update value=' . _('Update') . '>';
} else {
	echo '<CENTER><input type=Submit name=submit value=' . _('Insert') . '>';
}

echo '</td></tr></table><p></p>';

echo '</form></center>';

echo '<center><table>';
echo '<tr><th>'. _('Tag ID') .'</th>';
echo '<th>'. _('Description'). '</th>';

$sql='SELECT tagref, tagdescription FROM tags order by tagref';
$result= DB_query($sql,$db);

while ($myrow = DB_fetch_array($result,$db)){
	echo '<tr><td>'.$myrow[0].'</td><td>'.$myrow[1].'</td><td><A HREF="' .
		$_SERVER['PHP_SELF'] . '?' . SID . '&SelectedTag=' . $myrow[0] . '&Action=edit">' . _('Edit') . '</A></td></tr>';
}

echo '</table><p></p></center>';

include('includes/footer.inc');

?>