<?php
/* $Revision: 1.7 $ */

$PageSecurity = 8;

include('includes/session.inc');

$title = _('Search GL Accounts');

include('includes/header.inc');

$msg='';

If (isset($_POST['Select'])) {

	$result = DB_query("SELECT accountname FROM chartmaster WHERE accountcode=" . $_POST['Select'],$db);
	$myrow = DB_fetch_row($result);

	echo '<p>' . _('Account Code') . ' <B>' . $_POST['Select'] . ' - ' . $myrow[0]  . ' </B>' . _('has been selected') . '. <br>' . _('Select one of the links below to operate using this Account') . '.';
	$AccountID = $_POST['Select'];
	$_POST['Select'] = NULL;

	echo '<BR><A HREF="' . $rootpath . '/GLAccounts.php?' . SID . '&SelectedAccount=' . $AccountID . '">' . _('Edit Account') . '</A>';
	echo '<BR><A HREF="' . $rootpath . '/GLAccountInquiry.php?' . SID . '&Account=' . $AccountID . '">' . _('Account Inquiry') . '</A>';
	echo '<CENTER><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID .  '">' . _('New Search') . '</A></CENTER>';

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


echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . '?' . SID . "' METHOD=POST>";

if(strlen($msg)>1){
	prnMsg($msg,'info');
}

echo '<TABLE CELLPADDING=3 COLSPAN=4>
	<TR>
	<TD><FONT SIZE=1>' . _('Enter extract of text in the Account name') .":</FONT></TD>
	<TD><INPUT TYPE='Text' NAME='Keywords' SIZE=20 MAXLENGTH=25></TD>
	<TD><FONT SIZE=3><B>" .  _('OR') . "</B></FONT></TD>
	<TD><FONT SIZE=1>" . _('Enter Account No. to search from') . ":</FONT></TD>
	<TD><INPUT TYPE='Text' NAME='GLCode' SIZE=15 MAXLENGTH=18></TD>
	</TR>
	</TABLE>";

echo '<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE=' . _('Search Now') . '">
	<INPUT TYPE=SUBMIT ACTION=RESET VALUE="' . _('Reset') .'"></CENTER>';


If (isset($result)) {

	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>';

	$TableHeader = '<TR><TD class="tableheader">' . _('Code') . '</TD>
                      <TD class="tableheader">' . _('Account Name') . '</TD>
                      <TD class="tableheader">' . _('Group') . '</TD>
                      <TD class="tableheader">' . _('Account Type') . '</TD></TR>';

	echo $TableHeader;

	$j = 1;

	while ($myrow=DB_fetch_array($result)) {

		printf("<tr><td><FONT SIZE=1><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</FONT></td>
                <td><FONT SIZE=1>%s</FONT></td>
                <td><FONT SIZE=1>%s</FONT></td>
                <td><FONT SIZE=1>%s</FONT></td>
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

	echo '</TABLE>';

}
//end if results to show

?>

</FORM>

<?php } //end AccountID already selected

include('includes/footer.inc');
?>