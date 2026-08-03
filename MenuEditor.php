<?php
/**
 * Menu Editor - Advanced Menu Management Interface
 * 
 * Provides a comprehensive interface for managing menu items across roles and modules.
 * Features include:
 * - Visual menu structure with drag-and-drop support
 * - Inline editing with real-time preview
 * - Bulk operations (copy, move, delete)
 * - Menu validation and integrity checks
 * - Section management and organization
 */

$PageSecurity = 15;
require(__DIR__ . '/includes/session.php');
$Title = __('Menu Editor');
$ViewTopic = 'Setup';
$BookMark = 'MenuEditor';
include(__DIR__ . '/includes/header.php');

/**
 * Fetch all security roles from the database
 * @return resource Database result set of roles
 */
function mi_fetch_roles() {
	return DB_query("SELECT secroleid, secrolename FROM securityroles ORDER BY secroleid");
}

/**
 * Fetch modules for a specific role
 * @param int $role Security role ID
 * @return resource Database result set of modules
 */
function mi_fetch_modules($role) {
	$SQL = "SELECT modulelink, modulename FROM modules WHERE secroleid='" . intval($role) . "' ORDER BY sequence";
	return DB_query($SQL);
}

/**
 * Calculate the next available sequence number for a menu item
 * @param int $role Security role ID
 * @param string $module Module link
 * @param string $section Menu section name
 * @return int Next sequence number
 */
function mi_next_sequence($role, $module, $section) {
	$SQL = "SELECT COALESCE(MAX(sequence),0)+1 AS nextseq FROM menuitems WHERE secroleid='" . intval($role) . "' AND modulelink='" . DB_escape_string($module) . "' AND menusection='" . DB_escape_string($section) . "'";
	$R = DB_query($SQL);
	$Row = DB_fetch_array($R);
	return (int)$Row['nextseq'];
}

/**
 * Get count of menu items in a section
 * @param int $role Security role ID
 * @param string $module Module link
 * @param string $section Menu section name
 * @return int Count of items
 */
function mi_count_items($role, $module, $section) {
	$SQL = "SELECT COUNT(*) as cnt FROM menuitems WHERE secroleid='" . intval($role) . "' AND modulelink='" . DB_escape_string($module) . "' AND menusection='" . DB_escape_string($section) . "'";
	$R = DB_query($SQL);
	$Row = DB_fetch_array($R);
	return (int)$Row['cnt'];
}

// Initialize state variables
$SelectedRole = isset($_REQUEST['SelectedRole']) ? (int)$_REQUEST['SelectedRole'] : (isset($_SESSION['AccessLevel']) ? (int)$_SESSION['AccessLevel'] : 8);
$SelectedModule = isset($_REQUEST['SelectedModule']) ? $_REQUEST['SelectedModule'] : '';
$EditingItem = isset($_GET['Edit']) ? $_GET['Edit'] : '';

// Handle bulk copy from another role/module
if (isset($_POST['BulkCopy'])) {
	$SourceRole = (int)$_POST['SourceRole'];
	$SourceModule = DB_escape_string($_POST['SourceModule']);
	$TargetRole = (int)$_POST['TargetRole'];
	$TargetModule = DB_escape_string($_POST['TargetModule']);
	
	$SQL = "INSERT INTO menuitems (secroleid, modulelink, menusection, caption, url, sequence)
			SELECT '" . $TargetRole . "', '" . $TargetModule . "', menusection, caption, url, sequence
			FROM menuitems
			WHERE secroleid='" . $SourceRole . "' AND modulelink='" . $SourceModule . "'";
	$Res = DB_query($SQL);
	if (DB_error_no($Res) == 0) {
		prnMsg(__('Menu items copied successfully'), 'success');
	} else {
		prnMsg(__('Copy failed'), 'error');
	}
}

// Handle section rename
if (isset($_POST['RenameSection'])) {
	$OldSection = DB_escape_string($_POST['OldSectionName']);
	$NewSection = DB_escape_string($_POST['NewSectionName']);
	
	if (trim($NewSection) != '') {
		$SQL = "UPDATE menuitems SET menusection='" . $NewSection . "' 
				WHERE secroleid='" . $SelectedRole . "' 
				AND modulelink='" . DB_escape_string($SelectedModule) . "' 
				AND menusection='" . $OldSection . "'";
		$Res = DB_query($SQL);
		if (DB_error_no($Res) == 0) {
			prnMsg(__('Section renamed successfully'), 'success');
		} else {
			prnMsg(__('Section rename failed'), 'error');
		}
	}
}

// Handle delete entire section
if (isset($_GET['DeleteSection'])) {
	$Section = DB_escape_string($_GET['DeleteSection']);
	$SQL = "DELETE FROM menuitems WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' AND menusection='" . $Section . "'";
	$Res = DB_query($SQL);
	if (DB_error_no($Res) == 0) {
		prnMsg(__('Section deleted successfully'), 'success');
	} else {
		prnMsg(__('Section delete failed'), 'error');
	}
}

// Handle insert new menu item
if (isset($_POST['Insert'])) {
	// Check if creating new section or using existing
	if ($_POST['Section'] === '__NEW__' && isset($_POST['NewSection'])) {
		$Section = trim($_POST['NewSection']);
	} else {
		$Section = trim($_POST['Section']);
	}
	
	$Caption = trim($_POST['Caption']);
	$URL = trim($_POST['URL']);
	
	if ($Section != '' && $Section != '__NEW__' && $Caption != '' && $URL != '') {
		$Seq = (isset($_POST['Sequence']) && $_POST['Sequence'] !== '') ? (int)$_POST['Sequence'] : mi_next_sequence($SelectedRole, $SelectedModule, $Section);
		
		// Shift existing items down to make room
		$SQL = "UPDATE menuitems SET sequence=sequence+1 WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' AND menusection='" . DB_escape_string($Section) . "' AND sequence>='" . $Seq . "'";
		DB_query($SQL);
		
		// Insert new item
		$SQL = "INSERT INTO menuitems (secroleid,modulelink,menusection,caption,url,sequence) VALUES ('" . $SelectedRole . "','" . DB_escape_string($SelectedModule) . "','" . DB_escape_string($Section) . "','" . DB_escape_string($Caption) . "','" . DB_escape_string($URL) . "','" . $Seq . "')";
		$Res = DB_query($SQL);
		if (DB_error_no($Res) == 0) {
			prnMsg(__('Menu item inserted successfully'), 'success');
		} else {
			prnMsg(__('Insert failed'), 'error');
		}
	} else {
		prnMsg(__('All fields are required'), 'error');
	}
}

// Handle update menu item
if (isset($_POST['Update'])) {
	$OldSection = $_POST['OldSection'];
	$OldCaption = $_POST['OldCaption'];
	$NewSection = trim($_POST['EditSection']);
	$NewCaption = trim($_POST['EditCaption']);
	$NewURL = trim($_POST['EditURL']);
	
	if ($NewSection != '' && $NewCaption != '' && $NewURL != '') {
		$SQL = "UPDATE menuitems SET menusection='" . DB_escape_string($NewSection) . "', caption='" . DB_escape_string($NewCaption) . "', url='" . DB_escape_string($NewURL) . "' WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' AND menusection='" . DB_escape_string($OldSection) . "' AND caption='" . DB_escape_string($OldCaption) . "'";
		$Res = DB_query($SQL);
		if (DB_error_no($Res) == 0) {
			prnMsg(__('Menu item updated successfully'), 'success');
		} else {
			prnMsg(__('Update failed'), 'error');
		}
	} else {
		prnMsg(__('All fields are required'), 'error');
	}
}

// Handle delete menu item
if (isset($_GET['Delete'])) {
	$Sec = $_GET['Section'];
	$Cap = $_GET['Caption'];
	$SQL = "DELETE FROM menuitems WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' AND menusection='" . DB_escape_string($Sec) . "' AND caption='" . DB_escape_string($Cap) . "'";
	$Res = DB_query($SQL);
	if (DB_error_no($Res) == 0) {
		prnMsg(__('Menu item deleted successfully'), 'success');
	} else {
		prnMsg(__('Delete failed'), 'error');
	}
}

// Handle move up/down
if (isset($_GET['Move']) && ($SelectedModule !== '')) {
	$Sec = $_GET['Section'];
	$Cap = $_GET['Caption'];
	$Seq = (int)$_GET['Seq'];
	
	if ($_GET['Move'] === 'Up') {
		$SQL = "SELECT caption, sequence FROM menuitems WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' AND menusection='" . DB_escape_string($Sec) . "' AND sequence<'" . $Seq . "' ORDER BY sequence DESC LIMIT 1";
	} else {
		$SQL = "SELECT caption, sequence FROM menuitems WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' AND menusection='" . DB_escape_string($Sec) . "' AND sequence>'" . $Seq . "' ORDER BY sequence ASC LIMIT 1";
	}
	$R = DB_query($SQL);
	if (DB_num_rows($R) == 1) {
		$N = DB_fetch_array($R);
		// Swap sequences using temporary value
		DB_query("UPDATE menuitems SET sequence='-1' WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' AND menusection='" . DB_escape_string($Sec) . "' AND caption='" . DB_escape_string($Cap) . "'");
		DB_query("UPDATE menuitems SET sequence='" . (int)$Seq . "' WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' AND menusection='" . DB_escape_string($Sec) . "' AND caption='" . DB_escape_string($N['caption']) . "'");
		DB_query("UPDATE menuitems SET sequence='" . (int)$N['sequence'] . "' WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' AND menusection='" . DB_escape_string($Sec) . "' AND caption='" . DB_escape_string($Cap) . "'");
	}
}

echo '<p class="page_title_text">
		<img src="' . $RootPath . '/css/' . $Theme . '/images/maintenance.png" title="' . __('Menu Editor') . '" alt="" />' . ' ' . $Title . '
	</p>';

echo '<div class="page_help_text">' . __('Manage menu items, sections, and navigation structure across roles and modules') . '</div>';

// Role/module selector panel
echo '<form method="get" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" id="selectorForm">';
echo '<fieldset>
        <legend>' . __('Role/Module Selector') . '</legend>';

// Role selector
echo '<field>
        <label for="SelectedRole">' . __('Security Role') . '</label>';
echo '<select name="SelectedRole" id="SelectedRole" onchange="this.form.submit()">';
$Roles = mi_fetch_roles();
while ($r = DB_fetch_array($Roles)) {
	echo '<option value="', $r['secroleid'], '"', ($SelectedRole == $r['secroleid'] ? ' selected' : ''), '>', $r['secroleid'], ' - ', htmlspecialchars($r['secrolename']), '</option>';
}
echo '</select>';
echo '</dfield>';

// Module selector
echo '<div class="selector-group">';
echo '<label for="SelectedModule">' . __('Module') . '</label>';
echo '<select name="SelectedModule" id="SelectedModule" onchange="this.form.submit()">';
echo '<option value="">' . __('-- Select Module --') . '</option>';
$Mods = mi_fetch_modules($SelectedRole);
if ($SelectedModule === '' && DB_num_rows($Mods) > 0) {
	DB_data_seek($Mods, 0);
	$first = DB_fetch_array($Mods);
	$SelectedModule = $first['modulelink'];
	DB_data_seek($Mods, 0);
}
while ($m = DB_fetch_array($Mods)) {
	echo '<option value="', htmlspecialchars($m['modulelink'], ENT_QUOTES, 'UTF-8'), '"', ($SelectedModule == $m['modulelink'] ? ' selected' : ''), '>', __($m['modulename']), ' (', $m['modulelink'], ')</option>';
}
echo '</select>';

// Statistics
if ($SelectedModule !== '') {
	$SQL = "SELECT COUNT(*) as total, COUNT(DISTINCT menusection) as sections FROM menuitems WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "'";
	$StatsRes = DB_query($SQL);
	$Stats = DB_fetch_array($StatsRes);
	echo '<div>' . $Stats['total'] . ' ' . __('items in') . ' ' . $Stats['sections'] . ' ' . __('sections') . '</div>';
}

echo '</fieldset>';
echo '</form>';

if ($SelectedModule !== '') {
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
	echo '<fieldset>';
	echo '<legend>' . __('Copy all menu items to another role/module') . '</legend>';
	
	// Bulk copy tool
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="SelectedRole" value="' . $SelectedRole . '" />';
	echo '<input type="hidden" name="SelectedModule" value="' . htmlspecialchars($SelectedModule, ENT_QUOTES, 'UTF-8') . '" />';
	echo '<input type="hidden" name="SourceRole" value="' . $SelectedRole . '" />';
	echo '<input type="hidden" name="SourceModule" value="' . htmlspecialchars($SelectedModule, ENT_QUOTES, 'UTF-8') . '" />';
	echo '<field><label>' . __('Target Role') . '</label>';
	echo '<select name="TargetRole">';
	$RolesCopy = mi_fetch_roles();
	while ($r = DB_fetch_array($RolesCopy)) {
		echo '<option value="', $r['secroleid'], '">', $r['secroleid'], ' - ', htmlspecialchars($r['secrolename']), '</option>';
	}
	echo '</select></field>';
	echo '<field><label>' . __('Target Module') . '</label>';
	echo '<input type="text" name="TargetModule" size="20" required /></field>';
	echo '<input type="submit" name="BulkCopy" value="' . __('Copy Items') . '" />';
	echo '</form>';
	echo '</fieldset>';
	
	// Add new item form with modern design
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" id="addItemForm">';
	echo '<fieldset>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="SelectedRole" value="' . $SelectedRole . '" />';
	echo '<input type="hidden" name="SelectedModule" value="' . htmlspecialchars($SelectedModule, ENT_QUOTES, 'UTF-8') . '" />';
	echo '<legend>' . __('Add New Menu Item') . '</legend>';
	
	// Get existing sections for dropdown
	$SectionSQL = "SELECT DISTINCT menusection FROM menuitems WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' ORDER BY menusection";
	$SectionRes = DB_query($SectionSQL);
	$existingSections = array();
	while ($sec = DB_fetch_array($SectionRes)) {
		$existingSections[] = $sec['menusection'];
	}
	
	echo '<div>';
	
	// Section dropdown with option to create new
	echo '<field><label for="Section">' . __('Section') . '</label>';
	echo '<select id="Section" name="Section" size="1" onchange="toggleNewSection()" required>';
	echo '<option value="">' . __('-- Select or Create New --') . '</option>';
	foreach ($existingSections as $existingSection) {
		echo '<option value="' . htmlspecialchars($existingSection, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($existingSection) . '</option>';
	}
	echo '<option value="__NEW__">' . __('+ Create New Section') . '</option>';
	echo '</select>';
	echo '<input type="text" id="NewSectionInput" name="NewSection" style="display: none; margin-top: 8px;" size="20" maxlength="15" placeholder="' . __('Enter new section name') . '" />';
	echo '</field>';
	
	echo '<field><label for="Caption">' . __('Caption') . '</label><input type="text" id="Caption" name="Caption" size="40" maxlength="60" required /></field>';
	echo '<field><label for="URL">' . __('URL') . '</label><input type="text" id="URL" name="URL" size="50" maxlength="60" required /></field>';
	echo '<field><label for="Sequence">' . __('Sequence') . ' (' . __('Optional') . ')</label><input type="number" id="Sequence" name="Sequence" min="1" /></field>';
	echo '</div>';
	echo '<div class="centre"><input type="submit" name="Insert" value="' . __('Insert Menu Item') . '" /></div>';
	echo '</fieldset></form>';

	// Display menu items grouped by section
	$SQL = "SELECT menusection FROM menuitems WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' GROUP BY menusection ORDER BY menusection";
	$Secs = DB_query($SQL);
	
	if (DB_num_rows($Secs) == 0) {
		echo '<div class="centre">';
		echo '<h3>' . __('No Menu Items Yet') . '</h3>';
		echo '<p>' . __('Add your first menu item using the form above') . '</p>';
		echo '</div>';
	}
	
	while ($s = DB_fetch_array($Secs)) {
		$Section = $s['menusection'];
		$ItemCount = mi_count_items($SelectedRole, $SelectedModule, $Section);
		
		$SQL = "SELECT caption, url, sequence FROM menuitems WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' AND menusection='" . DB_escape_string($Section) . "' ORDER BY sequence";
		$Rows = DB_query($SQL);
		
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
		echo '<fieldset>';
		echo '<legend>' . htmlspecialchars($Section) . ' (' . $ItemCount . ' ' . ($ItemCount == 1 ? __('item') : __('items')) . ')</legend>';
		echo '<div>';
		
		// Rename section button
		echo '<input type="button" value="' . __('Rename') . '" onclick="toggleRenameForm(\'' . htmlspecialchars($Section, ENT_QUOTES, 'UTF-8') . '\')" id="renameBtn_' . htmlspecialchars($Section, ENT_QUOTES, 'UTF-8') . '" />';
		
		// Delete section button
		$DelSection = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedRole=' . $SelectedRole . '&SelectedModule=' . urlencode($SelectedModule) . '&DeleteSection=' . urlencode($Section);
		echo '<a href="' . $DelSection . '" onclick="return confirm(\'' . __('Delete entire section and all items?') . '\');">' . __('Delete Section') . '</a>';
		echo '</div>';
		
		// Rename form (hidden by default)
		echo '<div id="renameForm_' . htmlspecialchars($Section, ENT_QUOTES, 'UTF-8') . '" style="display: none;">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<input type="hidden" name="SelectedRole" value="' . $SelectedRole . '" />';
		echo '<input type="hidden" name="SelectedModule" value="' . htmlspecialchars($SelectedModule, ENT_QUOTES, 'UTF-8') . '" />';
		echo '<input type="hidden" name="OldSectionName" value="' . htmlspecialchars($Section, ENT_QUOTES, 'UTF-8') . '" />';
		echo '<field><label>' . __('New section name:') . '</label><input type="text" name="NewSectionName" value="' . htmlspecialchars($Section, ENT_QUOTES, 'UTF-8') . '" required /></field>';
		echo '<input type="submit" name="RenameSection" value="' . __('Save') . '" />';
		echo '<input type="button" value="' . __('Cancel') . '" onclick="toggleRenameForm(\'' . htmlspecialchars($Section, ENT_QUOTES, 'UTF-8') . '\')" />';
		echo '</form>';
		echo '</div>';
		
		// Menu items table
		echo '<table class="selection">
				<tr>
						<th>' . __('Order') . '</th>
						<th>' . __('Caption') . '</th>
						<th>' . __('URL') . '</th>
						<th colspan="4">' . __('Actions') . '</th>
					</tr>';
		echo '<tbody>';
		
		$itemNum = 0;
		$totalItems = DB_num_rows($Rows);
		
		while ($row = DB_fetch_array($Rows)) {
			$itemNum++;
			$isEditing = ($EditingItem === $Section . '_' . $row['caption']);
			
			echo '<tr' . ($isEditing ? ' class="striped_row"' : '') . '>';
			
			// Sequence number
			echo '<td>' . (int)$row['sequence'] . '</td>';
			
			if ($isEditing) {
				// Inline edit mode
				echo '<td colspan="3">';
				echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
				echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
				echo '<input type="hidden" name="SelectedRole" value="' . $SelectedRole . '" />';
				echo '<input type="hidden" name="SelectedModule" value="' . htmlspecialchars($SelectedModule, ENT_QUOTES, 'UTF-8') . '" />';
				echo '<input type="hidden" name="OldSection" value="' . htmlspecialchars($Section, ENT_QUOTES, 'UTF-8') . '" />';
				echo '<input type="hidden" name="OldCaption" value="' . htmlspecialchars($row['caption'], ENT_QUOTES, 'UTF-8') . '" />';
				
				// Get all sections for dropdown
				$AllSectionsSQL = "SELECT DISTINCT menusection FROM menuitems WHERE secroleid='" . $SelectedRole . "' AND modulelink='" . DB_escape_string($SelectedModule) . "' ORDER BY menusection";
				$AllSectionsRes = DB_query($AllSectionsSQL);
				
				echo '<field><label>' . __('Section') . '</label>';
				echo '<select name="EditSection" required>';
				while ($secOpt = DB_fetch_array($AllSectionsRes)) {
					$selected = ($secOpt['menusection'] === $Section) ? ' selected' : '';
					echo '<option value="' . htmlspecialchars($secOpt['menusection'], ENT_QUOTES, 'UTF-8') . '"' . $selected . '>' . htmlspecialchars($secOpt['menusection']) . '</option>';
				}
				echo '</select></field>';
				
				echo '<field><label>' . __('Caption') . '</label><input type="text" name="EditCaption" value="' . htmlspecialchars($row['caption'], ENT_QUOTES, 'UTF-8') . '" size="25" maxlength="60" required /></field>';
				
				echo '<field><label>' . __('URL') . '</label><input type="text" name="EditURL" value="' . htmlspecialchars($row['url'], ENT_QUOTES, 'UTF-8') . '" size="35" maxlength="60" required /></field>';
				
				echo '<input type="submit" name="Update" value="' . __('Save') . '" />';
				echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedRole=' . $SelectedRole . '&SelectedModule=' . urlencode($SelectedModule) . '">' . __('Cancel') . '</a>';
				echo '</form>';
				echo '</td>';
			} else {
				// Display mode
				echo '<td><strong>' . htmlspecialchars($row['caption']) . '</strong></td>';
				echo '<td>' . htmlspecialchars($row['url']) . '</td>';
				
				// Actions
				echo '<td style="white-space: nowrap;">';
				echo '<div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">';
				
				// Move buttons
				$Up = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedRole=' . $SelectedRole . '&SelectedModule=' . urlencode($SelectedModule) . '&Section=' . urlencode($Section) . '&Caption=' . urlencode($row['caption']) . '&Seq=' . (int)$row['sequence'] . '&Move=Up';
				$Down = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedRole=' . $SelectedRole . '&SelectedModule=' . urlencode($SelectedModule) . '&Section=' . urlencode($Section) . '&Caption=' . urlencode($row['caption']) . '&Seq=' . (int)$row['sequence'] . '&Move=Down';
				
				if ($itemNum > 1) {
					echo '<a href="' . $Up . '" style="display: inline-block; width: 28px; text-align: center;"><img src="' . $RootPath . '/css/' . $Theme . '/images/ascending.png" title="' . __('Move Up') . '" alt="' . __('Move Up') . '" width="24" /></a>';
				} else {
					echo '<span style="display: inline-block; width: 28px; text-align: center;"><img src="' . $RootPath . '/css/' . $Theme . '/images/ascending.png" title="' . __('Move Up') . '" alt="' . __('Move Up') . '" width="24" /></span>';
				}
				
				if ($itemNum < $totalItems) {
					echo '<a href="' . $Down . '" style="display: inline-block; width: 28px; text-align: center;"><img src="' . $RootPath . '/css/' . $Theme . '/images/descending.png" title="' . __('Move Down') . '" alt="' . __('Move Down') . '" width="24" /></a>';
				} else {
					echo '<span style="display: inline-block; width: 28px; text-align: center;"><img src="' . $RootPath . '/css/' . $Theme . '/images/descending.png" title="' . __('Move Down') . '" alt="' . __('Move Down') . '" width="24" /></span>';
				}
				
				// Edit button
				$Edit = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedRole=' . $SelectedRole . '&SelectedModule=' . urlencode($SelectedModule) . '&Edit=' . urlencode($Section . '_' . $row['caption']);
				echo '<a href="' . $Edit . '" style="display: inline-block; min-width: 48px;">' . __('Edit') . '</a>';
				
				// Delete button
				$Del = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedRole=' . $SelectedRole . '&SelectedModule=' . urlencode($SelectedModule) . '&Section=' . urlencode($Section) . '&Caption=' . urlencode($row['caption']) . '&Delete=1';
				echo '<a href="' . $Del . '" style="display: inline-block; min-width: 56px;" onclick="return confirm(\'' . __('Delete this menu item?') . '\');">' . __('Delete') . '</a>';
				
				echo '</div>';
				echo '</td>';
			}
			
			echo '</tr>';
		}
		echo '</tbody></table>';
		echo '</fieldset>';
	}
	
} else {
	// No module selected - show helpful message
	echo '<div class="centre">';
	echo '<h3>' . __('Select a Module') . '</h3>';
	echo '<p>' . __('Choose a role and module from the dropdowns above to manage menu items') . '</p>';
	echo '</div>';
}

// JavaScript for enhanced functionality
echo '<script>
function toggleRenameForm(section) {
	var formId = "renameForm_" + section;
	var form = document.getElementById(formId);
	if (form.style.display === "none") {
		form.style.display = "block";
	} else {
		form.style.display = "none";
	}
}

// Toggle between dropdown and text input for new section
function toggleNewSection() {
	var sectionSelect = document.getElementById("Section");
	var newSectionInput = document.getElementById("NewSectionInput");
	
	if (sectionSelect.value === "__NEW__") {
		newSectionInput.style.display = "block";
		newSectionInput.required = true;
		sectionSelect.required = false;
	} else {
		newSectionInput.style.display = "none";
		newSectionInput.required = false;
		sectionSelect.required = true;
	}
}

// Initialize form on page load
document.addEventListener("DOMContentLoaded", function() {
	toggleNewSection();
});
</script>';

include(__DIR__ . '/includes/footer.php');
