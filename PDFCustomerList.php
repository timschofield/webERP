<?php
/* $Revision: 1.13 $ */

/* $Id$*/

$PageSecurity = 2;
include('includes/session.inc');

if (isset($_POST['PrintPDF'])){

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', _('Customer Listing') );
	$pdf->addInfo('Subject', _('Customer Listing') );
	$line_height=12;
	$PageNumber = 0;
	$FontSize=10;

	if ($_POST['Activity']!='All'){
		if (!is_numeric($_POST['ActivityAmount'])){
			$title = _('Customer List') . ' - ' . _('Problem Report') . '....';
			include('includes/header.inc');
			echo '<p>';
			prnMsg( _('The activity amount is not numeric and you elected to print customer relative to a certain amount of activity') . ' - ' . _('this level of activity must be specified in the local currency') .'.', 'error');
			include('includes/footer.inc');
			exit;
		}
	}

	/* Now figure out the customer data to report for the selections made */

	if (in_array('All', $_POST['Areas'])){
		if (in_array('All', $_POST['SalesPeople'])){
			$SQL = 'SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.salestype,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.email,
					custbranch.area,
					custbranch.salesman,
					areas.areadescription,
					salesman.salesmanname
				FROM debtorsmaster INNER JOIN custbranch
				ON debtorsmaster.debtorno=custbranch.debtorno
				INNER JOIN areas
				ON custbranch.area = areas.areacode
				INNER JOIN salesman
				ON custbranch.salesman=salesman.salesmancode
				ORDER BY area,
					salesman,
					debtorsmaster.debtorno,
					custbranch.branchcode';
		} else {
		/* there are a range of salesfolk selected need to build the where clause */
			$SQL = 'SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.salestype,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.email,
					custbranch.area,
					custbranch.salesman,
					areas.areadescription,
					salesman.salesmanname
				FROM debtorsmaster INNER JOIN custbranch
				ON debtorsmaster.debtorno=custbranch.debtorno
				INNER JOIN areas
				ON custbranch.area = areas.areacode
				INNER JOIN salesman
				ON custbranch.salesman=salesman.salesmancode
				WHERE (';

				$i=0;
				foreach ($_POST['SalesPeople'] as $Salesperson){
					if ($i>0){
						$SQL .= ' OR ';
					}
					$i++;
					$SQL .= "custbranch.salesman='" . $Salesperson ."'";
				}

				$SQL .=') ORDER BY area,
						salesman,
						debtorsmaster.debtorno,
						custbranch.branchcode';
		} /*end if SalesPeople =='All' */
	} else { /* not all sales areas has been selected so need to build the where clause */
		if (in_array('All', $_POST['SalesPeople'])){
			$SQL = 'SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.salestype,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.email,
					custbranch.area,
					custbranch.salesman,
					areas.areadescription,
					salesman.salesmanname
				FROM debtorsmaster INNER JOIN custbranch
				ON debtorsmaster.debtorno=custbranch.debtorno
				INNER JOIN areas
				ON custbranch.area = areas.areacode
				INNER JOIN salesman
				ON custbranch.salesman=salesman.salesmancode
				WHERE (';

			$i=0;
			foreach ($_POST['Areas'] as $Area){
				if ($i>0){
					$SQL .= ' OR ';
				}
				$i++;
				$SQL .= "custbranch.area='" . $Area ."'";
			}

			$SQL .= ') ORDER BY custbranch.area,
					custbranch.salesman,
					debtorsmaster.debtorno,
					custbranch.branchcode';
		} else {
		/* there are a range of salesfolk selected need to build the where clause */
			$SQL = 'SELECT debtorsmaster.debtorno,
					debtorsmaster.name,
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,
					debtorsmaster.salestype,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4,
					custbranch.braddress5,
					custbranch.braddress6,
					custbranch.contactname,
					custbranch.phoneno,
					custbranch.faxno,
					custbranch.email,
					custbranch.area,
					custbranch.salesman,
					areas.areadescription,
					salesman.salesmanname
				FROM debtorsmaster INNER JOIN custbranch
				ON debtorsmaster.debtorno=custbranch.debtorno
				INNER JOIN areas
				ON custbranch.area = areas.areacode
				INNER JOIN salesman
				ON custbranch.salesman=salesman.salesmancode
				WHERE (';

			$i=0;
			foreach ($_POST['Areas'] as $Area){
				if ($i>0){
					$SQL .= ' OR ';
				}
				$i++;
				$SQL .= "custbranch.area='" . $Area ."'";
			}

			$SQL .= ') AND (';

			$i=0;
			foreach ($_POST['SalesPeople'] as $Salesperson){
				if ($i>0){
					$SQL .= ' OR ';
				}
				$i++;
				$SQL .= "custbranch.salesman='" . $Salesperson ."'";
			}

			$SQL .=') ORDER BY custbranch.area,
					custbranch.salesman,
					debtorsmaster.debtorno,
					custbranch.branchcode';
		} /*end if Salesfolk =='All' */

	} /* end if not all sales areas was selected */


	$CustomersResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
	  $title = _('Customer List') . ' - ' . _('Problem Report') . '....';
	  include('includes/header.inc');
	   prnMsg( _('The customer List could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db) );
	   echo '<br><a href="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
	   if ($debug==1){
	      echo '<br>'. $SQL;
	   }
	   include('includes/footer.inc');
	   exit;
	}

	if (DB_num_rows($CustomersResult) == 0) {
	  $title = _('Customer List') . ' - ' . _('Problem Report') . '....';
	  include('includes/header.inc');
	  prnMsg( _('This report has no output because there were no customers retrieved'), 'error' );
	  echo '<br><a href="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
	  include('includes/footer.inc');
	  exit;
	}


	include('includes/PDFCustomerListPageHeader.inc');

	$Area ='';
	$SalesPerson='';

	While ($Customers = DB_fetch_array($CustomersResult,$db)){

		if ($_POST['Activity']!='All'){

			/*Get the total turnover in local currency for the customer/branch
			since the date entered */

			$SQL = "SELECT SUM((ovamount+ovfreight+ovdiscount)/rate) AS turnover
				FROM debtortrans
				WHERE debtorno='" . $Customers['debtorno'] . "'
				AND branchcode='" . $Customers['branchcode'] . "'
				AND (type=10 or type=11)
				AND trandate >='" . FormatDateForSQL($_POST['ActivitySince']). "'";
			$ActivityResult = DB_query($SQL, $db, _('Could not retrieve the activity of the branch because'), _('The failed SQL was'));

			$ActivityRow = DB_fetch_row($ActivityResult);
			$LocalCurrencyTurnover = $ActivityRow[0];

			if ($_POST['Activity'] =='GreaterThan'){
				if ($LocalCurrencyTurnover > $_POST['ActivityAmount']){
					$PrintThisCustomer = true;
				} else {
					$PrintThisCustomer = false;
				}
			} elseif ($_POST['Activity'] =='LessThan'){
				if ($LocalCurrencyTurnover < $_POST['ActivityAmount']){
					$PrintThisCustomer = true;
				} else {
					$PrintThisCustomer = false;
				}
			}
		} else {
			$PrintThisCustomer = true;
		}

		if ($PrintThisCustomer){
			if ($Area!=$Customers['area']){
				$FontSize=10;
				$YPos -=$line_height;
				if ($YPos < ($Bottom_Margin + 80)){
					include('includes/PDFCustomerListPageHeader.inc');
				}
				$pdf->setFont('','B');
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Customers in') . ' ' . $Customers['areadescription']);
				$Area = $Customers['area'];
				$pdf->setFont('','');
				$FontSize=8;
				$YPos -=$line_height;
			}

			if ($SalesPerson!=$Customers['salesman']){
				$FontSize=10;
				$YPos -=($line_height);
				if ($YPos < ($Bottom_Margin + 80)){
					include('includes/PDFCustomerListPageHeader.inc');
				}
				$pdf->setFont('','B');
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300-$Left_Margin,$FontSize,$Customers['salesmanname']);
				$pdf->setFont('','');
				$SalesPerson = $Customers['salesman'];
				$FontSize=8;
				$YPos -=$line_height;
			}

			$YPos -=$line_height;

			$LeftOvers = $pdf->addTextWrap(20,$YPos,60,$FontSize,$Customers['debtorno']);
			$LeftOvers = $pdf->addTextWrap(80,$YPos,150,$FontSize,$Customers['name']);
			$LeftOvers = $pdf->addTextWrap(80,$YPos-10,150,$FontSize,$Customers['address1']);
			$LeftOvers = $pdf->addTextWrap(80,$YPos-20,150,$FontSize,$Customers['address2']);
			$LeftOvers = $pdf->addTextWrap(80,$YPos-30,150,$FontSize,$Customers['address3']);
			$LeftOvers = $pdf->addTextWrap(140,$YPos-30,150,$FontSize,$Customers['address4']);
			$LeftOvers = $pdf->addTextWrap(180,$YPos-30,150,$FontSize,$Customers['address5']);
			$LeftOvers = $pdf->addTextWrap(210,$YPos-30,150,$FontSize,$Customers['address6']);

			$LeftOvers = $pdf->addTextWrap(230,$YPos,60,$FontSize,$Customers['branchcode']);
			$LeftOvers = $pdf->addTextWrap(230,$YPos-10,60,$FontSize, _('Price List') . ': ' . $Customers['salestype']);

			if ($_POST['Activity']!='All'){
				$LeftOvers = $pdf->addTextWrap(230,$YPos-20,60,$FontSize,_('Turnover'),'right');
				$LeftOvers = $pdf->addTextWrap(230,$YPos-30,60,$FontSize,number_format($LocalCurrencyTurnover), 'right');
			}

			$LeftOvers = $pdf->addTextWrap(290,$YPos,150,$FontSize,$Customers['brname']);
			$LeftOvers = $pdf->addTextWrap(290,$YPos-10,150,$FontSize,$Customers['contactname']);
			$LeftOvers = $pdf->addTextWrap(290,$YPos-20,150,$FontSize, _('Ph'). ': ' . $Customers['phoneno']);
			$LeftOvers = $pdf->addTextWrap(290,$YPos-30,150,$FontSize, _('Fax').': ' . $Customers['faxno']);
			$LeftOvers = $pdf->addTextWrap(440,$YPos,150,$FontSize,$Customers['braddress1']);
			$LeftOvers = $pdf->addTextWrap(440,$YPos-10,150,$FontSize,$Customers['braddress2']);
			$LeftOvers = $pdf->addTextWrap(440,$YPos-20,150,$FontSize,$Customers['braddress3']);
			$LeftOvers = $pdf->addTextWrap(500,$YPos-20,150,$FontSize,$Customers['braddress4']);
			$LeftOvers = $pdf->addTextWrap(540,$YPos-20,150,$FontSize,$Customers['braddress5']);
			$LeftOvers = $pdf->addTextWrap(570,$YPos-20,150,$FontSize,$Customers['braddress6']);
			$LeftOvers = $pdf->addTextWrap(440,$YPos-30,150,$FontSize,$Customers['email']);

			$pdf->line($Page_Width-$Right_Margin, $YPos-32,$Left_Margin, $YPos-32);

			$YPos -=40;
			if ($YPos < ($Bottom_Margin +30)){
				include('includes/PDFCustomerListPageHeader.inc');
			}
		} /*end if $PrintThisCustomer == true */
	} /*end while loop */

    $pdf->OutputD($_SESSION['DatabaseName'] . '_CustomerList_' . date('Y-m-d').'.pdf');//UldisN
    $pdf->__destruct(); //UldisN
	exit;

} else {

	$title = _('Customer Details Listing');
	include('includes/header.inc');
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/customer.png" title="' .
		 $title . '" alt="">' . ' ' . $title . '</p>';

	echo '<form action=' . $_SERVER['PHP_SELF'] . ' method="POST"><table class=selection>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<tr><td>' . _('For Sales Areas') . ':</td><td><select name=Areas[] multiple>';

	$sql='SELECT areacode, areadescription FROM areas';
	$AreasResult= DB_query($sql,$db);

	echo '<option selected value="All">' . _('All Areas');

	While ($myrow = DB_fetch_array($AreasResult)){
		echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'];
	}
	echo '</select></td></tr>';

	echo '<tr><td>' . _('For Sales folk'). ':</td><td><select name=SalesPeople[] multiple>';

	echo '<option selected value="All">'. _('All sales folk');

	$sql = 'SELECT salesmancode, salesmanname FROM salesman';
	$SalesFolkResult = DB_query($sql,$db);

	While ($myrow = DB_fetch_array($SalesFolkResult)){
		echo '<option VALUE="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'];
	}
	echo '</select></td></tr>';

	echo '<tr><td>' . _('Level Of Activity'). ':</td><td><select name="Activity">';

	echo '<option selected value="All">'. _('All customers');
	echo '<option value="GreaterThan">'. _('Sales Greater Than');
	echo '<option value="LessThan">'. _('Sales Less Than');
	echo '</select></td><td>';

	echo '<input type="text" class=number name="ActivityAmount" size=8 maxlength=8 value=0></td></tr>';

	$DefaultActivitySince = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')-6,0,Date('y')));
	echo '<tr><td>' . _('Activity Since'). ':</td><td><input type="text" class=date alt="'.$_SESSION['DefaultDateFormat'].'"  name="ActivitySince" size=10 maxlength=10
		VALUE="' . $DefaultActivitySince . '"></td></tr>';

	echo '</table><br><div class="centre"><input type=Submit Name="PrintPDF" Value="'. _('Print PDF'). '"></div>';

	include('includes/footer.inc');

} /*end of else not PrintPDF */
?>