<?php

include('includes/session.php');
$Title = _('Tax Groups');
$ViewTopic = 'Tax';// Filename in ManualContents.php's TOC.
$BookMark = 'TaxGroups';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
		'/images/maintenance.png" title="' .
		_('Tax Group Maintenance') . '" />' . ' ' .
		_('Tax Group Maintenance') . '</p>';

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
		prnMsg(_('The Group description entered must be at least 4 characters long'),'error');
	}

	// if $_POST['GroupName'] then it is a modification of a tax group name
	// else it is either an add or remove of taxgroup
	unset($sql);
	if(isset($_POST['GroupName']) ) { // Update or Add a tax group
		if(isset($SelectedGroup)) { // Update a tax group
			$sql = "UPDATE taxgroups SET taxgroupdescription = '". $_POST['GroupName'] ."'
					WHERE taxgroupid = '".$SelectedGroup . "'";
			$ErrMsg = _('The update of the tax group description failed because');
			$SuccessMsg = _('The tax group description was updated to') . ' ' . $_POST['GroupName'];
		} else { // Add new tax group

			$result = DB_query("SELECT taxgroupid
								FROM taxgroups
								WHERE taxgroupdescription='" . $_POST['GroupName'] . "'");
			if(DB_num_rows($result)==1) {
				prnMsg( _('A new tax group could not be added because a tax group already exists for') . ' ' . $_POST['GroupName'],'warn');
				unset($sql);
			} else {
				$sql = "INSERT INTO taxgroups (taxgroupdescription)
						VALUES ('". $_POST['GroupName'] . "')";
				$ErrMsg = _('The addition of the group failed because');
				$SuccessMsg = _('Added the new tax group') . ' ' . $_POST['GroupName'];
			}
		}
		unset($_POST['GroupName']);
		unset($SelectedGroup);
	} elseif(isset($SelectedGroup) ) {
		$TaxAuthority = $_GET['TaxAuthority'];
		if( isset($_GET['add']) ) { // adding a tax authority to a tax group

			$sql = "INSERT INTO taxgrouptaxes ( taxgroupid,
												taxauthid,
												calculationorder)
					VALUES ('" . $SelectedGroup . "',
							'" . $TaxAuthority . "',
							0)";

			$ErrMsg = _('The addition of the tax failed because');
			$SuccessMsg = _('The tax was added.');
		} elseif( isset($_GET['remove']) ) { // remove a taxauthority from a tax group
			$sql = "DELETE FROM taxgrouptaxes
					WHERE taxgroupid = '".$SelectedGroup."'
					AND taxauthid = '".$TaxAuthority . "'";
			$ErrMsg = _('The removal of this tax failed because');
			$SuccessMsg = _('This tax was removed.');
		}
		unset($_GET['add']);
		unset($_GET['remove']);
		unset($_GET['TaxAuthority']);
	}
	// Need to exec the query
	if(isset($sql) AND $InputError != 1 ) {
		$result = DB_query($sql,$ErrMsg);
		if( $result ) {
			prnMsg( $SuccessMsg,'success');
		}
	}
} elseif(isset($_POST['UpdateOrder'])) {
	//A calculation order update
	$sql = "SELECT taxauthid FROM taxgrouptaxes WHERE taxgroupid='" . $SelectedGroup . "'";
	$Result = DB_query($sql,_('Could not get tax authorities in the selected tax group'));

	while($myrow=DB_fetch_row($Result)) {

		if(is_numeric($_POST['CalcOrder_' . $myrow[0]]) AND $_POST['CalcOrder_' . $myrow[0]] < 10) {

			$sql = "UPDATE taxgrouptaxes
				SET calculationorder='" . $_POST['CalcOrder_' . $myrow[0]] . "',
					taxontax='" . $_POST['TaxOnTax_' . $myrow[0]] . "'
				WHERE taxgroupid='" . $SelectedGroup . "'
				AND taxauthid='" . $myrow[0] . "'";

			$result = DB_query($sql);
		}
	}

	//need to do a reality check to ensure that taxontax is relevant only for taxes after the first tax
	$sql = "SELECT taxauthid,
					taxontax
			FROM taxgrouptaxes
			WHERE taxgroupid='" . $SelectedGroup . "'
			ORDER BY calculationorder";

	$Result = DB_query($sql,_('Could not get tax authorities in the selected tax group'));

	if(DB_num_rows($Result)>0) {
		$myrow=DB_fetch_array($Result);
		if($myrow['taxontax']==1) {
			prnMsg(_('It is inappropriate to set tax on tax where the tax is the first in the calculation order. The system has changed it back to no tax on tax for this tax authority'),'warning');
			$Result = DB_query("UPDATE taxgrouptaxes SET taxontax=0
								WHERE taxgroupid='" . $SelectedGroup . "'
								AND taxauthid='" . $myrow['taxauthid'] . "'");
		}
	}

} elseif(isset($_GET['Delete'])) {
	/* PREVENT DELETES IF DEPENDENT RECORDS IN 'custbranch, suppliers */
	$sql= "SELECT COUNT(*) FROM custbranch WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if($myrow[0]>0) {
		prnMsg( _('Cannot delete this tax group because some customer branches are setup using it'),'warn');
		echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('customer branches referring to this tax group');
	} else {
		$sql= "SELECT COUNT(*) FROM suppliers
				WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if($myrow[0]>0) {
			prnMsg( _('Cannot delete this tax group because some suppliers are setup using it'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('suppliers referring to this tax group');
		} else {

			$sql="DELETE FROM taxgrouptaxes
					WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
			$result = DB_query($sql);
			$sql="DELETE FROM taxgroups
					WHERE taxgroupid='" . $_GET['SelectedGroup'] . "'";
			$result = DB_query($sql);
			prnMsg( $_GET['GroupID'] . ' ' . _('tax group has been deleted') . '!','success');
		}
	} //end if taxgroup used in other tables
	unset($SelectedGroup);
	unset($_GET['GroupName']);
}

if(!isset($SelectedGroup)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax groups will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of tax group taxes*/

	$sql = "SELECT taxgroupid,
					taxgroupdescription
			FROM taxgroups";
	$result = DB_query($sql);

	if( DB_num_rows($result) == 0 ) {
		echo '<div class="centre">';
		prnMsg(_('There are no tax groups configured.'),'info');
		echo '</div>';
	} else {
		echo '<table class="selection">
			<thead>
				<tr>
					<th class="ascending" >' . _('Group No') . '</th>
					<th class="ascending" >' . _('Tax Group') . '</th>
					<th colspan="2" >&nbsp;</th>
				</tr>
			</thead>
			<tbody>';

		while($myrow = DB_fetch_array($result)) {
			printf('<tr class="striped_row">
					<td class="number">%s</td>
					<td>%s</td>
					<td><a href="%s&amp;SelectedGroup=%s">' . _('Edit') . '</a></td>
					<td><a href="%s&amp;SelectedGroup=%s&amp;Delete=1&amp;GroupID=%s" onclick="return confirm(\'' . _('Are you sure you wish to delete this tax group?') . '\');">' . _('Delete') . '</a></td>
					</tr>',
					$myrow['taxgroupid'],
					$myrow['taxgroupdescription'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?',
					$myrow['taxgroupid'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$myrow['taxgroupid'],
					urlencode($myrow['taxgroupdescription']));

		} //END WHILE LIST LOOP
		echo '</tbody></table>';
	}
} //end of ifs and buts!

if(isset($SelectedGroup)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Review Existing Groups') . '</a>
		</div>';
}

if(isset($SelectedGroup)) {
	//editing an existing role

	$sql = "SELECT taxgroupid,
					taxgroupdescription
			FROM taxgroups
			WHERE taxgroupid='" . $SelectedGroup . "'";
	$result = DB_query($sql);
	if( DB_num_rows($result) == 0 ) {
		prnMsg( _('The selected tax group is no longer available.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['SelectedGroup'] = $myrow['taxgroupid'];
		$_POST['GroupName'] = $myrow['taxgroupdescription'];
	}
}
echo '<br />';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if( isset($_POST['SelectedGroup'])) {
	echo '<input type="hidden" name="SelectedGroup" value="' . $_POST['SelectedGroup'] . '" />';
}
echo '<table class="selection">';

if(!isset($_POST['GroupName'])) {
	$_POST['GroupName']='';
}
echo '<tr><td>' . _('Tax Group') . ':</td>
		<td><input pattern="(?!^ +$)[^><+-]{4,}" title="'._('The group name must be more 4 and less than 40 characters and cannot be left blank').'" placeholder="'._('4 to 40 legal characters').'" type="text" name="GroupName" size="40" maxlength="40" value="' . $_POST['GroupName'] . '" /></td>';
echo '<td><input type="submit" name="submit" value="' . _('Enter Group') . '" /></td>
	</tr>
    </table>
    <br />
    </div>
	</form>';

if(isset($SelectedGroup)) {
	$sql = "SELECT taxid,
			description as taxname
			FROM taxauthorities
			ORDER BY taxid";

	$sqlUsed = "SELECT taxauthid,
				description AS taxname,
				calculationorder,
				taxontax
			FROM taxgrouptaxes INNER JOIN taxauthorities
				ON taxgrouptaxes.taxauthid=taxauthorities.taxid
			WHERE taxgroupid='". $SelectedGroup . "'
			ORDER BY calculationorder";

	$Result = DB_query($sql);

	/*Make an array of the used tax authorities in calculation order */
	$UsedResult = DB_query($sqlUsed);
	$TaxAuthsUsed = array(); //this array just holds the taxauthid of all authorities in the group
	$TaxAuthRow = array(); //this array holds all the details of the tax authorities in the group
	$i=1;
	while($myrow=DB_fetch_array($UsedResult)) {
		$TaxAuthsUsed[$i] = $myrow['taxauthid'];
		$TaxAuthRow[$i] = $myrow;
		$i++;
	}

	/* the order and tax on tax will only be an issue if more than one tax authority in the group */
	if(count($TaxAuthsUsed)>0) {
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
        echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
				<input type="hidden" name="SelectedGroup" value="' . $SelectedGroup .'" />';
		echo '<table class="selection">
				<tr>
					<th colspan="3"><h3>' . _('Calculation Order') . '</h3></th>
				</tr>
				<tr>
					<th>' . _('Tax Authority') . '</th>
					<th>' . _('Order') . '</th>
					<th>' . _('Tax on Prior Taxes') . '</th>
				</tr>';

		for ($i=1;$i < count($TaxAuthRow)+1;$i++) {

			if($TaxAuthRow[$i]['calculationorder']==0) {
				$TaxAuthRow[$i]['calculationorder'] = $i;
			}

			echo '<tr class="striped_row">
				<td>' . $TaxAuthRow[$i]['taxname'] . '</td>
				<td><input type="text" class="integer" pattern="(?!^0*$)(\d+)" title="'._('The input must be positive integer and less than 10').'" name="CalcOrder_' . $TaxAuthRow[$i]['taxauthid'] . '" value="' . $TaxAuthRow[$i]['calculationorder'] . '" size="1" maxlength="1" style="width: 90%" /></td>
				<td><select name="TaxOnTax_' . $TaxAuthRow[$i]['taxauthid'] . '" style="width: 100%">';
			if($TaxAuthRow[$i]['taxontax']==1) {
				echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
				echo '<option value="0">' . _('No') . '</option>';
			} else {
				echo '<option value="1">' . _('Yes') . '</option>';
				echo '<option selected="selected" value="0">' . _('No') . '</option>';
			}
			echo '</select></td>
				</tr>';

		}
		echo '</table>';
		echo '<br />
			<div class="centre">
				<input type="submit" name="UpdateOrder" value="' . _('Update Order') . '" />
			</div>';
	}
	echo '</div></form>';

	if(DB_num_rows($Result)>0 ) {
		echo '<br /><table class="selection">
			<tr>
				<th colspan="4">' . _('Assigned Taxes') . '</th>
				<th rowspan="2">&nbsp;</th>
				<th colspan="2">' . _('Available Taxes') . '</th>
			</tr>
			<tr>
				<th>' . _('Tax Auth ID') . '</th>
				<th>' . _('Tax Authority Name') . '</th>
				<th>' . _('Calculation Order') . '</th>
				<th>' . _('Tax on Prior Tax(es)') . '</th>
				<th>' . _('Tax Auth ID') . '</th>
				<th>' . _('Tax Authority Name') . '</th>
			</tr>';

	} else {
		echo '<br /><div class="centre">' .
				_('There are no tax authorities defined to allocate to this tax group') .
			'</div>';
	}

	while($AvailRow = DB_fetch_array($Result)) {

		$TaxAuthUsedPointer = array_search($AvailRow['taxid'],$TaxAuthsUsed);

		if($TaxAuthUsedPointer) {
			if($TaxAuthRow[$TaxAuthUsedPointer]['taxontax'] ==1) {
				$TaxOnTax = _('Yes');
			} else {
				$TaxOnTax = _('No');
			}
			printf('<tr class="striped_row">
				<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td>%s</td>
				<td class="centre"><a href="%sSelectedGroup=%s&amp;remove=1&amp;TaxAuthority=%s" onclick="return confirm(\'' .
					_('Are you sure you wish to remove this tax authority from the group?') . '\');">' . _('Remove') . '</a></td>
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
					_('Add') . '</a></td>
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

echo '<br />
	<div class="centre">
		<a href="' . $RootPath . '/TaxAuthorities.php">' . _('Tax Authorities and Rates Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxProvinces.php">' . _('Dispatch Tax Province Maintenance') .  '</a><br />
		<a href="' . $RootPath . '/TaxCategories.php">' . _('Tax Category Maintenance') .  '</a>
	</div>';

include('includes/footer.php');
?>
