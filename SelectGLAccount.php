<?php
/* $Revision: 1.4 $ */

$PageSecurity = 8;

include('includes/session.inc');

$title = _('Search GL Accounts');

include('includes/header.inc');

$msg='';

If (isset($_POST['Select'])) {

	$result = DB_query("SELECT AccountName FROM ChartMaster WHERE AccountCode=" . $_POST['Select'],$db);
	$myrow = DB_fetch_row($result);

	echo '<p>' . _('Account code') . ' <B>' . $_POST['Select'] . ' - ' . $myrow[0]  . ' </B>' . _('has been selected') . '. <br>' . _('Select one of the links below to operate using this Account') . '.';
	$AccountID = $_POST['Select'];
	$_POST['Select'] = NULL;

	echo '<BR><A HREF="' . $rootpath . '/GLAccounts.php?' . SID . 'SelectedAccount=' . $AccountID . '">' . _('Edit Account') . '</A>';
	echo '<BR><A HREF="' . $rootpath . '/GLAccountInquiry.php?' . SID . 'Account=' . $AccountID . '">' . _('Account Inquiry') . '</A>';
	echo '<CENTER><A HREF="' . $_SERVER['PHP_SELF'] . '?' . SID .  '">' . _('New Search') . '</A></CENTER>';

} elseif (isset($_POST['Search'])){

	If (strlen($_POST['Keywords']>0) AND strlen($_POST['GLCode'])>0) {
		$msg=_('Account name keywords have been used in preference to the Account code extract entered');
	}
	If ($_POST['Keywords']=='' AND $_POST['GLCode']=='') {
		$msg=_('At least one Account name keyword OR an extract of a Account code must be entered for the search');
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

			$SQL = "SELECT AccountCode,
					AccountName,
					ChartMaster.Group_,
					IF(PandL!=0,'Profit and Loss','Balance Sheet') AS PL
				FROM ChartMaster,
					AccountGroups
				WHERE ChartMaster.Group_ = AccountGroups.GroupName
				AND AccountName LIKE '$SearchString'
				ORDER BY AccountGroups.SequenceInTB,
					ChartMaster.AccountCode";

		} elseif (strlen($_POST['GLCode'])>0){

			$SQL = "SELECT AccountCode,
					AccountName,
					Group_,
					IF(PandL!=0,'Profit and Loss','Balance Sheet') AS PL
					FROM ChartMaster,
						AccountGroups
					WHERE ChartMaster.Group_=AccountGroups.GroupName
					AND AccountCode >= " . $_POST['GLCode'] . "
					ORDER BY ChartMaster.AccountCode";
		}

		$result = DB_query($SQL,$db);

	} //one of keywords or GLCode was more than a zero length string
} //end of if search

if (!isset($AccountID)) {
?>


<FORM ACTION="<?php $_SERVER['PHP_SELF'] . '?' . SID; ?>" METHOD=POST>
<B><?php echo '<BR>' . $msg; ?></B>
<TABLE CELLPADDING=3 COLSPAN=4>
<TR>
<TD><FONT SIZE=1><?php echo _('Enter extract of text in the Account name'); ?>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25></TD>
<TD><FONT SIZE=3><B><?php echo _('OR'); ?></B></FONT></TD>
<TD><FONT SIZE=1><?php echo _('Enter Account No. to search from'); ?>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="GLCode" SIZE=15 MAXLENGTH=18></TD>
</TR>
</TABLE>
<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>">
<INPUT TYPE=SUBMIT ACTION=RESET VALUE="<?php echo _('Reset'); ?>"></CENTER>


<?php

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
                $myrow['AccountCode'],
                $myrow['AccountName'],
                $myrow['Group_'],
                $myrow['PL']);

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
