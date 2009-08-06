<?php
/* $Revision: 1.12 $ */

$PageSecurity = 8;

include('includes/session.inc');

$title = _('Search GL Accounts');

include('includes/header.inc');

$msg='';

If (isset($_POST['Select'])) {

	$result = DB_query("SELECT accountname FROM chartmaster WHERE accountcode=" . $_POST['Select'],$db);
	$myrow = DB_fetch_row($result);

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . _('Search for General Ledger Accounts');

	echo '<div class="page_help_text">' . _('Account Code') . ' <b>' . $_POST['Select'] . ' - ' . $myrow[0]  . ' </b>' . _('has been selected') . '. <br>' . _('Select one of the links below to operate using this Account') . '.</div>';
	$AccountID = $_POST['Select'];
	$_POST['Select'] = NULL;

	echo '<br><div class="centre"><a href="' . $rootpath . '/GLAccounts.php?' . SID . '&SelectedAccount=' . $AccountID . '">' . _('Edit Account') . '</a>';
	echo '<br><a href="' . $rootpath . '/GLAccountInquiry.php?' . SID . '&Account=' . $AccountID . '">' . _('Account Inquiry') . '</a>';
	echo '<br><a href="' . $_SERVER['PHP_SELF'] . '?' . SID .  '">' . _('New Search') . '</a></div>';

} elseif (isset($_POST['Search'])){

	If (strlen($_POST['Keywords']>0) AND strlen($_POST['GLCode'])>0) {
		$msg=_('Account name keywords have been used in preference to the account code extract entered');
	}
	If ($_POST['Keywords']=='' AND $_POST['GLCode']=='') {
		$msg=_('At least one Account Name keyword OR an extract of an Account Code must be entered for the search');
	} else {
		If (strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces

			$i=0;
			$SearchString = '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['Keywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i) . '%';

			$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname,
					chartmaster.group_,
					CASE WHEN accountgroups.pandl!=0 
						THEN '" . _('Profit and Loss') . "' 
						ELSE '" . _('Balance Sheet') . "' END AS pl
				FROM chartmaster,
					accountgroups
				WHERE chartmaster.group_ = accountgroups.groupname
				AND accountname " . LIKE  . " '$SearchString'
				ORDER BY accountgroups.sequenceintb,
					chartmaster.accountcode";

		} elseif (strlen($_POST['GLCode'])>0 AND is_numeric($_POST['GLCode'])){

			$SQL = "SELECT chartmaster.accountcode,
					chartmaster.accountname,
					chartmaster.group_,
					CASE WHEN accountgroups.pandl!=0 THEN '" . _('Profit and Loss') . "' ELSE '" . _('Balance Sheet') ."' END AS pl
					FROM chartmaster,
						accountgroups
					WHERE chartmaster.group_=accountgroups.groupname
					AND chartmaster.accountcode >= " . $_POST['GLCode'] . "
					ORDER BY chartmaster.accountcode";
		} elseif(!is_numeric($_POST['GLCode'])){
			prnMsg(_('The general ledger code specified must be numeric - all account numbers must be numeric'),'warn');
			unset($SQL);
		}

		if (isset($SQL)){
			$result = DB_query($SQL, $db);
		}
	} //one of keywords or GLCode was more than a zero length string
} //end of if search

if (!isset($AccountID)) {


echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/magnifier.png" title="' . _('Search') . '" alt="">' . ' ' . _('Search for General Ledger Accounts'); 
echo "<br><form action='" . $_SERVER['PHP_SELF'] . '?' . SID . "' method=post>";

if(strlen($msg)>1){
	prnMsg($msg,'info');
}

echo '<table cellpadding=3 colspan=4>
	<tr>
	<td><font size=1>' . _('Enter extract of text in the Account name') .":</font></td>
	<td><input type='Text' name='Keywords' size=20 maxlength=25></td>
	<td><font size=3><b>" .  _('OR') . "</b></font></td>
	<td><font size=1>" . _('Enter Account No. to search from') . ":</font></td>
	<td><input type='Text' name='GLCode' size=15 maxlength=18 onKeyPress='return restrictToNumbers(this, event)' ></td>
	</tr>
	</table><br>";

echo '<div class="centre"><input type=submit name="Search" VALUE=' . _('Search Now') . '">
	<input type=submit action=RESET VALUE="' . _('Reset') .'"></div>';


If (isset($result)) {

	echo '<table cellpadding=2 colspan=7 BORDER=2>';

	$TableHeader = '<tr><th>' . _('Code') . '</th>
                      <th>' . _('Account Name') . '</th>
                      <th>' . _('Group') . '</th>
                      <th>' . _('Account Type') . '</th></tr>';

	echo $TableHeader;

	$j = 1;

	while ($myrow=DB_fetch_array($result)) {

		printf("<tr><td><font size=1><input type=submit name='Select' VALUE='%s'</font></td>
                <td><font size=1>%s</font></td>
                <td><font size=1>%s</font></td>
                <td><font size=1>%s</font></td>
                </tr>",
                $myrow['accountcode'],
                $myrow['accountname'],
                $myrow['group_'],
                $myrow['pl']);

		$j++;
		If ($j == 12){
			$j=1;
				echo $TableHeader;

		}
//end of page full new headings if
	}
//end of while loop

	echo '</table>';

}
//end if results to show

?>

</form>

<?php } //end AccountID already selected

include('includes/footer.inc');
?>
