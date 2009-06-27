<?php

/* $Revision: 1.1 $ */

$PageSecurity = 8;

include ('includes/session.inc');
$title = _('Income and Expenditure');
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable


if (isset($_POST['FromPeriod']) and ($_POST['FromPeriod'] > $_POST['ToPeriod'])){
	prnMsg(_('The selected period from is actually after the period to') . '! ' . _('Please reselect the reporting period'),'error');
	$_POST['SelectADifferentPeriod']='Select A Different Period';
}

if ((!isset($_POST['FromPeriod']) AND !isset($_POST['ToPeriod'])) OR isset($_POST['SelectADifferentPeriod'])){

	include('includes/header.inc');
	echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	
	if (Date('m') > $_SESSION['YearEnd']){
		/*Dates in SQL format */
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
	} else {
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
		$FromDate = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
	}
	$period=GetPeriod($FromDate, $db);
	
	/*Show a form to allow input of criteria for profit and loss to show */
	echo '<table><tr><td>'._('Select Period From').":</td><td><select Name='FromPeriod'>";

	$sql = 'SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno DESC';
	$Periods = DB_query($sql,$db);


	while ($myrow=DB_fetch_array($Periods,$db)){
		if(isset($_POST['FromPeriod']) AND $_POST['FromPeriod']!=''){
			if( $_POST['FromPeriod']== $myrow['periodno']){
				echo '<option selected VALUE=' . $myrow['periodno'] . '>' .MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			} else {
				echo '<option VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			}
		} else {
			if($myrow['lastdate_in_period']==$DefaultFromDate){
				echo '<option selected VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			} else {
				echo '<option VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
			}
		}
	}

	echo '</select></td></tr>';
	if (!isset($_POST['ToPeriod']) OR $_POST['ToPeriod']==''){
		$lastDate = date("Y-m-d",mktime(0,0,0,Date('m')+1,0,Date('Y')));
		$sql = "SELECT periodno FROM periods where lastdate_in_period = '$lastDate'";
		$MaxPrd = DB_query($sql,$db);
		$MaxPrdrow = DB_fetch_row($MaxPrd);
		$DefaultToPeriod = (int) ($MaxPrdrow[0]);

	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}
	
	echo '<tr><td>' . _('Select Period To') . ":</td><td><select Name='ToPeriod'>";

	$RetResult = DB_data_seek($Periods,0);

	while ($myrow=DB_fetch_array($Periods,$db)){

		if($myrow['periodno']==$DefaultToPeriod){
			echo '<option selected VALUE=' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		} else {
			echo '<option VALUE =' . $myrow['periodno'] . '>' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']);
		}
	}
	echo '</select></td></tr>';
	//Select the tag
	echo '<tr><td>'._('Select tag').'<td><select name="tag">';

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
	echo '</select></td>';
// End select tag
	
	echo '<tr><td>'._('Detail Or Summary').":</td><td><select Name='Detail'>";
		echo "<option selected VALUE='Summary'>"._('Summary');
		echo "<option selected VALUE='Detailed'>"._('All Accounts');
	echo '</select></td></tr>';

	echo '</table>';

	echo "<div class='centre'><input type=submit Name='ShowPL' Value='"._('Show Statement of Income and Expenditure')."'>";
	echo "<input type=submit Name='PrintPDF' Value='"._('PrintPDF')."'></div>";

	/*Now do the posting while the user is thinking about the period to select */

	include ('includes/GLPostings.inc');

} else if (isset($_POST['PrintPDF'])) {
	
	include('includes/PDFStarter.php');
	$PageNumber = 0;
	$FontSize = 10;
	$pdf->addinfo('Title', _('Income and Expenditure') );
	$pdf->addinfo('Subject', _('Income and Expenditure') );
	$line_height = 12;

	$NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;

	if ($NumberOfMonths > 12){
		include('includes/header.inc');
		echo '<p>';
		prnMsg(_('A period up to 12 months in duration can be specified') . ' - ' . _('the system automatically shows a comparative for the same period from the previous year') . ' - ' . _('it cannot do this if a period of more than 12 months is specified') . '. ' . _('Please select an alternative period range'),'error');
		include('includes/footer.inc');
		exit;
	}

	$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_POST['ToPeriod'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);


	$SQL = 'SELECT accountgroups.sectioninaccounts, 
			accountgroups.groupname,
			accountgroups.parentgroupname,
			gltrans.account ,
			chartmaster.accountname,
			Sum(CASE WHEN (gltrans.periodno>=' . $_POST['FromPeriod'] . ' and gltrans.periodno<=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalAllPeriods,
			Sum(CASE WHEN (gltrans.periodno=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalThisPeriod
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN gltrans
		ON chartmaster.accountcode= gltrans.account
		WHERE accountgroups.pandl=1
			AND gltrans.tag='.$_POST['tag'].'
		GROUP BY accountgroups.sectioninaccounts,
			accountgroups.groupname,
			accountgroups.parentgroupname,
			gltrans.account,
			chartmaster.accountname,
			accountgroups.sequenceintb
		ORDER BY accountgroups.sectioninaccounts, 
			accountgroups.sequenceintb,
			accountgroups.groupname,
			gltrans.account';

	
	$AccountsResult = DB_query($SQL,$db);

	if (DB_error_no($db) != 0) {
		$title = _('Income and Expenditure') . ' - ' . _('Problem Report') . '....';
		include('includes/header.inc');
		prnMsg( _('No general ledger accounts were returned by the SQL because') . ' - ' . DB_error_msg($db) );
		echo '<br><a href="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
		if ($debug == 1){
			echo '<br>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}
	
	include('includes/PDFProfitAndLossPageHeader.inc');

	$Section = '';
	$SectionPrdActual = 0;

	$ActGrp = '';
	$ParentGroups = array();
	$Level = 0;
	$ParentGroups[$Level]='';
	$GrpPrdActual = array(0);
	$PeriodProfitLoss = 0;
	while ($myrow = DB_fetch_array($AccountsResult)){

		// Print heading if at end of page
		if ($YPos < ($Bottom_Margin)){
			include('includes/PDFProfitAndLossPageHeader.inc');
		}
		
		if ($myrow['groupname'] != $ActGrp){
			if ($ActGrp != ''){
				if ($myrow['parentgroupname']!=$ActGrp){
					while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
						if ($_POST['Detail'] == 'Detailed'){
							$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
						} else {
							$ActGrpLabel = $ParentGroups[$Level];
						}
						if ($Section == 1){ /*Income */	
							$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($GrpPrdActual[$Level]),'right');
							$YPos -= (2 * $line_height);
						} else { /*Costs */
							$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
							$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format(-$GrpPrdActual[$Level]),'right');
							$YPos -= (2 * $line_height);
						}
						$GrpPrdLY[$Level] = 0;
						$GrpPrdActual[$Level] = 0;
						$GrpPrdBudget[$Level] = 0;
						$ParentGroups[$Level] ='';
						$Level--;
// Print heading if at end of page
						if ($YPos < ($Bottom_Margin + (2*$line_height))){
							include('includes/PDFProfitAndLossPageHeader.inc');
						}
					} //end of loop  
					//still need to print out the group total for the same level
					if ($_POST['Detail'] == 'Detailed'){
						$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
					} else {
						$ActGrpLabel = $ParentGroups[$Level];
					}
					if ($Section == 1){ /*Income */	
						$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel); $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($GrpPrdActual[$Level]),'right');
						$YPos -= (2 * $line_height);
					} else { /*Costs */
						$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
						$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format(-$GrpPrdActual[$Level]),'right');
						$YPos -= (2 * $line_height);
					}
					$GrpPrdActual[$Level] = 0;
					$ParentGroups[$Level] ='';
				}
			}
		}
		
		// Print heading if at end of page
		if ($YPos < ($Bottom_Margin +(2 * $line_height))){
			include('includes/PDFProfitAndLossPageHeader.inc');
		}

		if ($myrow['sectioninaccounts'] != $Section){

			$pdf->selectFont('./fonts/Helvetica-Bold.afm');
			$FontSize =10;
			if ($Section != ''){
				$pdf->line($Left_Margin+310, $YPos+$line_height,$Left_Margin+500, $YPos+$line_height);
				$pdf->line($Left_Margin+310, $YPos,$Left_Margin+500, $YPos);
				if ($Section == 1) { /*Income*/

					$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$Section]);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($SectionPrdActual),'right');
					$YPos -= (2 * $line_height);
					
					$TotalIncome = -$SectionPrdActual;
					$TotalBudgetIncome = -$SectionPrdBudget;
					$TotalLYIncome = -$SectionPrdLY;
				} else {
					$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$Section]);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format(-$SectionPrdActual),'right');
					$YPos -= (2 * $line_height);
				}
				if ($Section == 2){ /*Cost of Sales - need sub total for Gross Profit*/
					$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,_('Gross Profit'));
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($TotalIncome - $SectionPrdActual),'right');
					$pdf->line($Left_Margin+310, $YPos+$line_height,$Left_Margin+500, $YPos+$line_height);
					$pdf->line($Left_Margin+310, $YPos,$Left_Margin+500, $YPos);
					$YPos -= (2 * $line_height);

					if ($TotalIncome != 0){
						$PrdGPPercent = 100 *($TotalIncome - $SectionPrdActual) / $TotalIncome;
					} else {
						$PrdGPPercent = 0;
					}
					$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,_('Gross Profit Percent'));
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($PrdGPPercent,1) . '%','right');
					$YPos -= (2 * $line_height);
				}
			}
			$SectionPrdActual = 0;

			$Section = $myrow['sectioninaccounts'];

			if ($_POST['Detail'] == 'Detailed'){
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$myrow['sectioninaccounts']]);
				$YPos -= (2 * $line_height);
			}
			$FontSize =8;
			$pdf->selectFont('./fonts/Helvetica.afm');
		}

		if ($myrow['groupname'] != $ActGrp){
			if ($myrow['parentgroupname']==$ActGrp AND $ActGrp !=''){ //adding another level of nesting
					$Level++;
			}
			$ActGrp = $myrow['groupname'];
			$ParentGroups[$Level]=$ActGrp;
			if ($_POST['Detail'] == 'Detailed'){
				$FontSize =10;
				$pdf->selectFont('./fonts/Helvetica-Bold.afm');
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$myrow['groupname']);
				$YPos -= (2 * $line_height);
				$FontSize =8;
				$pdf->selectFont('./fonts/Helvetica.afm');
			}
		}

		$AccountPeriodActual = $myrow['TotalAllPeriods'];
		$PeriodProfitLoss += $AccountPeriodActual;

		for ($i=0;$i<=$Level;$i++){
//			$GrpPrdLY[$i] +=$AccountPeriodLY;
		}
		

		$SectionPrdActual +=$AccountPeriodActual;

		if ($_POST['Detail'] == _('Detailed')) {
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$myrow['account']);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+60,$YPos,190,$FontSize,$myrow['accountname']);
			if ($Section == 1) { /*Income*/
				$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($AccountPeriodActual),'right');
			} else {
				$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format(-$AccountPeriodActual),'right');
			}
			$YPos -= $line_height;
		}
	}
	//end of loop

	if ($ActGrp != ''){

		if ($myrow['parentgroupname']!=$ActGrp){
		
			while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
				if ($_POST['Detail'] == 'Detailed'){
					$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = $ParentGroups[$Level];
				}
				if ($Section == 1){ /*Income */	
					$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($GrpPrdActual[$Level]),'right');
					$YPos -= (2 * $line_height);
				} else { /*Costs */
					$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
					$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format(-$GrpPrdActual[$Level]),'right');
					$YPos -= (2 * $line_height);
				}
				$GrpPrdActual[$Level] = 0;
				$ParentGroups[$Level] ='';
				$Level--;
				// Print heading if at end of page
				if ($YPos < ($Bottom_Margin + (2*$line_height))){
					include('includes/PDFProfitAndLossPageHeader.inc');
				}
			} 
			//still need to print out the group total for the same level
			if ($_POST['Detail'] == 'Detailed'){
				$ActGrpLabel = $ParentGroups[$Level] . ' ' . _('total');
			} else {
				$ActGrpLabel = $ParentGroups[$Level];
			}
			if ($Section == 1){ /*Income */	
				$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel); $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($GrpPrdActual[$Level]),'right');
				$YPos -= (2 * $line_height);
			} else { /*Costs */
				$LeftOvers = $pdf->addTextWrap($Left_Margin +($Level*10),$YPos,200 -($Level*10),$FontSize,$ActGrpLabel);
				$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format(-$GrpPrdActual[$Level]),'right');
				$YPos -= (2 * $line_height);
			}
			$GrpPrdActual[$Level] = 0;
			$ParentGroups[$Level] ='';
		}
	}
	// Print heading if at end of page
	if ($YPos < ($Bottom_Margin + (2*$line_height))){
		include('includes/PDFProfitAndLossPageHeader.inc');
	}
	if ($Section != ''){

		$pdf->selectFont('./fonts/Helvetica-Bold.afm');
		$pdf->line($Left_Margin+310, $YPos+10,$Left_Margin+500, $YPos+10);
		$pdf->line($Left_Margin+310, $YPos,$Left_Margin+500, $YPos);
		
		if ($Section == 1) { /*Income*/
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200,$FontSize,$Sections[$Section]);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format(-$SectionPrdActual),'right');
			$YPos -= (2 * $line_height);
			
			$TotalIncome = -$SectionPrdActual;
			$TotalBudgetIncome = -$SectionPrdBudget;
			$TotalLYIncome = -$SectionPrdLY;
		} else {
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$Sections[$Section]);
			$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($SectionPrdActual),'right');
			$YPos -= (2 * $line_height);
		}
		if ($Section == 2){ /*Cost of Sales - need sub total for Gross Profit*/
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Gross Profit'));
			$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format($TotalIncome - $SectionPrdActual),'right');
			$YPos -= (2 * $line_height);
			
			$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format(100*($TotalIncome - $SectionPrdActual)/$TotalIncome,1) . '%','right');
			$YPos -= (2 * $line_height);
		}
	}

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Profit').' - '._('Loss'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+310,$YPos,70,$FontSize,number_format(-$PeriodProfitLoss),'right');
	
	$pdf->line($Left_Margin+310, $YPos+$line_height,$Left_Margin+500, $YPos+$line_height);
	$pdf->line($Left_Margin+310, $YPos,$Left_Margin+500, $YPos);	
	
	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);
	
	if ($len <= 20){
		$title = _('Print Income and Expenditure Error');
		include('includes/header.inc');
		echo '<p>';
		prnMsg( _('There were no entries to print out for the selections specified') );
		echo '<br><a href="'. $rootpath.'/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=Profit_Loss.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		$pdf->Stream();
	}
	exit;
	
} else {

	include('includes/header.inc');
	echo "<form method='POST' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo "<input type=hidden name='FromPeriod' VALUE=" . $_POST['FromPeriod'] . "><input type=hidden name='ToPeriod' VALUE=" . $_POST['ToPeriod'] . '>';

	$NumberOfMonths = $_POST['ToPeriod'] - $_POST['FromPeriod'] + 1;
	
	if ($NumberOfMonths >12){
		echo '<p>';
		prnMsg(_('A period up to 12 months in duration can be specified') . ' - ' . _('the system automatically shows a comparative for the same period from the previous year') . ' - ' . _('it cannot do this if a period of more than 12 months is specified') . '. ' . _('Please select an alternative period range'),'error');
		include('includes/footer.inc');
		exit;
	}

	$sql = 'SELECT lastdate_in_period FROM periods WHERE periodno=' . $_POST['ToPeriod'];
	$PrdResult = DB_query($sql, $db);
	$myrow = DB_fetch_row($PrdResult);
	$PeriodToDate = MonthAndYearFromSQLDate($myrow[0]);


	$SQL = 'SELECT accountgroups.sectioninaccounts, 
			accountgroups.groupname,
			accountgroups.parentgroupname,
			gltrans.account ,
			chartmaster.accountname,
			Sum(CASE WHEN (gltrans.periodno>=' . $_POST['FromPeriod'] . ' and gltrans.periodno<=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalAllPeriods,
			Sum(CASE WHEN (gltrans.periodno=' . $_POST['ToPeriod'] . ') THEN gltrans.amount ELSE 0 END) AS TotalThisPeriod
		FROM chartmaster INNER JOIN accountgroups
		ON chartmaster.group_ = accountgroups.groupname INNER JOIN gltrans
		ON chartmaster.accountcode= gltrans.account
		WHERE accountgroups.pandl=1
			AND gltrans.tag='.$_POST['tag'].'
		GROUP BY accountgroups.sectioninaccounts,
			accountgroups.groupname,
			accountgroups.parentgroupname,
			gltrans.account,
			chartmaster.accountname,
			accountgroups.sequenceintb
		ORDER BY accountgroups.sectioninaccounts, 
			accountgroups.sequenceintb,
			accountgroups.groupname,
			gltrans.account';

	
	$AccountsResult = DB_query($SQL,$db,_('No general ledger accounts were returned by the SQL because'),_('The SQL that failed was'));
	$sql='SELECT tagdescription FROM tags WHERE tagref='.$_POST['tag'];
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);
		
	echo '<div class="centre"><font size=4 color=BLUE><b>' . _('Statement of Income and Expenditure for ').$myrow[0]._('during the'). ' ' . $NumberOfMonths . ' ' . _('months to'). ' ' . $PeriodToDate . '</b></font></div><br>';

	/*show a table of the accounts info returned by the SQL
	Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */

	echo '<table cellpadding=2>';

	if ($_POST['Detail']=='Detailed'){
		$TableHeader = "<tr>
				<th>"._('Account')."</th>
				<th>"._('Account Name')."</th>
				<th colspan=2>"._('Period Actual')."</th>
				</tr>";
	} else { /*summary */
		$TableHeader = "<tr>
				<th colspan=2></th>
				<th colspan=2>"._('Period Actual')."</th>
				</tr>";
	}


	$j = 1;
	$k=0; //row colour counter
	$Section='';
	$SectionPrdActual= 0;
	$SectionPrdLY 	 = 0;
	$SectionPrdBudget= 0;
	
	$PeriodProfitLoss = 0;
	$PeriodProfitLoss = 0;
	$PeriodLYProfitLoss = 0;
	$PeriodBudgetProfitLoss = 0;
	

	$ActGrp ='';
	$ParentGroups = array();
	$Level = 0;
	$ParentGroups[$Level]='';
	$GrpPrdActual = array(0);
	$GrpPrdLY = array(0);
	$GrpPrdBudget = array(0);


	while ($myrow=DB_fetch_array($AccountsResult)) {


		if ($myrow['groupname']!= $ActGrp){
			if ($myrow['parentgroupname']!=$ActGrp AND $ActGrp!=''){
					while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
					if ($_POST['Detail']=='Detailed'){
						echo '<tr>
							<td colspan=2></td>
							<td colspan=6><hr></td>
						</tr>';
						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
					} else {
						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
					}
					
				if ($Section ==3){ /*Income */
						printf('<tr>
							<td colspan=2><font size=2><I>%s </I></font></td>
							<td></td>
							<td class=number>%s</td>
							</tr>',
							$ActGrpLabel,
							number_format($GrpPrdActual[$Level]));
					} else { /*Costs */
						printf('<tr>
							<td colspan=2><font size=2><I>%s </I></font></td>
							<td class=number>%s</td>
							<td></td>
							</tr>',
							$ActGrpLabel,
							number_format(-$GrpPrdActual[$Level]));
					}
					$GrpPrdLY[$Level] = 0;
					$GrpPrdActual[$Level] = 0;
					$GrpPrdBudget[$Level] = 0;
					$ParentGroups[$Level] ='';
					$Level--;
				}//end while
				//still need to print out the old group totals
				if ($_POST['Detail']=='Detailed'){
						echo '<tr>
							<td colspan=2></td>
							<td colspan=6><hr></td>
						</tr>';
						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
					} else {
						$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
					}

				if ($Section ==4){ /*Income */
					printf('<tr>
						<td colspan=2><font size=2><I>%s </I></font></td>
						<td></td>
						<td class=number>%s</td>
						</tr>',
						$ActGrpLabel,
						number_format(-$GrpPrdActual[$Level]));
				} else { /*Costs */
					printf('<tr>
						<td colspan=2><font size=2><I>%s </I></font></td>
						<td class=number>%s</td>
						<td></td>
						</tr>',
						$ActGrpLabel,
						number_format(-$GrpPrdActual[$Level]));
				}
				$GrpPrdActual[$Level] = 0;
				$ParentGroups[$Level] ='';
			}
			$j++;
		}

		if ($myrow['sectioninaccounts']!= $Section){

			if ($SectionPrdLY+$SectionPrdActual+$SectionPrdBudget !=0){
				if ($Section==4) { /*Income*/

					echo '<tr>
						<td colspan=3></td>
      						<td><hr></td>
						<td></td>
						<td><hr></td>
						<td></td>
						<td><hr></td>
					</tr>';

					printf('<tr>
					<td colspan=2><font size=4>%s</font></td>
					<td></td>
					</tr>',
					$Sections[$Section],
					number_format($SectionPrdActual));
					$TotalIncome = -$SectionPrdActual;
				} else {
					echo '<tr>
					<td colspan=2></td>
      					<td><hr></td>
					<td></td>
					<td><hr></td>
					<td></td>
					<td><hr></td>
					</tr>';
					printf('<tr>
					<td colspan=2><font size=4>%s</font></td>
					<td></td>
					<td class=number>%s</td>
					</tr>',
					$Sections[$Section],
					number_format($SectionPrdActual));
				}
				if ($Section==2){ /*Cost of Sales - need sub total for Gross Profit*/
					echo '<tr>
						<td colspan=2></td>
						<td colspan=6><hr></td>
					</tr>';
					printf('<tr>
						<td colspan=2><font size=4>'._('Gross Profit').'</font></td>
						<td></td>
						<td class=number>%s</td>
						</tr>',
					number_format($TotalIncome - $SectionPrdActual));

					if ($TotalIncome !=0){
						$PrdGPPercent = 100*($TotalIncome - $SectionPrdActual)/$TotalIncome;
					} else {
						$PrdGPPercent =0;
					}
					echo '<tr>
						<td colspan=2></td>
						<td colspan=6><hr></td>
					</tr>';
					printf('<tr>
						<td colspan=2><font size=2><I>'._('Gross Profit Percent').'</I></font></td>
						<td></td>
						<td class=number><I>%s</I></td>
						</tr><tr><td colspan=6> </td></tr>',
						number_format($PrdGPPercent,1) . '%');
					$j++;
				}
			}
			$SectionPrdActual =0;

			$Section = $myrow['sectioninaccounts'];

			if ($_POST['Detail']=='Detailed'){
				printf('<tr>
					<td colspan=6><font size=4 color=BLUE><b>%s</b></font></td>
					</tr>',
					$Sections[$myrow['sectioninaccounts']]);
			}
			$j++;

		}



		if ($myrow['groupname']!= $ActGrp){

			if ($myrow['parentgroupname']==$ActGrp AND $ActGrp !=''){ //adding another level of nesting
				$Level++;
			}
			
			$ParentGroups[$Level] = $myrow['groupname'];
			$ActGrp = $myrow['groupname'];
			if ($_POST['Detail']=='Detailed'){
				printf('<tr>
					<td colspan=6><font size=2 color=BLUE><b>%s</b></font></td>
					</tr>',
					$myrow['groupname']);
					echo $TableHeader;
			}
		}

		$AccountPeriodActual = $myrow['TotalAllPeriods'];
		if ($Section==4) {
			$PeriodProfitLoss -= $AccountPeriodActual;
		} else {
			$PeriodProfitLoss -= $AccountPeriodActual;			
		}

		for ($i=0;$i<=$Level;$i++){
			if (!isset($GrpPrdActual[$i])) {$GrpPrdActual[$i]=0;}
			$GrpPrdActual[$i] +=$AccountPeriodActual;
		}
		$SectionPrdActual -=$AccountPeriodActual;

		if ($_POST['Detail']==_('Detailed')){

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			$ActEnquiryURL = "<a href='$rootpath/GLAccountInquiry.php?" . SID . '&Period=' . $_POST['ToPeriod'] . '&Account=' . $myrow['account'] . "&Show=Yes'>" . $myrow['account'] . '<a>';

			if ($Section ==4){
				 printf('<td>%s</td>
					<td>%s</td>
					<td></td>
					<td class=number>%s</td>
					</tr>',
					$ActEnquiryURL,
					$myrow['accountname'],
					number_format(-$AccountPeriodActual));
			} else {
				printf('<td>%s</td>
					<td>%s</td>
					<td class=number>%s</td>
					</tr>',
					$ActEnquiryURL,
					$myrow['accountname'],
					number_format(-$AccountPeriodActual));
			}

			$j++;
		}
	}
	//end of loop


	if ($myrow['groupname']!= $ActGrp){
		if ($myrow['parentgroupname']!=$ActGrp AND $ActGrp!=''){
			while ($myrow['groupname']!=$ParentGroups[$Level] AND $Level>0) {
				if ($_POST['Detail']=='Detailed'){
					echo '<tr>
						<td colspan=2></td>
						<td colspan=6><hr></td>
					</tr>';
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
				}
				if ($Section ==4){ /*Income */
					printf('<tr>
						<td colspan=2><font size=2><I>%s </I></font></td>
						<td></td>
						<td class=number>%s</td>
						<td></td>
						<td class=number>%s</td>
						<td></td>
						<td class=number>%s</td>
						</tr>',
						$ActGrpLabel,
						number_format(-$GrpPrdActual[$Level]));
				} else { /*Costs */
					printf('<tr>
						<td colspan=2><font size=2><I>%s </I></font></td>
						<td class=number>%s</td>
						<td></td>
						</tr>',
						$ActGrpLabel,
						number_format($GrpPrdActual[$Level]));
				}
				$GrpPrdActual[$Level] = 0;
				$ParentGroups[$Level] ='';
				$Level--;
			}//end while
			//still need to print out the old group totals
			if ($_POST['Detail']=='Detailed'){
					echo '<tr>
						<td colspan=2></td>
						<td colspan=6><hr></td>
					</tr>';
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level] . ' ' . _('total');
				} else {
					$ActGrpLabel = str_repeat('___',$Level) . $ParentGroups[$Level];
				}

			if ($Section ==4){ /*Income */
				printf('<tr>
					<td colspan=2><font size=2><I>%s </I></font></td>
					<td></td>
					<td class=number>%s</td>
					</tr>',
					$ActGrpLabel,
					number_format(-$GrpPrdActual[$Level]));
			} else { /*Costs */
				printf('<tr>
					<td colspan=2><font size=2><I>%s </I></font></td>
					<td class=number>%s</td>
					<td></td>
					</tr>',
					$ActGrpLabel,
					number_format($GrpPrdActual[$Level]));
			}
			$GrpPrdActual[$Level] = 0;
			$ParentGroups[$Level] ='';
		}
		$j++;
	}

	if ($myrow['sectioninaccounts']!= $Section){

		if ($Section==4) { /*Income*/

			echo '<tr>
				<td colspan=3></td>
				<td><hr></td>
				<td></td>
				<td><hr></td>
				<td></td>
				<td><hr></td>
			</tr>';

			printf('<tr>
			<td colspan=2><font size=4>%s</font></td>
			<td></td>
			<td class=number>%s</td>
			</tr>',
			$Sections[$Section],
			number_format($SectionPrdActual));
			$TotalIncome = $SectionPrdActual;
		} else {
			echo '<tr>
			<td colspan=2></td>
			<td><hr></td>
			<td></td>
			<td><hr></td>
			<td></td>
			<td><hr></td>
			</tr>';
			printf('<tr>
			<td colspan=2><font size=4>%s</font></td>
			<td></td>
			<td class=number>%s</td>
			</tr>',
			$Sections[$Section],
			number_format(-$SectionPrdActual));
		}
		if ($Section==2){ /*Cost of Sales - need sub total for Gross Profit*/
			echo '<tr>
				<td colspan=2></td>
				<td colspan=6><hr></td>
			</tr>';
			printf('<tr>
				<td colspan=2><font size=4>'._('Gross Profit').'</font></td>
				<td></td>
				<td class=number>%s</td>
				<td></td>
				<td class=number>%s</td>
				<td></td>
				<td class=number>%s</td>
				</tr>',
			number_format($TotalIncome - $SectionPrdActual));

			if ($TotalIncome !=0){
				$PrdGPPercent = 100*($TotalIncome - $SectionPrdActual)/$TotalIncome;
			} else {
				$PrdGPPercent =0;
			}
			echo '<tr>
				<td colspan=2></td>
				<td colspan=6><hr></td>
			</tr>';
			printf('<tr>
				<td colspan=2><font size=2><I>'._('Gross Profit Percent').'</I></font></td>
				<td></td>
				<td class=number><I>%s</I></td>
				<td></td>
				<td class=number><I>%s</I></td>
				<td></td>
				<td class=number><I>%s</I></td>
				</tr><tr><td colspan=6> </td></tr>',
				number_format($PrdGPPercent,1) . '%');
			$j++;
		}
	
		$SectionPrdActual =0;

		$Section = $myrow['sectioninaccounts'];

		if ($_POST['Detail']=='Detailed' and isset($Sections[$myrow['sectioninaccounts']])){
			printf('<tr>
				<td colspan=6><font size=4 color=BLUE><b>%s</b></font></td>
				</tr>',
				$Sections[$myrow['sectioninaccounts']]);
		}
		$j++;

	}

	echo '<tr>
		<td colspan=2></td>
		<td colspan=6><hr></td>
		</tr>';

	printf("<tr bgcolor='#ffffff'>
		<td colspan=2><font size=4 color=BLUE><b>"._('Surplus').' - '._('Deficit')."</b></font></td>
		<td></td>
		<td class=number>%s</td>
		</tr>",
		number_format($PeriodProfitLoss)
		);

	echo '<tr>
		<td colspan=2></td>
		<td colspan=6><hr></td>
		</tr>';

	echo '</table>';
	echo "<div class='centre'><input type=submit Name='SelectADifferentPeriod' Value='"._('Select A Different Period')."'></div>";
}
echo '</form>';
include('includes/footer.inc');

?>
