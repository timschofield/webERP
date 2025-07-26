<?php
/* Creates a report of the customer and branch information held. This report has options to print only customer branches in a specified sales area and sales person. Additional option allows to list only those customers with activity either under or over a specified amount, since a specified date. */

include('includes/session.php');
use Dompdf\Dompdf;

if (isset($_POST['ActivitySince'])){$_POST['ActivitySince'] = ConvertSQLDate($_POST['ActivitySince']);}
$ViewTopic = 'ARReports';
$BookMark = 'CustomerListing';

if (isset($_POST['PrintPDF']) or isset($_POST['View'])) {

	include('includes/PDFStarter.php');

	if($_POST['Activity']!='All') {
		if(!is_numeric($_POST['ActivityAmount'])) {
			$Title = _('Customer List') . ' - ' . _('Problem Report') . '....';
			include('includes/header.php');
			echo '<p />';
			prnMsg( _('The activity amount is not numeric and you elected to print customer relative to a certain amount of activity') . ' - ' . _('this level of activity must be specified in the local currency') .'.', 'error');
			include('includes/footer.php');
			exit();
		}
	}

	/* Now figure out the customer data to report for the selections made */

	if(in_array('All', $_POST['Areas'])) {
		if(in_array('All', $_POST['SalesPeople'])) {
			$SQL = "SELECT debtorsmaster.debtorno,
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
						custbranch.branchcode";
		} else {
		/* there are a range of salesfolk selected need to build the where clause */
			$SQL = "SELECT debtorsmaster.debtorno,
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
					WHERE (";

				$i=0;
				foreach ($_POST['SalesPeople'] as $Salesperson) {
					if($i>0) {
						$SQL .= " OR ";
					}
					$i++;
					$SQL .= "custbranch.salesman='" . $Salesperson ."'";
				}

				$SQL .=") ORDER BY area,
						salesman,
						debtorsmaster.debtorno,
						custbranch.branchcode";
		} /*end if SalesPeople =='All' */
	} else { /* not all sales areas has been selected so need to build the where clause */
		if(in_array('All', $_POST['SalesPeople'])) {
			$SQL = "SELECT debtorsmaster.debtorno,
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
					WHERE (";

			$i=0;
			foreach ($_POST['Areas'] as $Area) {
				if($i>0) {
					$SQL .= " OR ";
				}
				$i++;
				$SQL .= "custbranch.area='" . $Area ."'";
			}

			$SQL .= ") ORDER BY custbranch.area,
					custbranch.salesman,
					debtorsmaster.debtorno,
					custbranch.branchcode";
		} else {
		/* there are a range of salesfolk selected need to build the where clause */
			$SQL = "SELECT debtorsmaster.debtorno,
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
				WHERE (";

			$i=0;
			foreach ($_POST['Areas'] as $Area) {
				if($i>0) {
					$SQL .= " OR ";
				}
				$i++;
				$SQL .= "custbranch.area='" . $Area ."'";
			}

			$SQL .= ") AND (";

			$i=0;
			foreach ($_POST['SalesPeople'] as $Salesperson) {
				if($i>0) {
					$SQL .= " OR ";
				}
				$i++;
				$SQL .= "custbranch.salesman='" . $Salesperson ."'";
			}

			$SQL .=") ORDER BY custbranch.area,
					custbranch.salesman,
					debtorsmaster.debtorno,
					custbranch.branchcode";
		} /*end if Salesfolk =='All' */

	} /* end if not all sales areas was selected */

	$CustomersResult = DB_query($SQL);

	if(DB_error_no() !=0) {
	  $Title = _('Customer List') . ' - ' . _('Problem Report') . '....';
	  include('includes/header.php');
	   prnMsg( _('The customer List could not be retrieved by the SQL because') . ' - ' . DB_error_msg() );
	   echo '<br /><a href="' .$RootPath .'/index.php">' .  _('Back to the menu'). '</a>';
	   if($Debug==1) {
	      echo '<br />' .  $SQL;
	   }
	   include('includes/footer.php');
	   exit();
	}

	if(DB_num_rows($CustomersResult) == 0) {
	  $Title = _('Customer List') . ' - ' . _('Problem Report') . '....';
	  include('includes/header.php');
	  prnMsg( _('This report has no output because there were no customers retrieved'), 'error' );
	  echo '<br /><a href="' .$RootPath .'/index.php">' .  _('Back to the menu'). '</a>';
	  include('includes/footer.php');
	  exit();
	}

	$HTML = '';

	if (isset($_POST['PrintPDF'])) {
		$HTML .= '<html>
					<head>';
		$HTML .= '<link href="css/reports.css" rel="stylesheet" type="text/css" />';
	}

	$HTML .= '<meta name="author" content="WebERP " . $Version">
					<meta name="Creator" content="webERP https://www.weberp.org">
				</head>
				<body>';

	$Heading = _('Customers List for'). ' ';

	if (in_array('All', $_POST['Areas'])){
		$Heading .= _('All Territories'). ' ';
	} else {
		if (count($_POST['Areas'])==1){
			$Heading .= _('Territory') . ' ' . $_POST['Areas'][0];
		} else {
			$Heading .= _('Territories'). ' ';
			$NoOfAreas = count($_POST['Areas']);
			$i=1;
			foreach ($_POST['Areas'] as $Area){
				if ($i==$NoOfAreas){
					$Heading .= _('and') . ' ' . $Area . ' ';
				} elseif ($i==($NoOfAreas-1)) {
					$Heading .= $Area . ' ';
				} else {
					$Heading .= $Area . ', ';
				}
			}
		}
	}

	$Heading .= ' '. _('and for').' ';
	if (in_array('All', $_POST['SalesPeople'])){
		$Heading .= _('All Salespeople');
	} else {
		if (count($_POST['SalesPeople'])==1){
			$Heading .= _('only') .' ' . $_POST['SalesPeople'][0];
		} else {
			$Heading .= _('Salespeople') .' ';
			$NoOfSalesfolk = count($_POST['SalesPeople']);
			$i=1;
			foreach ($_POST['SalesPeople'] as $Salesperson){
				if ($i==$NoOfSalesfolk){
					$Heading .= _('and') . ' ' . $Salesperson . " ";
				} elseif ($i==($NoOfSalesfolk-1)) {
					$Heading .= $Salesperson . " ";
				} else {
					$Heading .= $Salesperson . ", ";
				}
			}
		}
	}

	$HTML .= '<div class="centre" id="ReportHeader">
				' . $_SESSION['CompanyRecord']['coyname'] . '<br />
				' . $Heading . '<br />
				' . _('Printed') . ': ' . Date($_SESSION['DefaultDateFormat']) . '<br />
			</div>
			<table>
				<thead>
					<tr>
						<th>' . _('Act Code') . '</th>
						<th>' . _('Postal Address') . '</th>
						<th>' . _('Branch Code') . '</th>
						<th>' . _('Branch Contact Information') . '</th>
						<th>' . _('Branch Delivery Address') . '</th>
					</tr>
				</thead>
				<tbody>';

	$Area ='';
	$SalesPerson='';

	while($Customers = DB_fetch_array($CustomersResult)) {

		if($_POST['Activity']!='All') {

			/*Get the total turnover in local currency for the customer/branch
			since the date entered */

			$SQL = "SELECT SUM((ovamount+ovfreight+ovdiscount)/rate) AS turnover
					FROM debtortrans
					WHERE debtorno='" . $Customers['debtorno'] . "'
					AND branchcode='" . $Customers['branchcode'] . "'
					AND (type=10 or type=11)
					AND trandate >='" . FormatDateForSQL($_POST['ActivitySince']). "'";
			$ActivityResult = DB_query($SQL, _('Could not retrieve the activity of the branch because'), _('The failed SQL was'));

			$ActivityRow = DB_fetch_row($ActivityResult);
			$LocalCurrencyTurnover = $ActivityRow[0];

			if($_POST['Activity'] =='GreaterThan') {
				if($LocalCurrencyTurnover > $_POST['ActivityAmount']) {
					$PrintThisCustomer = true;
				} else {
					$PrintThisCustomer = false;
				}
			} elseif($_POST['Activity'] =='LessThan') {
				if($LocalCurrencyTurnover < $_POST['ActivityAmount']) {
					$PrintThisCustomer = true;
				} else {
					$PrintThisCustomer = false;
				}
			}
		} else {
			$PrintThisCustomer = true;
		}

		if($PrintThisCustomer) {

			$HTML .='<tr class="striped_row">';
			if($Area!=$Customers['area']) {
				$HTML .= '<th colspan="3">' . _('Customers in') . ' ' . $Customers['areadescription'] . '<br />';
				$Area = $Customers['area'];
			}

			if($SalesPerson!=$Customers['salesman']) {
				$HTML .= '' . _('Salesman') . ' ' . $Customers['salesmanname'] . '</th>';
				$SalesPerson = $Customers['salesman'];
			}
			$HTML .= '</tr>';

			$CustomerDetails = $Customers['name'];
			for ($i = 1; $i<=6; $i++) {
				if ($Customers['address' . $i] != '') {
					$CustomerDetails .= '<br />' . $Customers['address' . $i];
				}
			}

			$HTML .= '<tr class="striped_row">
						<td>' . $Customers['debtorno'] . '</td>
						<td>' . $CustomerDetails . '</td>
						<td>' . $Customers['branchcode'] . '<br />
							' . _('Price List') . ': ' . $Customers['salestype'] . '
						</td>';


			if($_POST['Activity']!='All') {
				$HTML .= '<td>' . _('Turnover') . ' - ' . locale_number_format($LocalCurrencyTurnover,0) . '</td>';
			}

			$HTML .= '<td>' . $Customers['brname'] . '<br />
						  ' . $Customers['contactname'] . '<br />
						  ' . _('Ph'). ': ' . $Customers['phoneno'] . '<br />
						  ' . _('Fax').': ' . $Customers['faxno'] . '
						</td>';

			$BranchAddress = $Customers['name'];
			for ($i = 1; $i<=6; $i++) {
				if ($Customers['braddress' . $i] != '') {
					$BranchAddress .= '<br />' . $Customers['braddress' . $i];
				}
			}

			$HTML .= '<td>' . $BranchAddress . '</td>
					</tr>';
		} /*end if $PrintThisCustomer == true */
	} /*end while loop */


	if (isset($_POST['PrintPDF'])) {
		$HTML .= '</tbody>
				<div class="footer fixed-section">
					<div class="right">
						<span class="page-number">Page </span>
					</div>
				</div>
			</table>';
	} else {
		$HTML .= '</tbody>
				</table>
				<div class="centre">
					<form><input type="submit" name="close" value="' . _('Close') . '" onclick="window.close()" /></form>
				</div>';
	}
	$HTML .= '</body>
		</html>';

	if (isset($_POST['PrintPDF'])) {
		$dompdf = new Dompdf(['chroot' => __DIR__]);
		$dompdf->loadHtml($HTML);

		// (Optional) Setup the paper size and orientation
		$dompdf->setPaper($_SESSION['PageSize'], 'portrait');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		$dompdf->stream($_SESSION['DatabaseName'] . '_CustomerListing_' . date('Y-m-d') . '.pdf', array(
			"Attachment" => false
		));
	} else {
		$Title = _('Customer Details Listing');
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/bank.png" title="' . _('Receipts') . '" alt="" />' . ' ' . _('Create PDF Customer Details Listing') . '</p>';
		echo $HTML;
		include ('includes/footer.php');
	}

} else {

	$Title = _('Customer Details Listing');
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' .
		 $Title . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" target="_blank">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>';
	echo '<field>
			<label for="Areas">' . _('For Sales Areas') . ':</label>
			<select name="Areas[]" multiple="multiple">';

	$SQL="SELECT areacode, areadescription FROM areas";
	$AreasResult= DB_query($SQL);

	echo '<option selected="selected" value="All">' . _('All Areas') . '</option>';

	while($MyRow = DB_fetch_array($AreasResult)) {
		echo '<option value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="SalesPeople">' . _('For Salesperson:') . '</label>
			<select name="SalesPeople[]" multiple="multiple">
				<option selected="selected" value="All">' .  _('All Salespeople') . '</option>';

	$SQL = "SELECT salesmancode, salesmanname FROM salesman";
	$SalesFolkResult = DB_query($SQL);

	while($MyRow = DB_fetch_array($SalesFolkResult)) {
		echo '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Activity">' . _('Level Of Activity'). ':</label>
			<select name="Activity">
				<option selected="selected" value="All">' .  _('All customers') . '</option>
				<option value="GreaterThan">' .  _('Sales Greater Than') . '</option>
				<option value="LessThan">' .  _('Sales Less Than') . '</option>
			</select>';

	echo '<input type="text" class="number" name="ActivityAmount" size="8" maxlength="8" value="0" />
		</field>';

	$DefaultActivitySince = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')-6,0,Date('y')));
	echo '<field>
			<label for="ActivitySince">' . _('Activity Since'). ':</label>
			<input type="date" name="ActivitySince" size="11" maxlength="10" value="' . FormatDateForSQL($DefaultActivitySince) . '" />
		</field>';

	echo '</fieldset>
			<div class="centre">
				<input type="submit" name="PrintPDF" title="PDF" value="' . _('Print PDF') . '" />
				<input type="submit" name="View" title="View" value="' . _('View') . '" />
			</div>';
    echo '</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */
