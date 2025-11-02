<?php

/*Variables required to configure this script must be set in config.php */

require(__DIR__ . '/includes/session.php');

$Title=__('FTP order to Radio Beacon');
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . __('Freight Costs') . '" alt="" />' . ' ' . $Title . '
	</p>';


/*Logic should allow entry of an order number which returns
some details of the order for confirming before producing the file for ftp */

$SQL = "SELECT salesorders.orderno,
				debtorsmaster.name,
				custbranch.brname,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.deliverto,
				salesorders.deliverydate,
				sum(salesorderdetails.linenetprice) as ordervalue,
				datepackingslipprinted,
				printedpackingslip
			FROM salesorders,
				salesorderdetails,
				debtorsmaster,
				custbranch
			WHERE salesorders.orderno = salesorderdetails.orderno
			AND salesorders.debtorno = debtorsmaster.debtorno
			AND debtorsmaster.debtorno = custbranch.debtorno
			AND salesorderdetails.completed=0
			AND salesorders.fromstkloc = '". $_SESSION['RadioBeaconStockLocation'] . "'
			GROUP BY salesorders.orderno,
				salesorders.debtorno,
				salesorders.branchcode,
				salesorders.customerref,
				salesorders.orddate,
				salesorders.deliverto";

$ErrMsg = __('No orders were returned because');
$SalesOrdersResult = DB_query($SQL, $ErrMsg);

/*show a table of the orders returned by the SQL */

echo '<table cellpadding="2" width="100%">';
$TableHeader =	'<tr>
				<th>' . __('Modify') . '</th>
				<th>' . __('Send to') . '<br />' . __('Radio Beacon') . '</th>
				<th>' . __('Customer') . '</th>
				<th>' . __('Branch') . '</th>
				<th>' . __('Cust Order') . ' #</th>
				<th>' . __('Order Date') . '</th>
				<th>' . __('Req Del Date') . '</th>
				<th>' . __('Delivery To') . '</th>
				<th>' . __('Order Total') . '</th>
				<th>' . __('Last Send') . '</th>
				</tr>';

echo $TableHeader;

$j = 1;
while ($MyRow=DB_fetch_array($SalesOrdersResult)) {

	$FTPDispatchNote = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?OrderNo=' . $MyRow['orderno'];
	$FormatedDelDate = ConvertSQLDate($MyRow['deliverydate']);
	$FormatedOrderDate = ConvertSQLDate($MyRow['orddate']);
	$FormatedOrderValue = locale_number_format($MyRow['ordervalue'],2);
	$FormatedDateLastSent = ConvertSQLDate($MyRow['datepackingslipprinted']);
	$ModifyPage = $RootPath . 'SelectOrderItems.php?ModifyOrderNumber=' . $MyRow['orderno'];

	if ($MyRow['printedpackingslip'] ==1){
		echo '<tr class="striped_row">
				<td><font size="2"><a href="', $ModifyPage, '">', $MyRow['orderno'], '</a></font></td>
				<td><font color=RED size="2">' . __('Already') . '<br />' . __('Sent') . '</font></td>
				<td><font size="2">', $MyRow['name'], '</font></td>
				<td><font size="2">', $MyRow['brname'], '</font></td>
				<td><font size="2">', $MyRow['customerref'], '</font></td>
				<td><font size="2">', $FormatedOrderDate, '</font></td>
				<td><font size="2">', $FormatedDelDate, '</font></td>
				<td><font size="2">', $MyRow['deliverto'], '</font></td>
				<td class="number"><font size="2">', $FormatedOrderValue, '</font></td>
				<td><font size="2">', $FormatedDateLastSent, '</font></td>
			</tr>';
	} else {
		echo '<tr class="striped_row">
				<td><font size="2"><a href="', $ModifyPage, '">', $MyRow['orderno'], '</a></font></td>
				<td><font size="2"><a href="', $FTPDispatchNote, '">' . __('Send') . '</a></font></td>
				<td><font size="2">', $MyRow['name'], '</font></td>
				<td><font size="2">', $MyRow['brname'], '</font></td>
				<td><font size="2">', $MyRow['customerref'], '</font></td>
				<td><font size="2">', $FormatedOrderDate, '</font></td>
				<td><font size="2">', $FormatedDelDate, '</font></td>
				<td><font size="2">', $MyRow['deliverto'], '</font></td>
				<td class="number"><font size="2">', $FormatedOrderValue, '</font></td>
				<td><font size="2">', $FormatedDateLastSent, '</font></td>
			</tr>';
	}
	$j++;
	if ($j == 12){
		$j=1;
		 echo $TableHeader;
	}
//end of page full new headings if
}
//end of while loop

echo '</table>';


if (isset($_GET['OrderNo'])){ /*An order has been selected for sending */

	if ($_SESSION['CompanyRecord']==0){
		/*CompanyRecord will be 0 if the company information could not be retrieved */
		prnMsg(__('There was a problem retrieving the company information ensure that the company record is correctly set up'),'error');
		include('includes/footer.php');
		exit();
	}

	/*Now get the order header info */

	$SQL = "SELECT salesorders.debtorno,
					customerref,
					comments,
					orddate,
					deliverydate,
					deliverto,
					deladd1,
					deladd2,
					deladd3,
					deladd4,
					deladd5,
					deladd6,
					contactphone,
					contactemail,
					name,
					address1,
					address2,
					address3,
					address4,
					address5,
					address6,
					printedpackingslip,
					datepackingslipprinted
				FROM salesorders,
					debtorsmaster
				WHERE salesorders.debtorno=debtorsmaster.debtorno
				AND salesorders.fromstkloc = '". $_SESSION['RadioBeaconStockLocation'] . "'
				AND salesorders.orderno='" . $_GET['OrderNo'] . "'";


	$ErrMsg = __('There was a problem retrieving the order header details for Order Number') . ' ' . $_GET['OrderNo'] . ' ' . __('from the database');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result)==1){ /*There is ony one order header returned */

		$MyRow = DB_fetch_array($Result);
		if ($MyRow['printedpackingslip']==1){
			prnMsg(__('Order Number') . ' ' . $_GET['OrderNo'] . ' ' . __('has previously been sent to Radio Beacon') . '. ' . __('It was sent on') . ' ' . ConvertSQLDate($MyRow['datepackingslipprinted']) . '<br />' . __('To re-send the order with the balance not previously dispatched and invoiced the order must be modified to allow a reprint (or re-send)') . '.<br />' . __('This check is there to ensure that duplication of dispatches to the customer are avoided'),'warn');
			echo '<p><a href="' . $RootPath . '/SelectOrderItems.php?ModifyOrderNumber=' . $_GET['OrderNo'] . '">' . __('Modify the order to allow a re-send or reprint') . ' (' . __('Select Delivery Details') . ')' . '</a>';
			echo '<p><a href="' . $RootPath . '/index.php">' . __('Back to the menu') . '</a>';
			include('includes/footer.php');
			exit();
		 }

		/*Now get the line items */
		$SQL = "SELECT stkcode,
						description,
						quantity,
						units,
						qtyinvoiced,
						unitprice
					FROM salesorderdetails,
						stockmaster
					WHERE salesorderdetails.stkcode=stockmaster.stockid
					AND salesorderdetails.orderno=" . $_GET['OrderNo'];

		$ErrMsg = __('There was a problem retrieving the line details for order number') . ' ' . $_GET['OrderNo'] . ' ' . __('from the database because');
		$Result = DB_query($SQL, $ErrMsg);

		if (DB_num_rows($Result)>0){
		/*Yes there are line items to start the ball rolling creating the Header record - the PHRecord*/

		/*First get the unique send id for the file name held in a separate file */
		/*Now  get the file information inorder to create the Radio Beacon format file */

			if (file_exists($FileCounter)){
				$fCounter = file($FileCounter);
				$FileNumber = intval($fCounter[0]);
				if ($FileNumber < 999){
					$FileNumber++;
				} else {
					$FileNumber =1;
				}
			} else {
				$FileNumber=1;
			}

			$fp = fopen($FileCounter,'w');
			fwrite($fp, $FileNumber);
			fclose ($fp);

			$PHRecord = 'PH^^^' . $MyRow['debtorno'] . '^' . $_GET['OrderNo'] . '^' . $FileNumber . '^' . $MyRow['customerref'] . '^^^^^';
			$PHRecord = $PHRecord . $MyRow['deliverto'] . '^' . $MyRow['deladd1'] . '^' . $MyRow['deladd2'] . '^' . $MyRow['deladd3'] . '^' . $MyRow['deladd4'] . '^' . $MyRow['deladd5'] . '^' . $MyRow['deladd6'] . '^^^^';
			$PHRecord = $PHRecord . $MyRow['contactphone'] . '^' . $MyRow['name'] . '^' . $MyRow['address1'] . '^' . $MyRow['address2'] . '^' .$MyRow['address3'] . '^' .$MyRow['address4'] . '^' .$MyRow['address5'] . '^' .$MyRow['address6'] . '^^^';
			$PHRecord = $PHRecord . $MyRow['deliverydate'] . '^^^^^^^' . $MyRow['orddate'] . '^^^^^^DX^^^^^^^^^^^^^' . $_SESSION['CompanyRecord']['coyname'] . '^' . $_SESSION['CompanyRecord']['regoffice1'] . '^' . $_SESSION['CompanyRecord']['regoffice2'] . '^';
			$PHRecord = $PHRecord . $_SESSION['CompanyRecord']['regoffice3'] . '^' . $_SESSION['CompanyRecord']['regoffice4'] . '^' . $_SESSION['CompanyRecord']['regoffice5'] . '^' . $_SESSION['CompanyRecord']['regoffice6'] . '^';
			$PHRecord = $PHRecord . '^^^^^^^N^N^^H^^^^^^' . $MyRow['deliverydate'] . '^^^^^^^' . $MyRow['contactphone'] . '^' . $MyRow['contactemail'] . '^^^^^^^^^^^^^^^^^^^^^^^^^^\n';

			$PDRec = array();
			$LineCounter =0;

			while ($MyRow2=DB_fetch_array($Result)){

				$PickQty = $MyRow2['quantity']- $MyRow2['qtyinvoiced'];
				$PDRec[$LineCounter] = 'PD^^^' . $MyRow['debtorno'] . '^' . $_GET['OrderNo'] . '^' . $FileNumber . '^^^^^^^' . $MyRow2['stkcode'] . '^^' . $MyRow2['description'] . '^1^^^' . $MyRow2['quantity'] . '^' . $PickQty . '^^^^^^^^^^^^^^DX^^^^^^^^^^^^^^1000000000^' . $MyRow['customerref'] . '^^^^^^^^^^^^^^^^^^^^^^';
				$LineCounter++;
			}

			/*the file number is used as an integer to uniquely identify multiple sendings of the order
			 for back orders dispatched later */
			if ($FileNumber<10){
				$FileNumber = '00' . $FileNumber;
			} elseif ($FileNumber <100){
				$FileNumber = '0' . $FileNumber;
			}
			$FileName = $_SESSION['RadioBeaconHomeDir'] . '/' . $FilePrefix .  $FileNumber . '.txt';
			$fp = fopen($FileName, 'w');

			fwrite($fp, $PHRecord);

			foreach ($PDRec AS $PD) {
				fwrite($fp, $PD);
			}
			fclose($fp);

			echo '<p>' . __('FTP Connection progress') . ' .....';
			// set up basic connection
			$conn_id = ftp_connect($_SESSION['RadioBeaconFTP_server']); // login with username and password
			$login_result = ftp_login($conn_id, $_SESSION['RadioBeaconFTP_user_name'], $_SESSION['RadioBeaconFTP_user_pass']); // check connection
			if ((!$conn_id) || (!$login_result)) {
				echo '<br />' . __('Ftp connection has failed');
				echo '<br />' . __('Attempted to connect to') . ' ' . $_SESSION['RadioBeaconFTP_server'] . ' ' . __('for user') . ' ' . $_SESSION['RadioBeaconFTP_user_name'];
				exit();
			} else {
				echo '<br />' . __('Connected to Radio Beacon FTP server at') . ' ' . $_SESSION['RadioBeaconFTP_server'] . ' ' . __('with user name') . ' ' . $_SESSION['RadioBeaconFTP_user_name'];
			} // upload the file
			$upload = ftp_put($conn_id, $FilePrefix .  $FileNumber . '.txt', $FileName, FTP_ASCII); // check upload status
			if (!$upload) {
				prnMsg(__('FTP upload has failed'),'success');
				exit();
			} else {
				echo '<br />' . __('Uploaded') . ' ' . $FileName . ' ' . __('to') . ' ' . $_SESSION['RadioBeaconFTP_server'];
			} // close the FTP stream
			ftp_quit($conn_id);

			/* Update the order printed flag to prevent double sendings */
			$SQL = "UPDATE salesorders
					SET printedpackingslip=1,
						datepackingslipprinted = CURRENT_DATE
					WHERE salesorders.orderno=" . $_GET['OrderNo'];
			$Result = DB_query($SQL);

			echo '<p>' . __('Order Number') . ' ' . $_GET['OrderNo'] . ' ' . __('has been sent via FTP to Radio Beacon a copy of the file that was sent is held on the server at') . '<br />' . $FileName;

		} else { /*perhaps several order headers returned or none (more likely) */

			echo '<p>' . __('The order') . ' ' . $_GET['OrderNo'] . ' ' . __('for dispatch via Radio Beacon could not be retrieved') . '. ' . __('Perhaps it is set to be dispatched from a different stock location');

		}
	} /*there are line items outstanding for dispatch */

} /*end of if page called with a OrderNo - OrderNo*/

include('includes/footer.php');
