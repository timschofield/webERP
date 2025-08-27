<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Search GL Accounts');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountInquiry';
include('includes/header.php');

$Msg='';
unset($Result);

if (isset($_POST['Search'])){

	if (mb_strlen($_POST['Keywords']>0) AND mb_strlen($_POST['GLCode'])>0) {
		$Msg=__('Account name keywords have been used in preference to the account code extract entered');
	}
	if ($_POST['Keywords']=='' AND $_POST['GLCode']=='') {
            $SQL = "SELECT chartmaster.accountcode,
                    chartmaster.accountname,
                    chartmaster.group_,
                    CASE WHEN accountgroups.pandl!=0 THEN '" . __('Profit and Loss') . "' ELSE '" . __('Balance Sheet') ."' END AS pl
                    FROM chartmaster,
                        accountgroups,
						glaccountusers
					WHERE glaccountusers.accountcode = chartmaster.accountcode
						AND glaccountusers.userid='" .  $_SESSION['UserID'] . "'
						AND glaccountusers.canview=1
						AND chartmaster.group_=accountgroups.groupname
                    ORDER BY chartmaster.accountcode";
    }
	elseif (mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname,
					chartmaster.group_,
					CASE WHEN accountgroups.pandl!=0
						THEN '" . __('Profit and Loss') . "'
						ELSE '" . __('Balance Sheet') . "' END AS pl
				FROM chartmaster,
					accountgroups,
					glaccountusers
				WHERE glaccountusers.accountcode = chartmaster.accountcode
					AND glaccountusers.userid='" .  $_SESSION['UserID'] . "'
					AND glaccountusers.canview=1
					AND chartmaster.group_ = accountgroups.groupname
					AND accountname " . LIKE  . "'". $SearchString ."'
				ORDER BY accountgroups.sequenceintb,
					chartmaster.accountcode";

		} elseif (mb_strlen($_POST['GLCode'])>0){
			if (!empty($_POST['GLCode'])) {
				echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/GLAccountInquiry.php?Account=' . $_POST['GLCode'] . '&Show=Yes">';
				exit();
			}

			$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname,
					chartmaster.group_,
					CASE WHEN accountgroups.pandl!=0 THEN '" . __('Profit and Loss') . "' ELSE '" . __('Balance Sheet') ."' END AS pl
					FROM chartmaster,
						accountgroups,
						glaccountusers
				WHERE glaccountusers.accountcode = chartmaster.accountcode
					AND glaccountusers.userid='" .  $_SESSION['UserID'] . "'
					AND glaccountusers.canview=1
					AND chartmaster.group_=accountgroups.groupname
					AND chartmaster.accountcode >= '" . $_POST['GLCode'] . "'
					ORDER BY chartmaster.accountcode";
		}
		if (isset($SQL) and $SQL!=''){
			$Result = DB_query($SQL);
			if (DB_num_rows($Result) == 1) {
				$AccountRow = DB_fetch_row($Result);
				/// @todo BUG this must happen before we include header.php. Either that, or we hould use ob_start...
				header('location:' . htmlspecialchars_decode($RootPath) . '/GLAccountInquiry.php?Account=' . urlencode(htmlspecialchars_decode($AccountRow[0])) . '&Show=Yes');
				exit();
			}
		}
} //end of if search

$TargetPeriod = GetPeriod(date($_SESSION['DefaultDateFormat']));

if (!isset($AccountID)) {

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Search for General Ledger Accounts') . '</p>
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(mb_strlen($Msg)>1){
		prnMsg($Msg,'info');
	}

	echo '<fieldset>
			<legend class="search">', __('General Ledger account Search'), '</legend>
		<field>
			<label for="Keywords">' . __('Enter extract of text in the Account name') .':</label>
			<input type="text" name="Keywords" size="20" maxlength="25" />
		</field>';

	$SQLAccountSelect="SELECT chartmaster.accountcode,
							chartmaster.accountname,
							chartmaster.group_
						FROM chartmaster
						INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canview=1
						INNER JOIN accountgroups ON chartmaster.group_=accountgroups.groupname
						ORDER BY accountgroups.sequenceintb, accountgroups.groupname, chartmaster.accountcode";

	$ResultSelection=DB_query($SQLAccountSelect);
	$OptGroup = '';
	echo '<field>
			<label for="GLCode">', '<b>' , __('OR') , '</b>' , __('Search for Account Code'), '</label>
			<select name="GLCode">';
	echo '<option value="">' . __('Select an Account Code') . '</option>';
	while ($MyRowSelection=DB_fetch_array($ResultSelection)){
		if ($OptGroup != $MyRowSelection['group_']) {
			echo '<optgroup label="' . $MyRowSelection['group_'] . '">';
			$OptGroup = $MyRowSelection['group_'];
		}
		if (isset($_POST['GLCode']) and $_POST['GLCode']==$MyRowSelection['accountcode']){
			echo '<option selected="selected" value="' . $MyRowSelection['accountcode'] . '">' . $MyRowSelection['accountcode'].' - ' .htmlspecialchars($MyRowSelection['accountname'], ENT_QUOTES,'UTF-8', false) . '</option>';
		} else {
			echo '<option value="' . $MyRowSelection['accountcode'] . '">' . $MyRowSelection['accountcode'].' - ' .htmlspecialchars($MyRowSelection['accountname'], ENT_QUOTES,'UTF-8', false)  . '</option>';
		}
	}
	echo '</select>';

	echo '</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="Search" value="' . __('Search Now') . '" />
			<input type="submit" name="reset" value="' . __('Reset') .'" />
		</div>';

	if (isset($Result) and DB_num_rows($Result)>0) {

		echo '<table class="selection">
				<thead style="position: -webkit-sticky; position: sticky; top: 0px; z-index: 100;">
					<tr>
						<th>' . __('Code') . '</th>
						<th>' . __('Account Name') . '</th>
						<th>' . __('Group') . '</th>
						<th>' . __('Account Type') . '</th>
						<th>' . __('Inquiry') . '</th>
						<th>' . __('Edit') . '</th>
					</tr>
				</thead>
				<tbody>';

		while ($MyRow=DB_fetch_array($Result)) {

			echo '<tr class="striped_row">
					<td>', htmlspecialchars($MyRow['accountcode'],ENT_QUOTES,'UTF-8',false), '</td>
					<td>', htmlspecialchars($MyRow['accountname'],ENT_QUOTES,'UTF-8',false), '</td>
					<td>', $MyRow['group_'], '</td>
					<td>', $MyRow['pl'], '</td>
					<td class="number"><a href="', $RootPath, '/GLAccountInquiry.php?Account=', $MyRow['accountcode'], '&amp;Show=Yes&FromPeriod=', $TargetPeriod, '&ToPeriod=', $TargetPeriod, '"><img width="32px" src="', $RootPath, '/css/', $Theme, '/images/magnifier.png" title="' . __('Inquiry') . '" alt="' . __('Inquiry') . '" /></td>
					<td><a href="', $RootPath, '/GLAccounts.php?SelectedAccount=', $MyRow['accountcode'], '"><img width="32px" src="', $RootPath, '/css/', $Theme, '/images/maintenance.png" title="' . __('Edit') . '" alt="' . __('Edit') . '" /></a>
				</tr>';

//end of page full new headings if
		}
//end of while loop

		echo '</tbody>
			</table>';

	}
//end if results to show

	echo '</div>
          </form>';

} //end AccountID already selected

include('includes/footer.php');
