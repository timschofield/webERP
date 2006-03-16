<?php

/* $Revision: 1.19 $ */


$PageSecurity = 8;
include ('includes/session.inc');
$title = _('General Ledger Account Inquiry');
include('includes/header.inc');
include('includes/GLPostings.inc');

if (isset($_POST['Account'])){
	$SelectedAccount = $_POST['Account'];
} elseif (isset($_GET['Account'])){
	$SelectedAccount = $_GET['Account'];
}

if (isset($_POST['Period'])){
	$SelectedPeriod = $_POST['Period'];
} elseif (isset($_GET['Period'])){
	$SelectedPeriod = $_GET['Period'];
}

echo "<FORM METHOD='POST' ACTION=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date ('Y-m-d', Mktime(0,0,0,Date('m'),0,Date('Y')));

/*Show a form to allow input of criteria for TB to show */
echo '<CENTER><TABLE>
        <TR>
         <TD>'._('Account').":</TD>
         <TD><SELECT Name='Account'>";
         $sql = 'SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode';
         $Account = DB_query($sql,$db);
         while ($myrow=DB_fetch_array($Account,$db)){
            if($myrow['accountcode'] == $SelectedAccount){
   	        echo '<OPTION SELECTED VALUE=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' ' . $myrow['accountname'];
	    } else {
		echo '<OPTION VALUE=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' ' . $myrow['accountname'];
	    }
         }
         echo '</SELECT></TD></TR>
         <TR>
         <TD>'._('For Period range').':</TD>
         <TD><SELECT Name=Period[] multiple>';
	 $sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
	 $Periods = DB_query($sql,$db);
         $id=0;
         while ($myrow=DB_fetch_array($Periods,$db)){

            if($myrow['periodno'] == $SelectedPeriod[$id]){
              echo '<OPTION SELECTED VALUE=' . $myrow['periodno'] . '>' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period']));
            $id++;
            } else {
              echo '<OPTION VALUE=' . $myrow['periodno'] . '>' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period']));
            }

         }
         echo "</SELECT></TD>
        </TR>
</TABLE><P>
<INPUT TYPE=SUBMIT NAME='Show' VALUE='"._('Show Account Transactions')."'></CENTER></FORM>";

/* End of the Form  rest of script is what happens if the show button is hit*/

if (isset($_POST['Show'])){

	if (!isset($SelectedPeriod)){
		prnMsg(_('A period or range of periods must be selected from the list box'),'info');
		include('includes/footer.inc');
		exit;
	}
	/*Is the account a balance sheet or a profit and loss account */
	$result = DB_query("SELECT pandl
				FROM accountgroups
				INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
				WHERE chartmaster.accountcode=$SelectedAccount",$db);
	$PandLRow = DB_fetch_row($result);
	if ($PandLRow[0]==1){
		$PandLAccount = True;
	}else{
		$PandLAccount = False; /*its a balance sheet account */
	}

	$FirstPeriodSelected = min($SelectedPeriod);
	$LastPeriodSelected = max($SelectedPeriod);

 	$sql= "SELECT type,
			typename,
			gltrans.typeno,
			trandate,
			narrative,
			amount,
			periodno
		FROM gltrans, systypes
		WHERE gltrans.account = $SelectedAccount
		AND systypes.typeid=gltrans.type
		AND posted=1
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		ORDER BY periodno, gltrans.trandate, counterindex";

	$ErrMsg = _('The transactions for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved because') ;
	$TransResult = DB_query($sql,$db,$ErrMsg);

	echo '<table>';

	$TableHeader = "<TR>
			<TD class='tableheader'>" . _('Type') . "</TD>
			<TD class='tableheader'>" . _('Number') . "</TD>
			<TD class='tableheader'>" . _('Date') . "</TD>
			<TD class='tableheader'>" . _('Debit') . "</TD>
			<TD class='tableheader'>" . _('Credit') . "</TD>
			<TD class='tableheader'>" . _('Narrative') . '</TD>
			</TR>';

	echo $TableHeader;

	if ($PandLAccount==True) {
		$RunningTotal = 0;
	} else {
	       // added to fix bug with Brought Forward Balance always being zero
					$sql = "SELECT bfwd, 
						actual,
						period 
					FROM chartdetails 
					WHERE chartdetails.accountcode= $SelectedAccount 
					AND chartdetails.period=" . $FirstPeriodSelected; 
					
				$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
				$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
				$ChartDetailRow = DB_fetch_array($ChartDetailsResult);
				// --------------------
				
		$RunningTotal =$ChartDetailRow['bfwd'];
		if ($RunningTotal < 0 ){ //its a credit balance b/fwd
			echo "<TR bgcolor='#FDFEEF'>
				<TD COLSPAN=3><B>" . _('Brought Forward Balance') . '</B><TD>
				</TD></TD>
				<TD ALIGN=RIGHT><B>' . number_format(-$RunningTotal,2) . '</B></TD>
				<TD></TD>
				</TR>';
		} else { //its a debit balance b/fwd
			echo "<TR bgcolor='#FDFEEF'>
				<TD COLSPAN=3><B>" . _('Brought Forward Balance') . '</B></TD>
				<TD ALIGN=RIGHT><B>' . number_format($RunningTotal,2) . '</B></TD>
				<TD COLSPAN=2></TD>
				</TR>';
		}
	}
	$PeriodTotal = 0;
	$PeriodNo = -9999;
	$ShowIntegrityReport = False;
	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($TransResult)) {

		if ($myrow['periodno']!=$PeriodNo){
			if ($PeriodNo!=-9999){ //ie its not the first time around
				/*Get the ChartDetails balance b/fwd and the actual movement in the account for the period as recorded in the chart details - need to ensure integrity of transactions to the chart detail movements. Also, for a balance sheet account it is the balance carried forward that is important, not just the transactions*/

				$sql = "SELECT bfwd, 
						actual,
						period 
					FROM chartdetails 
					WHERE chartdetails.accountcode= $SelectedAccount 
					AND chartdetails.period=" . $PeriodNo; 
					
				$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
				$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
				$ChartDetailRow = DB_fetch_array($ChartDetailsResult);
				
				echo "<TR bgcolor='#FDFEEF'>
					<TD COLSPAN=3><B>" . _('Total for period') . ' ' . $PeriodNo . '</B></TD>';
				if ($PeriodTotal < 0 ){ //its a credit balance b/fwd
					echo '<TD></TD>
						<TD ALIGN=RIGHT><B>' . number_format(-$PeriodTotal,2) . '</B></TD>
						<TD></TD>
						</TR>';
				} else { //its a debit balance b/fwd
					echo '<TD ALIGN=RIGHT><B>' . number_format($PeriodTotal,2) . '</B></TD>
						<TD COLSPAN=2></TD>
						</TR>';
				}
				$IntegrityReport .= '<BR>' . _('Period') . ': ' . $PeriodNo  . _('Account movement per transaction') . ': '  . number_format($PeriodTotal,2) . ' ' . _('Movement per ChartDetails record') . ': ' . number_format($ChartDetailRow['actual'],2) . ' ' . _('Period difference') . ': ' . number_format($PeriodTotal -$ChartDetailRow['actual'],3);
				
				if (ABS($PeriodTotal -$ChartDetailRow['actual'])>0.01){
					$ShowIntegrityReport = True;
				}
			}
			$PeriodNo = $myrow['periodno'];
			$PeriodTotal = 0;
		}

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}

		$RunningTotal += $myrow['amount'];
		$PeriodTotal += $myrow['amount'];

		if($myrow['amount']>=0){
			$DebitAmount = number_format($myrow['amount'],2);
			$CreditAmount = '';
		} else {
			$CreditAmount = number_format(-$myrow['amount'],2);
			$DebitAmount = '';
		}

		$FormatedTranDate = ConvertSQLDate($myrow['trandate']);
		$URL_to_TransDetail = $rootpath . '/GLTransInquiry.php?' . SID . '&TypeID=' . $myrow['type'] . '&TransNo=' . $myrow['typeno'];

		printf("<td>%s</td>
			<td><A HREF='%s'>%s</A></td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td>
			</tr>",
			$myrow['typename'],
			$URL_to_TransDetail,
			$myrow['typeno'],
			$FormatedTranDate,
			$DebitAmount,
			$CreditAmount,
			$myrow['narrative']);

		$j++;

		If ($j == 18){
			echo $TableHeader;
			$j=1;
		}
		
	}

	echo "<TR bgcolor='#FDFEEF'><TD COLSPAN=3><B>";
	if ($PandLAccount==True){
		echo _('Total Period Movement');
	} else { /*its a balance sheet account*/
		echo _('Balance C/Fwd');
	}
	echo '</B></TD>';

	if ($RunningTotal >0){
		echo '<TD ALIGN=RIGHT><B>' . number_format(($RunningTotal),2) . '</B></TD><TD COLSPAN=2></TD></TR>';
	}else {
		echo '<TD></TD><TD ALIGN=RIGHT><B>' . number_format((-$RunningTotal),2) . '</B></TD><TD COLSPAN=2></TD></TR>';
	}
	echo '</table>';
} /* end of if Show button hit */



if ($ShowIntegrityReport){

	prnMsg( _('There are differences between the sum of the transactions and the recorded movements in the ChartDetails table') . '. ' . _('A log of the account differences for the periods report shows below'),'warn');
	echo '<P>'.$IntegrityReport;
}
include('includes/footer.inc');
?>
