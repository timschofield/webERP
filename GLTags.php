<?php

/* $Id$*/

include('includes/session.inc');
$title = _('Maintain General Ledger Tags');

include('includes/header.inc');

if (isset($_GET['SelectedTag'])) {
	$sql="SELECT tagref, tagdescription FROM tags where tagref='".$_GET['SelectedTag']."'";
	$result= DB_query($sql,$db);
	$myrow = DB_fetch_array($result,$db);
	$ref=$myrow[0];
	$description=$myrow[1];
} else {
	$description='';
	$_GET['SelectedTag']='';
}

if (isset($_POST['submit'])) {
	$sql = "INSERT INTO tags values(NULL, '".$_POST['description']."')";
	$result= DB_query($sql,$db);
}

if (isset($_POST['update'])) {
	$sql = "UPDATE tags SET tagdescription='".$_POST['description'].
		"' WHERE tagref='".$_POST['reference']."'";
	$result= DB_query($sql,$db);
}
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' .
		_('Print') . '" alt="" />' . ' ' . $title . '</p>';

echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . ' name="form">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br><table><tr>';


echo '<td>'. _('Description') . '</td>
		<td><input type="text" size=30 maxlength=30 name="description" value="'.$description.'"></td><td>
		<input type="hidden" name="reference" value="'.$_GET['SelectedTag'].'">';

if (isset($_GET['Action']) and $_GET['Action']=='edit') {
	echo '<input type="submit" name=update value=' . _('Update') . '>';
} else {
	echo '<input type="submit" name=submit value=' . _('Insert') . '>';
}

echo '</td></tr></table><p></p>';

echo '</form>';

echo '<table class=selection>';
echo '<tr><th>'. _('Tag ID') .'</th>';
echo '<th>'. _('Description'). '</th>';

$sql="SELECT tagref, tagdescription FROM tags order by tagref";
$result= DB_query($sql,$db);

while ($myrow = DB_fetch_array($result,$db)){
	echo '<tr><td>'.$myrow[0].'</td><td>'.$myrow[1].'</td>
			<td><a href="' . $_SERVER['PHP_SELF'] . '?SelectedTag=' . $myrow[0] . '&Action=edit">' . _('Edit') . '</a></td></tr>';
}

echo '</table><p></p>';

echo '<script>defaultControl(document.form.description);</script>';

include('includes/footer.inc');

?>