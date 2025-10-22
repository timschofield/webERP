<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Tax Groups');
$ViewTopic = 'Tax';// Filename in ManualContents.php's TOC.
$BookMark = 'TaxGroups';// Anchor's id in the manual's html document.
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		__('Tax Group Maintenance') . '" />' . ' ' .
		__('Tax Group Maintenance') . '</p>';

if(isset($_GET['SelectedGroup'])) {
	$SelectedGroup = $_GET['SelectedGroup'];
} elseif(isset($_POST['SelectedGroup'])) {
	$SelectedGroup = $_POST['SelectedGroup'];
}

if(isset($_POST['submit']) OR isset($_GET['remove']) OR isset($_GET['add']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	//first off validate inputs sensible
	if(isset($_POST['GroupName']) AND mb_strlen($_POST['GroupName'])<4) {
		$InputError = 1;
		prnMsg(__('The Group description entered must be at least 4 characters long'),'error');
	}

	// if $_POST['GroupName'] then it is a modification of a tax group name
	// else it is either an add or remove of taxgroup
	unset($SQL);
	if(isset($_POST['GroupName']) ) { // Update or Add a tax group
		if(isset($SelectedGroup)) { // Update a tax group
			$SQL = "UPDATE taxgroups SET taxgroupdescription = '". $_POST['GroupName'] ."'
					WHERE taxgroupid = '".$SelectedGroup . "'";
			$ErrMsg = __('The update of the tax group description failed because');
			$SuccessMsg = __('The tax group description was updated to') . ' ' . $_POST['GroupName'];
		} else { // Add new tax group

			$Result = DB_query("SELECT taxgroupid
								FROM taxgroups
								WHERE taxgroupdescription='" . $_POST['GroupName'] . "'");
			if(DB_num_rows($Result)==1) {
				prnMsg( __('A new tax group could not be added because a tax group already exists for') . ' ' . $_POST['GroupName'],'warn');
				unset($SQL);
			} else {
				$SQL = "INSERT INTO taxgroups (taxgroupdescription)
						VALUES ('". $_POST['GroupName'] . "')";
				$ErrMsg = __('The addition of the group failed because');
				$SuccessMsg = __('Added the new tax group') . ' ' . $_POST['GroupName'];
			}
		}
		unset($_POST['GroupName']);
		unset($SelectedGroup);
	} elseif(isset($SelectedGroup) ) {
		$TaxAuthority = $_GET['TaxAuthority'];
		if( isset($_GET['add']) ) { // adding a tax authority to a tax group

			$SQL = "INSERT INTO taxgrouptaxes ( taxgroupid,
												taxauthid,
												calculationorder)
					VALUES ('" . $SelectedGroup . "',
							'" . $TaxAuthority . "',
							0)";

			$ErrMsg = __('The addition of the tax failed because');
			$SuccessMsg = __('The tax was added.');
		} elseif( isset($_GET['remove']) ) { // remove a taxauthority from a tax group
			$SQL = "DELETE FROM taxgrouptaxes
					WHERE taxgroupid = '".$SelectedGroup."'
					AND taxauthid = '".$TaxAuthority . "'";
			$ErrMsg = __('The removal of this tax failed because');
			$SuccessMsg = __('This tax was removed.');
		}
		unset($_GET['add']);
		unset($_GET['remove']);
		unset($_GET['TaxAuthority']);
	}
	// Need to exec the query
	if(isset($SQL) AND $InputError != 1 ) {
		$Result = DB_query($SQL, $ErrMsg);
		if( $Result ) {
			prnMsg( $SuccessMsg,'success');
		}
	}
} elseif(isset($_POST['UpdateOrder'])) {
	//A calculation order update
	unset($Result);
	$SQL = "SELECT taxauthid FROM taxgrouptaxes WHERE taxgroupid='" . $SelectedGroup . "'";
	$Result = DB_query($SQL,__('Could not get tax authorities in the selected tax group'));

	while ($MyRow=DB_fetch_row($Result)) {

		if(is_numeric($_POST['CalcOrder_' . $MyRow[0]]) AND $_POST['CalcOrder_' . $MyRow[0]] < 10) {

			$SQL = "UPDATE taxgrouptaxes
				SET calculationorder='" . $_POST['CalcOrder_' . $MyRow[0]] . "',
					taxontax='" . $_POST['TaxOnTax_' . $MyRow[0]] . "'
				WHERE taxgroupid='" . $SelectedGroup . "'
				AND taxauthid='" . $MyRow[0] . "'";

			$UpdateResult = DB_query($SQL);
		}
	}

	//need to do a reality check to ensure that taxontax is relevant only for taxes after the first tax
	$SQL = "SELECT taxauthid,
					taxontax
			FROM taxgrouptaxes
			WHERE taxgroupid='" . $SelectedGroup . "'
			ORDER BY calculationorder";

	$Result = DB_query($SQL,__('Could not get tax authorities in the selected tax group'));

	if(DB_num_rows($Result)>0) {
		$MyRow=DB_fetch_array($Result);
		if($MyRow['taxontax']==1) {
			prnMsg(__('It is inappropriate to set tax on tax where the tax is the first in the calculation order. The system has changed it back to no tax on tax for this tax authority'),'warning');
			$Result = DB_query("UPDATE taxgrouptaxes SET taxontax=0
								WHERE taxgroupid='" . $SelectedGroup . "'
								AND taxauthid='" . $MyRow['taxauthid'] . "'");
		}
	}

} elseif(isset($_GET['Delete'])) {
	/* PREVENT DELETES IF DEPENDENT RECORDS IN 'custbranch, suppliers */
	$SQL= "SELECT COUNT(*) FROM custbranch WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_row($Result);
	if($MyRow[0]>0) {
		prnMsg( __('Cannot delete this tax group because some customer branches are setup using it'),'warn');
		echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('customer branches referring to this tax group');
	} else {
		$SQL= "SELECT COUNT(*) FROM suppliers
				WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
		$Result = DB_query($SQL);
		$MyRow = DB_fetch_row($Result);
		if($MyRow[0]>0) {
			prnMsg( __('Cannot delete this tax group because some suppliers are setup using it'),'warn');
			echo '<br />' . __('There are') . ' ' . $MyRow[0] . ' ' . __('suppliers referring to this tax group');
		} else {

			$SQL="DELETE FROM taxgrouptaxes
					WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
			$Result = DB_query($SQL);
			$SQL="DELETE FROM taxgroups
					WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
			$Result = DB_query($SQL);
			prnMsg( $_GET['GroupID'] . ' ' . __('tax group has been deleted') . '!','success');
		}
	} //end if taxgroup used in other tables
	unset($SelectedGroup);
	unset($_GET['GroupName']);
}

if(!isset($SelectedGroup)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax groups will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of tax group taxes*/

	$SQL = "SELECT taxgroupid,
					taxgroupdescription
			FROM taxgroups";
	$Result = DB_query($SQL);

	if( DB_num_rows($Result) == 0 ) {
		echo '<div class="centre">';
		prnMsg(__('There are no tax groups configured.'),'info');
		echo '</div>';
	} else {
		echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn" >' . __('Group No') . '</th>
					<th class="SortedColumn" >' . __('Tax Group') . '</th>
					<th colspan="2" >&nbsp;</th>
				</tr>
			</thead>
			<tbody>';

		while($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
					<td class="number">', $MyRow['taxgroupid'], '</td>
					<td>', $MyRow['taxgroupdescription'], '</td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedGroup=', $MyRow['taxgroupid'], '">' . __('Edit') . '</a></td>
					<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?&amp;SelectedGroup=', $MyRow['taxgroupid'], '&amp;Delete=1&amp;GroupID=', urlencode($MyRow['taxgroupdescription']), '" onclick="return confirm(\'' . __('Are you sure you wish to delete this tax group?') . '\');">' . __('Delete') . '</a></td>
					</tr>';

		} //END WHILE LIST LOOP
		echo '</tbody></table>';
	}
} //end of ifs and buts!

if(isset($SelectedGroup)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . __('Review Existing Groups') . '</a>
		</div>';
}

if(isset($SelectedGroup)) {
	//editing an existing role

	$SQL = "SELECT taxgroupid,
					taxgroupdescription
			FROM taxgroups
			WHERE taxgroupid='" . $SelectedGroup . "'";
	$Result = DB_query($SQL);
	if( DB_num_rows($Result) == 0 ) {
		prnMsg( __('The selected tax group is no longer available.'),'warn');
	} else {
		$MyRow = DB_fetch_array($Result);
		$_POST['SelectedGroup'] = $MyRow['taxgroupid'];
		$_POST['GroupName'] = $MyRow['taxgroupdescription'];
	}
}
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<fieldset>';
if( isset($_POST['SelectedGroup'])) {
	echo '<input type="hidden" name="SelectedGroup" value="' . $_POST['SelectedGroup'] . '" />';
	echo '<legend>', __('Edit Tax Group'), '</legend>';
} else {
	echo '<legend>', __('Create Tax Group'), '</legend>';
}


if(!isset($_POST['GroupName'])) {
	$_POST['GroupName']='';
}
echo '<field>
		<label for="GroupName">' . __('Tax Group') . ':</label>
		<input pattern="(?!^ +$)[^><+-]{4,}" title="" placeholder="'.__('4 to 40 legal characters').'" type="text" name="GroupName" size="40" maxlength="40" value="' . $_POST['GroupName'] . '" />
		<fieldhelp>'.__('The group name must be more 4 and less than 40 characters and cannot be left blank').'</fieldhelp>
	</field>';
echo '</fieldset>';

echo '<div class="centre">
		<input type="submit" name="submit" value="' . __('Enter Group') . '" />
	</div>
	</form>';

if(isset($SelectedGroup)) {
	$SQL = "SELECT taxid,
			description as taxname
			FROM taxauthorities
			ORDER BY taxid";

	$SQLUsed = "SELECT taxauthid,
				description AS taxname,
				calculationorder,
				taxontax
			FROM taxgrouptaxes INNER JOIN taxauthorities
				ON taxgrouptaxes.taxauthid=taxauthorities.taxid
			WHERE taxgroupid='". $SelectedGroup . "'
			ORDER BY calculationorder";

	$Result = DB_query($SQL);

	/*Make an array of the used tax authorities in calculation order */
	$UsedResult = DB_query($SQLUsed);
	$TaxAuthsUsed = array(); //this array just holds the taxauthid of all authorities in the group
	$TaxAuthRow = array(); //this array holds all the details of the tax authorities in the group
	$i=1;
	while($MyRow=DB_fetch_array($UsedResult)) {
		$TaxAuthsUsed[$i] = $MyRow['taxauthid'];
		$TaxAuthRow[$i] = $MyRow;
		$i++;
	}

	/* the order and tax on tax will only be an issue if more than one tax authority in the group */
	if(count($TaxAuthsUsed)>0) {
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<input type="hidden" name="SelectedGroup" value="' . $SelectedGroup .'" />';
		echo '<table class="selection">
				<tr>
					<th colspan="3"><h3>' . __('Calculation Order') . '</h3></th>
				</tr>
				<tr>
					<th>' . __('Tax Authority') . '</th>
					<th>' . __('Order') . '</th>
					<th>' . __('Tax on Prior Taxes') . '</th>
				</tr>';

		for ($i=1;$i < count($TaxAuthRow)+1;$i++) {

			if($TaxAuthRow[$i]['calculationorder']==0) {
				$TaxAuthRow[$i]['calculationorder'] = $i;
			}

			echo '<tr class="striped_row">
				<td>' . $TaxAuthRow[$i]['taxname'] . '</td>
				<td><input type="text" class="integer" pattern="(?!^0*$)(\d+)" title="'.__('The input must be positive integer and less than 10').'" name="CalcOrder_' . $TaxAuthRow[$i]['taxauthid'] . '" value="' . $TaxAuthRow[$i]['calculationorder'] . '" size="1" maxlength="1" style="width: 90%" /></td>
				<td><select name="TaxOnTax_' . $TaxAuthRow[$i]['taxauthid'] . '" style="width: 100%">';
			if($TaxAuthRow[$i]['taxontax']==1) {
				echo '<option selected="selected" value="1">' . __('Yes') . '</option>';
				echo '<option value="0">' . __('No') . '</option>';
			} else {
				echo '<option value="1">' . __('Yes') . '</option>';
				echo '<option selected="selected" value="0">' . __('No') . '</option>';
			}
			echo '</select></td>
				</tr>';

		}
		echo '</table>';
		echo '<div class="centre">
				<input type="submit" name="UpdateOrder" value="' . __('Update Order') . '" />
			</div>';
	}
	echo '</form>';

	if(DB_num_rows($Result)>0 ) {
		echo '<br /><table class="selection">
			<tr>
				<th colspan="4">' . __('Assigned Taxes') . '</th>
				<th rowspan="2">&nbsp;</th>
				<th colspan="2">' . __('Available Taxes') . '</th>
			</tr>
			<tr>
				<th>' . __('Tax Auth ID') . '</th>
				<th>' . __('Tax Authority Name') . '</th>
				<th>' . __('Calculation Order') . '</th>
				<th>' . __('Tax on Prior Tax(es)') . '</th>
				<th>' . __('Tax Auth ID') . '</th>
				<th>' . __('Tax Authority Name') . '</th>
			</tr>';

	} else {
		echo '<br /><div class="centre">' .
				__('There are no tax authorities defined to allocate to this tax group') .
			'</div>';
	}

	while($AvailRow = DB_fetch_array($Result)) {

		$TaxAuthUsedPointer = array_search($AvailRow['taxid'],$TaxAuthsUsed);

		if($TaxAuthUsedPointer) {
			if($TaxAuthRow[$TaxAuthUsedPointer]['taxontax'] ==1) {
				$TaxOnTax = __('Yes');
			} else {
				$TaxOnTax = __('No');
			}
			printf('<tr class="striped_row">
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="centre"><a href="%sSelectedGroup=%s&amp;remove=1&amp;TaxAuthority=%s" onclick="return confirm(\'' .
					__('Are you sure you wish to remove this tax authority from the group?') . '\');">' . __('Remove') . '</a></td>
				<td class="number">&nbsp;</td>
				<td>&nbsp;</td>
				</tr>',
				$AvailRow['taxid'],
				$AvailRow['taxname'],
				$TaxAuthRow[$TaxAuthUsedPointer]['calculationorder'],
				$TaxOnTax,
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?',
				$SelectedGroup,
				$AvailRow['taxid']
				);

		} else {
			printf('<tr class="striped_row">
				<td class="number">&nbsp;</td>
				<td>&nbsp;</td>
				<td class="number">&nbsp;</td>
				<td>&nbsp;</td>
				<td class="centre"><a href="%sSelectedGroup=%s&amp;add=1&amp;TaxAuthority=%s">' .
					__('Add') . '</a></td>
				<td class="number">%s</td>
				<td>%s</td>
				</tr>',
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?',
				$SelectedGroup,
				$AvailRow['taxid'],
				$AvailRow['taxid'],
				$AvailRow['taxname']);
		}
	}
	echo '</table>';
}

echo '<div class="centre">
		<a href="' . $RootPath . '/TaxAuthorities.php">' . __('Tax Authorities and Rates Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxProvinces.php">' . __('Dispatch Tax Province Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxCategories.php">' . __('Tax Category Maintenance') .  '</a>
	</div>';

include('includes/footer.php');
