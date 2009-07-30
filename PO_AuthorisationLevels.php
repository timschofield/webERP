<?php
$PageSecurity=15;

include('includes/session.inc');

$title = _('Purchase Order Authorisation Maintenance');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/group_add.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'</p><br>';
$User='';
$Currency='';
$CanCreate=0;
$AuthLevel=0;
if (isset($_POST['Submit'])) {
	if ($_POST['cancreate']=='on') {
		$cancreate=0;
	} else {
		$cancreate=1;
	}
	$sql='INSERT INTO purchorderauth VALUES(
		"'.$_POST['userid'].'",
		"'.$_POST['currabrev'].'",
		'.$cancreate.',
		'.$_POST['authlevel'].')';
	$ErrMsg = _('The authentication details cannot be inserted because');
	$Result=DB_query($sql,$db,$ErrMsg);
}

if (isset($_POST['Update'])) {
	if ($_POST['cancreate']=='on') {
		$cancreate=0;
	} else {
		$cancreate=1;
	}
	$sql='UPDATE purchorderauth SET
			cancreate='.$cancreate.',
			authlevel='.$_POST['authlevel'].'
		WHERE userid="'.$_POST['userid'].'"
		AND currabrev="'.$_POST['currabrev'].'"';

	$ErrMsg = _('The authentication details cannot be updated because');
	$Result=DB_query($sql,$db,$ErrMsg);
}

if (isset($_GET['Delete'])) {
	$sql='DELETE FROM purchorderauth 
		WHERE userid="'.$_GET['UserID'].'"
		AND currabrev="'.$_GET['Currency'].'"';

	$ErrMsg = _('The authentication details cannot be deleted because');
	$Result=DB_query($sql,$db,$ErrMsg);
}

if (isset($_GET['Edit'])) {
	$sql='SELECT cancreate,
				authlevel 
			FROM purchorderauth 
		WHERE userid="'.$_GET['UserID'].'"
		AND currabrev="'.$_GET['Currency'].'"';
	$ErrMsg = _('The authentication details cannot be retrieved because');
	$result=DB_query($sql,$db,$ErrMsg);
	$myrow=DB_fetch_array($result);
	$UserID=$_GET['UserID'];
	$Currency=$_GET['Currency'];
	$CanCreate=$myrow['cancreate'];
	$AuthLevel=$myrow['authlevel'];
}

$sql='SELECT 
	purchorderauth.userid, 
	www_users.realname,
	currencies.currabrev,
	currencies.currency,
	purchorderauth.cancreate,
	purchorderauth.authlevel 
	FROM (purchorderauth 
	LEFT JOIN www_users ON purchorderauth.userid=www_users.userid)
	LEFT JOIN currencies ON purchorderauth.currabrev=currencies.currabrev';

$ErrMsg = _('The authentication details cannot be retrieved because');
$Result=DB_query($sql,$db,$ErrMsg);

echo '<table><tr>';
echo '<th>'._('User ID').'</th>';
echo '<th>'._('User Name').'</th>';
echo '<th>'._('Currency').'</th>';
echo '<th>'._('Create Order').'</th>';
echo '<th>'._('Authority Level').'</th></tr>';

while ($myrow=DB_fetch_array($Result)) {
	if ($myrow['cancreate']==0) {
		$cancreate=_('Yes');
	} else {
		$cancreate=_('No');
	}
	echo '<tr><td>'.$myrow['userid'].'</td>';
	echo '<td>'.$myrow['realname'].'</td>';
	echo '<td>'.$myrow['currency'].'</td>';
	echo '<td>'.$cancreate.'</td>';
	echo '<td class="number">'.number_format($myrow['authlevel'],2).'</td>';
	echo '<td><a href="'.$rootpath.'/PO_AuthorisationLevels.php?' . SID . 'Edit=Yes&UserID=' . $myrow['userid'] .
	 '&Currency='.$myrow['currabrev'].'">'._('Edit').'</td>';
	echo '<td><a href="'.$rootpath.'/PO_AuthorisationLevels.php?' . SID . 'Delete=Yes&UserID=' . $myrow['userid'] .
	 '&Currency='.$myrow['currabrev'].'">'._('Delete').'</td></tr>';
}

echo '</table><br><br>';

echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method=post name='form1'>";
echo '<table>';

echo '<tr><td>'._('User ID').'</td><td><select name=userid>';
$usersql='SELECT userid FROM www_users';
$userresult=DB_query($usersql,$db);
while ($myrow=DB_fetch_array($userresult)) {
	if ($myrow['userid']==$UserID) {
		echo '<option selected value="'.$myrow['userid'].'">'.$myrow['userid'].'</option>';
	} else {
		echo '<option value="'.$myrow['userid'].'">'.$myrow['userid'].'</option>';
	}
}
echo '</select></td></tr>';

echo '<tr><td>'._('Currency').'</td><td><select name=currabrev>';
$currencysql='SELECT currabrev,currency FROM currencies';
$currencyresult=DB_query($currencysql,$db);
while ($myrow=DB_fetch_array($currencyresult)) {
	if ($myrow['currabrev']==$Currency) {
		echo '<option selected value="'.$myrow['currabrev'].'">'.$myrow['currency'].'</option>';
	} else {
		echo '<option value="'.$myrow['currabrev'].'">'.$myrow['currency'].'</option>';
	}
}
echo '</select></td></tr>';

echo '<tr><td>'._('User can create orders').'</td>';
if ($CanCreate==1) {
	echo '<td><input type=checkbox name=cancreate></td</tr>';
} else {
	echo '<td><input type=checkbox checked name=cancreate></td</tr>';
} 

echo '<tr><td>'._('User can authorise orders up to :').'</td>';
echo '<td><input type=input name=authlevel size=11 class=number value='.$AuthLevel.'></td</tr>';
echo '</table>';

if (isset($_GET['Edit'])) {
	echo '<br><div class="centre"><input type=submit name="Update" value="'._('Update Information').'"></div></form>';
} else {
	echo '<br><div class="centre"><input type=submit name="Submit" value="'._('Enter Information').'"></div></form>';
}
include('includes/footer.inc');
?>