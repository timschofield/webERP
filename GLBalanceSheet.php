<?php

/* $Revision: 1.13 $ */

/*Through deviousness and cunning, this system allows shows the balance sheets as at the end of any period selected - so first off need to show the input of criteria screen while the user is selecting the period end of the balance date meanwhile the system is posting any unposted transactions */

$PageSecurity = 8;

include ('includes/session.inc');
$title = _('Balance Sheet');
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable

if (! isset($_POST['BalancePeriodEnd']) OR isset($_POST['SelectADifferentPeriod'])){

	/*Show a form to allow input of criteria for TB to show */
	include('includes/header.inc');
	echo "<FORM METHOD='POST' ACTION=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<CENTER><TABLE><TR><TD>'._('Select the balance date').":</TD><TD><SELECT Name='BalancePeriodEnd'>";

	$sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
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
	echo "<CENTER><INPUT TYPE=SUBMIT Name='PrintPDF' Value='"._('Print PDF')."'></CENTER>";

	/*Now do the posting while the user is thinking about the period to select */
	include ('includes/GLPostings.inc');

} elseif (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$PageNumber = 0;
	$FontSize = 10;
	$pdf->addinfo('Title', _('Balance Sheet') );
	$pdf->addinfo('Subject', _('Balance Sheet') );
	$line_height = 12;

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

	$AccumProfitResult = DB_query($SQL,$db);
	if (DB_error_no($db) !=0) {
		$title = _('Balance Sheet') . ' - ' . _('Problem Report') . '....';
		include('includes/header.inc');
		prnMsg( _('The accumulated profits brought forward could not be calculated by the SQL because') . ' - ' . DB_error_msg($db) );
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	$AccumProfitRow = DB_fetch_array($AccumProfitResult); /*should only be one row returned */

	$SQL = 'SELECT accountgroups.sectioninaccounts,
			accountgroups.groupname,
			accountgroups.parentgroupname,
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
			accountgroups.groupname,
			chartdetails.accountcode';

	$AccountsResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
		$title = _('Balance Sheet') . ' - ' . _('Problem Report') . '....';
		include('includes/header.inc');
		prnMsg( _('No general ledger accounts were returned by the SQL because') . ' - ' . DB_error_msg($db) );
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	include('includes/PDFBalanceSheetPageHeader.inc');

	$k=0; //row colour counter
	$Section='';
	$SectionBalance = 0;
	$SectionBalanceLY = 0;

	$LYCheckTotal = 0;
	$CheckTotal = 0;

	$ActGrp ='';
	$Level =0;
	$ParentGroups = array();
	$ParentGroups[$Level]='';
	$GroupTotal = array(0);
	$LYGroupTotal = array(0);

	
	while ($myrow=DB_fetch_array($AccountsResult)) {

		$AccountBalance = $myrow['balancecfwd'];
		$LYAccountBalance = $myrow['lybalancecfwd'];

		if ($myrow['accountcode'] == $RetainedEarningsAct){
			$AccountBalance += $AccumProfitRow['accumprofitbfwd'];
			$LYAccountBalance += $AccumProfitRow['lyaccumprofitbfwd'];
		}
		if ($ActGrp !=''){
        		if ($myrow['groupname']!=$ActGrp){
				$FontSize = 8;
				$pdf->selectFont('./fonts/Helvetica-Bold.afm');
        			while ($myrow['groupname']!= $ParentGroups[$Level] AND $Level>0) {
        				$YPos -= $line_height;
        				$LeftOvers = $pdf->addTextWrap($Left_Margin+(10 * ($Level+1)),$YPos,200,$FontSize,_('Total') . ' ' . $ParentGroups[$Level]);
        				$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($GroupTotal[$Level]),'right');
        				$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($LYGroupTotal[$Level]),'right');
        				$ParentGroups[$Level]='';
        				$GroupTotal[$Level]=0;
        				$LYGroupTotal[$Level]=0;
        				$Level--;
        			}
        			$YPos -= $line_height;
        			$LeftOvers = $pdf->addTextWrap($Left_Margin+(10 * ($Level+1)),$YPos,200,$FontSize,_('Total') . ' ' . $ParentGroups[$Level]);
        			$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($GroupTotal[$Level]),'right');
        			$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($LYGroupTotal[$Level]),'right');
        			$ParentGroups[$Level]='';
        			$GroupTotal[$Level]=0;
        			$LYGroupTotal[$Level]=0;
        			$YPos -= $line_height;
        		}
                }

		if ($myrow['sectioninaccounts']!= $Section){

			if ($Section !=''){
				$FontSize = 8;
				$pdf->selectFont('./fonts/Helvetica-Bold.afm');
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$Section]);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($SectionBalance),'right');
				$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($SectionBalanceLY),'right');
				$YPos -= (2 * $line_height);
			}
			$SectionBalanceLY = 0;
			$SectionBalance = 0;

			$Section = $myrow['sectioninaccounts'];
			if ($_POST['Detail']=='Detailed'){
				
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$myrow['sectioninaccounts']]);
				$YPos -= (2 * $line_height);

			}
		}

		if ($myrow['groupname']!= $ActGrp){
                        $FontSize =8;
                        $pdf->selectFont('./fonts/Helvetica-Bold.afm');
			if ($myrow['parentgroupname']==$ActGrp AND $ActGrp!=''){
				$Level++;
			}
			$ActGrp = $myrow['groupname'];
			$ParentGroups[$Level] = $ActGrp;
			if ($_POST['Detail']=='Detailed'){
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$myrow['groupname']);
				$YPos -= $line_height;
			}
			$GroupTotal[$Level]=0;
			$LYGroupTotal[$Level]=0;
		}

		$SectionBalanceLY +=	$LYAccountBalance;
		$SectionBalance	  +=	$AccountBalance;

		for ($i=0;$i<=$Level;$i++){
			$LYGroupTotal[$i]  +=	$LYAccountBalance;
			$GroupTotal[$i]	  +=	$AccountBalance;
		}
		$LYCheckTotal 	  +=	$LYAccountBalance;
		$CheckTotal  	  +=	$AccountBalance;


		if ($_POST['Detail']=='Detailed'){
		        $FontSize =8;
			$pdf->selectFont('./fonts/Helvetica.afm');
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,$myrow['accountcode']);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+55,$YPos,200,$FontSize,$myrow['accountname']);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($AccountBalance),'right');
			$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($LYAccountBalance),'right');
			$YPos -= $line_height;
		}
		if ($YPos < ($Bottom_Margin)){
			include('includes/PDFBalanceSheetPageHeader.inc');
		}
	}//end of loop

        $FontSize = 8;
	$pdf->selectFont('./fonts/Helvetica-Bold.afm');
	while ($Level>0) {
        	$YPos -= $line_height;
        	$LeftOvers = $pdf->addTextWrap($Left_Margin+(10 * ($Level+1)),$YPos,200,$FontSize,_('Total') . ' ' . $ParentGroups[$Level]);
        	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($GroupTotal[$Level]),'right');
        	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($LYGroupTotal[$Level]),'right');
        	$ParentGroups[$Level]='';
        	$GroupTotal[$Level]=0;
        	$LYGroupTotal[$Level]=0;
        	$Level--;
        }
        $YPos -= $line_height;
        $LeftOvers = $pdf->addTextWrap($Left_Margin+(10 * ($Level+1)),$YPos,200,$FontSize,_('Total') . ' ' . $ParentGroups[$Level]);
        $LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($GroupTotal[$Level]),'right');
        $LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($LYGroupTotal[$Level]),'right');
        $ParentGroups[$Level]='';
        $GroupTotal[$Level]=0;
        $LYGroupTotal[$Level]=0;
        $YPos -= $line_height;

        if ($SectionBalanceLY+$SectionBalance !=0){
	        $FontSize =8;
		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$Section]);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($SectionBalance),'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($SectionBalanceLY),'right');
		$YPos -= $line_height;
	}

	$YPos -= $line_height;

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,_('Check Total'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($CheckTotal),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($LYCheckTotal),'right');

	


	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

	if ($len<=20){
		$title = _('Print Balance Sheet Error');
		include('includes/header.inc');
		echo '<p>';
		prnMsg( _('There were no entries to print out for the selections specified') );
		echo '<BR><A HREF="'. $rootpath.'/index.php?' . SID . '">'. _('Back to the menu'). '</A>';
		include('includes/footer.inc');
		exit;
	} else {
	        header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename="BalanceSheet.pdf"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		$pdf->Stream();
	}
	exit;
} else {
	include('includes/header.inc');
	echo "<FORM METHOD='POST' ACTION=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
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
			accountgroups.parentgroupname,
			chartdetails.accountcode,
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
			accountgroups.groupname,
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
	$Level=0;
	$ParentGroups=array();
	$ParentGroups[$Level]='';
	$GroupTotal = array(0);
	$LYGroupTotal = array(0);

	echo $TableHeader;
	$j=0; //row counter

	while ($myrow=DB_fetch_array($AccountsResult)) {

		$AccountBalance = $myrow['balancecfwd'];
		$LYAccountBalance = $myrow['lybalancecfwd'];

		if ($myrow['accountcode'] == $RetainedEarningsAct){
			$AccountBalance += $AccumProfitRow['accumprofitbfwd'];
			$LYAccountBalance += $AccumProfitRow['lyaccumprofitbfwd'];
		}

		if ($myrow['groupname']!= $ActGrp AND $ActGrp != '') {
			if ($myrow['parentgroupname']!=$ActGrp){
				while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0){
					if ($_POST['Detail']=='Detailed'){
						echo '<TR>
							<TD COLSPAN=2></TD>
      							<TD><HR></TD>
							<TD></TD>
							<TD><HR></TD>
							<TD></TD>
							</TR>';
					}
					printf('<td COLSPAN=2><I>%s</I></td>
						<td ALIGN=RIGHT>%s</td>
						<TD></TD>
						<td ALIGN=RIGHT>%s</td>
						</tr>',
						$ParentGroups[$Level],
						number_format($GroupTotal[$Level]),
						number_format($LYGroupTotal[$Level])
						);
					$GroupTotal[$Level] = 0;
					$LYGroupTotal[$Level] = 0;
					$ParentGroups[$Level]='';
					$Level--;
					$j++;
				}
				if ($_POST['Detail']=='Detailed'){
					echo '<TR>
						<TD COLSPAN=2></TD>
						<TD><HR></TD>
						<TD></TD>
						<TD><HR></TD>
						<TD></TD>
						</TR>';
				}

				printf('<td COLSPAN=2>%s</td>
					<td ALIGN=RIGHT>%s</td>
					<TD></TD>
					<td ALIGN=RIGHT>%s</td>
					</tr>',
					$ParentGroups[$Level],
					number_format($GroupTotal[$Level]),
					number_format($LYGroupTotal[$Level])
					);
				$GroupTotal[$Level] = 0;
				$LYGroupTotal[$Level] = 0;
				$ParentGroups[$Level]='';
				$j++;
			}
		}
		if ($myrow['sectioninaccounts']!= $Section ){

			if ($Section!=''){
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
				$j++;
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

			if ($ActGrp!='' AND $myrow['parentgroupname']==$ActGrp){
				$Level++;
			}
		
			if ($_POST['Detail']=='Detailed'){
				$ActGrp = $myrow['groupname'];
				printf('<TR>
				<td COLSPAN=6><FONT SIZE=2 COLOR=BLUE><B>%s</B></FONT></TD>
				</TR>',
				$myrow['groupname']);
				echo $TableHeader;
			}
			$GroupTotal[$Level]=0;
			$LYGroupTotal[$Level]=0;
			$ActGrp = $myrow['groupname'];
			$ParentGroups[$Level]=$myrow['groupname'];
			$j++;
		}

		$SectionBalanceLY +=	$LYAccountBalance;
		$SectionBalance	  +=	$AccountBalance;
		for ($i=0;$i<=$Level;$i++){
			$LYGroupTotal[$i] += $LYAccountBalance;
			$GroupTotal[$i] += $AccountBalance;
		}
		$LYCheckTotal	  +=	$LYAccountBalance;
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
			$j++;
			
		}
		
	}
	//end of loop


	

	while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0){
		if ($_POST['Detail']=='Detailed'){
			echo '<TR>
				<TD COLSPAN=2></TD>
				<TD><HR></TD>
				<TD></TD>
				<TD><HR></TD>
				<TD></TD>
				</TR>';
		}
		printf('<td COLSPAN=2><I>%s</I></td>
			<td ALIGN=RIGHT>%s</td>
			<TD></TD>
			<td ALIGN=RIGHT>%s</td>
			</tr>',
			$ParentGroups[$Level],
			number_format($GroupTotal[$Level]),
			number_format($LYGroupTotal[$Level])
			);
		$Level--;
	}
	if ($_POST['Detail']=='Detailed'){
		echo '<TR>
			<TD COLSPAN=2></TD>
			<TD><HR></TD>
			<TD></TD>
			<TD><HR></TD>
			<TD></TD>
			</TR>';
	}

	printf('<td COLSPAN=2>%s</td>
		<td ALIGN=RIGHT>%s</td>
		<TD></TD>
		<td ALIGN=RIGHT>%s</td>
		</tr>',
		$ParentGroups[$Level],
		number_format($GroupTotal[$Level]),
		number_format($LYGroupTotal[$Level])
		);
	
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
	
	$Section = $myrow['sectioninaccounts'];
	
	if ($_POST['Detail']=='Detailed'){
		printf('<TR>
			<TD COLSPAN=6><FONT SIZE=4 COLOR=BLUE><B>%s</B></FONT></TD>
			</TR>',
			$Sections[$myrow['sectioninaccounts']]);
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

echo '</FORM>';
include('includes/footer.inc');
?>
