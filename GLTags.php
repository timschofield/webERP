<?php

/* $Id$*/

include('includes/session.inc');
$title = _('Maintain General Ledger Tags');

include('includes/header.inc');

if (isset($_GET['SelectedTag'])) {
	if($_GET['Action']=='delete'){
		//first off test there are no transactions created with this tag
		$Result = DB_query("SELECT counterindex 
							FROM gltrans 
							WHERE tag='" . $_GET['SelectedTag'] . "'",$db);
		if (DB_num_rows($Result)>0){
			prnMsg(_('This tag cannot be deleted since there are already general ledger transactions created using it.'),'error');
		} else	{
			$Result = DB_query("DELETE FROM tags WHERE tagref='" . $_GET['SelectedTag'] . "'",$db);
			prnMsg(_('The selected tag has been deleted'),'success');
		}
		$Description='';
	} else {
		$sql="SELECT tagref, 
					tagdescription 
				FROM tags 
				WHERE tagref='".$_GET['SelectedTag']."'";
			
		$result= DB_query($sql,$db);
		$myrow = DB_fetch_array($result,$db);
		$ref=$myrow['tagref'];
		$Description = $myrow['tagdescription'];
	}
} else {
	$Description='';
	$_GET['SelectedTag']='';
}

if (isset($_POST['submit'])) {
	$sql = "INSERT INTO tags values(NULL, '" . $_POST['Description'] . "')";
	$result= DB_query($sql,$db);
}

if (isset($_POST['update'])) {
	$sql = "UPDATE tags SET tagdescription='" . $_POST['Description'] . "' 
		WHERE tagref='".$_POST['reference']."'";
	$result= DB_query($sql,$db);
}
echo '<p class="page_title_text">
		<img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' .
		_('Print') . '" alt="" />' . ' ' . $title . '
	</p>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" name="form">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br />
	<table>
	<tr>
		<td>'. _('Description') . '</td>
		<td><input type="text" size="30" maxlength="30" name="Description" value="'.$Description.'"></td>
		<td><input type="hidden" name="reference" value="'.$_GET['SelectedTag'].'">';

if (isset($_GET['Action']) AND $_GET['Action']=='edit') {
	echo '<input type="submit" name="update" value="' . _('Update') . '" />';
} else {
	echo '<input type="submit" name="submit" value="' . _('Insert') . '" />';
}

echo '</td>
	</tr>
	</table>
	<p />
	</form>
	<table class="selection">
	<tr>
		<th>'. _('Tag ID') .'</th>
		<th>'. _('Description'). '</th>
	</tr>';

$sql="SELECT tagref, 
			tagdescription 
		FROM tags 
		ORDER BY tagref";
		
$result= DB_query($sql,$db);

while ($myrow = DB_fetch_array($result,$db)){
	echo '<tr>
			<td>' . $myrow['tagref'] . '</td>
			<td>' . $myrow['tagdescription'] . '</td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?SelectedTag=' . $myrow['tagref'] . '&Action=edit">' . _('Edit') . '</a></td>
			<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?SelectedTag=' . $myrow['tagref'] . '&Action=delete" onclick="return confirm(\'' . _('Are you sure you wish to delete this GL tag?') . '\');">' . _('Delete') . '</a></td>
		</tr>';
}

echo '</table>';

echo '<script>defaultControl(document.form.Description);</script>';

include('includes/footer.inc');

?>