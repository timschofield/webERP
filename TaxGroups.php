<?php
/* $Revision: 1.10 $ */
$PageSecurity=15;

include('includes/session.inc');

$title = _('Tax Groups');
include('includes/header.inc');

if (isset($_GET['SelectedGroup'])){
	$SelectedGroup = $_GET['SelectedGroup'];
} elseif (isset($_POST['SelectedGroup'])){
	$SelectedGroup = $_POST['SelectedGroup'];
}

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'<br>';

if (isset($_POST['submit']) OR isset($_GET['remove']) OR isset($_GET['add']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	//first off validate inputs sensible
	if (isset($_POST['GroupName']) && strlen($_POST['GroupName'])<4){
		$InputError = 1;
		prnMsg(_('The Group description entered must be at least 4 characters long'),'error');
	}
	
	// if $_POST['GroupName'] then it is a modification of a tax group name
	// else it is either an add or remove of taxgroup 
	unset($sql);
	if (isset($_POST['GroupName']) ){ // Update or Add a tax group
		if(isset($SelectedGroup)) { // Update a tax group
			$sql = "UPDATE taxgroups SET taxgroupdescription = '". $_POST['GroupName'] ."' 
					WHERE taxgroupid = ".$SelectedGroup;
			$ErrMsg = _('The update of the tax group description failed because');
			$SuccessMsg = _('The tax group description was updated to') . ' ' . $_POST['GroupName'];
		} else { // Add new tax group
		
			$result = DB_query("SELECT taxgroupid FROM taxgroups WHERE taxgroupdescription='" . $_POST['GroupName'] . "'",$db);
			if (DB_num_rows($result)==1){
				prnMsg( _('A new tax group could not be added because a tax group already exists for') . ' ' . $_POST['GroupName'],'warn');
				unset($sql);
			} else {
				$sql = "INSERT INTO taxgroups (taxgroupdescription) VALUES ('". $_POST['GroupName'] . "')";
				$ErrMsg = _('The addition of the group failed because');
				$SuccessMsg = _('Added the new tax group') . ' ' . $_POST['GroupName'];
			}
		}
		unset($_POST['GroupName']);
		unset($SelectedGroup);
	} elseif (isset($SelectedGroup) ) {
		$TaxAuthority = $_GET['TaxAuthority'];
		if( isset($_GET['add']) ) { // adding a tax authority to a tax group
			$sql = "INSERT INTO taxgrouptaxes ( taxgroupid, 
								taxauthid,
								calculationorder) 
					VALUES (" . $SelectedGroup . ", 
						" . $TaxAuthority . ",
						0)";
					
			$ErrMsg = _('The addition of the tax failed because');
			$SuccessMsg = _('The tax was added.');
		} elseif ( isset($_GET['remove']) ) { // remove a taxauthority from a tax group
			$sql = "DELETE FROM taxgrouptaxes 
					WHERE taxgroupid = ".$SelectedGroup."
					AND taxauthid = ".$TaxAuthority;
			$ErrMsg = _('The removal of this tax failed because');
			$SuccessMsg = _('This tax was removed.');
		}
		unset($_GET['add']);
		unset($_GET['remove']);
		unset($_GET['TaxAuthority']);
	}
	// Need to exec the query
	if (isset($sql) && $InputError != 1 ) {
		$result = DB_query($sql,$db,$ErrMsg);
		if( $result ) {
			prnMsg( $SuccessMsg,'success');
		}
	}
} elseif (isset($_POST['UpdateOrder'])) {
	//A calculation order update
	$sql = 'SELECT taxauthid FROM taxgrouptaxes WHERE taxgroupid=' . $SelectedGroup;
	$Result = DB_query($sql,$db,_('Could not get tax authorities in the selected tax group'));
	
	while ($myrow=DB_fetch_row($Result)){

		if (is_numeric($_POST['CalcOrder_' . $myrow[0]]) AND $_POST['CalcOrder_' . $myrow[0]] <5){
		
			$sql = 'UPDATE taxgrouptaxes 
				SET calculationorder=' . $_POST['CalcOrder_' . $myrow[0]] . ',
					taxontax=' . $_POST['TaxOnTax_' . $myrow[0]] . '
				WHERE taxgroupid=' . $SelectedGroup . '
				AND taxauthid=' . $myrow[0];
						
			$result = DB_query($sql,$db);
		}
	}
	
	//need to do a reality check to ensure that taxontax is relevant only for taxes after the first tax
	$sql = 'SELECT taxauthid, 
			taxontax 
		FROM taxgrouptaxes 
		WHERE taxgroupid=' . $SelectedGroup . '
		ORDER BY calculationorder';
		
	$Result = DB_query($sql,$db,_('Could not get tax authorities in the selected tax group'));
	
	if (DB_num_rows($Result)>0){
		$myrow=DB_fetch_array($Result);
		if ($myrow['taxontax']==1){
			prnMsg(_('It is inappropriate to set tax on tax where the tax is the first in the calculation order. The system has changed it back to no tax on tax for this tax authority'),'warning');
			$Result = DB_query('UPDATE taxgrouptaxes SET taxontax=0 WHERE taxgroupid=' . $SelectedGroup . ' AND taxauthid=' . $myrow['taxauthid'],$db);
		}
	}
} elseif (isset($_GET['Delete'])) { 
	
	/* PREVENT DELETES IF DEPENDENT RECORDS IN 'custbranch, suppliers */
	
	$sql= "SELECT COUNT(*) FROM custbranch WHERE taxgroupid=" . $_GET['SelectedGroup'];
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg( _('Cannot delete this tax group because some customer branches are setup using it'),'warn');
		echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('customer branches referring to this tax group');
	} else {
		$sql= "SELECT COUNT(*) FROM suppliers WHERE taxgroupid=" . $_GET['SelectedGroup'];
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('Cannot delete this tax group because some suppliers are setup using it'),'warn');
			echo '<br>' . _('There are') . ' ' . $myrow[0] . ' ' . _('suppliers referring to this tax group');
		} else {
	
			$sql="DELETE FROM taxgrouptaxes WHERE taxgroupid=" . $_GET['SelectedGroup'];
			$result = DB_query($sql,$db);
			$sql="DELETE FROM taxgroups WHERE taxgroupid=" . $_GET['SelectedGroup'];
			$result = DB_query($sql,$db);
			prnMsg( $_GET['GroupID'] . ' ' . _('tax group has been deleted') . '!','success');
		}
	} //end if taxgroup used in other tables
	unset($SelectedGroup);
	unset($_GET['GroupName']);
}

if (!isset($SelectedGroup)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of tax groups will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of tax group taxes*/

	$sql = "SELECT taxgroupid,
			taxgroupdescription
		FROM taxgroups";
	$result = DB_query($sql,$db);

	if( DB_num_rows($result) == 0 ) {
		echo '<div class="centre">';
		prnMsg(_('There are no tax groups configured.'),'info');
		echo '</div>';
	} else {
		echo '<table border=1>';
		echo "<tr><th>" . _('Group No') . "</th>
			<th>" . _('Tax Group') . "</th></tr>";
	
		$k=0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
	
			printf("<td>%s</td>
				<td>%s</td>
				<td><a href=\"%s&SelectedGroup=%s\">" . _('Edit') . "</a></td>
				<td><a href=\"%s&SelectedGroup=%s&Delete=1&GroupID=%s\">" . _('Delete') . "</a></td>
				</tr>",
				$myrow['taxgroupid'],
				$myrow['taxgroupdescription'],
				$_SERVER['PHP_SELF']  . "?" . SID,
				$myrow['taxgroupid'],
				$_SERVER['PHP_SELF'] . "?" . SID,
				$myrow['taxgroupid'],
				urlencode($myrow['taxgroupdescription']));
	
		} //END WHILE LIST LOOP
		echo '</table>';
	}
} //end of ifs and buts!


if (isset($SelectedGroup)) {
	echo '<div class="centre"><a href="' . $_SERVER['PHP_SELF'] ."?" . SID . '">' . _('Review Existing Groups') . '</a></div>';
}

if (isset($SelectedGroup)) {
	//editing an existing role

	$sql = "SELECT taxgroupid,
			taxgroupdescription
		FROM taxgroups
		WHERE taxgroupid=" . $SelectedGroup;
	$result = DB_query($sql, $db);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('The selected tax group is no longer available.'),'warn');
	} else {
		$myrow = DB_fetch_array($result);
		$_POST['SelectedGroup'] = $myrow['taxgroupid'];
		$_POST['GroupName'] = $myrow['taxgroupdescription'];
	}
}
echo '<br>';
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
if( isset($_POST['SelectedGroup'])) {
	echo "<input type=hidden name='SelectedGroup' value='" . $_POST['SelectedGroup'] . "'>";
}
echo '<table>';

if (!isset($_POST['GroupName'])) {
	$_POST['GroupName']='';
}
echo '<tr><td>' . _('Tax Group') . ":</td>
		<td><input type='text' name='GroupName' size=40 maxlength=40 value='" . $_POST['GroupName'] . "'></td>";
echo '<td><input type="submit" name="submit" value="' . _('Enter Group') . '"></td></tr></form>';


if (isset($SelectedGroup)) {
	echo '</table><br>';
	
	$sql = 'SELECT taxid, 
			description as taxname 
			FROM taxauthorities
		ORDER BY taxid';
	
	$sqlUsed = "SELECT taxauthid,
				description AS taxname,
				calculationorder, 
				taxontax 
			FROM taxgrouptaxes INNER JOIN taxauthorities
				ON taxgrouptaxes.taxauthid=taxauthorities.taxid
			WHERE taxgroupid=". $SelectedGroup . ' 
			ORDER BY calculationorder';
	
	$Result = DB_query($sql, $db);
	
	/*Make an array of the used tax authorities in calculation order */
	$UsedResult = DB_query($sqlUsed, $db);
	$TaxAuthsUsed = array(); //this array just holds the taxauthid of all authorities in the group
	$TaxAuthRow = array(); //this array holds all the details of the tax authorities in the group
	$i=1;
	while ($myrow=DB_fetch_array($UsedResult)){
		$TaxAuthsUsed[$i] = $myrow['taxauthid'];
		$TaxAuthRow[$i] = $myrow;
		$i++;
	}
	
	/* the order and tax on tax will only be an issue if more than one tax authority in the group */
	if (count($TaxAuthsUsed)>0) { 
		echo '<div class="centre"><font size=3 color=blue>'._('Calculation Order').'</font></div>';
		echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID .'">';
		echo '<input type=hidden name="SelectedGroup" value="' . $SelectedGroup .'">';
		echo '<table>';
			
		echo '<tr><th>'._('Tax Authority').'</th>
			<th>'._('Order').'</th>
			<th>'._('Tax on Prior Taxes').'</th></tr>';
		$k=0; //row colour counter
		for ($i=1;$i < count($TaxAuthRow)+1;$i++) {
			if ($k==1){
				echo '<tr class="OddTableRows">';
				$k=0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k=1;
			}
			
			if ($TaxAuthRow[$i]['calculationorder']==0){
				$TaxAuthRow[$i]['calculationorder'] = $i;
			}
			
			echo '<td>' . $TaxAuthRow[$i]['taxname'] . '</td><td>'.
				'<input type="text" class="number" name="CalcOrder_' . $TaxAuthRow[$i]['taxauthid'] . '" value="' . 
					$TaxAuthRow[$i]['calculationorder'] . '" size=2 maxlength=2 onKeyPress="return restrictToNumbers(this, event)" 
					 style="width: 100%"></td>';
			echo '<td><select name="TaxOnTax_' . $TaxAuthRow[$i]['taxauthid'] . '" style="width: 100%">';
			if ($TaxAuthRow[$i]['taxontax']==1){
				echo '<option selected value=1>' . _('Yes');
				echo '<option value=0>' . _('No');
			} else {
				echo '<option value=1>' . _('Yes');
				echo '<option selected value=0>' . _('No');
			}
			echo '</select></td></tr>';       
			
		}
		echo '</table>';
		echo '<br><div class="centre"><input type="submit" name="UpdateOrder" value="' . _('Update Order') . '"></div>';
	}
	
	echo '</form>';
	
	if (DB_num_rows($Result)>0 ) {
		echo '<br>';
		echo '<table><tr>';
		echo "<th colspan=4>"._('Assigned Taxes')."</th>";
		echo '<th></th>';
		echo "<th colspan=2>"._('Available Taxes')."</th>";
		echo '</tr>';

		echo '<tr>';		
		echo "<th>" . _('Tax Auth ID') . '</th>';
		echo "<th>" . _('Tax Authority Name') . '</th>';
		echo "<th>" . _('Calculation Order') . '</th>';
		echo "<th>" . _('Tax on Prior Tax(es)') . '</th>';
		echo '<th></th>';
		echo "<th>" . _('Tax Auth ID') . '</th>';
		echo "<th>" . _('Tax Authority Name') . '</th>';
		echo '</tr>';
		
	} else {
		echo '<br><div class="centre">' . _('There are no tax authorities defined to allocate to this tax group').'</div>';
	}
	
	$k=0; //row colour counter
	while($AvailRow = DB_fetch_array($Result)) {
				
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$TaxAuthUsedPointer = array_search($AvailRow['taxid'],$TaxAuthsUsed);
		
		if ($TaxAuthUsedPointer){
			
			if ($TaxAuthRow[$TaxAuthUsedPointer]['taxontax'] ==1){
				$TaxOnTax = _('Yes');
			} else {
				$TaxOnTax = _('No');
			}
			
			printf("<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href=\"%s&SelectedGroup=%s&remove=1&TaxAuthority=%s\">" . _('Remove') . "</a></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>",
				$AvailRow['taxid'],
				$AvailRow['taxname'],
				$TaxAuthRow[$TaxAuthUsedPointer]['calculationorder'],
				$TaxOnTax,
				$_SERVER['PHP_SELF']  . "?" . SID,
				$SelectedGroup,
				$AvailRow['taxid']
				);
			
		} else {
			printf("<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href=\"%s&SelectedGroup=%s&add=1&TaxAuthority=%s\">" . _('Add') . "</a></td>",
				$AvailRow['taxid'],
				$AvailRow['taxname'],
				$_SERVER['PHP_SELF']  . "?" . SID,
				$SelectedGroup,
				$AvailRow['taxid']
				);
		}	
		echo '</tr>';
	}
	echo '</table>';
	
}

include('includes/footer.inc');

?>