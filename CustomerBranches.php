<?php
/* Defines the details of customer branches such as delivery address and contact details - also sales area, representative etc.*/

include('includes/session.php');
$Title = _('Customer Branches');// Screen identification.
$ViewTopic = 'AccountsReceivable';// Filename's id in ManualContents.php's TOC.
$BookMark = 'NewCustomerBranch';// Anchor's id in the manual's html document.
include('includes/header.php');
include('includes/CountriesArray.php');

if (isset($_GET['DebtorNo'])) {
	$DebtorNo = mb_strtoupper($_GET['DebtorNo']);
} else if (isset($_POST['DebtorNo'])){
	$DebtorNo = mb_strtoupper($_POST['DebtorNo']);
}

if (!isset($DebtorNo)) {
	prnMsg(_('This page must be called with the debtor code of the customer for whom you wish to edit the branches for').'.
		<br />' . _('When the pages is called from within the system this will always be the case').' <br />' .
			_('Select a customer first then select the link to add/edit/delete branches'),'warn');
	include('includes/footer.php');
	exit;
}


if (isset($_GET['SelectedBranch'])){
	$SelectedBranch = mb_strtoupper($_GET['SelectedBranch']);
} else if (isset($_POST['SelectedBranch'])){
	$SelectedBranch = mb_strtoupper($_POST['SelectedBranch']);
}

if (isset($Errors)) {
	unset($Errors);
}

	//initialise no input errors assumed initially before we test
$Errors = array();
$InputError = 0;

if (isset($_POST['submit'])) {

	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$_POST['BranchCode'] = mb_strtoupper($_POST['BranchCode']);

	if ($_SESSION['SalesmanLogin'] != '') {
		$_POST['Salesman'] = $_SESSION['SalesmanLogin'];
	}
	if (ContainsIllegalCharacters($_POST['BranchCode']) OR mb_strstr($_POST['BranchCode'],' ')) {
		$InputError = 1;
		prnMsg(_('The Branch code cannot contain any of the following characters')." - &amp; \' &lt; &gt;",'error');
		$Errors[$i] = 'BranchCode';
		$i++;
	}
	if (mb_strlen($_POST['BranchCode'])==0) {
		$InputError = 1;
		prnMsg(_('The Branch code must be at least one character long'),'error');
		$Errors[$i] = 'BranchCode';
		$i++;
	}
	if (!is_numeric($_POST['FwdDate'])) {
		$InputError = 1;
		prnMsg(_('The date after which invoices are charged to the following month is expected to be a number and a recognised number has not been entered'),'error');
		$Errors[$i] = 'FwdDate';
		$i++;
	}
	if ($_POST['FwdDate'] >30) {
		$InputError = 1;
		prnMsg(_('The date (in the month) after which invoices are charged to the following month should be a number less than 31'),'error');
		$Errors[$i] = 'FwdDate';
		$i++;
	}
	if (!is_numeric(filter_number_format($_POST['EstDeliveryDays']))) {
		$InputError = 1;
		prnMsg(_('The estimated delivery days is expected to be a number and a recognised number has not been entered'),'error');
		$Errors[$i] = 'EstDeliveryDays';
		$i++;
	}
	if (filter_number_format($_POST['EstDeliveryDays']) >60) {
		$InputError = 1;
		prnMsg(_('The estimated delivery days should be a number of days less than 60') . '. ' . _('A package can be delivered by seafreight anywhere in the world normally in less than 60 days'),'error');
		$Errors[$i] = 'EstDeliveryDays';
		$i++;
	}
	if (!isset($_POST['EstDeliveryDays'])) {
		$_POST['EstDeliveryDays']=1;
	}
	if (!isset($Latitude)) {
		$Latitude=0.0;
		$Longitude=0.0;
	}
	if ($_SESSION['geocode_integration']==1 ){
		// Get the lat/long from our geocoding host
		$SQL = "SELECT * FROM geocode_param WHERE 1";
		$ErrMsg = _('An error occurred in retrieving the information');
		$Resultgeo = DB_query($SQL, $ErrMsg);
		$row = DB_fetch_array($Resultgeo);
		$api_key = $row['geocode_key'];
		$map_host = $row['map_host'];
		define('MAPS_HOST', $map_host);
		define('KEY', $api_key);
		if ($map_host=="") {
		// check that some sane values are setup already in geocode tables, if not skip the geocoding but add the record anyway.
			echo '<div class="warn">' . _('Warning - Geocode Integration is enabled, but no hosts are setup. Go to Geocode Setup') . '</div>';
		} else {
			$address = urlencode($_POST['BrAddress1'] . ', ' . $_POST['BrAddress2'] . ', ' . $_POST['BrAddress3'] . ', ' . $_POST['BrAddress4']);
			$base_url = "https://" . MAPS_HOST . "/maps/api/geocode/xml?address=";
			$request_url = $base_url . $address . '&key=' . KEY . '&sensor=true';
			$xml = simplexml_load_string(utf8_encode(file_get_contents($request_url))) or die('url not loading');

			$Status = $xml->status;
			if (strcmp($Status, 'OK') == 0) {
				// Successful geocode
				$Geocode_Pending = false;
				// Format: Longitude, Latitude, Altitude
				$Latitude = $xml->result->geometry->location->lat;
				$Longitude = $xml->result->geometry->location->lng;
			} else {
				// failure to geocode
				$Geocode_Pending = false;
				echo '<div class="page_help_text"><b>' . _('Geocode Notice') . ':</b> ' . _('Address') . ': ' . $address . ' ' . _('failed to geocode');
				echo _('Received status') . ' ' . $Status . '</div>';
			}
		}
	}
	if (isset($SelectedBranch) AND $InputError !=1) {

		/*SelectedBranch could also exist if submit had not been clicked this code would not run in this case cos submit is false of course see the 	delete code below*/

		$SQL = "UPDATE custbranch SET brname = '" . $_POST['BrName'] . "',
						braddress1 = '" . $_POST['BrAddress1'] . "',
						braddress2 = '" . $_POST['BrAddress2'] . "',
						braddress3 = '" . $_POST['BrAddress3'] . "',
						braddress4 = '" . $_POST['BrAddress4'] . "',
						braddress5 = '" . $_POST['BrAddress5'] . "',
						braddress6 = '" . $_POST['BrAddress6'] . "',
						lat = '" . $Latitude . "',
						lng = '" . $Longitude . "',
						specialinstructions = '" . $_POST['SpecialInstructions'] . "',
						phoneno='" . $_POST['PhoneNo'] . "',
						faxno='" . $_POST['FaxNo'] . "',
						fwddate= '" . $_POST['FwdDate'] . "',
						contactname='" . $_POST['ContactName'] . "',
						salesman= '" . $_POST['Salesman'] . "',
						area='" . $_POST['Area'] . "',
						estdeliverydays ='" . filter_number_format($_POST['EstDeliveryDays']) . "',
						email='" . $_POST['Email'] . "',
						taxgroupid='" . $_POST['TaxGroup'] . "',
						defaultlocation='" . $_POST['DefaultLocation'] . "',
						brpostaddr1 = '" . $_POST['BrPostAddr1'] . "',
						brpostaddr2 = '" . $_POST['BrPostAddr2'] . "',
						brpostaddr3 = '" . $_POST['BrPostAddr3'] . "',
						brpostaddr4 = '" . $_POST['BrPostAddr4'] . "',
						brpostaddr5 = '" . $_POST['BrPostAddr5'] . "',
						disabletrans='" . $_POST['DisableTrans'] . "',
						defaultshipvia='" . $_POST['DefaultShipVia'] . "',
						custbranchcode='" . $_POST['CustBranchCode'] ."',
						deliverblind='" . $_POST['DeliverBlind'] . "'
					WHERE branchcode = '".$SelectedBranch."' AND debtorno='".$DebtorNo."'";

		if ($_SESSION['SalesmanLogin'] != '') {
			$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
		}

		$msg = $_POST['BrName'] . ' '._('branch has been updated.');

	} else if ($InputError !=1) {

	/*Selected branch is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new Customer Branches form */

		$SQL = "INSERT INTO custbranch (branchcode,
						debtorno,
						brname,
						braddress1,
						braddress2,
						braddress3,
						braddress4,
						braddress5,
						braddress6,
						lat,
						lng,
 						specialinstructions,
						estdeliverydays,
						fwddate,
						salesman,
						phoneno,
						faxno,
						contactname,
						area,
						email,
						taxgroupid,
						defaultlocation,
						brpostaddr1,
						brpostaddr2,
						brpostaddr3,
						brpostaddr4,
						brpostaddr5,
						disabletrans,
						defaultshipvia,
						custbranchcode,
						deliverblind)
				VALUES ('" . $_POST['BranchCode'] . "',
					'" . $DebtorNo . "',
					'" . $_POST['BrName'] . "',
					'" . $_POST['BrAddress1'] . "',
					'" . $_POST['BrAddress2'] . "',
					'" . $_POST['BrAddress3'] . "',
					'" . $_POST['BrAddress4'] . "',
					'" . $_POST['BrAddress5'] . "',
					'" . $_POST['BrAddress6'] . "',
					'" . $Latitude . "',
					'" . $Longitude . "',
					'" . $_POST['SpecialInstructions'] . "',
					'" . filter_number_format($_POST['EstDeliveryDays']) . "',
					'" . $_POST['FwdDate'] . "',
					'" . $_POST['Salesman'] . "',
					'" . $_POST['PhoneNo'] . "',
					'" . $_POST['FaxNo'] . "',
					'" . $_POST['ContactName'] . "',
					'" . $_POST['Area'] . "',
					'" . $_POST['Email'] . "',
					'" . $_POST['TaxGroup'] . "',
					'" . $_POST['DefaultLocation'] . "',
					'" . $_POST['BrPostAddr1'] . "',
					'" . $_POST['BrPostAddr2'] . "',
					'" . $_POST['BrPostAddr3'] . "',
					'" . $_POST['BrPostAddr4'] . "',
					'" . $_POST['BrPostAddr5'] . "',
					'" . $_POST['DisableTrans'] . "',
					'" . $_POST['DefaultShipVia'] . "',
					'" . $_POST['CustBranchCode'] ."',
					'" . $_POST['DeliverBlind'] . "')";
	}
	echo '<br />';
	$msg = _('Customer branch') . '<b> ' . $_POST['BranchCode'] . ': ' . $_POST['BrName'] . ' </b>' . _('has been added, add another branch, or return to the') . ' <a href="index.php">' . _('Main Menu') . '</a>';

	//run the SQL from either of the above possibilites

	$ErrMsg = _('The branch record could not be inserted or updated because');
	if ($InputError==0) {
		$Result = DB_query($SQL, $ErrMsg);
	}

	if (DB_error_no() ==0 AND $InputError==0) {
		prnMsg($msg,'success');
		unset($_POST['BranchCode']);
		unset($_POST['BrName']);
		unset($_POST['BrAddress1']);
		unset($_POST['BrAddress2']);
		unset($_POST['BrAddress3']);
		unset($_POST['BrAddress4']);
		unset($_POST['BrAddress5']);
		unset($_POST['BrAddress6']);
		unset($_POST['SpecialInstructions']);
		unset($_POST['EstDeliveryDays']);
		unset($_POST['FwdDate']);
		unset($_POST['Salesman']);
		unset($_POST['PhoneNo']);
		unset($_POST['FaxNo']);
		unset($_POST['ContactName']);
		unset($_POST['Area']);
		unset($_POST['Email']);
		unset($_POST['TaxGroup']);
		unset($_POST['DefaultLocation']);
		unset($_POST['DisableTrans']);
		unset($_POST['BrPostAddr1']);
		unset($_POST['BrPostAddr2']);
		unset($_POST['BrPostAddr3']);
		unset($_POST['BrPostAddr4']);
		unset($_POST['BrPostAddr5']);
		unset($_POST['DefaultShipVia']);
		unset($_POST['CustBranchCode']);
		unset($_POST['DeliverBlind']);
		unset($SelectedBranch);
	}
} else if (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'DebtorTrans'

	$SQL= "SELECT COUNT(*) FROM debtortrans WHERE debtortrans.branchcode='".$SelectedBranch."' AND debtorno = '".$DebtorNo."'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if ($MyRow[0]>0) {
		prnMsg(_('Cannot delete this branch because customer transactions have been created to this branch') . '<br />' .
			 _('There are').' ' . $MyRow[0] . ' '._('transactions with this Branch Code'),'error');

	} else {
		$SQL= "SELECT COUNT(*) FROM salesanalysis WHERE salesanalysis.custbranch='".$SelectedBranch."' AND salesanalysis.cust = '".$DebtorNo."'";

		$Result = DB_query($SQL);

		$MyRow = DB_fetch_row($Result);
		if ($MyRow[0]>0) {
			prnMsg(_('Cannot delete this branch because sales analysis records exist for it'),'error');
			echo '<br />' . _('There are').' ' . $MyRow[0] . ' '._('sales analysis records with this Branch Code/customer');

		} else {

			$SQL= "SELECT COUNT(*) FROM salesorders WHERE salesorders.branchcode='".$SelectedBranch."' AND salesorders.debtorno = '".$DebtorNo."'";
			$Result = DB_query($SQL);

			$MyRow = DB_fetch_row($Result);
			if ($MyRow[0]>0) {
				prnMsg(_('Cannot delete this branch because sales orders exist for it') . '. ' . _('Purge old sales orders first'),'warn');
				echo '<br />' . _('There are').' ' . $MyRow[0] . ' '._('sales orders for this Branch/customer');
			} else {
				// Check if there are any users that refer to this branch code
				$SQL= "SELECT COUNT(*) FROM www_users WHERE www_users.branchcode='".$SelectedBranch."' AND www_users.customerid = '".$DebtorNo."'";

				$Result = DB_query($SQL);
				$MyRow = DB_fetch_row($Result);

				if ($MyRow[0]>0) {
					prnMsg(_('Cannot delete this branch because users exist that refer to it') . '. ' . _('Purge old users first'),'warn');
					echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' '._('users referring to this Branch/customer');
				} else {
						// Check if there are any contract that refer to this branch code
					$SQL = "SELECT COUNT(*) FROM contracts WHERE contracts.branchcode='" . $SelectedBranch . "' AND contracts.debtorno = '" . $DebtorNo . "'";

					$Result = DB_query($SQL);
					$MyRow = DB_fetch_row($Result);

					if ($MyRow[0]>0) {
						prnMsg(_('Cannot delete this branch because contract have been created that refer to it') . '. ' . _('Purge old contracts first'),'warn');
						echo '<br />' . _('There are') . ' ' . $MyRow[0] . ' '._('contracts referring to this branch/customer');
					} else {
						//check if this it the last customer branch - don't allow deletion of the last branch
						$SQL = "SELECT COUNT(*) FROM custbranch WHERE debtorno='" . $DebtorNo . "'";

						$Result = DB_query($SQL);
						$MyRow = DB_fetch_row($Result);

						if ($MyRow[0]==1) {
							prnMsg(_('Cannot delete this branch because it is the only branch defined for this customer.'),'warn');
						} else {
							$SQL="DELETE FROM custbranch WHERE branchcode='" . $SelectedBranch . "' AND debtorno='" . $DebtorNo . "'";
							if ($_SESSION['SalesmanLogin'] != '') {
								$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
							}
							$ErrMsg = _('The branch record could not be deleted') . ' - ' . _('the SQL server returned the following message');
							$Result = DB_query($SQL,$ErrMsg);
							if (DB_error_no()==0){
								prnMsg(_('Branch Deleted'),'success');
							}
						}
					}
				}
			}
		}
	}//end ifs to test if the branch can be deleted

}
if (!isset($SelectedBranch)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedBranch will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of branches will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$SQL = "SELECT debtorsmaster.name,
					custbranch.branchcode,
					brname,
					salesman.salesmanname,
					areas.areadescription,
					contactname,
					phoneno,
					faxno,
					custbranch.email,
					taxgroups.taxgroupdescription,
					custbranch.disabletrans
				FROM custbranch INNER JOIN debtorsmaster
				ON custbranch.debtorno=debtorsmaster.debtorno
				INNER JOIN areas
				ON custbranch.area=areas.areacode
				INNER JOIN salesman
				ON custbranch.salesman=salesman.salesmancode
				INNER JOIN taxgroups
				ON custbranch.taxgroupid=taxgroups.taxgroupid
				WHERE custbranch.debtorno = '".$DebtorNo."'";

	if ($_SESSION['SalesmanLogin'] != '') {
		$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
	}

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	$TotalEnable = 0;
	$TotalDisable = 0;
	if ($MyRow) {
		echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
			'/images/customer.png" title="',// Icon image.
			_('Customer'), '" /> ',// Icon title.
			_('Branches defined for'), ' ', $DebtorNo, ' - ', $MyRow[0], '</p>';// Page title.
		echo '<table class="selection">
			<thead>
			<tr>
				<th class="SortedColumn">' . _('Code') . '</th>
				<th class="SortedColumn">' . _('Name') . '</th>
				<th class="SortedColumn">' . _('Branch Contact') . '</th>
				<th class="SortedColumn">' . _('Salesman') . '</th>
				<th class="SortedColumn">' . _('Area') . '</th>
				<th class="SortedColumn">' . _('Phone No') . '</th>
				<th class="SortedColumn">' . _('Fax No') . '</th>
				<th class="SortedColumn">' . _('Email') . '</th>
				<th class="SortedColumn">' . _('Tax Group') . '</th>
				<th class="SortedColumn">' . _('Enabled?') . '</th>
				</tr>
			</thead>
			<tbody>';

		do {

			printf('<tr class="striped_row">
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="Mailto:%s">%s</a></td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%s?DebtorNo=%s&amp;SelectedBranch=%s">%s</a></td>
				<td><a href="%s?DebtorNo=%s&amp;SelectedBranch=%s&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this branch?') . '");\'>%s</a></td></tr>',
				$MyRow[1],
				$MyRow[2],
				$MyRow[5],
				$MyRow[3],
				$MyRow[4],
				$MyRow[6],
				$MyRow[7],
				$MyRow[8],
				$MyRow[8],
				$MyRow[9],
				($MyRow[10]?_('No'):_('Yes')),
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
				$DebtorNo,
				urlencode($MyRow[1]),
				_('Edit'),
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'),
				$DebtorNo,
				urlencode($MyRow[1]),
				_('Delete Branch'));

			if ($MyRow[10]){
				$TotalDisable++;
			} else {
				$TotalEnable++;
			}
		} while ($MyRow = DB_fetch_row($Result));
		//END WHILE LIST LOOP

		echo '</tbody>
			</table>
			<table class="selection">
			<tr>
				<td><div class="centre">';
		echo '<b>' . $TotalEnable . '</b> ' . _('Branches are enabled.') . '<br />';
		echo '<b>' . $TotalDisable . '</b> ' . _('Branches are disabled.') . '<br />';
		echo '<b>' . ($TotalEnable+$TotalDisable). '</b> ' . _('Total Branches') . '</div></td>
			</tr>
			</table>';
	} else {
		$SQL = "SELECT debtorsmaster.name,
						address1,
						address2,
						address3,
						address4,
						address5,
						address6
					FROM debtorsmaster
					WHERE debtorno = '".$DebtorNo."'";

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		echo '<div class="page_help_text">' . _('No Branches are defined for').' - '.$MyRow[0]. '. ' . _('You must have a minimum of one branch for each Customer. Please add a branch now.') . '</div>';
		$_POST['BranchCode'] = mb_substr($DebtorNo,0,10);
		$_POST['BrName'] = $MyRow[0];
		$_POST['BrAddress1'] = $MyRow[1];
		$_POST['BrAddress2'] = $MyRow[2];
		$_POST['BrAddress3'] = $MyRow[3];
		$_POST['BrAddress4'] = $MyRow[4];
		$_POST['BrAddress5'] = $MyRow[5];
		$_POST['BrAddress6'] = $MyRow[6];
		unset($MyRow);
	}
}

if (!isset($_GET['delete'])) {
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedBranch)) {
		//editing an existing branch

		$SQL = "SELECT branchcode,
						brname,
						braddress1,
						braddress2,
						braddress3,
						braddress4,
						braddress5,
						braddress6,
						specialinstructions,
						estdeliverydays,
						fwddate,
						salesman,
						area,
						phoneno,
						faxno,
						contactname,
						email,
						taxgroupid,
						defaultlocation,
						brpostaddr1,
						brpostaddr2,
						brpostaddr3,
						brpostaddr4,
						brpostaddr5,
						disabletrans,
						defaultshipvia,
						custbranchcode,
						deliverblind
					FROM custbranch
					WHERE branchcode='".$SelectedBranch."'
					AND debtorno='".$DebtorNo."'";

		if ($_SESSION['SalesmanLogin'] != '') {
			$SQL .= " AND custbranch.salesman='" . $_SESSION['SalesmanLogin'] . "'";
		}

		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		if ($InputError==0) {
			$_POST['BranchCode'] = $MyRow['branchcode'];
			$_POST['BrName'] = $MyRow['brname'];
			$_POST['BrAddress1'] = $MyRow['braddress1'];
			$_POST['BrAddress2'] = $MyRow['braddress2'];
			$_POST['BrAddress3'] = $MyRow['braddress3'];
			$_POST['BrAddress4'] = $MyRow['braddress4'];
			$_POST['BrAddress5'] = $MyRow['braddress5'];
			$_POST['BrAddress6'] = $MyRow['braddress6'];
			$_POST['SpecialInstructions'] = $MyRow['specialinstructions'];
			$_POST['BrPostAddr1'] = $MyRow['brpostaddr1'];
			$_POST['BrPostAddr2'] = $MyRow['brpostaddr2'];
			$_POST['BrPostAddr3'] = $MyRow['brpostaddr3'];
			$_POST['BrPostAddr4'] = $MyRow['brpostaddr4'];
			$_POST['BrPostAddr5'] = $MyRow['brpostaddr5'];
			$_POST['EstDeliveryDays'] = locale_number_format($MyRow['estdeliverydays'],0);
			$_POST['FwdDate'] =$MyRow['fwddate'];
			$_POST['ContactName'] = $MyRow['contactname'];
			$_POST['Salesman'] =$MyRow['salesman'];
			$_POST['Area'] =$MyRow['area'];
			$_POST['PhoneNo'] =$MyRow['phoneno'];
			$_POST['FaxNo'] =$MyRow['faxno'];
			$_POST['Email'] =$MyRow['email'];
			$_POST['TaxGroup'] = $MyRow['taxgroupid'];
			$_POST['DisableTrans'] = $MyRow['disabletrans'];
			$_POST['DefaultLocation'] = $MyRow['defaultlocation'];
			$_POST['DefaultShipVia'] = $MyRow['defaultshipvia'];
			$_POST['CustBranchCode'] = $MyRow['custbranchcode'];
			$_POST['DeliverBlind'] = $MyRow['deliverblind'];
		}

		echo '<input type="hidden" name="SelectedBranch" value="' . $SelectedBranch . '" />';
		echo '<input type="hidden" name="BranchCode" value="' . $_POST['BranchCode'] . '" />';

		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Customer') . '" alt="" />
				 ' . ' ' . _('Change Details for Branch'). ' '. $SelectedBranch . '</p>';
		if (isset($SelectedBranch)) {
			echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?DebtorNo=' . $DebtorNo. '">' . _('Show all branches defined for'). ' '. $DebtorNo . '</a></div>';
		}
		echo '<fieldset>
				<legend>', _('Edit Branch Details'), '</legend>
				<field>
					<label for="BranchCode">' . _('Branch Code').':</label>
					<fieldtext>' . $_POST['BranchCode'] . '</fieldtext>
				</field>';

	} else {//end of if $SelectedBranch only do the else when a new record is being entered

	/* SETUP ANY $_GET VALUES THAT ARE PASSED. This really is just used coming from the Customers.php when a new customer is created.
			Maybe should only do this when that page is the referrer?
	*/
		if (isset($_GET['BranchCode'])){
			$SQL="SELECT name,
						address1,
						address2,
						address3,
						address4,
						address5,
						address6
					FROM
					debtorsmaster
					WHERE debtorno='".$_GET['BranchCode']."'";
			$Result = DB_query($SQL);
			$MyRow = DB_fetch_array($Result);
			$_POST['BranchCode'] = $_GET['BranchCode'];
			$_POST['BrName'] = $MyRow['name'];
		 	$_POST['BrAddress1'] = $MyRow['addrsss1'];
			$_POST['BrAddress2'] = $MyRow['addrsss2'];
			$_POST['BrAddress3'] = $MyRow['addrsss3'];
		 	$_POST['BrAddress4'] = $MyRow['addrsss4'];
			$_POST['BrAddress5'] = $MyRow['addrsss5'];
			$_POST['BrAddress6'] = $MyRow['addrsss6'];
		}
		if (!isset($_POST['BranchCode'])) {
			$_POST['BranchCode']='';
		}
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Customer') . '" alt="" />' . ' ' . _('Add a Branch') . '</p>';
		echo '<fieldset>
				<legend>', _('Create New Branch Details'), '</legend>
				<field>
					<label for="BranchCode">' . _('Branch Code'). ':</label>
					<input data-type="no-illegal-chars" ' . (in_array('BranchCode',$Errors) ? 'class="inputerror"' : '' ) . ' tabindex="1" type="text" name="BranchCode" required="required" title ="" placeholder="'._('alpha-numeric').'" size="12" maxlength="10" value="' . $_POST['BranchCode'] . '" />
					<fieldhelp>'._('Up to 10 characters for the branch code. The following characters are prohibited:') . ' \' &quot; + . &amp; \\ &gt; &lt;</fieldhelp>
				</field>';
		$_POST['DeliverBlind'] = $_SESSION['DefaultBlindPackNote'];
	}

	echo '<input type="hidden" name="DebtorNo" value="'. $DebtorNo . '" />';

	echo '<field>
			<label for="BrName">', _('Branch Name').':</label>';
	if (!isset($_POST['BrName'])) {$_POST['BrName']='';}
	echo '<input tabindex="2" type="text" autofocus="autofocus" required="required" name="BrName" title="" minlength="5" size="41" maxlength="40" value="'. $_POST['BrName'].'" />
		<fieldhelp>' . _('The branch name should identify the particular delivery address of the customer and must be entered') . '</fieldhelp>
	</field>';

	echo '<field>
			<label for="ContactName">' . _('Branch Contact').':</label>';
	if (!isset($_POST['ContactName'])) {$_POST['ContactName']='';}
	echo '<input tabindex="3" type="text" name="ContactName" required="required" size="41" maxlength="40" value="'. $_POST['ContactName'].'" />
		</field>';

	echo '<field>
			<label for="BrAddress1">' . _('Street Address 1 (Street)').':</label>';
	if (!isset($_POST['BrAddress1'])) {
		$_POST['BrAddress1']='';
	}
	echo '<input tabindex="4" type="text" name="BrAddress1" size="41" maxlength="40" value="'. $_POST['BrAddress1'].'" />
		</field>';

	echo '<field>
			<label for="BrAddress2">' . _('Street Address 2 (Street)').':</label>';
	if (!isset($_POST['BrAddress2'])) {
		$_POST['BrAddress2']='';
	}
	echo '<input tabindex="5" type="text" name="BrAddress2" size="41" maxlength="40" value="'. $_POST['BrAddress2'].'" />
		</field>';

	echo '<field>
			<label for="BrAddress3">' . _('Street Address 3 (Suburb/City)').':</label>';
	if (!isset($_POST['BrAddress3'])) {
		$_POST['BrAddress3']='';
	}
	echo '<input tabindex="6" type="text" name="BrAddress3" size="41" maxlength="40" value="'. $_POST['BrAddress3'].'" />
		</field>';

	echo '<field>
			<label for="BrAddress4">' . _('Street Address 4 (State/Province)').':</label>';
	if (!isset($_POST['BrAddress4'])) {
		$_POST['BrAddress4']='';
	}
	echo '<input tabindex="7" type="text" name="BrAddress4" size="51" maxlength="50" value="'. $_POST['BrAddress4'].'" />
		</field>';

	echo '<field>
			<label for="BrAddress5">' . _('Street Address 5 (Postal Code)').':</label>';
	if (!isset($_POST['BrAddress5'])) {
		$_POST['BrAddress5']='';
	}
	echo '<input tabindex="8" type="text" name="BrAddress5" size="21" maxlength="20" value="'. $_POST['BrAddress5'].'" />
		</field>';

	echo '<field>
			<label for="BrAddress6">' . _('Country').':</label>';
	if (!isset($_POST['BrAddress6'])) {
		$_POST['BrAddress6']='';
	}
	echo '<select name="BrAddress6">';
	foreach ($CountriesArray as $CountryEntry => $CountryName){
		if (isset($_POST['BrAddress6']) AND ($_POST['BrAddress6'] == $CountryName)) {
			echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
		} elseif (!isset($_POST['BrAddress6']) AND $CountryName == "") {
			echo '<option selected="selected" value="' . $CountryName . '">' . $CountryName . '</option>';
		} else {
			echo '<option value="' . $CountryName . '">' . $CountryName . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="SpecialInstructions">' . _('Special Instructions').':</label>';
	if (!isset($_POST['SpecialInstructions'])) {
		$_POST['SpecialInstructions']='';
	}
	echo '<input tabindex="10" type="text" name="SpecialInstructions" size="56" value="'. $_POST['SpecialInstructions'].'" />
		</field>';

	echo '<field>
			<label for=EstDeliveryDays">' . _('Default days to deliver').':</label>';
	if (!isset($_POST['EstDeliveryDays'])) {
		$_POST['EstDeliveryDays']=0;
	}
	echo '<input ' .(in_array('EstDeliveryDays',$Errors) ? 'class="inputerror"' : '' ) .' tabindex="11" type="text" class="integer" name="EstDeliveryDays" size="4" maxlength="2" value="'. $_POST['EstDeliveryDays'].'" />
		</field>';

	echo '<field>
			<label for="FwdDate">' . _('Forward Date After (day in month)').':</label>';
	if (!isset($_POST['FwdDate'])) {
		$_POST['FwdDate']=0;
	}
	echo '<input ' .(in_array('FwdDate',$Errors) ? 'class="inputerror"' : '' ) .' tabindex="12" class="integer" name="FwdDate" size="4" maxlength="2" value="'. $_POST['FwdDate'].'" />
		</field>';

	if ($_SESSION['SalesmanLogin'] != '') {
		echo '<field>
				<label for="Salesman">' . _('Salesperson').':</label>
				<fieldtext>', $_SESSION['UsersRealName'], '</fieldtext>
			</field>';
	} else {

		//SQL to poulate account selection boxes
		$SQL = "SELECT salesmanname,
						salesmancode
				FROM salesman
				WHERE current = 1
				ORDER BY salesmanname";

		$Result = DB_query($SQL);

		if (DB_num_rows($Result)==0){
			echo '</fieldset>';
			prnMsg(_('There are no sales people defined as yet') . ' - ' . _('customer branches must be allocated to a sales person') . '. ' . _('Please use the link below to define at least one sales person'),'error');
			echo '<p align="center"><a href="' . $RootPath . '/SalesPeople.php">' . _('Define Sales People') . '</a>';
			include('includes/footer.php');
			exit;
		}

		echo '<field>
				<label for="Salesman">' . _('Salesperson').':</label>
				<select tabindex="13" name="Salesman">';
		while ($MyRow = DB_fetch_array($Result)) {
			if (isset($_POST['Salesman']) AND $MyRow['salesmancode']==$_POST['Salesman']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';

		}//end while loop
		echo '</select>
			</field>';

	//	DB_data_seek($Result,0);//by thumb
	}
	$SQL = "SELECT areacode, areadescription FROM areas ORDER BY areadescription";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0){
		echo '</fieldset>';
		prnMsg(_('There are no areas defined as yet') . ' - ' . _('customer branches must be allocated to an area') . '. ' . _('Please use the link below to define at least one sales area'),'error');
		echo '<br /><a href="' . $RootPath. '/Areas.php">' . _('Define Sales Areas') . '</a>';
		include('includes/footer.php');
		exit;
	}

	echo '<field>
			<label for="Area">' . _('Sales Area').':</label>
			<select tabindex="14" name="Area">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['Area']) AND $MyRow['areacode']==$_POST['Area']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';

	}//end while loop
	echo '</select>
		</field>';
	DB_data_seek($Result,0);

	$SQL = "SELECT locations.loccode,
					locationname
			FROM locations
			INNER JOIN locationusers
			ON locationusers.loccode=locations.loccode
				AND locationusers.userid='" . $_SESSION['UserID'] . "'
				AND locationusers.canupd=1
			WHERE locations.allowinvoicing='1'
			ORDER BY locationname";
	$Result = DB_query($SQL);

	if (DB_num_rows($Result)==0){
		echo '</table>';
		prnMsg(_('There are no stock locations defined for which this user has access to as yet') . ' - ' . _('customer branches must refer to a default location where stock is normally drawn from') . '. ' . _('Please use the link below to define at least one stock location'),'error');
		echo '<br /><a href="', $RootPath, '/Locations.php">', _('Define Stock Locations'), '</a>';
		include('includes/footer.php');
		exit;
	}

	echo '<field>
			<label for="DefaultLocation">', _('Draw Stock From'), ':</label>
			<select name="DefaultLocation" tabindex="15">';
	while ($MyRow = DB_fetch_array($Result)) {
		if (isset($_POST['DefaultLocation']) AND $MyRow['loccode']==$_POST['DefaultLocation']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['loccode'], '">', $MyRow['locationname'], '</option>';

	}// End while loop.
	echo '</select>
		</field>';

	echo '<field>
			<label for="PhoneNo">' . _('Phone Number').':</label>';
	if (!isset($_POST['PhoneNo'])) {
		$_POST['PhoneNo']='';
	}
	echo '<input tabindex="16" type="tel" name="PhoneNo" pattern="[0-9+()\s-]*" size="22" maxlength="20" value="'. $_POST['PhoneNo'].'" />
		</field>';

	echo '<field>
			<label for="FaxNo">' . _('Fax Number').':</label>';
	if (!isset($_POST['FaxNo'])) {
		$_POST['FaxNo']='';
	}
	echo '<input tabindex="17" type="tel" name="FaxNo" pattern="[0-9+()\s-]*" size="22" maxlength="20" value="'. $_POST['FaxNo'].'" />
		</field>';

	if (!isset($_POST['Email'])) {
		$_POST['Email']='';
	}
	echo '<field>
			<label for="Email">' . (($_POST['Email']) ? '<a href="Mailto:'.$_POST['Email'].'">' . _('Email').':</a>' : _('Email').':') . '</label>';
	//only display email link if there is an email address
	echo '<input tabindex="18" type="email" name="Email" placeholder="e.g. example@domain.com" size="56" maxlength="55" value="'. $_POST['Email'].'" />
		</field>';

	DB_data_seek($Result,0);

	$SQL = "SELECT taxgroupid, taxgroupdescription FROM taxgroups";
	$TaxGroupResults = DB_query($SQL);
	if (DB_num_rows($TaxGroupResults)==0){
		echo '</fieldset>';
		prnMsg(_('There are no tax groups defined - these must be set up first before any branches can be set up') . '
				<br /><a href="' . $RootPath . '/TaxGroups.php">' . _('Define Tax Groups') . '</a>','error');
		include('includes/footer.php');
		exit;
	}
	echo '<field>
			<label for="TaxGroup">' . _('Tax Group').':</label>
			<select tabindex="19" name="TaxGroup">';

	while ($MyRow = DB_fetch_array($TaxGroupResults)) {
		if (isset($_POST['TaxGroup']) AND $MyRow['taxgroupid']==$_POST['TaxGroup']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $MyRow['taxgroupid'] . '">' . $MyRow['taxgroupdescription'] . '</option>';

	}//end while loop

	echo '</select>
		</field>';

	echo '<field>
			<label for="DisableTrans">' . _('Transactions on this branch') . ':</label>
			<select tabindex="20" name="DisableTrans">';
	if (!isset($_POST['DisableTrans']) OR $_POST['DisableTrans']==0){
		echo '<option selected="selected" value="0">' . _('Enabled') . '</option>
				<option value="1">' . _('Disabled') . '</option>';
	} else {
		echo '<option selected="selected" value="1">' . _('Disabled') . '</option>
				<option value="0">' . _('Enabled') . '</option>';
	}
	echo '</select>
		</field>';

	$SQL = "SELECT shipper_id, shippername FROM shippers";
	$ShipperResults = DB_query($SQL);
	if (DB_num_rows($ShipperResults)==0){
		echo '</fieldset>';
		prnMsg(_('There are no shippers defined - these must be set up first before any branches can be set up') . '
				<br /><a href="' . $RootPath . '/Shippers.php">' . _('Define Shippers') . '</a>','error');
		include('includes/footer.php');
		exit;
	}
	echo '<field>
			<label for="DefaultShipVia">' . _('Default freight/shipper method') . ':</label>
			<select tabindex="21" name="DefaultShipVia">';
	while ($MyRow=DB_fetch_array($ShipperResults)){
		if ((isset($_POST['DefaultShipVia'])and $MyRow['shipper_id']==$_POST['DefaultShipVia']) OR ($_SESSION['Default_Shipper'] == $MyRow['shipper_id'])) {
			echo '<option selected="selected" value="' . $MyRow['shipper_id'] . '">' . $MyRow['shippername'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['shipper_id'] . '">' . $MyRow['shippername'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	/* This field is a default value that will be used to set the value
	on the sales order which will control whether or not to display the
	company logo and address on the packlist */
	echo '<field>
			<label for="DeliverBlind">' . _('Default Packlist') . ':</label>
			<select tabindex="22" name="DeliverBlind">';
	if ($_POST['DeliverBlind']==2){
		echo '<option value="1">' . _('Show company details and logo') . '</option>
				<option selected="selected" value="2">' . _('Hide company details and logo') . '</option>';
	} else {
		echo '<option selected="selected" value="1">' . _('Show company details and logo') . '</option>
				<option value="2">' . _('Hide company details and logo') . '</option>';
	}
	echo '</select>
		</field>';

	if (!isset($_POST['BrPostAddr1'])) {// Postal address, line 1. Database: custbranch.brpostaddr1, varchar(40)
		$_POST['BrPostAddr1']='';
	}
	echo '<field>
			<label for="BrPostAddr1">' . _('Postal Address 1 (Street)') . ':</label>
			<input maxlength="40" name="BrPostAddr1" size="41" tabindex="23" type="text" value="', $_POST['BrPostAddr1'].'" />
		</field>';

	if (!isset($_POST['BrPostAddr2'])){// Postal address, line 2. Database: custbranch.brpostaddr2, varchar(40)
		$_POST['BrPostAddr2']='';
	}
	echo '<field>
			<label for="BrPostAddr2">' , _('Postal Address 2 (Suburb/City)'), ':</label>
			<input maxlength="40" name="BrPostAddr2" size="41" tabindex="24" type="text" value="', $_POST['BrPostAddr2'].'" />
		</field>';

	if (!isset($_POST['BrPostAddr3'])) {// Postal address, line 3. Database: custbranch.brpostaddr3, varchar(40)
		$_POST['BrPostAddr3']='';
	}
	echo '<field>
			<label for="BrPostAddr3">', _('Postal Address 3 (State)'), ':</label>
			<input maxlength="40" name="BrPostAddr3" size="41" tabindex="25" type="text" value="', $_POST['BrPostAddr3'].'" />
		</field>';

	if (!isset($_POST['BrPostAddr4'])) {// Postal address, line 4. Database: custbranch.brpostaddr4, varchar(40)
		$_POST['BrPostAddr4']='';
	}
	echo '<field>
			<label for="BrPostAddr4">', _('Postal Address 4 (Postal Code)'), ':</label>
			<input maxlength="40" name="BrPostAddr4" size="41" tabindex="26" type="text" value="', $_POST['BrPostAddr4'].'" />
		</field>';

	if (!isset($_POST['BrPostAddr5'])) {// Postal address, line 5. Database: custbranch.brpostaddr5, varchar(20)
		$_POST['BrPostAddr5']='';
	}
	echo '<field>
			<label for="BrPostAddr5">', _('Postal Address 5'), ':</label>
			<input maxlength="20" name="BrPostAddr5" size="21" tabindex="27" type="text" value="', $_POST['BrPostAddr5'].'" />
		</field>';

	if(!isset($_POST['CustBranchCode'])) {
		$_POST['CustBranchCode']='';
	}
	echo '<field>
			<label for="CustBranchCode">', _('Customers Internal Branch Code (EDI)'), ':</label>
			<input maxlength="30" name="CustBranchCode" size="31" tabindex="28" type="text" value="', $_POST['CustBranchCode'], '" />
		</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input name="submit" tabindex="29" type="submit" value="', _('Enter Or Update Branch'), '" />
		</div>
		</form>';

}//end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
