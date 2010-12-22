<?php

/* $Id$*/

//$PageSecurity=15;

include('includes/session.inc');

$title = _('Purchase Order Authorisation Maintenance');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/group_add.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'</p><br>';
$User='';
$Currency='';
$CanCreate=1;
$OffHold=1;
$AuthLevel=0;
if (isset($_POST['Submit'])) {
	if (isset($_POST['cancreate']) and $_POST['cancreate']=='on') {
		$cancreate=0;
	} else {
		$cancreate=1;
	}
	if (isset($_POST['offhold']) and $_POST['offhold']=='on') {
		$offhold=0;
	} else {
		$offhold=1;
	}
	if ($_POST['authlevel']=='') {
		$_POST['authlevel']=0;
	}
	$sql="SELECT COUNT(*)
			FROM purchorderauth
			WHERE userid='".$_POST['userid']."'
		AND currabrev='".$_POST['currabrev']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);
	if ($myrow[0]==0) {
		$sql="INSERT INTO purchorderauth (
			userid,
			currabrev,
			cancreate,
			offhold,
			authlevel)
			VALUES(
			'".$_POST['userid']."',
			'".$_POST['currabrev']."',
			'".$cancreate."',
			'".$offhold."',
			'".$_POST['authlevel']."')";
		$ErrMsg = _('The authentication details cannot be inserted because');
		$Result=DB_query($sql,$db,$ErrMsg);
	} else {
		prnMsg(_('There already exists an entry for this user/currency combination'), 'error');
		echo '<br />';
	}
}

if (isset($_POST['Update'])) {
	if (isset($_POST['cancreate']) and $_POST['cancreate']=='on') {
		$cancreate=0;
	} else {
		$cancreate=1;
	}
	if (isset($_POST['offhold']) and $_POST['offhold']=='on') {
		$offhold=0;
	} else {
		$offhold=1;
	}
	$sql="UPDATE purchorderauth SET
			cancreate='".$cancreate."',
			offhold='".$offhold."',
			authlevel='".$_POST['authlevel']."'
		WHERE userid='".$_POST['userid']."'
		AND currabrev='".$_POST['currabrev']."'";

	$ErrMsg = _('The authentication details cannot be updated because');
	$Result=DB_query($sql,$db,$ErrMsg);
}

if (isset($_GET['Delete'])) {
	$sql="DELETE FROM purchorderauth
		WHERE userid='".$_GET['UserID']."'
		AND currabrev='".$_GET['Currency']."'";

	$ErrMsg = _('The authentication details cannot be deleted because');
	$Result=DB_query($sql,$db,$ErrMsg);
}

if (isset($_GET['Edit'])) {
	$sql="SELECT cancreate,
				offhold,
				authlevel
			FROM purchorderauth
		WHERE userid='".$_GET['UserID']."'
		AND currabrev='".$_GET['Currency']."'";
	$ErrMsg = _('The authentication details cannot be retrieved because');
	$result=DB_query($sql,$db,$ErrMsg);
	$myrow=DB_fetch_array($result);
	$UserID=$_GET['UserID'];
	$Currency=$_GET['Currency'];
	$CanCreate=$myrow['cancreate'];
	$OffHold=$myrow['offhold'];
	$AuthLevel=$myrow['authlevel'];
}

$sql="SELECT
	purchorderauth.userid,
	www_users.realname,
	currencies.currabrev,
	currencies.currency,
	purchorderauth.cancreate,
	purchorderauth.offhold,
	purchorderauth.authlevel
	FROM (purchorderauth
	LEFT JOIN www_users ON purchorderauth.userid=www_users.userid)
	LEFT JOIN currencies ON purchorderauth.currabrev=currencies.currabrev";

$ErrMsg = _('The authentication details cannot be retrieved because');
$Result=DB_query($sql,$db,$ErrMsg);

echo '<table class=selection><tr>';
echo '<th>'._('User ID').'</th>';
echo '<th>'._('User Name').'</th>';
echo '<th>'._('Currency').'</th>';
echo '<th>'._('Create Order').'</th>';
echo '<th>'._('Can Release').'<br />'. _('Invoices').'</th>';
echo '<th>'._('Authority Level').'</th></tr>';

while ($myrow=DB_fetch_array($Result)) {
	if ($myrow['cancreate']==0) {
		$cancreate=_('Yes');
	} else {
		$cancreate=_('No');
	}
	if ($myrow['offhold']==0) {
		$offhold=_('Yes');
	} else {
		$offhold=_('No');
	}
	echo '<tr><td>'.$myrow['userid'].'</td>';
	echo '<td>'.$myrow['realname'].'</td>';
	echo '<td>'.$myrow['currency'].'</td>';
	echo '<td>'.$cancreate.'</td>';
	echo '<td>'.$offhold.'</td>';
	echo '<td class="number">'.number_format($myrow['authlevel'],2).'</td>';
	echo '<td><a href="'.$rootpath.'/PO_AuthorisationLevels.php?' . SID . 'Edit=Yes&UserID=' . $myrow['userid'] .
	 '&Currency='.$myrow['currabrev'].'">'._('Edit').'</td>';
	echo '<td><a href="'.$rootpath.'/PO_AuthorisationLevels.php?' . SID . 'Delete=Yes&UserID=' . $myrow['userid'] .
	 '&Currency='.$myrow['currabrev'].'">'._('Delete').'</td></tr>';
}

echo '</table><br><br>';

echo "<form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method=post name='form1'>";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class=selection>';

if (isset($_GET['Edit'])) {
	echo '<tr><td>'._('User ID').'</td><td>'.$UserID.'</td></tr>';
	echo '<input type=hidden name=userid value="'.$UserID.'"';
} else {
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
}

if (isset($_GET['Edit'])) {
	$currencysql='SELECT currency FROM currencies WHERE currabrev="'.$Currency.'"';
	$currencyresult=DB_query($currencysql,$db);
	$myrow=DB_fetch_array($currencyresult);
	echo '<tr><td>'._('Currency').'</td><td>'.$myrow['currency'].'</td></tr>';
	echo '<input type=hidden name=currabrev value="'.$Currency.'"';
} else {
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
}

echo '<tr><td>'._('User can create orders').'</td>';
if ($CanCreate==1) {
	echo '<td><input type=checkbox name=cancreate></td></tr>';
} else {
	echo '<td><input type=checkbox checked name=cancreate></td></tr>';
}

echo '<tr><td>'._('User can release invoices').'</td>';
if ($OffHold==1) {
	echo '<td><input type=checkbox name=offhold></td></tr>';
} else {
	echo '<td><input type=checkbox checked name=offhold></td></tr>';
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