<?php
$PageSecurity = 0;

include('includes/session.php');

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

$Title = _('Main Menu');
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
	echo '<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/company.png" data-title="', _('General Setup Options'), '" alt="', _('General Setup Options'), '" /><b>', _('General Setup Options'), '</b>';
} elseif ($_SESSION['Module'] == 'hospsetup') {
	echo '<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/company.png" data-title="', _('General Hospital Setup'), '" alt="', _('General Hospital Setup'), '" /><b>', _('General Hospital Setup'), '</b>';
} else {
	echo '<img src="', $RootPath, '/css/', $_SESSION['Theme'], '/images/transactions.png" data-title="', _('Transactions'), '" alt="', _('Transactions'), '" /><b>', _('Transactions'), '</b>';
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
	$Header = '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/ar.png" data-title="' . _('Receivables/Payables Setup') . '" alt="' . _('Receivables/Payables Setup') . '" /><b>' . _('Receivables/Payables Setup') . '</b>';
} elseif ($_SESSION['Module'] == 'hospsetup') {
	$Header = '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/ar.png" data-title="' . _('ERP Integration') . '" alt="' . _('ERP Integration') . '" /><b>' . _('ERP Integration') . '</b>';
} else {
	$Header = '<img data-title="' . _('Inquiries and Reports') . '" src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/reports.png" alt="' . _('Inquiries and Reports') . '" /><b>' . _('Inquiries and Reports') . '</b>';
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
	$Header = '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/inventory.png" data-title="' . _('Inventory Setup') . '" alt="' . _('Inventory Setup') . '" /><b>' . _('Inventory Setup') . '</b>';
} elseif ($_SESSION['Module'] == 'hospsetup') {
	$Header = '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" data-title="' . _('Maintain types') . '" alt="' . _('Maintain Types') . '" /><b>' . _('Maintain Types') . '</b>';
} else {
	$Header = '<img src="' . $RootPath . '/css/' . $_SESSION['Theme'] . '/images/maintenance.png" data-title="' . _('Maintenance') . '" alt="' . _('Maintenance') . '" /><b>' . _('Maintenance') . '</b>';
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
		$_SESSION['FormGroups'] = array('gl:chk' => _('Bank Checks'), // Bank checks grouped with the gl report group
		'ar:col' => _('Collection Letters'), 'ar:cust' => _('Customer Statements'), 'gl:deps' => _('Bank Deposit Slips'), 'ar:inv' => _('Invoices and Packing Slips'), 'ar:lblc' => _('Labels - Customer'), 'prch:lblv' => _('Labels - Vendor'), 'prch:po' => _('Purchase Orders'), 'ord:quot' => _('Customer Quotes'), 'ar:rcpt' => _('Sales Receipts'), 'ord:so' => _('Sales Orders'), 'misc:misc' => _('Miscellaneous')); // do not delete misc category

	}
	if (isset($_SESSION['ReportList'][$GroupID])) {
		$GroupID = $_SESSION['ReportList'][$GroupID];
	}
	$Title = array(_('Custom Reports'), _('Standard Reports and Forms'));

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
					$RptLinks.= urlencode($Report['id']) . '">&nbsp; ' . _($Report['reportname']) . '</a></p>';
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
		if ($NoEntries) $RptLinks.= '<li class="menu_group_item">' . _('There are no reports to show!') . '</li>';
	}
	return $RptLinks;
}
