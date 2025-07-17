<?php

include('includes/session.php');
$Title = _('Customer Info Card Maintenance');

include('includes/KLCountriesForRetail.php');
include('includes/KLGeneralFunctions.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');
include('includes/KLPOSGeneral.php');
include('includes/header.php');

if (isset($_GET['SelectedOrder'])){
	$SelectedOrder =mb_strtoupper($_GET['SelectedOrder']);
} elseif(isset($_POST['SelectedOrder'])){
	$SelectedOrder =mb_strtoupper($_POST['SelectedOrder']);
}

if (isset($Errors)){
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	$i=1;

	//first off validate inputs sensible

	if (!isset($_POST['FirstName'])){
		$_POST['FisrtName'] = '';
	}
	if (!isset($_POST['LastName'])){
		$_POST['LastName'] = '';
	}
	if (!isset($_POST['Country'])){
		$_POST['Country'] = '';
	}
	if (!isset($_POST['DateOfBirth'])){
		$_POST['DateOfBirth'] = '';
	}
	if (!isset($_POST['Email'])){
		$_POST['Email'] = '';
	}
	if (!isset($_POST['Sex'])){
		$_POST['Sex'] = '';
	}

	if ($InputError != 1) {
		RecordRetailCustomerInformation($SelectedOrder, $_POST['FirstName'], $_POST['LastName'], $_POST['Country'], $_POST['DateOfBirth'], $_POST['Email'], $_POST['Sex']);
		unset($SelectedOrder);
		unset($_POST['FirstName']);
		unset($_POST['LastName']);
		unset($_POST['Country']);
		unset($_POST['DateOfBirth']);
		unset($_POST['Sex']);
	}

} elseif (isset($_GET['delete'])) {

	$SQL = "DELETE FROM klretailcustomers 
			WHERE orderno='". $SelectedOrder."'";
	$ErrMsg = _('The customer retail info could not be deleted because');
	$Result = DB_query($SQL,$ErrMsg);

	prnMsg(_('Customer Info Card for order') . ' ' . $SelectedOrder . ' ' . _('has been deleted'),'success');
	unset ($SelectedOrder);
	unset($Delete);
}

if (!isset($SelectedOrder)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedOrder will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of Sales-persons will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$SQL = "SELECT salesorders.orderno,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.klpaidcash,
					salesorders.klpaidcreditcard,
					salesorders.klreturnedgoods,
					salesorders.klvouchers,
					klretailcustomers.firstname,
					klretailcustomers.lastname,
					klretailcustomers.Country,
					IF(klretailcustomers.date_of_birth IS NULL,'',klretailcustomers.date_of_birth) AS date_of_birth,
					klretailcustomers.age,
					klretailcustomers.email,
					klretailcustomers.sex
			FROM salesorders
			LEFT JOIN klretailcustomers
				ON salesorders.orderno = klretailcustomers.orderno
			WHERE salesorders.salesperson ='".$_SESSION['SalesmanLogin']."'
				AND salesorders.orddate >=CURDATE()
			ORDER BY salesorders.orddate DESC";
	$Result = DB_query($SQL);

	echo '<table class="selection">';
	echo '<thead>
			<tr>
				<th>' . _('Order #') . '</th>
				<th>' . _('Invoice #') . '</th>
				<th>' . _('Date') . '</th>
				<th>' . _('First Name') . '</th>
				<th>' . _('Last Name') . '</th>
				<th>' . _('Country') . '</th>
				<th>' . _('Date of Birth') . '</th>
				<th>' . _('email') . '</th>
				<th>' . _('Sex') . '</th>
			</tr>
		</thead>
		<tbody>';

	while ($MyRow=DB_fetch_array($Result)) {
		echo '<tr class="striped_row">';

		if ($MyRow['date_of_birth'] == '') {
			$TextDOB = '';
		} else {
			$TextDOB = ConvertSQLDate($MyRow['date_of_birth']);
		}
		if ($MyRow['Country'] == '0') {
			$TextCountry = '';
		} else {
			$TextCountry = $MyRow['Country'];
		}

		echo '<td>'.$MyRow['orderno'].'</td>
			<td>'.$MyRow['customerref'].'</td>
			<td>'.ConvertSQLDate($MyRow['orddate']).'</td>
			<td>'.$MyRow['firstname'].'</td>
			<td>'.$MyRow['lastname'].'</td>
			<td>'.$TextCountry.'</td>
			<td>'.$TextDOB.'</td>
			<td>'.$MyRow['email'].'</td>
			<td>'.$MyRow['sex'].'</td>
			<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?SelectedOrder='.$MyRow['orderno'].'">'._('Edit').'</a></td>
			<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?SelectedOrder='.$MyRow['orderno'].'&amp;delete=1" onclick="return confirm(\''._('Are you sure you wish to delete this customer info card data?').'\');">'._('Delete').'</a></td>
			</tr>';
	} //END WHILE LIST LOOP
	echo '</tbody>
		</table>
		<br />
		</div>
		</form>';
} //end of ifs and buts!

if (isset($SelectedOrder)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show Other Sales for SPG ') . $_SESSION['SalesmanLogin'] . '</a></div>';
}

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedOrder)) {
		//editing an existing Order
	$SQL = "SELECT salesorders.orderno,
					salesorders.customerref,
					salesorders.orddate,
					salesorders.klpaidcash,
					salesorders.klpaidcreditcard,
					salesorders.klreturnedgoods,
					salesorders.klvouchers,
					klretailcustomers.firstname,
					klretailcustomers.lastname,
					klretailcustomers.country,
					IF(klretailcustomers.date_of_birth IS NULL,'',klretailcustomers.date_of_birth) AS date_of_birth,
					klretailcustomers.email,
					klretailcustomers.sex
			FROM salesorders
			LEFT JOIN klretailcustomers
				ON salesorders.orderno = klretailcustomers.orderno
			WHERE salesorders.orderno = '".$SelectedOrder."'";
				
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_array($Result);

		$_POST['orderno'] = $MyRow['orderno'];
		$_POST['customerref'] = $MyRow['customerref'];
		$_POST['orddate'] = $MyRow['orddate'];
		$_POST['klpaidcash'] = $MyRow['klpaidcash'];
		$_POST['klpaidcreditcard'] = $MyRow['klpaidcreditcard'];
		$_POST['klreturnedgoods'] = $MyRow['klreturnedgoods'];
		$_POST['klvouchers'] = $MyRow['klvouchers'];
		$_POST['FirstName'] = $MyRow['firstname'];
		$_POST['LastName'] = $MyRow['lastname'];
		$_POST['Country'] = $MyRow['country'];
		$_POST['date_of_birth'] = $MyRow['date_of_birth'];
		$_POST['Email'] = $MyRow['email'];
		$_POST['Sex'] = $MyRow['sex'];
		if ($_POST['Sex'] == ''){
			$_POST['Sex'] = 'F';
		}
		echo '<input type="hidden" name="SelectedOrder" value="' . $SelectedOrder . '" />';
		echo '<fieldset>
				<legend>' . _('Customer Info Card for webERP Order :') . $SelectedOrder . '</legend>';
		
		echo FieldToSelectOneText('FirstName', $_POST['FirstName'], 32, 32, _('First Name'), '', '', '', true, false);
		echo FieldToSelectOneText('LastName', $_POST['LastName'], 32, 32, _('Last Name'), '', '', '', true, false);
		echo FieldToSelectOneEntryFromArray($CountriesForRetail, 'Country', $_POST['Country'], _('Country'), '', '', '', false, false);
		echo FieldToSelectOneDate('DateOfBirth', $_POST['DateOfBirth'], _('Date Of Birth'), '', '', '', false, false);
		echo FieldToSelectFromTwoOptions('F', _('Female'), 'M', _('Male'), 'Sex', $_POST['Sex'], _('Sex'), '', '', '', true, false);
		echo FieldToSelectOneText('Email', $_POST['Email'], 32, 255, _('Email'), '', '', '', false, false);

		echo '</fieldset>';
		
		echo OneButtonCenteredForm('submit', _('Enter Information'));

		echo '</div>
			</form>';
	}
} //end if record deleted no point displaying form to add record

include('includes/footer.php');
