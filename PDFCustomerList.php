<?php
/* $Revision: 1.3 $ */

$PageSecurity = 2;

if (isset($_POST['PrintPDF'])){

	include('config.php');
	include('includes/ConnectDB.inc');
	include('includes/PDFStarter_ros.inc');
	include('includes/DateFunctions.inc');


	if ($_POST['Activity']!='All'){
		if (!is_numeric($_POST['ActivityAmount'])){
			$title = _('Customer List - Problem Report....');
	  		include('includes/header.inc');
	   		echo '<P>';
			prnMsg( _('The acitivty amount is not numeric and you elected to print customer relative to a certain amount of activity - this level of activity must be specified in the local currency') .'.', 'error');
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
			$SQL = "SELECT DebtorsMaster.DebtorNo,
					DebtorsMaster.Name,
					DebtorsMaster.Address1,
					DebtorsMaster.Address2,
					DebtorsMaster.Address3,
					DebtorsMaster.Address4,
					DebtorsMaster.SalesType,
					CustBranch.BranchCode,
					CustBranch.BrName,
					CustBranch.BrAddress1,
					CustBranch.BrAddress2,
					CustBranch.BrAddress3,
					CustBranch.ContactName,
					CustBranch.PhoneNo,
					CustBranch.FaxNo,
					CustBranch.Email,
					CustBranch.Area,
					CustBranch.Salesman,
					Areas.AreaDescription,
					Salesman.SalesmanName
				FROM DebtorsMaster INNER JOIN CustBranch
				ON DebtorsMaster.DebtorNo=CustBranch.DebtorNo
				INNER JOIN Areas
				ON CustBranch.Area = Areas.AreaCode
				INNER JOIN Salesman
				ON CustBranch.Salesman=Salesman.SalesmanCode
				ORDER BY Area,
					Salesman,
					DebtorsMaster.DebtorNo,
					CustBranch.BranchCode";
		} else {
		/* there are a range of salesfolk selected need to build the where clause */
			$SQL = "SELECT DebtorsMaster.DebtorNo,
					DebtorsMaster.Name,
					DebtorsMaster.Address1,
					DebtorsMaster.Address2,
					DebtorsMaster.Address3,
					DebtorsMaster.Address4,
					DebtorsMaster.SalesType,
					CustBranch.BranchCode,
					CustBranch.BrName,
					CustBranch.BrAddress1,
					CustBranch.BrAddress2,
					CustBranch.BrAddress3,
					CustBranch.ContactName,
					CustBranch.PhoneNo,
					CustBranch.FaxNo,
					CustBranch.Email,
					CustBranch.Area,
					CustBranch.Salesman,
					Areas.AreaDescription,
					Salesman.SalesmanName
				FROM DebtorsMaster INNER JOIN CustBranch
				ON DebtorsMaster.DebtorNo=CustBranch.DebtorNo
				INNER JOIN Areas
				ON CustBranch.Area = Areas.AreaCode
				INNER JOIN Salesman
				ON CustBranch.Salesman=Salesman.SalesmanCode
				WHERE (";

				$i=0;
				foreach ($_POST['SalesPeople'] as $Salesperson){
					if ($i>0){
						$SQL .= " OR ";
					}
					$i++;
					$SQL .= "CustBranch.Salesman='" . $Salesperson ."'";
				}

				$SQL .=") ORDER BY Area,
						Salesman,
						DebtorsMaster.DebtorNo,
						CustBranch.BranchCode";
		} /*end if SalesPeople =='All' */
	} else { /* not all sales areas has been selected so need to build the where clause */
		if (in_array("All", $_POST['SalesPeople'])){
			$SQL = "SELECT DebtorsMaster.DebtorNo,
					DebtorsMaster.Name,
					DebtorsMaster.Address1,
					DebtorsMaster.Address2,
					DebtorsMaster.Address3,
					DebtorsMaster.Address4,
					DebtorsMaster.SalesType,
					CustBranch.BranchCode,
					CustBranch.BrName,
					CustBranch.BrAddress1,
					CustBranch.BrAddress2,
					CustBranch.BrAddress3,
					CustBranch.ContactName,
					CustBranch.PhoneNo,
					CustBranch.FaxNo,
					CustBranch.Email,
					CustBranch.Area,
					CustBranch.Salesman,
					Areas.AreaDescription,
					Salesman.SalesmanName
				FROM DebtorsMaster INNER JOIN CustBranch
				ON DebtorsMaster.DebtorNo=CustBranch.DebtorNo
				INNER JOIN Areas
				ON CustBranch.Area = Areas.AreaCode
				INNER JOIN Salesman
				ON CustBranch.Salesman=Salesman.SalesmanCode
				WHERE (";

			$i=0;
			foreach ($_POST['Areas'] as $Area){
				if ($i>0){
					$SQL .= " OR ";
				}
				$i++;
				$SQL .= "CustBranch.Area='" . $Area ."'";
			}

			$SQL .= ") ORDER BY Area,
					Salesman,
					DebtorsMaster.DebtorNo,
					CustBranch.BranchCode";
		} else {
		/* there are a range of salesfolk selected need to build the where clause */
			$SQL = "SELECT DebtorsMaster.DebtorNo,
					DebtorsMaster.Name,
					DebtorsMaster.Address1,
					DebtorsMaster.Address2,
					DebtorsMaster.Address3,
					DebtorsMaster.Address4,
					DebtorsMaster.SalesType,
					CustBranch.BranchCode,
					CustBranch.BrName,
					CustBranch.BrAddress1,
					CustBranch.BrAddress2,
					CustBranch.BrAddress3,
					CustBranch.ContactName,
					CustBranch.PhoneNo,
					CustBranch.FaxNo,
					CustBranch.Email,
					CustBranch.Area,
					CustBranch.Salesman,
					Areas.AreaDescription,
					Salesman.SalesmanName
				FROM DebtorsMaster INNER JOIN CustBranch
				ON DebtorsMaster.DebtorNo=CustBranch.DebtorNo
				INNER JOIN Areas
				ON CustBranch.Area = Areas.AreaCode
				INNER JOIN Salesman
				ON CustBranch.Salesman=Salesman.SalesmanCode
				WHERE (";

			$i=0;
			foreach ($_POST['Areas'] as $Area){
				if ($i>0){
					$SQL .= " OR ";
				}
				$i++;
				$SQL .= "CustBranch.Area='" . $Area ."'";
			}

			$SQL .= ") AND (";

			$i=0;
			foreach ($_POST['SalesPeople'] as $Salesperson){
				if ($i>0){
					$SQL .= " OR ";
				}
				$i++;
				$SQL .= "CustBranch.Salesman='" . $Salesperson ."'";
			}

			$SQL .=") ORDER BY Area,
					Salesman,
					DebtorsMaster.DebtorNo,
					CustBranch.BranchCode";
		} /*end if Salesfolk =='All' */

	} /* end if not all sales areas was selected */


	$CustomersResult = DB_query($SQL,$db);

	if (DB_error_no($db) !=0) {
	  $title = _('Customer List - Problem Report....');
	  include('includes/header.inc');
	   prnMsg( _('The customer List could not be retrieved by the SQL because') . ' - ' . DB_error_msg($db) );
	   echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the Menu'). '</A>';
	   if ($debug==1){
	      echo '<BR>'. $SQL;
	   }
	   include('includes/footer.inc');
	   exit;
	}

	include('includes/PDFCustomerListPageHeader.inc');

	$Area ="";
	$SalesPerson="";

	While ($Customers = DB_fetch_array($CustomersResult,$db)){

		if ($_POST['Activity']!='All'){

			/*Get the total turnover in local currency for the customer/branch
			since the date entered */

			$SQL = "SELECT Sum((OvAmount+OvFreight+OvDiscount)/Rate) AS Turnover
				FROM DebtorTrans
				WHERE DebtorNo='" . $Customers['DebtorNo'] . "'
				AND BranchCode='" . $Customers['BranchCode'] . "'
				AND (Type=10 OR Type=11)
				AND TranDate >='" . FormatDateForSQL($_POST['ActivitySince']). "'";
			$ActivityResult = DB_query($SQL, $db, _('Could not retrieve the activity of the branch because'), _('The failed SQL was:'));

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
			if ($Area!=$Customers["Area"]){
				$FontSize=10;
				$YPos -=$line_height;
				if ($YPos < ($Bottom_Margin + 80)){
					include("includes/PDFCustomerListPageHeader.inc");
				}
				$pdf->selectFont('./fonts/Helvetica-Bold.afm');
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Customers In') . ' ' . $Customers["AreaDescription"]);
				$Area = $Customers["Area"];
				$pdf->selectFont('./fonts/Helvetica.afm');
				$FontSize=8;
				$YPos -=$line_height;
			}

			if ($SalesPerson!=$Customers["Salesman"]){
				$FontSize=10;
				$YPos -=($line_height);
				if ($YPos < ($Bottom_Margin + 80)){
					include("includes/PDFCustomerListPageHeader.inc");
				}
				$pdf->selectFont('./fonts/Helvetica-Bold.afm');
				$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300-$Left_Margin,$FontSize,$Customers["SalesmanName"]);
				$pdf->selectFont('./fonts/Helvetica.afm');
				$SalesPerson = $Customers["Salesman"];
				$FontSize=8;
				$YPos -=$line_height;
			}

			$YPos -=$line_height;


			$LeftOvers = $pdf->addTextWrap(20,$YPos,60,$FontSize,$Customers["DebtorNo"]);
			$LeftOvers = $pdf->addTextWrap(80,$YPos,150,$FontSize,$Customers["Name"]);
			$LeftOvers = $pdf->addTextWrap(80,$YPos-10,150,$FontSize,$Customers["Address1"]);
			$LeftOvers = $pdf->addTextWrap(80,$YPos-20,150,$FontSize,$Customers["Address2"]);
			$LeftOvers = $pdf->addTextWrap(80,$YPos-30,150,$FontSize,$Customers["Address3"]);

			$LeftOvers = $pdf->addTextWrap(230,$YPos,60,$FontSize,$Customers["BranchCode"]);
			$LeftOvers = $pdf->addTextWrap(230,$YPos-10,60,$FontSize, _('Price List:') . ' ' . $Customers["SalesType"]);

			if ($_POST['Activity']!='All'){
				$LeftOvers = $pdf->addTextWrap(230,$YPos-20,60,$FontSize,_('Turnover'),'right');
				$LeftOvers = $pdf->addTextWrap(230,$YPos-30,60,$FontSize,number_format($LocalCurrencyTurnover), 'right');
			}

			$LeftOvers = $pdf->addTextWrap(290,$YPos,150,$FontSize,$Customers["BrName"]);
			$LeftOvers = $pdf->addTextWrap(290,$YPos-10,150,$FontSize,$Customers["ContactName"]);
			$LeftOvers = $pdf->addTextWrap(290,$YPos-20,150,$FontSize, _('Ph:'). ' ' . $Customers["PhoneNo"]);
			$LeftOvers = $pdf->addTextWrap(290,$YPos-30,150,$FontSize, _('Fax:').' ' . $Customers["FaxNo"]);
			$LeftOvers = $pdf->addTextWrap(440,$YPos,150,$FontSize,$Customers["BrAddress1"]);
			$LeftOvers = $pdf->addTextWrap(440,$YPos-10,150,$FontSize,$Customers["BrAddress2"]);
			$LeftOvers = $pdf->addTextWrap(440,$YPos-20,150,$FontSize,$Customers["BrAddress3"]);
			$LeftOvers = $pdf->addTextWrap(440,$YPos-30,150,$FontSize,$Customers["Email"]);

			$pdf->line($Page_Width-$Right_Margin, $YPos-32,$Left_Margin, $YPos-32);

			$YPos -=40;
			if ($YPos < ($Bottom_Margin +30)){
				include("includes/PDFCustomerListPageHeader.inc");
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
		echo '<BR><A HREF="'. $rootpath.' /index.php?' . SID . '">'. _('Back to the Menu'). '</A>';
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

	include('includes/session.inc');
	$title = _('Customer Details Listing');
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');

	$CompanyRecord = ReadInCompanyRecord($db);

	echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . ' METHOD="POST"><CENTER><TABLE>';
	echo '<TR><TD>' . _('For Sales Areas') . ':</TD><TD><SELECT name=Areas[] multiple>';

	$sql="SELECT AreaCode, AreaDescription FROM Areas";
	$AreasResult= DB_query($sql,$db);

	echo '<OPTION SELECTED VALUE="All">' . _('All Areas');

	While ($myrow = DB_fetch_array($AreasResult)){
		echo '<OPTION VALUE="' . $myrow['AreaCode'] . '">' . $myrow['AreaDescription'];
	}
	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('For Sales folk'). ':</TD><TD><SELECT name=SalesPeople[] multiple>';

	echo '<OPTION SELECTED VALUE="All">'. _('All sales folk');

	$sql = "SELECT SalesmanCode, SalesmanName FROM Salesman";
	$SalesFolkResult = DB_query($sql,$db);

	While ($myrow = DB_fetch_array($SalesFolkResult)){
		echo '<OPTION VALUE="' . $myrow['SalesmanCode'] . '">' . $myrow['SalesmanName'];
	}
	echo '</SELECT></TD></TR>';

	echo '<TR><TD>' . _('Level Of Activity'). ':</TD><TD><SELECT name="Activity">';

	echo '<OPTION SELECTED VALUE="All">'. _('All customers');
	echo '<OPTION VALUE="GreaterThan">'. _('Sales Greater Than');
	echo '<OPTION VALUE="LessThan">'. _('Sales Less Than');
	echo '</SELECT>';

	echo '<INPUT TYPE="text" NAME="ActivityAmount" SIZE=8 MAXLENGTH=8></TD></TR>';

	$DefaultActivitySince = Date($DefaultDateFormat, Mktime(0,0,0,Date("m")-6,0,Date("y")));
	echo '<TR><TD>' . _('Activity Since'). ':</TD><TD><INPUT TYPE="text" NAME="ActivitySince" SIZE=10 MAXLENGTH=10
		VALUE="' . $DefaultActivitySince . '"></TD></TR>';

	echo '</TABLE><INPUT TYPE=Submit Name="PrintPDF" Value="'. _('Print PDF'). '"></CENTER>';

	include('includes/footer.inc');

} /*end of else not PrintPDF */
?>