<?php
/* $Revision: 1.9 $ */

$PageSecurity = 2;
include('includes/session.inc');

if (isset($_POST['PrintPDF'])){

	include('includes/PDFStarter.php');

	if ($_POST['Activity']!='All'){
		if (!is_numeric($_POST['ActivityAmount'])){
			$title = _('Customer List') . ' - ' . _('Problem Report') . '....';
	  		include('includes/header.inc');
	   		echo '<P>';
			prnMsg( _('The acitivty amount is not numeric and you elected to print customer relative to a certain amount of activity') . ' - ' . _('this level of activity must be specified in the local currency') .'.', 'error');
			include('includes/footer.inc');
			exit;
		}
	}

	$PageNumber = 0;

	$FontSize=10;
	$pdf->addinfo('Title', _('Customer Listing') );
	$pdf->addinfo('Subject', _('Customer Listing') );

	$line_height=12;

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
	   echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</A>';
	   if ($debug==1){
	      echo '<BR>'. $SQL;
	   }
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
				$pdf->selectFont('./fonts/Helvetica-Bold.afm');
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Customers in') . ' ' . $Customers['areadescription']);
				$Area = $Customers['area'];
				$pdf->selectFont('./fonts/Helvetica.afm');
				$FontSize=8;
				$YPos -=$line_height;
			}

			if ($SalesPerson!=$Customers['salesman']){
				$FontSize=10;
				$YPos -=($line_height);
				if ($YPos < ($Bottom_Margin + 80)){
					include('includes/PDFCustomerListPageHeader.inc');
				}
				$pdf->selectFont('./fonts/Helvetica-Bold.afm');
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300-$Left_Margin,$FontSize,$Customers['salesmanname']);
				$pdf->selectFont('./fonts/Helvetica.afm');
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


	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

      if ($len<=20){
		$title = _('Print Customer List Error');
		include('includes/header.inc');
		echo '<p>';
		prnMsg( _('There were no customers to print out for the selections specified') );
		echo '<BR><A HREF="'. $rootpath.' /index.php?' . SID . '">'. _('Back to the menu'). '</A>';
		include('includes/footer.inc');
		exit;
	} else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=CustomerList.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();

	}
	exit;

} else {

	$title = _('Customer Details Listing');
	include('includes/header.inc');

	echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . ' METHOD="POST"><CENTER><TABLE>';
	echo '<TR><TD>' . _('For Sales Areas') . ':</TD><TD><SELECT name=Areas[] multiple>';

	$sql='SELECT areacode, areadescription FROM areas';
	$AreasResult= DB_query($sql,$db);

	echo '<OPTION SELECTED VALUE="All">' . _('All Areas');

	While ($myrow = DB_fetch_array($AreasResult)){
		echo '<OPTION VALUE="' . $myrow['areacode'] . '">' . $myrow['areadescription'];
	}
	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('For Sales folk'). ':</TD><TD><SELECT name=SalesPeople[] multiple>';

	echo '<OPTION SELECTED VALUE="All">'. _('All sales folk');

	$sql = 'SELECT salesmancode, salesmanname FROM salesman';
	$SalesFolkResult = DB_query($sql,$db);

	While ($myrow = DB_fetch_array($SalesFolkResult)){
		echo '<OPTION VALUE="' . $myrow['salesmancode'] . '">' . $myrow['salesmanname'];
	}
	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('Level Of Activity'). ':</TD><TD><SELECT name="Activity">';

	echo '<OPTION SELECTED VALUE="All">'. _('All customers');
	echo '<OPTION VALUE="GreaterThan">'. _('Sales Greater Than');
	echo '<OPTION VALUE="LessThan">'. _('Sales Less Than');
	echo '</SELECT>';

	echo '<INPUT TYPE="text" NAME="ActivityAmount" SIZE=8 MAXLENGTH=8></TD></TR>';

	$DefaultActivitySince = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')-6,0,Date('y')));
	echo '<TR><TD>' . _('Activity Since'). ':</TD><TD><INPUT TYPE="text" NAME="ActivitySince" SIZE=10 MAXLENGTH=10
		VALUE="' . $DefaultActivitySince . '"></TD></TR>';

	echo '</TABLE><INPUT TYPE=Submit Name="PrintPDF" Value="'. _('Print PDF'). '"></CENTER>';

	include('includes/footer.inc');

} /*end of else not PrintPDF */
?>