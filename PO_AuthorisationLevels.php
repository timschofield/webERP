<?php

include('includes/session.php');

$Title = __('Purchase Order Authorisation Maintenance');
$ViewTopic = '';
$BookMark = 'PO_AuthorisationLevels';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/group_add.png" title="', // Icon image.
	$Title, '" /> ', // Icon title.
	$Title, '</p>';// Page title.


/*Note: If CanCreate==0 then this means the user can create orders
 *     Also if OffHold==0 then the user can release purchase invocies
 *     This logic confused me a bit to start with
 */


if (isset($_POST['Submit'])) {
	if (isset($_POST['CanCreate']) AND $_POST['CanCreate']=='on') {
		$CanCreate=0;
	} else {
		$CanCreate=1;
	}
	if (isset($_POST['OffHold']) AND $_POST['OffHold']=='on') {
		$OffHold=0;
	} else {
		$OffHold=1;
	}
	if ($_POST['AuthLevel']=='') {
		$_POST['AuthLevel']=0;
	}
	$SQL="SELECT COUNT(*)
		FROM purchorderauth
		WHERE userid='" . $_POST['UserID'] . "'
		AND currabrev='" . $_POST['CurrCode'] . "'";
	$Result = DB_query($SQL);
	$MyRow=DB_fetch_array($Result);
	if ($MyRow[0]==0) {
		$SQL="INSERT INTO purchorderauth ( userid,
						currabrev,
						cancreate,
						offhold,
						authlevel)
					VALUES( '".$_POST['UserID']."',
						'".$_POST['CurrCode']."',
						'".$CanCreate."',
						'".$OffHold."',
						'" . filter_number_format($_POST['AuthLevel'])."')";
	$ErrMsg = __('The authentication details cannot be inserted because');
	$Result = DB_query($SQL, $ErrMsg);
	} else {
		prnMsg(__('There already exists an entry for this user/currency combination'), 'error');
		echo '<br />';
	}
}

if (isset($_POST['Update'])) {
	if (isset($_POST['CanCreate']) AND $_POST['CanCreate']=='on') {
		$CanCreate=0;
	} else {
		$CanCreate=1;
	}
	if (isset($_POST['OffHold']) AND $_POST['OffHold']=='on') {
		$OffHold=0;
	} else {
		$OffHold=1;
	}
	$SQL="UPDATE purchorderauth SET
			cancreate='".$CanCreate."',
			offhold='".$OffHold."',
			authlevel='".filter_number_format($_POST['AuthLevel'])."'
			WHERE userid='".$_POST['UserID']."'
			AND currabrev='".$_POST['CurrCode']."'";

	$ErrMsg = __('The authentication details cannot be updated because');
	$Result = DB_query($SQL, $ErrMsg);
}

if (isset($_GET['Delete'])) {
	$SQL="DELETE FROM purchorderauth
		WHERE userid='".$_GET['UserID']."'
		AND currabrev='".$_GET['Currency']."'";

	$ErrMsg = __('The authentication details cannot be deleted because');
	$Result = DB_query($SQL, $ErrMsg);
}

if (isset($_GET['Edit'])) {
	$SQL="SELECT cancreate,
				offhold,
				authlevel
			FROM purchorderauth
			WHERE userid='".$_GET['UserID']."'
			AND currabrev='".$_GET['Currency']."'";
	$ErrMsg = __('The authentication details cannot be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow=DB_fetch_array($Result);
	$UserID=$_GET['UserID'];
	$Currency=$_GET['Currency'];
	$CanCreate=$MyRow['cancreate'];
	$OffHold=$MyRow['offhold'];
	$AuthLevel=$MyRow['authlevel'];
}

$SQL="SELECT purchorderauth.userid,
			www_users.realname,
			currencies.currabrev,
			currencies.currency,
			currencies.decimalplaces,
			purchorderauth.cancreate,
			purchorderauth.offhold,
			purchorderauth.authlevel
	FROM purchorderauth INNER JOIN www_users
		ON purchorderauth.userid=www_users.userid
	INNER JOIN currencies
		ON purchorderauth.currabrev=currencies.currabrev";

$ErrMsg = __('The authentication details cannot be retrieved because');
$Result = DB_query($SQL, $ErrMsg);

echo '<table class="selection">
		<thead>
			<tr>
				<th class="SortedColumn">' . __('User ID') . '</th>
				<th class="SortedColumn">' . __('User Name') . '</th>
				<th class="SortedColumn">' . __('Currency') . '</th>
				<th class="SortedColumn">' . __('Create Order') . '</th>
				<th class="SortedColumn">' . __('Can Release') . '<br />' .  __('Invoices') . '</th>
				<th class="SortedColumn">' . __('Authority Level') . '</th>
				<th colspan="2">&nbsp;</th>
			</tr>
		</thead>';

while ($MyRow=DB_fetch_array($Result)) {
	if ($MyRow['cancreate']==0) {
		$DisplayCanCreate=__('Yes');
	} else {
		$DisplayCanCreate=__('No');
	}
	if ($MyRow['offhold']==0) {
		$DisplayOffHold=__('Yes');
	} else {
		$DisplayOffHold=__('No');
	}
	echo '<tr class="striped_row">
			<td>' . $MyRow['userid'] . '</td>
			<td>' . $MyRow['realname'] . '</td>
			<td>', __($MyRow['currency']), '</td>
			<td>' . $DisplayCanCreate . '</td>
			<td>' . $DisplayOffHold . '</td>
			<td class="number">' . locale_number_format($MyRow['authlevel'],$MyRow['decimalplaces']) . '</td>
			<td><a href="'.$RootPath.'/PO_AuthorisationLevels.php?Edit=Yes&amp;UserID=' . $MyRow['userid'] .
	'&amp;Currency='.$MyRow['currabrev'].'">' . __('Edit') . '</a></td>
			<td><a href="'.$RootPath.'/PO_AuthorisationLevels.php?Delete=Yes&amp;UserID=' . $MyRow['userid'] .
	'&amp;Currency='.$MyRow['currabrev'].'" onclick="return confirm(\'' . __('Are you sure you wish to delete this authorisation level?') . '\');">' . __('Delete') . '</a></td>
		</tr>';
}

echo '</table>';

if (!isset($_GET['Edit'])) {
	$UserID=$_SESSION['UserID'];
	$Currency=$_SESSION['CompanyRecord']['currencydefault'];
	$CanCreate=0;
	$OffHold=0;
	$AuthLevel=0;
}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" id="form1">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>
		<legend>', __('Set Authorisation Levels'), '</legend>';

if (isset($_GET['Edit'])) {
	echo '<field>
			<label for="UserID">' . __('User ID') . '</label>
			<fieldtext>' . $_GET['UserID'] . '</fieldtext>
		</field>';
	echo '<input type="hidden" name="UserID" value="'.$_GET['UserID'].'" />';
} else {
	echo '<field>
			<label for="UserID">' . __('User ID') . '</label>
			<select name="UserID">';
	$UserSQL="SELECT userid FROM www_users";
	$Userresult=DB_query($UserSQL);
	while ($MyRow=DB_fetch_array($Userresult)) {
		if ($MyRow['userid']==$UserID) {
			echo '<option selected="selected" value="'.$MyRow['userid'].'">' . $MyRow['userid'] . '</option>';
		} else {
			echo '<option value="'.$MyRow['userid'].'">' . $MyRow['userid'] . '</option>';
		}
	}
	echo '</select>
		</field>';
}

if (isset($_GET['Edit'])) {
	$SQL="SELECT cancreate,
				offhold,
				authlevel,
				currency,
				decimalplaces
			FROM purchorderauth INNER JOIN currencies
			ON purchorderauth.currabrev=currencies.currabrev
			WHERE userid='".$_GET['UserID']."'
			AND purchorderauth.currabrev='".$_GET['Currency']."'";
	$ErrMsg = __('The authentication details cannot be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);
	$MyRow=DB_fetch_array($Result);
	$UserID=$_GET['UserID'];
	$Currency=$_GET['Currency'];
	$CanCreate=$MyRow['cancreate'];
	$OffHold=$MyRow['offhold'];
	$AuthLevel=$MyRow['authlevel'];
	$CurrDecimalPlaces=$MyRow['decimalplaces'];

	echo '<field>
			<label for="CurrCode">' . __('Currency') . '</label>
			<fieldtext>' . $MyRow['currency'] . '</fieldtext>
		</field>';
	echo '<input type="hidden" name="CurrCode" value="'.$Currency.'" />';
} else {
	echo '<field>
			<label for="CurrCode">' . __('Currency') . '</label>
			<select name="CurrCode">';
	$Currencysql="SELECT currabrev,currency,decimalplaces FROM currencies";
	$Currencyresult=DB_query($Currencysql);
	while ($MyRow=DB_fetch_array($Currencyresult)) {
		if ($MyRow['currabrev']==$Currency) {
			echo '<option selected="selected" value="'.$MyRow['currabrev'].'">' . $MyRow['currency'] . '</option>';
		} else {
			echo '<option value="'.$MyRow['currabrev'].'">' . $MyRow['currency'] . '</option>';
		}
	}
	$CurrDecimalPlaces=$MyRow['decimalplaces'];
	echo '</select>
		</field>';
}

echo '<field>
		<label for="CanCreate">' . __('User can create orders') . '</label>';
if ($CanCreate==1) {
	echo '<input type="checkbox" name="CanCreate" />
		</field>';
} else {
	echo '<input type="checkbox" checked="checked" name="CanCreate" />
		</field>';
}

echo '<field>
		<label for="OffHold">' . __('User can release invoices') . '</label>';
if ($OffHold==1) {
	echo '<input type="checkbox" name="OffHold" />
		</field>';
} else {
	echo '<input type="checkbox" checked="checked" name="OffHold" />
		</field>';
}

echo '<field>
		<label for="AuthLevel">' . __('User can authorise orders up to :') . '</label>
		<input type="text" name="AuthLevel" size="11" class="integer" title="" value="'  . locale_number_format($AuthLevel,$CurrDecimalPlaces) . '" />
		<fieldhelp>' . __('Enter the amount that this user is premitted to authorise purchase orders up to') . '</fieldhelp>
	</field>
	</fieldset>';

if (isset($_GET['Edit'])) {
	echo '<div class="centre">
			<input type="submit" name="Update" value="'.__('Update Information').'" />
		</div>';
} else {
	echo '<div class="centre">
			<input type="submit" name="Submit" value="'.__('Enter Information').'" />
		</div>';
}
echo '</div>
        </form>';
include('includes/footer.php');
