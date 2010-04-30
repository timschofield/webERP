<?php

/* $Revision: 1.0 $ */

$PageSecurity = 6;

include ('includes/session.inc');
include ('includes/SQL_CommonFunctions.inc');

$title = _('Petty Cash Management Report');

if (isset($_POST['SelectedTabs'])){
	$SelectedTabs = strtoupper($_POST['SelectedTabs']);
} elseif (isset($_GET['SelectedTabs'])){
	$SelectedTabs = strtoupper($_GET['SelectedTabs']);
}

if ((! isset($_POST['FromDate']) AND ! isset($_POST['ToDate'])) OR isset($_POST['SelectDifferentDate'])){

	include  ('includes/header.inc');
	echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';

	if (!isset($_POST['FromDate'])){
		$_POST['FromDate']=Date($_SESSION['DefaultDateFormat'], mktime(0,0,0,Date('m'),1,Date('Y')));
	}

	if (!isset($_POST['ToDate'])){
		$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
	}

	/*Show a form to allow input of criteria for Tabs to show */
	echo '<table>';
	echo '<tr><td>' . _('Code Of Petty Cash Tab') . ":</td><td><select name='SelectedTabs'>";

	if ($_SESSION['AccessLevel'] >= 15){ // superuser can supervise the supervisors
		$SQL = "SELECT tabcode
			FROM pctabs
			ORDER BY tabcode";
	}else{
		$SQL = "SELECT tabcode
			FROM pctabs
			WHERE ( authorizer='" . $_SESSION['UserID'] . "' OR usercode ='" . $_SESSION['UserID'] . "' )
			ORDER BY tabcode";
	}
	$result = DB_query($SQL,$db);

	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['SelectTabs']) and $myrow['tabcode']==$_POST['SelectTabs']) {
			echo "<option selected VALUE='";
		} else {
			echo "<option VALUE='";
		}
		echo $myrow['tabcode'] . "'>" . $myrow['tabcode'];

	} //end while loop get type of tab

	DB_free_result($result);


	echo '</select></td></tr>';
	echo'<tr><td>' . _('From Date :') . '</td><td>';
	echo '<input tabindex="2" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="TEXT" name="FromDate" maxlength="10" size="11" VALUE="' . $_POST['FromDate'] . '">';
	echo '</td></tr>';
	echo '<tr><td>' . _('To Date:') .'</td><td>';
	echo '<input tabindex="3" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="TEXT" name="ToDate" maxlength="10" size="11" VALUE="' . $_POST['ToDate'] . '">';
	echo '</td></tr></table><br>';
	echo '<div class="centre"><input type=submit Name="ShowTB" Value="' . _('Show HTML') .'">';
	echo "<input type=submit Name='PrintPDF' Value='"._('PrintPDF')."'></div>";

} else if (isset($_POST['PrintPDF'])) {


	include('includes/PDFStarter.php');
	$PageNumber = 0;
	$FontSize = 10;
	$pdf->addinfo('Title', _('Petty Cash Report Of Tab') );
	$pdf->addinfo('Subject', _('Petty Cash Report Of Tab') );
	$line_height = 12;

	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
	$SQL_ToDate = FormatDateForSQL($_POST['ToDate']);

	$SQL = "SELECT * FROM pcashdetails
			WHERE tabcode='$SelectedTabs'
			AND date >='" . $SQL_FromDate . "' AND date <= '" . $SQL_ToDate . "'
			ORDER BY date, counterindex ASC";

	$TabDetail = DB_query($SQL,$db);

if (DB_error_no($db)!=0){
	include('includes/header.inc');
	prnMsg(_('An error occurred getting the orders details'),'',_('Database Error'));
	if ($debug==1){
		prnMsg( _('The SQL used to get the orders that failed was') . '<br>' . $SQL, '',_('Database Error'));
	}
	include ('includes/footer.inc');
	exit;
} elseif (DB_num_rows($TabDetail)==0){
  	include('includes/header.inc');
	prnMsg(_('There were no expenses found in the database within the period from') . ' ' . $_POST['FromDate'] . ' ' . _('to') . ' '. $_POST['ToDate'] . '. ' . _('Please try again selecting a different date range'),'warn');
	if ($debug==1) {
		prnMsg(_('The SQL that returned no rows was') . '<br>' . $SQL,'',_('Database Error'));
	}
	include('includes/footer.inc');
	exit;
}

	include('includes/PDFTabReportHeader.inc');

	$SqlTabs = "SELECT * FROM pctabs
			WHERE tabcode='$SelectedTabs'";

	$TabResult = DB_query($SqlTabs,	$db, _('No Petty Cash tabs were returned by the SQL because'), _('The SQL that failed was:'));

	$Tabs=DB_fetch_array($TabResult);

	$SqlBalance = "SELECT SUM(amount) FROM pcashdetails
			WHERE tabcode='$SelectedTabs'
			AND date<'".$SQL_FromDate."'";

	$TabBalance = DB_query($SqlBalance,
				$db);

	$Balance=DB_fetch_array($TabBalance);

	if( !isset($Balance['0'])){
		$Balance['0']=0;
	}

	$YPos -= (2 * $line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Tab Code :'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,20,$FontSize,_(': '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+110,$YPos,70,$FontSize,$SelectedTabs);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+290,$YPos,70,$FontSize,_('From '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,20,$FontSize,_(': '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+340,$YPos,70,$FontSize,$_POST['FromDate']);

	$YPos -= $line_height;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('User '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,20,$FontSize,_(': '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+110,$YPos,70,$FontSize,$Tabs['usercode']);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+290,$YPos,70,$FontSize,_('To '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+320,$YPos,20,$FontSize,_(': '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+340,$YPos,70,$FontSize,$_POST['ToDate']);

	$YPos -= $line_height;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Authorizer '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,20,$FontSize,_(': '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+110,$YPos,70,$FontSize,$Tabs['authorizer']);

	$YPos -= $line_height;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,_('Currency '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,20,$FontSize,_(': '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+110,$YPos,70,$FontSize,$Tabs['currency']);

	$YPos -= $line_height;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Balance before '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+55,$YPos,70,$FontSize,$_POST['FromDate']);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+100,$YPos,20,$FontSize,_(': '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+110,$YPos,70,$FontSize,number_format($Balance['0'],2));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+150,$YPos,70,$FontSize,$Tabs['currency']);

	$YPos -= (2 * $line_height);
	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);

	$YPos -= (2 * $line_height);
	$FontSize = 8;
	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,70,$FontSize,_('Date Of Expense'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+70,$YPos,100,$FontSize,_('Description'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+200,$YPos,100,$FontSize,_('Amount'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,_('Note'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,100,$FontSize,_('Receipt'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+420,$YPos,100,$FontSize,_('Date Authorizer'));
	$YPos -= (2 * $line_height);

	while ($myrow=DB_fetch_array($TabDetail)) {

	$sqldes="SELECT description
				FROM pcexpenses
				WHERE codeexpense='". $myrow[3] . "'";

	$ResultDes = DB_query($sqldes,$db);
	$Description=DB_fetch_array($ResultDes);

	if (!isset($Description[0])){
		$Description[0]='ASSIGNCASH';
	}

		// Print total for each account
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,70,$FontSize,ConvertSQLDate($myrow['date']));
		$LeftOvers = $pdf->addTextWrap($Left_Margin+70,$YPos,130,$FontSize,$Description[0]);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+180,$YPos,50,$FontSize,number_format($myrow['amount'],2),'right');
		$LeftOvers = $pdf->addTextWrap($Left_Margin+250,$YPos,100,$FontSize,$myrow['notes']);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+350,$YPos,70,$FontSize,$myrow['receipt']);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+430,$YPos,70,$FontSize,ConvertSQLDate($myrow['authorized']));
		$YPos -= $line_height;

	}  //end of while loop

	$sqlamount="SELECT sum(amount)
				FROM pcashdetails
				WHERE tabcode='$SelectedTabs'
				AND date<='".$SQL_ToDate."'";

	$ResultAmount = DB_query($sqlamount,$db);
	$Amount=DB_fetch_array($ResultAmount);

	if (!isset($Amount[0])) {
		$Amount[0]=0;
	}



	$YPos -= (2 * $line_height);
	$pdf->line($Left_Margin+250, $YPos+$line_height,$Left_Margin+500, $YPos+$line_height);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+70,$YPos,100,$FontSize,_('Balance at'));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+110,$YPos,70,$FontSize,$_POST['ToDate']);
	$LeftOvers = $pdf->addTextWrap($Left_Margin+160,$YPos,20,$FontSize,_(': '));
	$LeftOvers = $pdf->addTextWrap($Left_Margin+160,$YPos,70,$FontSize,number_format($Amount[0],2),'right');
	$LeftOvers = $pdf->addTextWrap($Left_Margin+240,$YPos,70,$FontSize,$Tabs['currency']);
	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Left_Margin, $YPos+$line_height);

	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

	if ($len<=20){
		$title = _('Print Report Tab Error');
		include('includes/header.inc');
		echo '<p>';
		prnMsg( _('There were no entries to print out for the selections specified') );
		echo '<br><a href="'. $rootpath.'/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
		include('includes/footer.inc');
		exit;
	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=TabReports.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Output('PcReportTabs.pdf','I');

	}
	exit;
} else {

	include('includes/header.inc');


	$SQL_FromDate = FormatDateForSQL($_POST['FromDate']);
	$SQL_ToDate = FormatDateForSQL($_POST['ToDate']);

	echo '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	echo '<input type=hidden name="FromDate" VALUE="' . $_POST['FromDate'] . '"><input type=hidden name="ToDate" VALUE="' . $_POST['ToDate'] . '">';

	$SqlTabs = "SELECT * FROM pctabs
			WHERE tabcode='$SelectedTabs'";

	$TabResult = DB_query($SqlTabs,
				$db,
				 _('No Petty Cash Tabs were returned by the SQL because'),
				 _('The SQL that failed was:'));

	$Tabs=DB_fetch_array($TabResult);

	echo "<br><table >";

	echo '<tr><td>' . _('Tab Code') . '</td>
				<td>:</td>
				<td width=200>' . ''.$SelectedTabs.'</td>'  .
				'<td>' . _('From') . '</td><td>:</td><td>' .
			''.$_POST['FromDate'].'</td></tr>';

	echo '<tr><td>' . _('User') . '</td>
				<td>:</td>
				<td>' . ''.$Tabs['usercode'].'</td>'.
				'<td>' . _('To') . '</td><td>:</td><td>' .
			''.$_POST['ToDate'].'</td></tr>';

	echo '<tr><td>' . _('Authorizer') . '</td><td>:</td><td>' . ''.$Tabs['authorizer'].'</td>
			</tr>';
	echo '<tr><td>' . _('Currency') . '</td><td>:</td><td>' . ''.$Tabs['currency'].'</td>
			</tr>';

	$SqlBalance = "SELECT SUM(amount)
			FROM pcashdetails
			WHERE tabcode='$SelectedTabs'
			AND date<'".$SQL_FromDate."'";

	$TabBalance = DB_query($SqlBalance, $db);

	$Balance=DB_fetch_array($TabBalance);

	if( !isset($Balance['0'])){
		$Balance['0']=0;
	}

	echo '<tr><td>' . _('Balance before ') . ''.$_POST['FromDate'].'</td><td>:</td><td>' . ''.$Balance['0'].' '.$Tabs['currency'].'</td></tr>';

	$SqlBalanceNotAut = "SELECT SUM(amount)
			FROM pcashdetails
			WHERE tabcode= '$SelectedTabs'
			AND authorized = '0000-00-00'
			AND date<'".$SQL_FromDate."'";

	$TabBalanceNotAut = DB_query($SqlBalanceNotAut, $db);

	$BalanceNotAut=DB_fetch_array($TabBalanceNotAut);

	if( !isset($BalanceNotAut['0'])){
		$BalanceNotAut['0']=0;
	}

	echo '<tr><td>' . _('Total not authorized before ') . ''.$_POST['FromDate'].'</td><td>:</td><td>' . ''.$BalanceNotAut['0'].' '.$Tabs['currency'].'</td></tr>';


	echo '</table>';

	/*show a table of the accounts info returned by the SQL
	Account Code ,   Account Name , Month Actual, Month Budget, Period Actual, Period Budget */


	$SQL = "SELECT * FROM pcashdetails
			WHERE tabcode='$SelectedTabs'
				AND date >='" . $SQL_FromDate . "'
				AND date <= '" . $SQL_ToDate . "'
			ORDER BY date, counterindex Asc";

	$TabDetail = DB_query($SQL,
				$db,
				 _('No Petty Cash movements for this tab were returned by the SQL because'),
				 _('The SQL that failed was:'));

	echo '<br><table BORDER=1>';
	echo "<tr>
		<th>" . _('Date Of Expense') . "</th>
		<th>" . _('Expense Description') . "</th>
		<th>" . _('Amount') . "</th>
		<th>" . _('Notes') . "</th>
		<th>" . _('Receipt') . "</th>
		<th>" . _('Date Authorized') . "</th>
	</tr>";

	$j = 1;
	$k=0; //row colour counter

	while ($myrow = DB_fetch_row($TabDetail)) {
	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}


	$sqldes="SELECT description
				FROM pcexpenses
				WHERE codeexpense='". $myrow['3'] . "'";

	$ResultDes = DB_query($sqldes,$db);
	$Description=DB_fetch_array($ResultDes);

	if (!isset($Description['0'])){
	$Description['0']='ASSIGNCASH';
	}
	if ($myrow['5'] != '0000-00-00'){
		printf("<td>%s</td>
			<td>%s</td>
			<td class=number>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			</tr>",
			ConvertSQLDate($myrow['2']),
			$Description['0'],
			number_format($myrow['4'],2),
			$myrow['7'],
			$myrow['8'],
			ConvertSQLDate($myrow['5']));
	}else{
		printf("<td>%s</td>
			<td>%s</td>
			<td class=number>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			</tr>",
			ConvertSQLDate($myrow['2']),
			$Description['0'],
			number_format($myrow['4'],2),
			$myrow['7'],
			$myrow['8'],
			'          ');
	}

	}

	$sqlamount="SELECT sum(amount)
				FROM pcashdetails
				WHERE tabcode='$SelectedTabs'
				AND date<='".$SQL_ToDate."'";

	$ResultAmount = DB_query($sqlamount,$db);
	$Amount=DB_fetch_array($ResultAmount);

	if (!isset($Amount[0])) {
		$Amount[0]=0;
	}

	echo '<tr><td colspan=2 style=text-align:right >' . _('Balance At') . ' '.$_POST['ToDate'].':</td>
				<td>'.number_format($Amount[0],2).' </td><td>'.$Tabs['currency'].'</td></tr>';

	echo '</table>';
	echo '<br><div class="centre"><input type=submit Name="SelectDifferentDate" Value="' . _('Select A Different Date') . '"></div>';
}
echo '</form>';
include('includes/footer.inc');

?>