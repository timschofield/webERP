<?php

$PageSecurity = 0;

require(__DIR__ . '/includes/session.php');

/*The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
include('includes/MainMenuLinksArray.php');

if (isset($_SESSION['FirstLogIn']) and $_SESSION['FirstLogIn'] == '1' and isset($_SESSION['DatabaseName'])) {
	$_SESSION['FirstRun'] = true;
	echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/InitialScripts.php">';
	exit();
} else {
	$_SESSION['FirstRun'] = false;
}

if (isset($_POST['CompanyNameField'])) {
	setcookie('Company', $_POST['CompanyNameField'], time() + 3600 * 24 * 30);
}

$Title = __('Main Menu');
$SQL = "SELECT value FROM session_data WHERE userid='" . $_SESSION['UserID'] . "' AND field='module'";
$Result = DB_query($SQL);
$MyRow = DB_fetch_array($Result);
$_SESSION['Module'] = $MyRow['value'];
if (isset($_GET['Application']) and ($_GET['Application'] != '')) {
	/*This is sent by this page (to itself) when the user clicks on a tab */
	$_SESSION['Module'] = $_GET['Application'];
	setcookie('Module', $_GET['Application'], time() + 3600 * 24 * 30);
} elseif (isset($_COOKIE['Module'])) {
	$_SESSION['Module'] = $_COOKIE['Module'];
} else {
	$_SESSION['Module'] = '';
}

include('includes/header.php');

if (isset($SupplierLogin) AND $SupplierLogin==1){
	echo '<section class="MainBody clearfix">';
	echo '<form class="centre" style="width:30%">
			<fieldset>
				<legend>', __('Menu Options'), '</legend>';
	echo '<table style="width:100%">
			<tr>
				<td style="width:auto">
					<p>&bull; <a href="' . $RootPath . '/SupplierTenders.php?TenderType=1">' . __('View or Amend outstanding offers') . '</a></p>
				</td>
			</tr>
			<tr>
				<td class="menu_group_item">
					<p>&bull; <a href="' . $RootPath . '/SupplierTenders.php?TenderType=2">' . __('Create a new offer') . '</a></p>
				</td>
			</tr>
			<tr>
				<td class="menu_group_item">
					<p>&bull; <a href="' . $RootPath . '/SupplierTenders.php?TenderType=3">' . __('View any open tenders without an offer') . '</a></p>
				</td>
			</tr>
		</table>';
	echo '</fieldset>
		</form>
	</section>';
	include('includes/footer.php');
	exit;
} elseif (isset($CustomerLogin) AND $CustomerLogin==1){
	echo '<section class="MainBody clearfix">';
	echo '<form class="centre" style="width:30%">
			<fieldset>
				<legend>', __('Menu Options'), '</legend>';
	echo '<table style="width:100%">
			<tr>
				<td>
					<p>&bull; <a href="' . $RootPath . '/CustomerInquiry.php?CustomerID=' . $_SESSION['CustomerID'] . '">' . __('Account Status') . '</a></p>
				</td>
			</tr>
			<tr>
				<td class="menu_group_item">
					<p>&bull; <a href="' . $RootPath . '/SelectOrderItems.php?NewOrder=Yes">' . __('Place An Order') . '</a></p>
				</td>
			</tr>
			<tr>
				<td class="menu_group_item">
					<p>&bull; <a href="' . $RootPath . '/SelectCompletedOrder.php?SelectedCustomer=' . $_SESSION['CustomerID'] . '">' . __('Order Status') . '</a></p>
				</td>
			</tr>
		</table>';
	echo '</fieldset>
		</form>
	</section>';
	include('includes/footer.php');
	exit;
}

//=== MainMenuDiv =======================================================================
echo '<nav class="ModuleList">
		<ul>'; //===HJ===
$i = 0;
while ($i < count($ModuleLink)) {
	// This determines if the user has display access to the module see config.php and header.php
	// for the authorisation and security code
	if ($_SESSION['ModulesEnabled'][$i] == 1) {
		// If this is the first time the application is loaded then it is possible that
		// SESSION['Module'] is not set if so set it to the first module that is enabled for the user
		if (!isset($_SESSION['Module']) or $_SESSION['Module'] == '') {
			$_SESSION['Module'] = $ModuleLink[$i];
		}
		if ($ModuleLink[$i] == $_SESSION['Module']) {
			echo '<li class="ModuleSelected">';
		} else {
			echo '<li class="ModuleUnSelected">';

		}
		echo '<a href="', htmlspecialchars(basename(__FILE__), ENT_QUOTES, 'UTF-8'), '?Application=', urlencode($ModuleLink[$i]), '">', $ModuleList[$i], '</a></li>';
	}
	++$i;
}
echo '</ul>
	</nav>'; // MainMenuDiv ===HJ===


//=== SubMenuDiv (wrapper) ==============================================================================
echo '<section class="MainBody clearfix">';
echo '<fieldset class="MenuList">'; //=== TransactionsDiv ===
echo '<legend>'; //=== SubMenuHeader ===
if ($_SESSION['Module'] == 'system') {
	echo '<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/company.png" data-title="', __('General Setup Options'), '" alt="', __('General Setup Options'), '" /><b>', __('General Setup Options'), '</b>';
} elseif ($_SESSION['Module'] == 'hospsetup') {
	echo '<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/company.png" data-title="', __('General Hospital Setup'), '" alt="', __('General Hospital Setup'), '" /><b>', __('General Hospital Setup'), '</b>';
} else {
	echo '<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" data-title="', __('Transactions'), '" alt="', __('Transactions'), '" /><b>', __('Transactions'), '</b>';
}

echo '</legend><ul>'; // SubMenuHeader
//=== SubMenu Items ===
$i = 0;
foreach ($MenuItems[$_SESSION['Module']]['Transactions']['Caption'] as $Caption) {
	/* Transactions Menu Item */
	$ScriptNameArray = explode('?', substr($MenuItems[$_SESSION['Module']]['Transactions']['URL'][$i], 1));
	if (isset($_SESSION['PageSecurityArray'][$ScriptNameArray[0]])) {
		$PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[0]];
	}
	if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) and $PageSecurity != '')) {
		echo '<li class="MenuItem">
				<a href="', $RootPath, $MenuItems[$_SESSION['Module']]['Transactions']['URL'][$i], '">&bull; ', $Caption, '</a>
			</li>';
	}
	++$i;
}
echo '</ul>
	</fieldset>'; //=== TransactionsDiv ===
echo '<fieldset class="MenuList">'; //=== TransactionsDiv ===
echo '<legend>'; //=== SubMenuHeader ===
if ($_SESSION['Module'] == 'system') {
	$Header = '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/ar.png" data-title="' . __('Receivables/Payables Setup') . '" alt="' . __('Receivables/Payables Setup') . '" /><b>' . __('Receivables/Payables Setup') . '</b>';
} elseif ($_SESSION['Module'] == 'hospsetup') {
	$Header = '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/ar.png" data-title="' . __('ERP Integration') . '" alt="' . __('ERP Integration') . '" /><b>' . __('ERP Integration') . '</b>';
} else {
	$Header = '<img data-title="' . __('Inquiries and Reports') . '" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/reports.png" alt="' . __('Inquiries and Reports') . '" /><b>' . __('Inquiries and Reports') . '</b>';
}
echo $Header;
echo '</legend>
	<ul>';

$i = 0;
if (isset($MenuItems[$_SESSION['Module']]['Reports'])) {
	foreach ($MenuItems[$_SESSION['Module']]['Reports']['Caption'] as $Caption) {
		/* Transactions Menu Item */
		$ScriptNameArray = explode('?', substr($MenuItems[$_SESSION['Module']]['Reports']['URL'][$i], 1));
		$PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[0]];
		if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) or !isset($PageSecurity))) {
			echo '<li class="MenuItem">
				<a href="' . $RootPath . $MenuItems[$_SESSION['Module']]['Reports']['URL'][$i] . '">&bull; ' . $Caption . '</a>
			</li>';
		}
		++$i;
	}
}

echo GetRptLinks($_SESSION['Module']); //=== GetRptLinks() must be modified!!! ===
echo '</ul>
	</fieldset>'; //=== InquiriesDiv ===
echo '<fieldset class="MenuList">'; //=== MaintenanceDive ===
echo '<legend>';
if ($_SESSION['Module'] == 'system') {
	$Header = '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" data-title="' . __('Inventory Setup') . '" alt="' . __('Inventory Setup') . '" /><b>' . __('Inventory Setup') . '</b>';
} elseif ($_SESSION['Module'] == 'hospsetup') {
	$Header = '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" data-title="' . __('Maintain types') . '" alt="' . __('Maintain Types') . '" /><b>' . __('Maintain Types') . '</b>';
} else {
	$Header = '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" data-title="' . __('Maintenance') . '" alt="' . __('Maintenance') . '" /><b>' . __('Maintenance') . '</b>';
}
echo $Header;
echo '</legend>
	<ul>';

$i = 0;
if (isset($MenuItems[$_SESSION['Module']]['Maintenance'])) {
	foreach ($MenuItems[$_SESSION['Module']]['Maintenance']['Caption'] as $Caption) {
		/* Transactions Menu Item */
		$ScriptNameArray = explode('?', substr($MenuItems[$_SESSION['Module']]['Maintenance']['URL'][$i], 1));
		if (isset($_SESSION['PageSecurityArray'][$ScriptNameArray[0]])) {
			$PageSecurity = $_SESSION['PageSecurityArray'][$ScriptNameArray[0]];
			if ((in_array($PageSecurity, $_SESSION['AllowedPageSecurityTokens']) or !isset($PageSecurity))) {
				echo '<li class="MenuItem">
						<a href="' . $RootPath . $MenuItems[$_SESSION['Module']]['Maintenance']['URL'][$i] . '">&bull; ' . $Caption . '</a>
					</li>';
			}
		}
		++$i;
	}
}
echo '</ul>
</fieldset>'; // MaintenanceDive ===HJ===
include('includes/footer.php');

function GetRptLinks($GroupID) {
	/*
	This function retrieves the reports given a certain group id as defined in /reports/admin/defaults.php
	in the acssociative array $ReportGroups[]. It will fetch the reports belonging solely to the group
	specified to create a list of links for insertion into a table to choose a report. Two table sections will
	be generated, one for standard reports and the other for custom reports.
	*/
	global $RootPath;
	if (!isset($_SESSION['FormGroups'])) {
		$_SESSION['FormGroups'] = array('gl:chk' => __('Bank Checks'), // Bank checks grouped with the gl report group
		'ar:col' => __('Collection Letters'), 'ar:cust' => __('Customer Statements'), 'gl:deps' => __('Bank Deposit Slips'), 'ar:inv' => __('Invoices and Packing Slips'), 'ar:lblc' => __('Labels - Customer'), 'prch:lblv' => __('Labels - Vendor'), 'prch:po' => __('Purchase Orders'), 'ord:quot' => __('Customer Quotes'), 'ar:rcpt' => __('Sales Receipts'), 'ord:so' => __('Sales Orders'), 'misc:misc' => __('Miscellaneous')); // do not delete misc category

	}
	if (isset($_SESSION['ReportList'][$GroupID])) {
		$GroupID = $_SESSION['ReportList'][$GroupID];
	}
	$Title = array(__('Custom Reports'), __('Standard Reports and Forms'));

	if (!isset($_SESSION['ReportList'])) {
		$SQL = "SELECT id,
						reporttype,
						defaultreport,
						groupname,
						reportname
					FROM reports
					ORDER BY groupname,
							reportname";
		$Result = DB_query($SQL, '', '', false, true);
		$_SESSION['ReportList'] = array();
		while ($Temp = DB_fetch_assoc($Result)) {
			$_SESSION['ReportList'][] = $Temp;
		}
	}
	$RptLinks = '';
	for ($Def = 1;$Def >= 0;$Def--) {
		$RptLinks.= '<li class="CustomMenuList">';
		$RptLinks.= '<b>' . $Title[$Def] . '</b>';
		$RptLinks.= '</li>';
		$NoEntries = true;
		if (isset($_SESSION['ReportList']['groupname']) and count($_SESSION['ReportList']['groupname']) > 0) { // then there are reports to show, show by grouping
			foreach ($_SESSION['ReportList'] as $Report) {
				if (isset($Report['groupname']) and $Report['groupname'] == $GroupID and $Report['defaultreport'] == $Def) {
					$RptLinks.= '<li class="menu_group_item">';
					$RptLinks.= '<p><a href="' . $RootPath . '/reportwriter/ReportMaker.php?action=go&amp;reportid=';
					$RptLinks.= urlencode($Report['id']) . '">&nbsp; ' . __($Report['reportname']) . '</a></p>';
					$RptLinks.= '</li>';
					$NoEntries = false;
				}
			}
			// now fetch the form groups that are a part of this group (List after reports)
			$NoForms = true;
			foreach ($_SESSION['ReportList'] as $Report) {
				$Group = explode(':', $Report['groupname']); // break into main group and form group array
				if ($NoForms and $Group[0] == $GroupID and $Report['reporttype'] == 'frm' and $Report['defaultreport'] == $Def) {
					$RptLinks.= '<li class="menu_group_item">';
					$RptLinks.= '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/folders.gif" width="16" height="13" alt="" />&nbsp; ';
					$RptLinks.= '<p><a href="' . $RootPath . '/reportwriter/FormMaker.php?id=' . urlencode($Report['groupname']) . '">';
					$RptLinks.= $_SESSION['FormGroups'][$Report['groupname']] . '</a></p>';
					$RptLinks.= '</li>';
					$NoForms = false;
					$NoEntries = false;
				}
			}
		}
		if ($NoEntries) $RptLinks.= '<li class="menu_group_item">' . __('There are no reports to show!') . '</li>';
	}
	return $RptLinks;
}
