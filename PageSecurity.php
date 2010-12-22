<?php


include('includes/session.inc');

$title = _('Page Security Levels');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/security.png" title="' . _('Page Security Levels') . '" alt="" />' . ' ' . $title.'</p><br />';

if (isset($_POST['update'])) {
	foreach ($_POST as $key => $value) {
		if ($key!='update' and $key!='FormID') {
			$key=substr($key, 0, strlen($key)-4).'.php';
			$sql="UPDATE pagesecurity SET security='".$value."' WHERE script='".$key."'";
			$updateresult=DB_query($sql, $db);
		}
	}
}

$sql="SELECT script,
			security
		FROM pagesecurity";

$result=DB_query($sql, $db);

echo '<br /><form method="post" id="AccountGroups" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class="selection">';

$TokenSql="SELECT tokenid,
					tokenname
				FROM securitytokens";
$TokenResult=DB_query($TokenSql, $db);

while ($myrow=DB_fetch_array($result)) {
	echo '<tr><td>'.$myrow['script'].'</td>';
	echo '<td><select name="'.$myrow['script'].'">';
	while ($mytokenrow=DB_fetch_array($TokenResult)) {
		if ($mytokenrow['tokenid']==$myrow['security']) {
			echo '<option selected="True" value="'.$mytokenrow['tokenid'].'">'.htmlentities($mytokenrow['tokenname']).'</option>';
		} else {
			echo '<option value="'.$mytokenrow['tokenid'].'">'.htmlentities($mytokenrow['tokenname']).'</option>';
		}
	}
	echo '</select></td></tr>';
	DB_data_seek($TokenResult, 0);
}

echo '</table><br />';

echo '<div class="centre"><input type="submit" name="update" value="'._('Update Security Levels').'" /></div><br /></form>';

include('includes/footer.inc');

?>