<?php

/* $Id: $*/
/* $Revision: 1.00 $ */

$PageSecurity = 8;
include ('includes/session.inc');
$title = _('General Ledger Account Report');
include('includes/header.inc');
include('includes/GLPostings.inc');

if (isset($_POST['Period'])){
	$SelectedPeriod = $_POST['Period'];
} elseif (isset($_GET['Period'])){
	$SelectedPeriod = $_GET['Period'];
}

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/transactions.png" title="' . _('General Ledger Account Inquiry') . '" alt="">' . ' ' . _('General Ledger Account Report') . '</p>';

echo '<div class="page_help_text">' . _('Use the keyboard Shift key to select multiple accounts and periods') . '</div><br>';

echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?'  . SID . '>';

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date ('Y-m-d', Mktime(0,0,0,Date('m'),0,Date('Y')));

/*Show a form to allow input of criteria for the report */
echo '<table>
	        <tr>
	         <td>'._('Selected Accounts') . ':</td>
	         <td><select name="Account[]" multiple>';
$sql = 'SELECT accountcode, accountname FROM chartmaster ORDER BY accountcode';
$AccountsResult = DB_query($sql,$db);
$i=0;
while ($myrow=DB_fetch_array($AccountsResult,$db)){
	if(isset($_POST['Account'][$i]) AND $myrow['accountcode'] == $_POST['Account'][$i]){
		echo '<option selected VALUE=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' ' . $myrow['accountname'];
		$i++;
	} else {
		echo '<option VALUE=' . $myrow['accountcode'] . '>' . $myrow['accountcode'] . ' ' . $myrow['accountname'];
	}
}
echo '</select></td>';

echo '<td>'._('For Period range').':</td>
		<td><select Name=Period[] multiple>';
$sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
$Periods = DB_query($sql,$db);
$id=0;

while ($myrow=DB_fetch_array($Periods,$db)){
	if (isset($SelectedPeriod[$id]) and $myrow['periodno'] == $SelectedPeriod[$id]){
		echo '<option selected VALUE=' . $myrow['periodno'] . '>' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period']));
		$id++;
	} else {
		echo '<option VALUE=' . $myrow['periodno'] . '>' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period']));
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
echo '<option value=0>0 - '._('All tags');
while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['tag']) and $_POST['tag']==$myrow['tagref']){
	   echo '<option selected value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
	} else {
	   echo '<option value=' . $myrow['tagref'] . '>' . $myrow['tagref'].' - ' .$myrow['tagdescription'];
	}
}
echo '</select></td></tr>';
// End select tag

echo "</table><p>
<div class='centre'><input type=submit name='RunReport' VALUE='"._('Run Report')."'></div></form>";

/* End of the Form  rest of script is what happens if the show button is hit*/

if (isset($_POST['RunReport'])){

	if (!isset($SelectedPeriod)){
		prnMsg(_('A period or range of periods must be selected from the list box'),'info');
		include('includes/footer.inc');
		exit;
	}
	if (!isset($_POST['Account'])){
		prnMsg(_('An account or range of accounts must be selected from the list box'),'info');
		include('includes/footer.inc');
		exit;

	}


        $FilePointer = fopen('./companies/' . $_SESSION['DatabaseName'] . '/' . $_SESSION['ReportsDir'] . '/accounts.csv','w');


	foreach ($_POST['Account'] as $SelectedAccount){
		/*Is the account a balance sheet or a profit and loss account */
		$result = DB_query("SELECT chartmaster.accountname,
					accountgroups.pandl
     				    FROM accountgroups
				    INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
				    WHERE chartmaster.accountcode=$SelectedAccount",$db);
		$AccountDetailRow = DB_fetch_row($result);
		$AccountName = $AccountDetailRow[1];
		if ($AccountDetailRow[1]==1){
			$PandLAccount = True;
		}else{
			$PandLAccount = False; /*its a balance sheet account */
		}

		$FirstPeriodSelected = min($SelectedPeriod);
		$LastPeriodSelected = max($SelectedPeriod);

		if ($_POST['tag']==0) {
	 		$sql= "SELECT type,
				      typename,
				      gltrans.typeno,
				      gltrans.trandate,
				      gltrans.narrative,
          			      gltrans.amount,
				      gltrans.periodno,
				      gltrans.tag
				FROM gltrans, systypes
				WHERE gltrans.account = $SelectedAccount
				AND systypes.typeid=gltrans.type
				AND posted=1
				AND periodno>=$FirstPeriodSelected
				AND periodno<=$LastPeriodSelected
				ORDER BY periodno, gltrans.trandate, counterindex";

		} else {
	 		$sql= "SELECT gltrans.type,
                                      gltrans.typename,
				      gltrans.typeno,
				      gltrans.trandate,
				      gltrans.narrative,
          			      gltrans.amount,
				      gltrans.periodno,
				      gltrans.tag
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

		fwrite($FilePointer, $SelectedAccount . ' - ' . $AccountName . ' ' . _('for period'). ' ' . $FirstPeriodSelected . ' ' . _('to') . ' ' . $LastPeriodSelected);
		if ($PandLAccount==True) {
			$RunningTotal = 0;
		} else {
			$sql = "SELECT bfwd,
					actual,
					period
				FROM chartdetails
				WHERE chartdetails.accountcode= $SelectedAccount
				AND chartdetails.period=" . $FirstPeriodSelected;

			$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
			$ChartDetailsResult = DB_query($sql,$db,$ErrMsg);
			$ChartDetailRow = DB_fetch_array($ChartDetailsResult);

			$RunningTotal =$ChartDetailRow['bfwd'];

			if ($RunningTotal < 0 ){ //its a credit balance b/fwd
                            fwrite($FilePointer, _('Brought Forward Balance') . ',,,' . number_format(-$RunningTotal,2));
                        } else { //its a debit balance b/fwd
                            fwrite($FilePointer,_('Brought Forward Balance') . ',,,,' . number_format($RunningTotal,2));
			}
		}
		$PeriodTotal = 0;
		$PeriodNo = -9999;

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

                  	                if ($PeriodTotal < 0 ){
                                               fwrite($FilePointer, _('Period Total') . ',,,' . number_format(-$PeriodTotal,2));
                                        } else { //its a debit balance b/fwd
                                               fwrite($FilePointer,_('Period Total') . ',,,,' . number_format($PeriodTotal,2));
                                        }
				}
				$PeriodNo = $myrow['periodno'];
				$PeriodTotal = 0;
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

			$tagsql='SELECT tagdescription FROM tags WHERE tagref='.$myrow['tag'];
			$tagresult=DB_query($tagsql,$db);
			$tagrow = DB_fetch_array($tagresult);

			// to edit this block
			$YPos -=$line_height;
			$FontSize=8;

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,30,$FontSize,$myrow['typename']);
			$LeftOvers = $pdf->addTextWrap(80,$YPos,30,$FontSize,$myrow['typeno'],'right');
			$LeftOvers = $pdf->addTextWrap(110,$YPos,50,$FontSize,$FormatedTranDate);
			$LeftOvers = $pdf->addTextWrap(160,$YPos,50,$FontSize,$DebitAmount,'right');
			$LeftOvers = $pdf->addTextWrap(210,$YPos,50,$FontSize,$CreditAmount,'right');
			$LeftOvers = $pdf->addTextWrap(320,$YPos,150,$FontSize,$myrow['narrative']);
			$LeftOvers = $pdf->addTextWrap(470,$YPos,80,$FontSize,$tagrow['tagdescription']);

			if ($YPos < $Bottom_Margin + $line_height){
				NewPageHeader();
				$YPos -=$line_height;
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,$SelectedAccount . ' - ' . $AccountName);
			}

		}
		$YPos -=$line_height;
		if ($PandLAccount==True){
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize, _('Total Period Movement'));
		} else { /*its a balance sheet account*/
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize, _('Balance C/Fwd'));
		}

		if ($RunningTotal >0){
		   $LeftOvers = $pdf->addTextWrap(210,$YPos,50,$FontSize, number_format(-$RunningTotal,2) , 'right');
		} else { //its a debit balance b/fwd
                   $LeftOvers = $pdf->addTextWrap(160,$YPos,50,$FontSize, number_format($RunningTotal,2) , 'right');
                }
       		$YPos -=$line_height;
       		//draw a line under each account printed
                $pdf->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos);
	} /*end for each SelectedAccount */
} /* end of if PrintReport button hit */


/*Now check that there is some output and print the report out */
if (count($_POST['Account'])==0) {
   prnMsg(_('An account or range of accounts must be selected from the list box'),'info');
   include('includes/footer.inc');
   exit;

} else { //print the report













|

function NewPageHeader () {
	global $PageNumber,
				$pdf,
				$YPos,
				$Page_Height,
				$Page_Width,
				$Top_Margin,
				$FontSize,
				$Left_Margin,
				$Right_Margin,
				$line_height;
				$SelectedAccount;
				$AccountName;

	/*PDF page header for GL Account report */

	if ($PageNumber > 1){
		$pdf->newPage();
	}

	$FontSize=10;
	$YPos= $Page_Height-$Top_Margin;

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$line_height;

	$FontSize=10;

	$ReportTitle = _('GL Account Report');


	$LeftOvers = $pdf->addTextWrap($Left_Margin, $YPos,450,$FontSize, $ReportTitle . ' ' . _('for all stock locations'));

	$FontSize=8;
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos,120,$FontSize,_('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber);

	$YPos -=(2*$line_height);

	/*Draw a rectangle to put the headings in     */

	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
	$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
	$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

	/*set up the headings */
	$XPos = $Left_Margin+1;

	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,30,$FontSize,_('Type'),'centre');
	$LeftOvers = $pdf->addTextWrap(80,$YPos,30,$FontSize,_('Reference'),'centre');
	$LeftOvers = $pdf->addTextWrap(110,$YPos,50,$FontSize,_('Date'),'centre');
	$LeftOvers = $pdf->addTextWrap(160,$YPos,50,$FontSize,_('Debit'),'centre');
	$LeftOvers = $pdf->addTextWrap(210,$YPos,50,$FontSize,_('Credit'),'centre');
	$LeftOvers = $pdf->addTextWrap(320,$YPos,150,$FontSize,_('Narrative'),'centre');
	$LeftOvers = $pdf->addTextWrap(470,$YPos,80,$FontSize,_('Tag'),'centre');


	$YPos =$YPos - (2*$line_height);
	$FontSize=8;
}

?>
