<?php

/* $Id$*/
//$PageSecurity = 10;
include('includes/session.inc');
$title = _('Maintain Security Tokens');

include('includes/header.inc');

if (isset($_GET['SelectedToken'])) {
	$sql="SELECT tokenid, tokenname FROM securitytokens where tokenid='".$_GET['SelectedToken']."'";
	$result= DB_query($sql,$db);
	$myrow = DB_fetch_array($result,$db);
	$ref=$myrow[0];
	$description=$myrow[1];
} else if (!isset($_POST['TokenID'])){
	$description='';
	$_POST['TokenID']='';
	$_GET['SelectedToken']='';
} else {
	$description=$_POST['Description'];
}

if (isset($_POST['submit'])) {
	$TestSQL="SELECT tokenid FROM securitytokens WHERE tokenid='".$_POST['TokenID']."'";
	$TestResult=DB_query($TestSQL, $db);
	if (DB_num_rows($TestResult)==0) {
		$sql = "INSERT INTO securitytokens values('".$_POST['TokenID']."', '".$_POST['Description']."')";
		$result= DB_query($sql,$db);
		unset($description);
		unset($_POST['TokenID']);
	} else {
		prnMsg( _('This token ID has already been used. Please use a new one') , 'warn');
	}
}

if (isset($_POST['update'])) {
	$sql = "UPDATE securitytokens SET tokenname='".$_POST['Description'].
		"' WHERE tokenid='".$_POST['TokenID']."'";
	$result= DB_query($sql,$db);
	unset($description);
	unset($_POST['TokenID']);
}
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' .
		_('Print') . '" alt="" />' . ' ' . $title . '</p>';

echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '" name="form">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br><table><tr>';



if (isset($_GET['Action']) and $_GET['Action']=='edit') {
	echo '<td>'. _('Description') . '</td>
		<td><input type="text" size=30 maxlength=30 name="Description" value="'.$description.'"></td><td>
		<input type="hidden" name="TokenID" value="'.$_GET['SelectedToken'].'">';
	echo '<input type=Submit name=update value=' . _('Update') . '>';
} else {
	echo '<td>'._('Token ID') . '<td><input type="text" name="TokenID" value="'.$_POST['TokenID'].'"></td></tr>
		<tr><td>'. _('Description') . '</td><td><input type="text" size=30 maxlength=30 name="Description" value="'.$description.'"></td><td>';
	echo '<input type=Submit name=submit value=' . _('Insert') . '>';
}

echo '</td></tr></table><p></p>';

echo '</form>';

echo '<table class=selection>';
echo '<tr><th>'. _('Token ID') .'</th>';
echo '<th>'. _('Description'). '</th>';

$sql="SELECT tokenid, tokenname FROM securitytokens ORDER BY tokenid";
$result= DB_query($sql,$db);

while ($myrow = DB_fetch_array($result,$db)){
	echo '<tr><td>'.$myrow[0].'</td><td>'.$myrow[1].'</td><td><a href="' .
		$_SERVER['PHP_SELF'] . '?SelectedToken=' . $myrow[0] . '&Action=edit">' . _('Edit') . '</a></td></tr>';
}

echo '</table><p></p>';

echo "<script>defaultControl(document.form.description);</script>";

include('includes/footer.inc');

?>