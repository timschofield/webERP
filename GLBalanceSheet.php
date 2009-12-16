<?php

/* $Revision: 1.21 $ */

/* $Id$*/

/*Through deviousness and cunning, this system allows shows the balance sheets as at the end of any period selected - so first off need to show the input of criteria screen while the user is selecting the period end of the balance date meanwhile the system is posting any unposted transactions */

$PageSecurity = 8;

include ('includes/session.inc');
$title = _('Balance Sheet');
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable

if (! isset($_POST['BalancePeriodEnd']) or isset($_POST['SelectADifferentPeriod'])){

	/*Show a form to allow input of criteria for TB to show */
	include('includes/header.inc');
echo '<div class="centre"><p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/printer.png" title="' . _('Print') . '" alt="">' . ' ' . _('Balance Sheet') . '</div>';
echo '<div class="page_help_text">'
. _('Balance Sheet (or statement of financial position) is a summary  of balances. Assets, liabilities and ownership equity are listed as of a specific date, such as the end of its financial year. Of the four basic financial statements, the balance sheet is the only statement which applies to a single point in time.') . '<br>'
. _('The balance sheet has three parts: assets, liabilities and ownership equity. The main categories of assets are listed first and are followed by the liabilities. The difference between the assets and the liabilities is known as equity or the net assets or the net worth or capital of the company and according to the accounting equation, net worth must equal assets minus liabilities.') . '<br>'
. _('webERP is an "accrual" based system (not a "cash based" system).  Accrual systems include items when they are invoiced to the customer, and when expenses are owed based on the supplier invoice date.') . '</div>';

	echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<p><table class="table"><tr><td>'._('Select the balance date').":</td><td><select Name='BalancePeriodEnd'>";

	$periodno=GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
	$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno='.$periodno;
	$result = DB_query($sql,$db);
	$myrow=DB_fetch_array($result, $db);
	$lastdate_in_period=$myrow[0];
	
	$sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
	$Periods = DB_query($sql,$db);

	while ($myrow=DB_fetch_array($Periods,$db)){
		if( $myrow['periodno']== $periodno){
			echo '<option selected VALUE=' . $myrow['periodno'] . '>' . ConvertSQLDate($lastdate_in_period);
		} else {
			echo '<option VALUE=' . $myrow['periodno'] . '>' . ConvertSQLDate($myrow['lastdate_in_period']);
		}
	}

	echo '</select></td></tr>';

	echo '<tr><td>'._('Detail Or Summary').":</td><td><select Name='Detail'>";
	echo "<option selected VALUE='Summary'>"._('Summary');
	echo "<option selected VALUE='Detailed'>"._('All Accounts');
	echo '</select></td></tr>';

	echo '</table>';

	echo "<br><div class='centre'><input type=submit Name='ShowBalanceSheet' Value='"._('Show on Screen (HTML)')."'</div>";
	echo "<br><div class='centre'><input type=submit Name='PrintPDF' Value='"._('Produce PDF Report')."'></div>";

	/*Now do the posting while the user is thinking about the period to select */
	include ('includes/GLPostings.inc');

} elseif (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', _('Balance Sheet') );
	$pdf->addInfo('Subject', _('Balance Sheet') );
	$line_height = 12;
	$PageNumber = 0;
	$FontSize = 10;

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
		echo '<br><a href="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br>'. $SQL;
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
			accountgroups.parentgroupname,
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
		echo '<br><a href="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

    $ListCount = DB_num_rows($AccountsResult); // UldisN

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
					$pdf->setFont('','B');
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
				$pdf->setFont('','B');
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
            $pdf->setFont('','B');
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
			$pdf->setFont('','');
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
	$pdf->setFont('','B');
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
		$pdf->setFont('','B');
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$Section]);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($SectionBalance),'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($SectionBalanceLY),'right');
		$YPos -= $line_height;
	}

	$YPos -= $line_height;

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,_('Check Total'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,number_format($CheckTotal),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,number_format($LYCheckTotal),'right');

	if ($ListCount == 0) {   //UldisN
		$title = _('Print Balance Sheet Error');
		include('includes/header.inc');
		prnMsg( _('There were no entries to print out for the selections specified') );
		echo '<br><a href="'. $rootpath.'/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
		include('includes/footer.inc');
		exit;
	} else {
	    $pdf->OutputD($_SESSION['DatabaseName'] . '_GL_Balance_Sheet_' . date('Y-m-d') . '.pdf');//UldisN
        $pdf->__destruct(); //UldisN
	}
	exit;
} else {
	include('includes/header.inc');
	echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo "<input type=hidden name='BalancePeriodEnd' VALUE=" . $_POST['BalancePeriodEnd'] . '>';

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
			accountgroups.parentgroupname,
			accountgroups.sequenceintb,
			accountgroups.sectioninaccounts
		ORDER BY accountgroups.sectioninaccounts,
			accountgroups.sequenceintb,
			accountgroups.groupname,
			chartdetails.accountcode';

	$AccountsResult = DB_query($SQL,$db,_('No general ledger accounts were returned by the SQL because'));

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/preview.gif" title="' . _('Search') . '" alt=""> ' . _('HTML View') . '<a href="GLBalanceSheet.php"> ' . _('Return') . '</a></p>';
        echo '<div class=invoice><table class="table1"><div class="centre"><font size=4 color=BLUE><b>' ._('Balance Sheet as at') . ' ' . $BalanceDate .'</b></font></div><br>';

	echo '<table cellpadding=2>';

	if ($_POST['Detail']=='Detailed'){
		$TableHeader = "<tr>
				<th>"._('Account')."</td>
				<th>"._('Account Name')."</td>
				<th colspan=2>$BalanceDate</th>
				<th colspan=2>"._('Last Year').'</th>
				</tr>';
	} else { /*summary */
		$TableHeader = "<tr>
				<th colspan=2></th>
				<th colspan=2>$BalanceDate</th>
				<th colspan=2>"._('Last Year').'</th>
				</tr>';
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
						echo '<tr>
							<td colspan=2></td>
      							<td><hr></td>
							<td></td>
							<td><hr></td>
							<td></td>
							</tr>';
					}
					printf('<td colspan=2><I>%s</I></td>
						<td class=number>%s</td>
						<td></td>
						<td class=number>%s</td>
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
					echo '<tr>
						<td colspan=2></td>
						<td><hr></td>
						<td></td>
						<td><hr></td>
						<td></td>
						</tr>';
				}

				printf('<td colspan=2>%s</td>
					<td class=number>%s</td>
					<td></td>
					<td class=number>%s</td>
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
					echo '<tr>
					<td colspan=2></td>
					<td><hr></td>
					<td></td>
					<td><hr></td>
					<td></td>
					</tr>';
				} else {
					echo '<tr>
					<td colspan=3></td>
					<td><hr></td>
					<td></td>
					<td><hr></td>
					</tr>';
				}
	
				printf('<tr>
					<td colspan=3><font size=4>%s</font></td>
					<td class=number>%s</td>
					<td></td>
					<td class=number>%s</td>
				</tr>',
				$Sections[$Section],
				number_format($SectionBalance),
				number_format($SectionBalanceLY));
				$j++;
			}
			$SectionBalanceLY = 0;
			$SectionBalance = 0;
			$Section = $myrow['sectioninaccounts'];
			

			if ($_POST['Detail']=='Detailed'){
				printf('<tr>
					<td colspan=6><font size=4 color=BLUE><b>%s</b></font></td>
					</tr>',
					$Sections[$myrow['sectioninaccounts']]);
			}
		}

		if ($myrow['groupname']!= $ActGrp){

			if ($ActGrp!='' AND $myrow['parentgroupname']==$ActGrp){
				$Level++;
			}
		
			if ($_POST['Detail']=='Detailed'){
				$ActGrp = $myrow['groupname'];
				printf('<tr>
				<td colspan=6><font size=2 color=BLUE><b>%s</b></font></td>
				</tr>',
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
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k++;
			}

			$ActEnquiryURL = "<a href='$rootpath/GLAccountInquiry.php?" . SID . "Period=" . $_POST['BalancePeriodEnd'] . '&Account=' . $myrow['accountcode'] . "'>" . $myrow['accountcode'] . '<a>';

			$PrintString = '<td>%s</td>
					<td>%s</td>
					<td class=number>%s</td>
					<td></td>
					<td class=number>%s</td>
					<td></td>
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
			echo '<tr>
				<td colspan=2></td>
				<td><hr></td>
				<td></td>
				<td><hr></td>
				<td></td>
				</tr>';
		}
		printf('<td colspan=2><I>%s</I></td>
			<td class=number>%s</td>
			<td></td>
			<td class=number>%s</td>
			</tr>',
			$ParentGroups[$Level],
			number_format($GroupTotal[$Level]),
			number_format($LYGroupTotal[$Level])
			);
		$Level--;
	}
	if ($_POST['Detail']=='Detailed'){
		echo '<tr>
			<td colspan=2></td>
			<td><hr></td>
			<td></td>
			<td><hr></td>
			<td></td>
			</tr>';
	}

	printf('<td colspan=2>%s</td>
		<td class=number>%s</td>
		<td></td>
		<td class=number>%s</td>
		</tr>',
		$ParentGroups[$Level],
		number_format($GroupTotal[$Level]),
		number_format($LYGroupTotal[$Level])
		);
	
	if ($_POST['Detail']=='Detailed'){
		echo '<tr>
		<td colspan=2></td>
		<td><hr></td>
		<td></td>
		<td><hr></td>
		<td></td>
		</tr>';
	} else {
		echo '<tr>
		<td colspan=3></td>
		<td><hr></td>
		<td></td>
		<td><hr></td>
		</tr>';
	}

	printf('<tr>
		<td colspan=3><font size=4>%s</font></td>
		<td class=number>%s</td>
		<td></td>
		<td class=number>%s</td>
	</tr>',
	$Sections[$Section],
	number_format($SectionBalance),
	number_format($SectionBalanceLY));
	
	$Section = $myrow['sectioninaccounts'];

	if (isset($myrow['sectioninaccounts']) and $_POST['Detail']=='Detailed'){
		printf('<tr>
			<td colspan=6><font size=4 color=BLUE><b>%s</b></font></td>
			</tr>',
			$Sections[$myrow['sectioninaccounts']]);
	}
	
	echo '<tr>
		<td colspan=3></td>
      		<td><hr></td>
		<td></td>
		<td><hr></td>
		</tr>';

	printf('<tr>
		<td colspan=3>'._('Check Total').'</font></td>
		<td class=number>%s</td>
		<td></td>
		<td class=number>%s</td>
		</tr>',
		number_format($CheckTotal),
		number_format($LYCheckTotal));

	echo '<tr>
		<td colspan=3></td>
      		<td><hr></td>
		<td></td>
		<td><hr></td>
		</tr>';

	echo '</table>';
	echo "<div class='centre'><input type=submit Name='SelectADifferentPeriod' Value='"._('Select A Different Balance Date')."'></div>";
}

echo '</form>';
include('includes/footer.inc');
?>
