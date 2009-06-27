<?php
/* $Revision: 1.2 $ */
// MRPReport.php - Shows supply and demand for a part as determined by MRP
$PageSecurity = 2;
include('includes/session.inc');

If (isset($_POST['PrintPDF']) AND $_POST['Part']!='') {

	include('includes/PDFStarter.php');

	$FontSize=9;
	$pdf->addinfo('Title',_('MRP Report'));
	$pdf->addinfo('Subject',_('MRP Report'));

	$PageNumber=1;
	$line_height=10   ;

    // Load mrprequirements into $requirements array
    // Use weekindex to assign supplies, requirements, and planned orders to weekly buckets
    $sql = "SELECT mrprequirements.*,
                   TRUNCATE(((TO_DAYS(daterequired) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
                   TO_DAYS(daterequired) - TO_DAYS(CURRENT_DATE) AS datediff
            FROM mrprequirements 
              WHERE part = '" . $_POST['Part'] . 
             "' ORDER BY daterequired,whererequired";
    
	$result = DB_query($sql,$db,'','',False,False);
	if (DB_error_no($db) !=0) {
	    $errors = 1;
	    $holddb = $db;
	    $title = _('Print MRP Report Error');
		include('includes/header.inc');
		prnMsg(_('The MRP calculation must be run before this report will have any output. MRP reguires set up of many parameters, including, EOQ, lead times, minimums, bills of materials, demand types, master schedule etc'),'error');        
		echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
	}
	$requirements = array();
	$weeklyreq = array();
	$pastduereq = 0;
	$futurereq = 0;
	$grossreq = 0;
	
	while ($myrow=DB_fetch_array($result)) {
			array_push($requirements,$myrow);
			$grossreq += $myrow['quantity'];
			if ($myrow['datediff'] < 0) {
			    $pastduereq += $myrow['quantity'];
			} elseif ($myrow['weekindex'] > 27) {
			    $futurereq += $myrow['quantity'];
			} else {
			$weeklyreq[$myrow['weekindex']] += $myrow['quantity'];
			}
	}  //end of while loop

    // Load mrpsupplies into $supplies array
    $sql = "SELECT mrpsupplies.*, 
                   TRUNCATE(((TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
                   TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE) AS datediff
             FROM mrpsupplies WHERE part = '" . $_POST['Part'] . "' ORDER BY mrpdate";
	$result = DB_query($sql,$db,'','',false,true);
	if (DB_error_no($db) !=0) {
	    $errors = 1;
	    $holddb = $db;
	}
	$supplies = array();
	$weeklysup = array();
	$pastduesup = 0;
	$futuresup = 0;
	$qoh = 0; // Get quantity on Hand to display
	$openord = 0;
	while ($myrow=DB_fetch_array($result)) {
	       if ($myrow['ordertype'] == 'QOH') {
	           $qoh += $myrow['supplyquantity'];
	       } else {
	           $openord += $myrow['supplyquantity'];
	           if ($myrow['datediff'] < 0) {
			       $pastduesup += $myrow['supplyquantity'];
			   } elseif ($myrow['weekindex'] > 27) {
			       $futuresup += $myrow['supplyquantity'];
			   } else {
			       $weeklysup[$myrow['weekindex']] += $myrow['supplyquantity'];
			   }
	       }
		   array_push($supplies,$myrow);
	}  //end of while loop

    $sql = "SELECT mrpplannedorders.*,
                   TRUNCATE(((TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE)) / 7),0) AS weekindex,
                   TO_DAYS(duedate) - TO_DAYS(CURRENT_DATE) AS datediff
                FROM mrpplannedorders WHERE part = '" . $_POST['Part'] . "' ORDER BY mrpdate";
	$result = DB_query($sql,$db,'','',false,true);
	if (DB_error_no($db) !=0) {
	    $errors = 1;
	    $holddb = $db;
	}
	
	// Fields for Order Due weekly buckets based on planned orders
	$weeklyplan = array();
	$pastdueplan = 0;
	$futureplan = 0;
	while ($myrow=DB_fetch_array($result)) {
			array_push($supplies,$myrow);
			if ($myrow['datediff'] < 0) {
			    $pastdueplan += $myrow['supplyquantity'];
			} elseif ($myrow['weekindex'] > 27) {
			    $futureplan += $myrow['supplyquantity'];
			} else {
			$weeklyplan[$myrow['weekindex']] += $myrow['supplyquantity'];
			}
	}  //end of while loop
    // The following sorts the $supplies array by mrpdate. Have to sort because are loading
    // mrpsupplies and mrpplannedorders into same array
    foreach ($supplies as $key => $row) {
             $mrpdate[$key] = $row['mrpdate'];
     }
     
	if ($errors !=0) {
	  $title = _('MRP Report') . ' - ' . _('Problem Report');
	  include('includes/header.inc');
	   prnMsg( _('The MRP Report could not be retrieved by the SQL because') . ' '  . DB_error_msg($holddb),'error');
	   echo "<br><a href='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the menu') . '</a>';
	   if ($debug==1){
	      echo "<br>$sql";
	   }
	   include('includes/footer.inc');
	   exit;
	}
     
    if (count($supplies)) {
        array_multisort($mrpdate, SORT_ASC, $supplies);
    }
	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin);

    $fill = false;
    $pdf->SetFillColor(224,235,255);  // Defines color to make alternating lines highlighted
    
    // Get and display part information
    $sql = "SELECT levels.*,
                   stockmaster.description,
                   stockmaster.lastcost,
                   stockmaster.decimalplaces,
                   stockmaster.mbflag
                   FROM levels 
                    LEFT JOIN stockmaster
                      ON levels.part = stockmaster.stockid
                        WHERE part = '" . $_POST['Part'] . "'";
	$result = DB_query($sql,$db,'','',false,true);
	$myrow=DB_fetch_array($result);
	$pdf->addTextWrap($Left_Margin,$YPos,35,$FontSize,_('Part:'),'');				
	$pdf->addTextWrap(70,$YPos,100,$FontSize,$myrow['part'],'');
	$pdf->addTextWrap(245,$YPos,40,$FontSize,_('EOQ:'),'right');
	$pdf->addTextWrap(285,$YPos,45,$FontSize,number_format($myrow['eoq'],$myrow['decimalplaces']),'right');
	$pdf->addTextWrap(360,$YPos,50,$FontSize,_('On Hand:'),'right');
	$pdf->addTextWrap(410,$YPos,50,$FontSize,number_format($qoh,$myrow['decimalplaces']),'right');
	$YPos -=$line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,30,$FontSize,_('Desc:'),'');				
	$pdf->addTextWrap(70,$YPos,150,$FontSize,$myrow['description'],'');
	$pdf->addTextWrap(245,$YPos,40,$FontSize,_('Pan Size:'),'right');
	$pdf->addTextWrap(285,$YPos,45,$FontSize,number_format($myrow['pansize'],$myrow['decimalplaces']),'right');
	$pdf->addTextWrap(360,$YPos,50,$FontSize,_('On Order:'),'right');
	$pdf->addTextWrap(410,$YPos,50,$FontSize,number_format($openord,$myrow['decimalplaces']),'right');
	$YPos -=$line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,30,$FontSize,'M/B:','');				
	$pdf->addTextWrap(70,$YPos,150,$FontSize,$myrow['mbflag'],'');
	$pdf->addTextWrap(225,$YPos,60,$FontSize,'Shrinkage:','right');				
	$pdf->addTextWrap(300,$YPos,30,$FontSize,number_format($myrow['shrinkfactor'],$myrow['decimalplaces']),'right');
	$pdf->addTextWrap(360,$YPos,50,$FontSize,_('Gross Req:'),'right');
	$pdf->addTextWrap(410,$YPos,50,$FontSize,number_format($grossreq,$myrow['decimalplaces']),'right');
	$YPos -=$line_height;
	$pdf->addTextWrap(225,$YPos,60,$FontSize,'Lead Time:','right');				
	$pdf->addTextWrap(300,$YPos,30,$FontSize,$myrow['leadtime'],'right');
	$pdf->addTextWrap(360,$YPos,50,$FontSize,_('Last Cost:'),'right');
	$pdf->addTextWrap(410,$YPos,50,$FontSize,number_format($myrow['lastcost'],2),'right');	
	$YPos -= (2*$line_height);
	
    // Calculate fields for prjected available weekly buckets
    $pastdueavail = ($qoh + $pastduesup + $pastdueplan) - $pastduereq;
    $weeklyavail = array();
    $weeklyavail[0] = ($pastdueavail + $weeklysup[0] + $weeklyplan[0]) - $weeklyreq[0];
    for ($i = 1; $i < 28; $i++) {
         $weeklyavail[$i] = ($weeklyavail[$i - 1] + $weeklysup[$i] + $weeklyplan[$i]) - $weeklyreq[$i];
    }
    $futureavail = ($weeklyavail[27] + $futuresup + $futureplan) - $futurereq;
    
    // Headers for Weekly Buckets
    $FontSize =7;
    $dateformat = $_SESSION['DefaultDateFormat'];
    $today = date("$dateformat");
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,_('Past Due'),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,$today,'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,DateAdd($today,"w",1),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,DateAdd($today,"w",2),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,DateAdd($today,"w",3),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,DateAdd($today,"w",4),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,DateAdd($today,"w",5),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,DateAdd($today,"w",6),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,DateAdd($today,"w",7),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,DateAdd($today,"w",8),'right');
    $YPos -=$line_height;
    
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Gross Reqts'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($pastduereq,0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklyreq[0],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklyreq[1],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklyreq[2],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklyreq[3],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklyreq[4],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklyreq[5],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklyreq[6],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklyreq[7],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($weeklyreq[8],0),'right');
    $YPos -=$line_height;
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Open Order'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($pastduesup,0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklysup[0],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklysup[1],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklysup[2],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklysup[3],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklysup[4],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklysup[5],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklysup[6],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklysup[7],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($weeklysup[8],0),'right');
    $YPos -=$line_height;
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Planned'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($pastdueplan,0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklyplan[0],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklyplan[1],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklyplan[2],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklyplan[3],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklyplan[4],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklyplan[5],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklyplan[6],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklyplan[7],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($weeklyplan[8],0),'right');
    $YPos -=$line_height;
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Proj Avail'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($pastdueavail,0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklyavail[0],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklyavail[1],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklyavail[2],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklyavail[3],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklyavail[4],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklyavail[5],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklyavail[6],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklyavail[7],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($weeklyavail[8],0),'right');
    $YPos -= 2 * $line_height;

    // Second Group of Weeks
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,DateAdd($today,"w",9),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,DateAdd($today,"w",10),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,DateAdd($today,"w",11),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,DateAdd($today,"w",12),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,DateAdd($today,"w",13),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,DateAdd($today,"w",14),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,DateAdd($today,"w",15),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,DateAdd($today,"w",16),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,DateAdd($today,"w",17),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,DateAdd($today,"w",18),'right');
    $YPos -=$line_height;
    
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Gross Reqts'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($weeklyreq[9],0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklyreq[10],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklyreq[11],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklyreq[12],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklyreq[13],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklyreq[14],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklyreq[15],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklyreq[16],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklyreq[17],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($weeklyreq[18],0),'right');
    $YPos -=$line_height;
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Open Order'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($weeklysup[9],0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklysup[10],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklysup[11],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklysup[12],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklysup[13],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklysup[14],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklysup[15],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklysup[16],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklysup[17],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($weeklysup[18],0),'right');
    $YPos -=$line_height;
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Planned'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($weeklyplan[9],0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklyplan[10],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklyplan[11],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklyplan[12],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklyplan[13],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklyplan[14],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklyplan[15],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklyplan[16],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklyplan[17],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($weeklyplan[18],0),'right');
    $YPos -=$line_height;
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Proj Avail'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($weeklyavail[9],0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklyavail[10],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklyavail[11],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklyavail[12],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklyavail[13],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklyavail[14],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklyavail[15],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklyavail[16],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklyavail[17],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($weeklyavail[18],0),'right');
    $YPos -= 2 * $line_height;    

    // Third Group of Weeks
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,DateAdd($today,"w",19),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,DateAdd($today,"w",20),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,DateAdd($today,"w",21),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,DateAdd($today,"w",22),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,DateAdd($today,"w",23),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,DateAdd($today,"w",24),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,DateAdd($today,"w",25),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,DateAdd($today,"w",26),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,DateAdd($today,"w",27),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,"Future",'right');
    $YPos -=$line_height;
    
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Gross Reqts'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($weeklyreq[19],0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklyreq[20],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklyreq[21],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklyreq[22],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklyreq[23],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklyreq[24],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklyreq[25],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklyreq[26],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklyreq[27],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($futurereq,0),'right');
    $YPos -=$line_height;
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Open Order'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($weeklysup[19],0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklysup[20],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklysup[21],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklysup[22],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklysup[23],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklysup[24],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklysup[25],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklysup[26],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklysup[27],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($futuresup,0),'right');
    $YPos -=$line_height;
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Planned'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($weeklyplan[19],0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklyplan[20],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklyplan[21],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklyplan[22],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklyplan[23],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklyplan[24],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklyplan[25],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklyplan[26],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklyplan[27],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($futureplan,0),'right');
    $YPos -=$line_height;
    $pdf->addTextWrap($Left_Margin,$YPos,40,$FontSize,_('Proj Avail'));
    $pdf->addTextWrap($Left_Margin+40,$YPos,45,$FontSize,number_format($weeklyavail[19],0),'right');
    $pdf->addTextWrap(130,$YPos,45,$FontSize,number_format($weeklyavail[20],0),'right');
    $pdf->addTextWrap(175,$YPos,45,$FontSize,number_format($weeklyavail[21],0),'right');
    $pdf->addTextWrap(220,$YPos,45,$FontSize,number_format($weeklyavail[22],0),'right');
    $pdf->addTextWrap(265,$YPos,45,$FontSize,number_format($weeklyavail[23],0),'right');
    $pdf->addTextWrap(310,$YPos,45,$FontSize,number_format($weeklyavail[24],0),'right');
    $pdf->addTextWrap(355,$YPos,45,$FontSize,number_format($weeklyavail[25],0),'right');
    $pdf->addTextWrap(400,$YPos,45,$FontSize,number_format($weeklyavail[26],0),'right');
    $pdf->addTextWrap(445,$YPos,45,$FontSize,number_format($weeklyavail[27],0),'right');
    $pdf->addTextWrap(490,$YPos,45,$FontSize,number_format($futureavail,0),'right');
    $YPos -=$line_height;    
    
    // Headers for Demand/Supply Sections
    $YPos -= (2*$line_height);
    $pdf->addTextWrap($Left_Margin,$YPos,265,$FontSize,'D E M A N D','center');
    $pdf->addTextWrap(290,$YPos,260,$FontSize,'S U P P L Y','center');
    $YPos -=$line_height;
    
    $pdf->addTextWrap($Left_Margin,$YPos,55,$FontSize,_('Dem Type'));				
	$pdf->addTextWrap(80,$YPos,90,$FontSize,_('Where Required'));
	$pdf->addTextWrap(170,$YPos,30,$FontSize,_('Order'),'');
	$pdf->addTextWrap(200,$YPos,40,$FontSize,_('Quantity'),'right');
	$pdf->addTextWrap(240,$YPos,50,$FontSize,_('Due Date'),'right');
	
	$pdf->addTextWrap(310,$YPos,45,$FontSize,_('Order No.'),'');
	$pdf->addTextWrap(355,$YPos,35,$FontSize,_('Sup Type'),'');
	$pdf->addTextWrap(390,$YPos,25,$FontSize,_('For'),'');
	$pdf->addTextWrap(415,$YPos,40,$FontSize,_('Quantity'),'right');
	$pdf->addTextWrap(455,$YPos,50,$FontSize,_('Due Date'),'right');
	$pdf->addTextWrap(505,$YPos,50,$FontSize,_('MRP Date'),'right');
			
    // Details for Demand/Supply Sections
    $i = 0;
	While (strlen($supplies[$i]['part']) > 1 || strlen($requirements[$i]['part']) > 1){

		$YPos -=$line_height;
		$FontSize=7;
		
        // Use to alternate between lines with transparent and painted background
		if ($_POST['Fill'] == 'yes'){
		    $fill=!$fill;
		}

		// Parameters for addTextWrap are defined in /includes/class.pdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text To Display  6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set for transparent
		if (strlen($requirements[$i]['part']) > 1) {
			$FormatedReqDueDate = ConvertSQLDate($requirements[$i]['daterequired']);
			$pdf->addTextWrap($Left_Margin,$YPos,55,$FontSize,$requirements[$i]['mrpdemandtype'],'');				
			$pdf->addTextWrap(80,$YPos,90,$FontSize,$requirements[$i]['whererequired'],'');
			$pdf->addTextWrap(170,$YPos,30,$FontSize,$requirements[$i]['orderno'],'');
			$pdf->addTextWrap(200,$YPos,40,$FontSize,number_format($requirements[$i]['quantity'],
			                                                    $myrow['decimalplaces']),'right');
			$pdf->addTextWrap(240,$YPos,50,$FontSize,$FormatedReqDueDate,'right');
        }
		if (strlen($supplies[$i]['part']) > 1) {
		    $suptype = $supplies[$i]['ordertype'];
		    // If ordertype is not QOH,PO,or WO, it is an MRP generated planned order and the
		    // ordertype is actually the demandtype that caused the planned order
		    if ($suptype == 'QOH' || $suptype == 'PO' || $suptype == 'WO') {
		        $displaytype = $suptype;
		        $fortype = " ";
		    } else {
		        $displaytype = 'Planned';
		        $fortype = $suptype;
		    }
			$FormatedSupDueDate = ConvertSQLDate($supplies[$i]['duedate']);
			$FormatedSupMRPDate = ConvertSQLDate($supplies[$i]['mrpdate']);
			// Order no is meaningless for QOH and REORD ordertypes
			if ($suptype == "QOH" || $suptype == "REORD") { 
			    $pdf->addTextWrap(310,$YPos,45,$FontSize," ",'');
			} else {
			    $pdf->addTextWrap(310,$YPos,45,$FontSize,$supplies[$i]['orderno'],'');
			}
			$pdf->addTextWrap(355,$YPos,35,$FontSize,$displaytype,'');
			$pdf->addTextWrap(390,$YPos,25,$FontSize,$fortype,'');
			$pdf->addTextWrap(415,$YPos,40,$FontSize,number_format($supplies[$i]['supplyquantity'],
			                                                     $myrow['decimalplaces']),'right');
			$pdf->addTextWrap(455,$YPos,50,$FontSize,$FormatedSupDueDate,'right');
			$pdf->addTextWrap(505,$YPos,50,$FontSize,$FormatedSupMRPDate,'right');
        }
        
		if ($YPos < $Bottom_Margin + $line_height){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin);
		}
        $i++;
	} /*end while loop */

	$FontSize =8;
	$YPos -= (2*$line_height);

	if ($YPos < $Bottom_Margin + $line_height){
		   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin);
	}

	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

	if ($len<=20){
			$title = _('Print MRP Report Error');
			include('includes/header.inc');
			prnMsg(_('The selected item did not have any MRP demand'),'error');
			echo "<br><a href='$rootpath/index.php?" . SID . "'>" . _('Back to the menu') . '</a>';
			include('includes/footer.inc');
			exit;
	} else {
			header('Content-type: application/pdf');
			header('Content-Length: ' . $len);
			header('Content-Disposition: inline; filename=MRPReport.pdf');
			header('Expires: 0');
			header('Cache-Control: private, post-check=0, pre-check=0');
			header('Pragma: public');
	
			$pdf->Stream();
	}
	
} else { /*The option to print PDF was not hit so display form */

	$title=_('MRP Report');
	include('includes/header.inc');

	if (isset($_POST['PrintPDF'])) {
		prnMsg(_('This report shows the MRP calculation for a specific item - a part code must be selected'),'warn');
	}

	echo '</br></br><form action=' . $_SERVER['PHP_SELF'] . " method='post'><table>";
	echo '<tr><td>' . _('Part') . ":</td>";
	echo "<td><input type ='text' name='Part' size='20'>";
	echo "</table></br><div class='centre'><input type=submit name='PrintPDF' value='" . _('Print PDF') . "'></div>";

	include('includes/footer.inc');

} /*end of else not PrintPDF */


function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
                     $Page_Width,$Right_Margin) {


$line_height=12;
/*PDF page header for MRP Report */
if ($PageNumber>1){
	$pdf->newPage();
}

$FontSize=9;
$YPos= $Page_Height-$Top_Margin;

$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

$YPos -=$line_height;

$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,_('MRP Report'));
$pdf->addTextWrap($Page_Width-$Right_Margin-110,$YPos,160,$FontSize,_('Printed') . ': ' . 
     Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');

$YPos -=(2*$line_height);


/*set up the headings */
$Xpos = $Left_Margin+1;

$FontSize=8;
$YPos =$YPos - (2*$line_height);
$PageNumber++;
} // End of PrintHeader function


?>
