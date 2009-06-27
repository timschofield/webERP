<?php

/* $Revision: 1.27 $ */


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

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('General Ledger Account Inquiry') . '" alt="">' . ' ' . _('General Ledger Account Inquiry') . '</p>';

echo '<div class="page_help_text">' . _('Use the keyboard Shift key to select multiple periods') . '</div><br>';

echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date ('Y-m-d', Mktime(0,0,0,Date('m'),0,Date('Y')));

/*Show a form to allow input of criteria for TB to show */
echo '<table>
        <tr>
         <td>'._('Account').":</td>
         <td><select Name='Account'>";
         $sql = 'SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode';
         $Account = DB_query($sql,$db);
         while ($myrow=DB_fetch_array($Account,$db)){
            if($myrow['accountcode'] == $SelectedAccount){
   	        echo '<option selected VALUE=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' ' . $myrow['accountname'];
	    } else {
		echo '<option VALUE=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' ' . $myrow['accountname'];
	    }
         }
         echo '</select></td></tr>';

	//Select the tag
	echo '<tr><td>' . _('Select Tag') . ':</td><td><select name="tag">';

	$SQL = 'SELECT tagref,
				tagdescription
		FROM tags
		ORDER BY tagref';

	$result=DB_query($SQL,$db);
	echo '<option value=0>0 - None';
	while ($myrow=DB_fetch_array($result)){
    	if (isset($_POST['tag']) and $_POST['tag']==$myrow["tagref"]){
		echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
    	} else {
			echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
    	}
	}
	echo '</select></td></tr>';
// End select tag
         echo '<tr>
         <td>'._('For Period range').':</td>
         <td><select Name=Period[] multiple>';
	 $sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
	 $Periods = DB_query($sql,$db);
         $id=0;
         while ($myrow=DB_fetch_array($Periods,$db)){

            if(isset($SelectedPeriod[$id]) and $myrow['periodno'] == $SelectedPeriod[$id]){
              echo '<option selected VALUE=' . $myrow['periodno'] . '>' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period']));
            $id++;
            } else {
              echo '<option VALUE=' . $myrow['periodno'] . '>' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period']));
            }

         }
         echo "</select></td>
        </tr>
</table><p>
<div class='centre'><input type=submit name='Show' VALUE='"._('Show Account Transactions')."'></div></form>";

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

	if ($_POST['tag']=='all') {
 		$sql= "SELECT type,
			typename,
			gltrans.typeno,
			trandate,
			narrative,
			amount,
			periodno,
			tag
		FROM gltrans, systypes
		WHERE gltrans.account = $SelectedAccount
		AND systypes.typeid=gltrans.type
		AND posted=1
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		ORDER BY periodno, gltrans.trandate, counterindex";

	} else {
 		$sql= "SELECT type,
			typename,
			gltrans.typeno,
			trandate,
			narrative,
			amount,
			periodno,
			tag
		FROM gltrans, systypes
		WHERE gltrans.account = $SelectedAccount
		AND systypes.typeid=gltrans.type
		AND posted=1
		AND periodno>=$FirstPeriodSelected
		AND periodno<=$LastPeriodSelected
		AND tag='".$_POST['tag']."'
		ORDER BY periodno, gltrans.trandate, counterindex";
	}

	$ErrMsg = _('The transactions for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved because') ;
	$TransResult = DB_query($sql,$db,$ErrMsg);

	echo '<table>';

	$TableHeader = "<tr>
			<th>" . _('Type') . "</th>
			<th>" . _('Number') . "</th>
			<th>" . _('Date') . "</th>
			<th>" . _('Debit') . "</th>
			<th>" . _('Credit') . "</th>
			<th>" . _('Narrative') . "</th>
			<th>" . _('Tag') . '</th>
			</tr>';

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
			echo "<tr bgcolor='#FDFEEF'>
				<td colspan=3><b>" . _('Brought Forward Balance') . '</b><td>
				</td></td>
				<td class=number><b>' . number_format(-$RunningTotal,2) . '</b></td>
				<td></td>
				</tr>';
		} else { //its a debit balance b/fwd
			echo "<tr bgcolor='#FDFEEF'>
				<td colspan=3><b>" . _('Brought Forward Balance') . '</b></td>
				<td class=number><b>' . number_format($RunningTotal,2) . '</b></td>
				<td colspan=2></td>
				</tr>';
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

				echo "<tr bgcolor='#FDFEEF'>
					<td colspan=3><b>" . _('Total for period') . ' ' . $PeriodNo . '</b></td>';
				if ($PeriodTotal < 0 ){ //its a credit balance b/fwd
					echo '<td></td>
						<td class=number><b>' . number_format(-$PeriodTotal,2) . '</b></td>
						<td></td>
						</tr>';
				} else { //its a debit balance b/fwd
					echo '<td class=number><b>' . number_format($PeriodTotal,2) . '</b></td>
						<td colspan=2></td>
						</tr>';
				}
				$IntegrityReport .= '<br>' . _('Period') . ': ' . $PeriodNo  . _('Account movement per transaction') . ': '  . number_format($PeriodTotal,2) . ' ' . _('Movement per ChartDetails record') . ': ' . number_format($ChartDetailRow['actual'],2) . ' ' . _('Period difference') . ': ' . number_format($PeriodTotal -$ChartDetailRow['actual'],3);

				if (ABS($PeriodTotal -$ChartDetailRow['actual'])>0.01){
					$ShowIntegrityReport = True;
				}
			}
			$PeriodNo = $myrow['periodno'];
			$PeriodTotal = 0;
		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
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

		$tagsql='SELECT tagdescription FROM tags WHERE tagref='.$myrow['tag'];
		$tagresult=DB_query($tagsql,$db);
		$tagrow = DB_fetch_array($tagresult);
		
		printf("<td>%s</td>
			<td class=number><a href='%s'>%s</a></td>
			<td>%s</td>
			<td class=number>%s</td>
			<td class=number>%s</td>
			<td>%s</td>
			<td>%s</td>
			</tr>",
			$myrow['typename'],
			$URL_to_TransDetail,
			$myrow['typeno'],
			$FormatedTranDate,
			$DebitAmount,
			$CreditAmount,
			$myrow['narrative'],
			$tagrow['tagdescription']);

	}

	echo "<tr bgcolor='#FDFEEF'><td colspan=3><b>";
	if ($PandLAccount==True){
		echo _('Total Period Movement');
	} else { /*its a balance sheet account*/
		echo _('Balance C/Fwd');
	}
	echo '</b></td>';

	if ($RunningTotal >0){
		echo '<td align=right><b>' . number_format(($RunningTotal),2) . '</b></td><td colspan=2></td></tr>';
	}else {
		echo '<td></td><td align=right><b>' . number_format((-$RunningTotal),2) . '</b></td><td colspan=2></td></tr>';
	}
	echo '</table>';
} /* end of if Show button hit */



if (isset($ShowIntegrityReport) and $ShowIntegrityReport==True){
	if (!isset($IntegrityReport)) {$IntegrityReport='';}
	prnMsg( _('There are differences between the sum of the transactions and the recorded movements in the ChartDetails table') . '. ' . _('A log of the account differences for the periods report shows below'),'warn');
	echo '<p>'.$IntegrityReport;
}
include('includes/footer.inc');
?>
