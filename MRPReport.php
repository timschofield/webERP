<?php

// Shows supply and demand for a part as determined by MRP

require(__DIR__ . '/includes/session.php');

if (isset($_POST['Select'])) {
	$_POST['Part']=$_POST['Select'];
	$_POST['PrintPDF']='Yes';
}

if (isset($_POST['PrintPDF']) AND $_POST['Part']!='') {

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',__('MRP Report'));
	$pdf->addInfo('Subject',__('MRP Report'));
	$FontSize=9;
	$PageNumber=1;
	$LineHeight=10   ;

	// Load mrprequirements into $Requirements array
	// Use weekindex to assign supplies, requirements, and planned orders to weekly buckets
	$SQL = "SELECT mrprequirements.*,
				TRUNCATE(((TO_DAYS(daterequired) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
				TO_DAYS(daterequired) - TO_DAYS(CURRENT_DATE) AS datediff
			FROM mrprequirements
			WHERE part = '" . $_POST['Part'] ."'
			ORDER BY daterequired,whererequired";

	$ErrMsg = __('The MRP calculation must be run before this report will have any output. MRP requires set up of many parameters, including, EOQ, lead times, minimums, bills of materials, demand types, master schedule etc');
	$Result = DB_query($SQL, $ErrMsg);
	if (DB_error_no() !=0) {
		$Errors = 1;
	}

	if (DB_num_rows($Result) == 0) {
		$Errors = 1;
		$Title = __('Print MRP Report Warning');
		include('includes/header.php');
		echo '<br /><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	$Requirements = array();
	$WeeklyReq = array();
	for ($i=0;$i<28;$i++) {
		$WeeklyReq[$i]=0;
	}
	$PastDueReq = 0;
	$FutureReq = 0;
	$GrossReq = 0;

	while ($MyRow=DB_fetch_array($Result)) {
			array_push($Requirements,$MyRow);
			$GrossReq += $MyRow['quantity'];
			if ($MyRow['datediff'] < 0) {
				$PastDueReq += $MyRow['quantity'];
			} elseif ($MyRow['weekindex'] > 27) {
				$FutureReq += $MyRow['quantity'];
			} else {
			$WeeklyReq[$MyRow['weekindex']] += $MyRow['quantity'];
			}
	}  //end of while loop

	// Load mrpsupplies into $Supplies array
	$SQL = "SELECT mrpsupplies.*,
				   TRUNCATE(((TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
				   TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE) AS datediff
			 FROM mrpsupplies
			 WHERE part = '" . $_POST['Part'] . "'
			 ORDER BY mrpdate";
	$Result = DB_query($SQL);
	if (DB_error_no() !=0) {
		$Errors = 1;
	}
	$Supplies = array();
	$WeeklySup = array();
	for ($i=0;$i<28;$i++) {
		$WeeklySup[$i]=0;
	}
	$PastDueSup = 0;
	$FutureSup = 0;
	$QOH = 0; // Get quantity on Hand to display
	$OpenOrd = 0;
	while ($MyRow=DB_fetch_array($Result)) {
		if ($MyRow['ordertype'] == 'QOH') {
			$QOH += $MyRow['supplyquantity'];
		} else {
			$OpenOrd += $MyRow['supplyquantity'];
			if ($MyRow['datediff'] < 0) {
				$PastDueSup += $MyRow['supplyquantity'];
			} elseif ($MyRow['weekindex'] > 27) {
				$FutureSup += $MyRow['supplyquantity'];
			} else {
				$WeeklySup[$MyRow['weekindex']] += $MyRow['supplyquantity'];
			}
		}
		array_push($Supplies,$MyRow);
	}  //end of while loop

	$SQL = "SELECT mrpplannedorders.*,
				   TRUNCATE(((TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
				   TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE) AS datediff
				FROM mrpplannedorders WHERE part = '" . $_POST['Part'] . "' ORDER BY mrpdate";
	$Result = DB_query($SQL,'','',false);
	if (DB_error_no() !=0) {
		$Errors = 1;
	}

	// Fields for Order Due weekly buckets based on planned orders
	$WeeklyPlan = array();
	for ($i=0;$i<28;$i++) {
		$WeeklyPlan[$i]=0;
	}
	$PastDuePlan = 0;
	$FuturePlan = 0;
	while ($MyRow=DB_fetch_array($Result)) {
			array_push($Supplies,$MyRow);
			if ($MyRow['datediff'] < 0) {
				$PastDuePlan += $MyRow['supplyquantity'];
			} elseif ($MyRow['weekindex'] > 27) {
				$FuturePlan += $MyRow['supplyquantity'];
			} else {
			$WeeklyPlan[$MyRow['weekindex']] += $MyRow['supplyquantity'];
			}
	}  //end of while loop
	// The following sorts the $Supplies array by mrpdate. Have to sort because are loading
	// mrpsupplies and mrpplannedorders into same array
	foreach ($Supplies as $key => $Row) {
			 $MRPDate[$key] = $Row['mrpdate'];
	 }

	if (isset($Errors)) {
		$Title = __('MRP Report') . ' - ' . __('Problem Report');
		include('includes/header.php');
		prnMsg( __('The MRP Report could not be retrieved'), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' . __('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	if (count($Supplies)) {
		array_multisort($MRPDate, SORT_ASC, $Supplies);
	}
	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
					   $Right_Margin);

	$Fill = false;
	$pdf->SetFillColor(224,235,255);  // Defines color to make alternating lines highlighted

	// Get and display part information
	$SQL = "SELECT levels.*,
				   stockmaster.description,
				   stockmaster.lastcost,
				   stockmaster.decimalplaces,
				   stockmaster.mbflag
				   FROM levels
			LEFT JOIN stockmaster
			ON levels.part = stockmaster.stockid
			WHERE part = '" . $_POST['Part'] . "'";
	$Result = DB_query($SQL,'','',false);
	$MyRow=DB_fetch_array($Result);
	$pdf->addTextWrap($Left_Margin,$YPos,35,$FontSize,__('Part:'),'');
	$pdf->addTextWrap(70,$YPos,100,$FontSize,$MyRow['part'],'');
	$pdf->addTextWrap(245,$YPos,40,$FontSize,__('EOQ').':','right');
	$pdf->addTextWrap(285,$YPos,45,$FontSize,locale_number_format($MyRow['eoq'],$MyRow['decimalplaces']),'right');
	$pdf->addTextWrap(360,$YPos,50,$FontSize,__('On Hand:'),'right');
	$pdf->addTextWrap(410,$YPos,50,$FontSize,locale_number_format($QOH,$MyRow['decimalplaces']),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,30,$FontSize,__('Desc:'),'');
	$pdf->addTextWrap(70,$YPos,240,$FontSize,$MyRow['description'],'');
	$pdf->addTextWrap(245,$YPos,40,$FontSize,__('Pan Size:'),'right');
	$pdf->addTextWrap(285,$YPos,45,$FontSize,locale_number_format($MyRow['pansize'],$MyRow['decimalplaces']),'right');
	$pdf->addTextWrap(360,$YPos,50,$FontSize,__('On Order:'),'right');
	$pdf->addTextWrap(410,$YPos,50,$FontSize,locale_number_format($OpenOrd,$MyRow['decimalplaces']),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,30,$FontSize,'M/B:','');
	$pdf->addTextWrap(70,$YPos,150,$FontSize,$MyRow['mbflag'],'');
	$pdf->addTextWrap(225,$YPos,60,$FontSize,'Shrinkage:','right');
	$pdf->addTextWrap(300,$YPos,30,$FontSize,locale_number_format($MyRow['shrinkfactor'],$MyRow['decimalplaces']),'right');
	$pdf->addTextWrap(360,$YPos,50,$FontSize,__('Gross Req:'),'right');
	$pdf->addTextWrap(410,$YPos,50,$FontSize,locale_number_format($GrossReq,$MyRow['decimalplaces']),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap(225,$YPos,60,$FontSize,'Lead Time:','right');
	$pdf->addTextWrap(300,$YPos,30,$FontSize,$MyRow['leadtime'],'right');
	$pdf->addTextWrap(360,$YPos,50,$FontSize,__('Last Cost:'),'right');
	$pdf->addTextWrap(410,$YPos,50,$FontSize,locale_number_format($MyRow['lastcost'],2),'right');
	$YPos -= (2*$LineHeight);

	// Calculate fields for prjected available weekly buckets
	$PlannedAccum = array();
	$PastDueAvail = ($QOH + $PastDueSup + $PastDuePlan) - $PastDueReq;
	$WeeklyAvail = array();
	$WeeklyAvail[0] = ($PastDueAvail + $WeeklySup[0] + $WeeklyPlan[0]) - $WeeklyReq[0];
	$PlannedAccum[0] = $PastDuePlan + $WeeklyPlan[0];
	for ($i = 1; $i < 28; $i++) {
		 $WeeklyAvail[$i] = ($WeeklyAvail[$i - 1] + $WeeklySup[$i] + $WeeklyPlan[$i]) - $WeeklyReq[$i];
		 $PlannedAccum[$i] = $PlannedAccum[$i-1] + $WeeklyPlan[$i];
	}
	$FutureAvail = ($WeeklyAvail[27] + $FutureSup + $FuturePlan) - $FutureReq;
	$FuturePlannedaccum = $PlannedAccum[27] + $FuturePlan;

	// Headers for Weekly Buckets
	$FontSize =7;
	$Dateformat = $_SESSION['DefaultDateFormat'];
	$Today = date("$Dateformat");
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,__('Past Due'),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,$Today,'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,DateAdd($Today,'w',1),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,DateAdd($Today,'w',2),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,DateAdd($Today,'w',3),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,DateAdd($Today,'w',4),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,DateAdd($Today,'w',5),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,DateAdd($Today,'w',6),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,DateAdd($Today,'w',7),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,DateAdd($Today,'w',8),'right');
	$YPos -=$LineHeight;

	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Gross Reqts'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($PastDueReq,0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklyReq[0],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklyReq[1],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklyReq[2],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklyReq[3],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklyReq[4],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklyReq[5],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklyReq[6],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklyReq[7],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($WeeklyReq[8],0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Open Order'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($PastDueSup,0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklySup[0],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklySup[1],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklySup[2],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklySup[3],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklySup[4],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklySup[5],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklySup[6],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklySup[7],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($WeeklySup[8],0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Planned'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($PastDuePlan,0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[0],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[1],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[2],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[3],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[4],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[5],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[6],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[7],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[8],0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Proj Avail'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($PastDueAvail,0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[0],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[1],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[2],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[3],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[4],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[5],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[6],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[7],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[8],0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Planned Acc'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($PastDuePlan,0),'right');
	$InitialPoint = 130;
	for($c=0;$c<9;$c++){
		$pdf->addTextWrap($InitialPoint,$YPos,45,$FontSize,locale_number_format($PlannedAccum[$c],0),'right');
		$InitialPoint += 45;
	}
	$YPos -= 2 * $LineHeight;

	// Second Group of Weeks
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,DateAdd($Today,'w',9),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,DateAdd($Today,'w',10),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,DateAdd($Today,'w',11),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,DateAdd($Today,'w',12),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,DateAdd($Today,'w',13),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,DateAdd($Today,'w',14),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,DateAdd($Today,'w',15),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,DateAdd($Today,'w',16),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,DateAdd($Today,'w',17),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,DateAdd($Today,'w',18),'right');
	$YPos -=$LineHeight;

	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Gross Reqts'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($WeeklyReq[9],0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklyReq[10],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklyReq[11],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklyReq[12],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklyReq[13],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklyReq[14],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklyReq[15],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklyReq[16],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklyReq[17],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($WeeklyReq[18],0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Open Order'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($WeeklySup[9],0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklySup[10],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklySup[11],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklySup[12],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklySup[13],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklySup[14],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklySup[15],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklySup[16],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklySup[17],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($WeeklySup[18],0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Planned'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[9],0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[10],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[11],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[12],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[13],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[14],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[15],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[16],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[17],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[18],0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Proj Avail'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[9],0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[10],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[11],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[12],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[13],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[14],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[15],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[16],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[17],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[18],0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Planned Acc'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($PlannedAccum[9],0),'right');
	$InitialPoint = 130;
	for($c=10;$c<19;$c++){
		$pdf->addTextWrap($InitialPoint,$YPos,45,$FontSize,locale_number_format($PlannedAccum[$c],0),'right');
		$InitialPoint += 45;
	}
	$YPos -= 2 * $LineHeight;

	// Third Group of Weeks
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,DateAdd($Today,'w',19),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,DateAdd($Today,'w',20),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,DateAdd($Today,'w',21),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,DateAdd($Today,'w',22),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,DateAdd($Today,'w',23),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,DateAdd($Today,'w',24),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,DateAdd($Today,'w',25),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,DateAdd($Today,'w',26),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,DateAdd($Today,'w',27),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,"Future",'right');
	$YPos -=$LineHeight;

	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Gross Reqts'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($WeeklyReq[19],0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklyReq[20],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklyReq[21],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklyReq[22],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklyReq[23],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklyReq[24],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklyReq[25],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklyReq[26],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklyReq[27],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($FutureReq,0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Open Order'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($WeeklySup[19],0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklySup[20],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklySup[21],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklySup[22],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklySup[23],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklySup[24],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklySup[25],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklySup[26],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklySup[27],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($FutureSup,0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Planned'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[19],0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[20],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[21],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[22],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[23],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[24],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[25],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[26],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklyPlan[27],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($FuturePlan,0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Proj Avail'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[19],0),'right');
	$pdf->addTextWrap(130,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[20],0),'right');
	$pdf->addTextWrap(175,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[21],0),'right');
	$pdf->addTextWrap(220,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[22],0),'right');
	$pdf->addTextWrap(265,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[23],0),'right');
	$pdf->addTextWrap(310,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[24],0),'right');
	$pdf->addTextWrap(355,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[25],0),'right');
	$pdf->addTextWrap(400,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[26],0),'right');
	$pdf->addTextWrap(445,$YPos,45,$FontSize,locale_number_format($WeeklyAvail[27],0),'right');
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($FutureAvail,0),'right');
	$YPos -=$LineHeight;
	$pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,__('Planned Acc'));
	$pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,locale_number_format($PlannedAccum[19],0),'right');
	$InitialPoint = 130;
	for($c=20;$c<28;$c++){
		$pdf->addTextWrap($InitialPoint,$YPos,45,$FontSize,locale_number_format($PlannedAccum[$c],0),'right');
		$InitialPoint += 45;
	}
	$pdf->addTextWrap(490,$YPos,45,$FontSize,locale_number_format($FuturePlannedaccum,0),'right');

	// Headers for Demand/Supply Sections
	$YPos -= (2*$LineHeight);
	$pdf->addTextWrap($Left_Margin,$YPos,265,$FontSize,'D E M A N D','center');
	$pdf->addTextWrap(290,$YPos,260,$FontSize,'S U P P L Y','center');
	$YPos -=$LineHeight;

	$pdf->addTextWrap($Left_Margin,$YPos,55,$FontSize,__('Dem Type'));
	$pdf->addTextWrap(80,$YPos,90,$FontSize,__('Where Required'));
	$pdf->addTextWrap(170,$YPos,30,$FontSize,__('Order'),'');
	$pdf->addTextWrap(200,$YPos,40,$FontSize,__('Quantity'),'right');
	$pdf->addTextWrap(240,$YPos,50,$FontSize,__('Due Date'),'right');

	$pdf->addTextWrap(310,$YPos,45,$FontSize,__('Order No.'),'');
	$pdf->addTextWrap(355,$YPos,35,$FontSize,__('Sup Type'),'');
	$pdf->addTextWrap(390,$YPos,25,$FontSize,__('For'),'');
	$pdf->addTextWrap(415,$YPos,40,$FontSize,__('Quantity'),'right');
	$pdf->addTextWrap(455,$YPos,50,$FontSize,__('Due Date'),'right');
	$pdf->addTextWrap(505,$YPos,50,$FontSize,__('MRP Date'),'right');

	// Details for Demand/Supply Sections
	$i = 0;
	while ((isset($Supplies[$i]) AND mb_strlen($Supplies[$i]['part']) > 1)
			OR (isset($Requirements[$i]) AND mb_strlen($Requirements[$i]['part']) > 1)){

		$YPos -=$LineHeight;
		$FontSize=7;

		/* Use to alternate between lines with transparent and painted background
		if ($_POST['Fill'] == 'yes'){
			$Fill=!$Fill;
		}
		*/
		// Parameters for addTextWrap are defined in /includes/class.cpdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text To Display  6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set for transparent
		if (isset($Requirements[$i]['part']) and mb_strlen($Requirements[$i]['part']) > 1) {
			$FormatedReqDueDate = ConvertSQLDate($Requirements[$i]['daterequired']);
			$pdf->addTextWrap($Left_Margin,$YPos,55,$FontSize,$Requirements[$i]['mrpdemandtype'],'');
			$pdf->addTextWrap(80,$YPos,90,$FontSize,$Requirements[$i]['whererequired'],'');
			$pdf->addTextWrap(170,$YPos,30,$FontSize,$Requirements[$i]['orderno'],'');
			$pdf->addTextWrap(200,$YPos,40,$FontSize,locale_number_format($Requirements[$i]['quantity'],
																$MyRow['decimalplaces']),'right');
			$pdf->addTextWrap(240,$YPos,50,$FontSize,$FormatedReqDueDate,'right');
		}
		if (mb_strlen($Supplies[$i]['part']) > 1) {
			$SupType = $Supplies[$i]['ordertype'];
			// If ordertype is not QOH,PO,or WO, it is an MRP generated planned order and the
			// ordertype is actually the demandtype that caused the planned order
			if ($SupType == 'QOH' || $SupType == 'PO' || $SupType == 'WO') {
				$DisplayType = $SupType;
				$ForType = " ";
			} else {
				$DisplayType = 'Planned';
				$ForType = $SupType;
			}
			$FormatedSupDueDate = ConvertSQLDate($Supplies[$i]['duedate']);
			$FormatedSupMRPDate = ConvertSQLDate($Supplies[$i]['mrpdate']);
			// Order no is meaningless for QOH and REORD ordertypes
			if ($SupType == 'QOH' OR $SupType == 'REORD') {
				$pdf->addTextWrap(310,$YPos,45,$FontSize,' ','');
			} else {
				$pdf->addTextWrap(310,$YPos,45,$FontSize,$Supplies[$i]['orderno'],'');
			}
			$pdf->addTextWrap(355,$YPos,35,$FontSize,$DisplayType,'');
			$pdf->addTextWrap(390,$YPos,25,$FontSize,$ForType,'');
			$pdf->addTextWrap(415,$YPos,40,$FontSize,locale_number_format($Supplies[$i]['supplyquantity'],$MyRow['decimalplaces']),'right');
			$pdf->addTextWrap(455,$YPos,50,$FontSize,$FormatedSupDueDate,'right');
			$pdf->addTextWrap(505,$YPos,50,$FontSize,$FormatedSupMRPDate,'right');
		}

		if ($YPos < $Bottom_Margin + $LineHeight){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
					   $Right_Margin);
		}
		$i++;
	} /*end while loop */

	$FontSize =8;
	$YPos -= (2*$LineHeight);

	if ($YPos < $Bottom_Margin + $LineHeight){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
					   $Right_Margin);
	}

	$pdf->OutputD($_SESSION['DatabaseName'] . '_MRPReport_' . date('Y-m-d').'.pdf');//UldisN
	$pdf->__destruct();

} else { /*The option to print PDF was not hit so display form */

	$Title=__('MRP Report');
	$ViewTopic = 'MRP';
	$BookMark = '';
	include('includes/header.php');

	if (isset($_POST['PrintPDF'])) {
		prnMsg(__('This report shows the MRP calculation for a specific item - a part code must be selected'),'warn');
	}
	// Always show the search facilities
	$SQL = "SELECT categoryid,
					categorydescription
			FROM stockcategory
			ORDER BY categorydescription";
	$Result1 = DB_query($SQL);
	if (DB_num_rows($Result1) == 0) {
		echo '<p class="bad">' . __('Problem Report') . ':<br />' . __('There are no stock categories currently defined please use the link below to set them up');
		echo '<a href="' . $RootPath . '/StockCategories.php">' . __('Define Stock Categories') . '</a>';
		exit();
	}

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . __('Search') . '" alt="" />' . ' ' . __('Search for Inventory Items') . '</p>
		<fieldset>
			<legend>', __('Search Criteria'), '</legend>
			<field>
				<label for="StockCat">' . __('In Stock Category') . ':</label>
				<select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = '';
	}
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="selected" value="All">' . __('All') . '</option>';
	} else {
		echo '<option value="All">' . __('All') . '</option>';
	}
	while ($MyRow1 = DB_fetch_array($Result1)) {
		if ($MyRow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow1['categoryid'] . '">' . $MyRow1['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';
	echo '<field>
			<label for="Keywords">' . __('Enter partial') . '<b> ' . __('Description') . '</b>:</label>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="text" autofocus="autofocus" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
	} else {
		echo '<input type="text" autofocus="autofocus" name="Keywords" size="20" maxlength="25" />';
	}
	echo '</field>';

	echo '<field>
			<label for="StockCode">' . '<b>' . __('OR') . ' </b>' . __('Enter partial') . ' <b>' . __('Stock Code') . '</b>:</label>';
	if (isset($_POST['StockCode'])) {
		echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" name="StockCode" size="15" maxlength="18" />';
	}
	echo '</field>
		</fieldset>
		<div class="centre">
			<input type="submit" name="Search" value="' . __('Search Now') . '" />
		</div>
		</form>';
	if (!isset($_POST['Search'])) {
		include('includes/footer.php');
	}

} /*end of else not PrintPDF */
// query for list of record(s)
if(isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	$_POST['Search']='Search';
}
if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg( __('Stock description keywords have been used in preference to the Stock code extract entered'), 'info' );
	}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.description " . LIKE . " '".$SearchString."'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND description " . LIKE . " '".$SearchString."'
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		}
	} elseif (isset($_POST['StockCode'])) {
		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					sum(locstock.quantity) as qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		}
	} elseif (!isset($_POST['StockCode']) AND !isset($_POST['Keywords'])) {
		if ($_POST['StockCat'] == 'All') {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.mbflag,
					SUM(locstock.quantity) AS qoh,
					stockmaster.units,
					stockmaster.decimalplaces
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid=locstock.stockid
				AND categoryid='" . $_POST['StockCat'] . "'
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					stockmaster.decimalplaces
				ORDER BY stockmaster.stockid";
		}
	}
	$ErrMsg = __('No stock items were returned by the SQL because');
	$SearchResult = DB_query($SQL, $ErrMsg);
	if (DB_num_rows($SearchResult) == 0) {
		prnMsg(__('No stock items were returned by this search please re-enter alternative criteria to try again'), 'info');
	}
	unset($_POST['Search']);
}
/* end query for list of records */
/* display list if there is more than one record */
if (isset($SearchResult) AND !isset($_POST['Select'])) {
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$ListCount = DB_num_rows($SearchResult);
	if ($ListCount > 0) {
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre">
					<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . __('of') . ' ' . $ListPageMax . ' ' . __('pages') . '. ' . __('Go to Page') . ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
				} else {
					echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go" value="' . __('Go') . '" />
				<input type="submit" name="Previous" value="' . __('Previous') . '" />
				<input type="submit" name="Next" value="' . __('Next') . '" />
				<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'" />
				<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'" />
				<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'" />
				</div>';
		}
		echo '<table class="selection">';
		$Tableheader = '<tr>
							<th>' . __('Code') . '</th>
							<th>' . __('Description') . '</th>
							<th>' . __('Total Qty On Hand') . '</th>
							<th>' . __('Units') . '</th>
							<th>' . __('Stock Status') . '</th>
						</tr>';
		echo $Tableheader;
		$j = 1;
		$RowIndex = 0;
		if (DB_num_rows($SearchResult) <> 0) {
			DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		while (($MyRow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($MyRow['mbflag'] == 'D') {
				$QOH = 'N/A';
			} else {
				$QOH = locale_number_format($MyRow['qoh'], $MyRow['decimalplaces']);
			}
			echo '<tr class="striped_row">
				<td><input type="submit" name="Select" value="'.$MyRow['stockid']. '" /></td>
				<td>' . $MyRow['description'] . '</td>
				<td class="number">' . $QOH . '</td>
				<td>' . $MyRow['units'] . '</td>
				<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $MyRow['stockid'] .'">' . __('View') . '</a></td>
				</tr>';
			$j++;
			if ($j == 20 AND ($RowIndex + 1 != $_SESSION['DisplayRecordsMax'])) {
				$j = 1;
				echo $Tableheader;
			}
			$RowIndex = $RowIndex + 1;
			//end of page full new headings if
		}
		//end of while loop
		echo '</table>
            </div>
			</form>
			<br />';
	}

	include('includes/footer.php');
}
/* end display list if there is more than one record */

function PrintHeader($pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin) {

	$LineHeight=12;
	/*PDF page header for MRP Report */
	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$LineHeight;

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,__('MRP Report'));
	$pdf->addTextWrap($Page_Width-$Right_Margin-110,$YPos,160,$FontSize,__('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . __('Page') . ' ' . $PageNumber,'left');

	$YPos -=(2*$LineHeight);

	/*set up the headings */
	$Xpos = $Left_Margin+1;

	$FontSize=8;
	$YPos =$YPos - (2*$LineHeight);
	$PageNumber++;

} // End of PrintHeader function
