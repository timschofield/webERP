<?php

/* $Revision: 1.5 $ */

/*Through deviousness and cunning, this system allows shows the balance sheets as at the end of any period selected - so first off need to show the input of criteria screen while the user is selecting the period end of the balance date meanwhile the system is posting any unposted transactions */

$PageSecurity = 8;

include ('includes/session.inc');
$title = _('Balance Sheet');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable

echo "<FORM METHOD='POST' ACTION=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';


if (! isset($_POST['BalancePeriodEnd']) OR isset($_POST['SelectADifferentPeriod'])){


/*Show a form to allow input of criteria for TB to show */
	echo '<CENTER><TABLE><TR><TD>'._('Select the balance date').":</TD><TD><SELECT Name='BalancePeriodEnd'>";

	$sql = 'SELECT periodno, lastdate_in_period FROM periods';
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){
		if( $_POST['BalancePeriodEnd']== $myrow['periodno']){
			echo '<OPTION SELECTED VALUE=' . $myrow['periodno'] . '>' . ConvertSQLDate($myrow['lastdate_in_period']);
		} else {
			echo '<OPTION VALUE=' . $myrow['periodno'] . '>' . ConvertSQLDate($myrow['lastdate_in_period']);
		}
	}

	echo '</SELECT></TD></TR>';

	echo '<TR><TD>'._('Detail Or Summary').":</TD><TD><SELECT Name='Detail'>";
		echo "<OPTION SELECTED VALUE='Summary'>"._('Summary');
		echo "<OPTION SELECTED VALUE='Detailed'>"._('All Accounts');
	echo '</SELECT></TD></TR>';

	echo '</TABLE>';

	echo "<INPUT TYPE=SUBMIT Name='ShowBalanceSheet' Value='"._('Show Balance Sheet')."'></CENTER>";

/*Now do the posting while the user is thinking about the period to select */

	include ('includes/GLPostings.inc');

} else {

	echo "<INPUT TYPE=HIDDEN NAME='BalancePeriodEnd' VALUE=" . $_POST['BalancePeriodEnd'] . '>';

	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];

	$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_POST['BalancePeriodEnd'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$BalanceDate = ConvertSQLDate($myrow[0]);

	/*Calculate B/Fwd retained earnings */

	$SQL = 'SELECT Sum(CASE WHEN chartdetails.period=' . $_POST['BalancePeriodEnd'] . ' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS accumprofitbfwd,
			Sum(CASE WHEN chartdetails.period=' . ($_POST['BalancePeriodEnd'] - 12) . " THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lyaccumprofitbfwd
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN chartdetails
		ON chartmaster.accountcode= chartdetails.accountcode
		WHERE accountgroups.pandl=1";

	$AccumProfitResult = DB_query($SQL,$db,_('The accumulated profits brought forward could not be calculated by the SQL because'));

	$AccumProfitRow = DB_fetch_array($AccumProfitResult); /*should only be one row returned */

	$SQL = 'SELECT accountgroups.sectioninaccounts, 
			accountgroups.groupname,
			chartdetails.accountcode ,
			chartmaster.accountname,
			Sum(CASE WHEN chartdetails.period=' . $_POST['BalancePeriodEnd'] . ' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS balancecfwd,
			Sum(CASE WHEN chartdetails.period=' . ($_POST['BalancePeriodEnd'] - 12) . ' THEN chartdetails.bfwd + chartdetails.actual ELSE 0 END) AS lybalancecfwd
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN chartdetails
		ON chartmaster.accountcode= chartdetails.accountcode
		WHERE accountgroups.pandl=0
		GROUP BY accountgroups.groupname,
			chartdetails.accountcode,
			chartmaster.accountname,
			accountgroups.sequenceintb,
			accountgroups.sectioninaccounts
		ORDER BY accountgroups.sectioninaccounts, 
			accountgroups.sequenceintb, 
			chartdetails.accountcode';

	$AccountsResult = DB_query($SQL,$db,_('No general ledger accounts were returned by the SQL because'));

	echo '<CENTER><FONT SIZE=4 COLOR=BLUE><B>'._('Balance Sheet as at')." $BalanceDate</B></FONT><BR>";

	echo '<TABLE CELLPADDING=2>';

	if ($_POST['Detail']=='Detailed'){
		$TableHeader = "<TR>
				<TD class='tableheader'>"._('Account')."</TD>
				<TD class='tableheader'>"._('Account Name')."</TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>$BalanceDate</TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>"._('Last Year').'</TD>
				</TR>';
	} else { /*summary */
		$TableHeader = "<TR>
				<TD COLSPAN=2 class='tableheader'></TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>$BalanceDate</TD>
				<TD COLSPAN=2 class='tableheader' ALIGN=CENTER>"._('Last Year').'</TD>
				</TR>';
	}


	$k=0; //row colour counter
	$Section='';
	$SectionBalance = 0;
	$SectionBalanceLY = 0;

	$LYCheckTotal = 0;
	$CheckTotal = 0;

	$ActGrp ='';

	$GroupTotal = 0;
	$LYGroupTotal = 0;

	while ($myrow=DB_fetch_array($AccountsResult)) {

		$AccountBalance = $myrow['balancecfwd'];
		$LYAccountBalance = $myrow['lybalancecfwd'];

		if ($myrow['accountcode'] == $RetainedEarningsAct){
			$AccountBalance += $AccumProfitRow['accumprofitbfwd'];
			$LYAccountBalance += $AccumProfitRow['lyaccumprofitbfwd'];
		}

		if ($myrow['groupname']!= $ActGrp AND $_POST['Detail']=='Summary' AND $ActGrp != '') {

			printf('<td COLSPAN=3>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<TD></TD>
			<td ALIGN=RIGHT>%s</td>
			</tr>',
			$ActGrp,
			number_format($GroupTotal),
			number_format($LYGroupTotal)
			);

		}
		if ($myrow['sectioninaccounts']!= $Section){

			if ($SectionBalanceLY+$SectionBalance !=0){
				if ($_POST['Detail']=='Detailed'){
					echo '<TR>
					<TD COLSPAN=2></TD>
      					<TD><HR></TD>
					<TD></TD>
					<TD><HR></TD>
					<TD></TD>
					</TR>';
				} else {
					echo '<TR>
					<TD COLSPAN=3></TD>
      					<TD><HR></TD>
					<TD></TD>
					<TD><HR></TD>
					</TR>';
				}

				printf('<TR>
					<TD COLSPAN=3><FONT SIZE=4>%s</FONT></td>
					<TD ALIGN=RIGHT>%s</TD>
					<TD></TD>
					<TD ALIGN=RIGHT>%s</TD>
				</TR>',
				$Sections[$Section],
				number_format($SectionBalance),
				number_format($SectionBalanceLY));
			}
			$SectionBalanceLY = 0;
			$SectionBalance = 0;

			$Section = $myrow['sectioninaccounts'];

			if ($_POST['Detail']=='Detailed'){
				printf('<TR>
					<TD COLSPAN=6><FONT SIZE=4 COLOR=BLUE><B>%s</B></FONT></TD>
					</TR>',
					$Sections[$myrow['sectioninaccounts']]);
			}
		}

		if ($myrow['groupname']!= $ActGrp){

			if ($_POST['Detail']=='Detailed'){
				$ActGrp = $myrow['groupname'];
				printf('<TR>
				<td COLSPAN=6><FONT SIZE=2 COLOR=BLUE><B>%s</B></FONT></TD>
				</TR>',
				$myrow['groupname']);
				echo $TableHeader;
			}
			$GroupTotal=0;
			$LYGroupTotal=0;
			$ActGrp = $myrow["groupname"];
		}

		$SectionBalanceLY +=	$LYAccountBalance;
		$SectionBalance	  +=	$AccountBalance;

		$LYGroupTotal	  +=	$LYAccountBalance;
		$GroupTotal	  +=	$AccountBalance;

		$LYCheckTotal 	  +=	$LYAccountBalance;
		$CheckTotal  	  +=	$AccountBalance;


		if ($_POST['Detail']=='Detailed'){

			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k++;
			}

			$ActEnquiryURL = "<A HREF='$rootpath/GLAccountInquiry.php?" . SID . "Period=" . $_POST['BalancePeriodEnd'] . '&Account=' . $myrow['accountcode'] . "'>" . $myrow['accountcode'] . '<A>';

			$PrintString = '<td>%s</td>
					<td>%s</td>
					<td ALIGN=RIGHT>%s</td>
					<TD></TD>
					<td ALIGN=RIGHT>%s</td>
					<TD></TD>
					</tr>';

			printf($PrintString,
				$ActEnquiryURL,
				$myrow['accountname'],
				number_format($AccountBalance),
				number_format($LYAccountBalance)
				);

		}
	}
	//end of loop


	if ($SectionBalanceLY+$SectionBalance !=0){
		if ($_POST['Detail']=='Summary'){
			printf('<td COLSPAN=3>%s</td>
				<td ALIGN=RIGHT>%s</td>
				<TD></TD>
				<td ALIGN=RIGHT>%s</td>
				</tr>',
			$ActGrp,
			number_format($GroupTotal),
			number_format($LYGroupTotal)
			);
		}
		echo "<TR>
			<TD COLSPAN=3></TD>
      			<TD><HR></TD>
			<TD></TD>
			<TD><HR></TD>
			</TR>";

		printf('<TR>
			<TD COLSPAN=3><FONT SIZE=4>%s</FONT></td>
			<TD ALIGN=RIGHT>%s</TD>
			<TD></TD>
			<TD ALIGN=RIGHT>%s</TD>
			</TR>',
			$Sections[$Section],
			number_format($SectionBalance),
			number_format($SectionBalanceLY));
	}

	echo '<TR>
		<TD COLSPAN=3></TD>
      		<TD><HR></TD>
		<TD></TD>
		<TD><HR></TD>
		</TR>';

	printf('<TR>
		<TD COLSPAN=3>'._('Check Total').'</FONT></td>
		<TD ALIGN=RIGHT>%s</TD>
		<TD></TD>
		<TD ALIGN=RIGHT>%s</TD>
		</TR>',
		number_format($CheckTotal),
		number_format($LYCheckTotal));

	echo '<TR>
		<TD COLSPAN=3></TD>
      		<TD><HR></TD>
		<TD></TD>
		<TD><HR></TD>
		</TR>';

	echo '</TABLE>';
	echo "<INPUT TYPE=SUBMIT Name='SelectADifferentPeriod' Value='"._('Select A Different Balance Date')."'></CENTER>";
}
echo '</form>';
include('includes/footer.inc');
?>
