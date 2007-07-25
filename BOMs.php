<?php
/* $Revision: 1.25 $ */

$PageSecurity = 9;

include('includes/session.inc');

$title = _('Multi-Level Bill Of Materials Maintenance');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');


// *** POPAD&T -  ... Phil modified to english variables
function display_children($parent, $level, &$BOMTree) {

	global $db;
	global $i;
	
	// retrive all children of parent
	$c_result = DB_query("SELECT parent,
					component
				FROM bom WHERE parent='" . $parent. "'"
				 ,$db);
	if (DB_num_rows($c_result) > 0) {
		//echo ("<UL>\n");
		
		
		while ($row = DB_fetch_array($c_result)) {
			//echo '<BR>Parent: ' . $parent . ' Level: ' . $level . ' row[component]: ' . $row['component'] .'<BR>';
			if ($parent != $row['component']) {
				// indent and display the title of this child
				$BOMTree[$i]['Level'] = $level; 		// Level
				if ($level > 15) {
					prnMsg(_('A maximum of 15 levels of bill of materials only can be displayed'),'error');
					exit;
				}
				$BOMTree[$i]['Parent'] = $parent;		// Assemble
				$BOMTree[$i]['Component'] = $row['component'];	// Component
				// call this function again to display this
				// child's children
				$i++;
				display_children($row['component'], $level + 1, $BOMTree);
			}
		}
	}
}


function CheckForRecursiveBOM ($UltimateParent, $ComponentToCheck, $db) {

/* returns true ie 1 if the BOM contains the parent part as a component
ie the BOM is recursive otherwise false ie 0 */

	$sql = "SELECT component FROM bom WHERE parent='$ComponentToCheck'";
	$ErrMsg = _('An error occurred in retrieving the components of the BOM during the check for recursion');
	$DbgMsg = _('The SQL that was used to retrieve the components of the BOM and that failed in the process was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if ($result!=0) {
		while ($myrow=DB_fetch_row($result)){
			if ($myrow[0]==$UltimateParent){
				return 1;
			}
			if (CheckForRecursiveBOM($UltimateParent, $myrow[0],$db)){
				return 1;
			}
		} //(while loop)
	} //end if $result is true

	return 0;

} //end of function CheckForRecursiveBOM

function DisplayBOMItems($UltimateParent, $Parent, $Component,$Level, $db) {

		global $ParentMBflag;
		// Modified by POPAD&T
		$sql = "SELECT bom.component,
				stockmaster.description,
				locations.locationname,
				workcentres.description,
				bom.quantity,
				bom.effectiveafter,
				bom.effectiveto,
				stockmaster.mbflag,
				bom.autoissue,
				stockmaster.controlled,
				locstock.quantity AS qoh,
				stockmaster.decimalplaces
			FROM bom,
				stockmaster,
				locations,
				workcentres,
				locstock
			WHERE bom.component='$Component'
			AND bom.parent = '$Parent'
			AND bom.component=stockmaster.stockid
			AND bom.loccode = locations.loccode
			AND locstock.loccode=bom.loccode
			AND bom.component = locstock.stockid
			AND bom.workcentreadded=workcentres.code
			AND stockmaster.stockid=bom.component";

		$ErrMsg = _('Could not retrieve the BOM components because');
		$DbgMsg = _('The SQL used to retrieve the components was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		//echo $TableHeader;
		$RowCounter =0;

		while ($myrow=DB_fetch_row($result)) {

			$Level1 = str_repeat('-&nbsp;',$Level-1).$Level;
			if( $myrow[7]=='B' || $myrow[7]=='K') {
				$DrillText = '%s%s';
				$DrillLink = '<center>----</center>';
				$DrillID='';
			} else {
				$DrillText = '<a href="%s&Select=%s">' . _('Drill Down');
				$DrillLink = $_SERVER['PHP_SELF'] . '?' . SID;
				$DrillID=$myrow[0];
			}
			if ($ParentMBflag!='M'){
				$AutoIssue = _('N/A');
			} elseif ($myrow[9]==0 AND $myrow[8]==1){//autoissue and not controlled
				$AutoIssue = _('Yes');
			} elseif ($myrow[9]==0) {
				$AutoIssue = _('No');
			} else {
				$AutoIssue = _('N/A');
			}
			printf("<td>%s</td>
				<td>%s</td>
			    <td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td align=right>%s</td>
				<td><a href=\"%s&Select=%s&SelectedComponent=%s\">" . _('Edit') . "</a></td>
				<td>".$DrillText."</a></td>
				 <td><a href=\"%s&Select=%s&SelectedComponent=%s&delete=1&ReSelect=%s\">" . _('Delete') . "</a></td>
				 </tr>",
				$Level1,
				$myrow[0],
				$myrow[1],
				$myrow[2],
				$myrow[3],
				$myrow[4],
				ConvertSQLDate($myrow[5]),
				ConvertSQLDate($myrow[6]),
				$AutoIssue,
				number_format($myrow[10],$myrow[11]),
				$_SERVER['PHP_SELF'] . '?' . SID,
				$Parent,
				$myrow[0],
				$DrillLink,
				$DrillID,
				$_SERVER['PHP_SELF'] . '?' . SID,
				$Parent,
				$myrow[0],
				$UltimateParent);

			$RowCounter++;
			if ($RowCounter==20){
				echo $TableHeader;
				$RowCounter=0;
			}
		} //END WHILE LIST LOOP
} //end of function DisplayBOMItems

//---------------------------------------------------------------------------------

/* SelectedParent could come from a post or a get */
if (isset($_GET['SelectedParent'])){
	$SelectedParent = $_GET['SelectedParent'];
}else if (isset($_POST['SelectedParent'])){
	$SelectedParent = $_POST['SelectedParent'];
}



/* SelectedComponent could also come from a post or a get */
if (isset($_GET['SelectedComponent'])){
	$SelectedComponent = $_GET['SelectedComponent'];
} elseif (isset($_POST['SelectedComponent'])){
	$SelectedComponent = $_POST['SelectedComponent'];
}

if (isset($_GET['Select'])){
	$Select = $_GET['Select'];
} elseif (isset($_POST['Select'])){
	$Select = $_POST['Select'];
}


$msg='';

if (isset($Select)) { //Parent Stock Item selected so display BOM or edit Component
	$SelectedParent = $Select;
	unset($Select);// = NULL;

	If (isset($SelectedParent) AND isset($_POST['Submit'])) {

		//editing a component need to do some validation of inputs

		if (!Is_Date($_POST['EffectiveAfter'])) {
			$InputError = 1;
			prnMsg(_('The effective after date field must be a date in the format dd/mm/yy or dd/mm/yyyy or ddmmyy or ddmmyyyy or dd-mm-yy or dd-mm-yyyy'),'error');
			includes('includes/footer.inc');
			exit;
		} elseif (!Is_Date($_POST['EffectiveTo'])) {
			$InputError = 1;
			prnMsg(_('The effective to date field must be a date in the format dd/mm/yy or dd/mm/yyyy or ddmmyy or ddmmyyyy or dd-mm-yy or dd-mm-yyyy'),'error');
			includes('includes/footer.inc');
			exit;
		} elseif (!is_double((double) $_POST['Quantity'])) {
			$InputError = 1;
			prnMsg(_('The quantity entered must be numeric'),'error');
			includes('includes/footer.inc');
			exit;
		} elseif(!Date1GreaterThanDate2($_POST['EffectiveTo'], $_POST['EffectiveAfter'])){
			$InputError = 1;
			prnMsg(_('The effective to date must be a date after the effective after date') . '<BR>' . _('The effective to date is') . ' ' . DateDiff($_POST['EffectiveTo'], $_POST['EffectiveAfter'], 'd') . ' ' . _('days before the effective after date') . '! ' . _('No updates have been performed') . '.<BR>' . _('Effective after was') . ': ' . $_POST['EffectiveAfter'] . ' ' . _('and effective to was') . ': ' . $_POST['EffectiveTo'],'error');
			includes('includes/footer.inc');
			exit;
		} elseif($_POST['AutoIssue']==1){
			$sql = "SELECT controlled FROM stockmaster WHERE stockid='" . $_POST['Component'] . "'";
			$CheckControlledResult = DB_query($sql,$db);
			$CheckControlledRow = DB_fetch_row($CheckControlledResult);
			if ($CheckControlledRow[0]==1){
				prnMsg(_('Only non-serialised or non-lot controlled items can be set to auto issue. These items require the lot/serial numbers of items issued to the works orders to be specified so autoissue is not an option. Auto issue has been automatically set to off for this component'),'warn');
				$_POST['AutoIssue']=0;
			}
		}

		$EffectiveAfterSQL = FormatDateForSQL($_POST['EffectiveAfter']);
		$EffectiveToSQL = FormatDateForSQL($_POST['EffectiveTo']);

		if (isset($SelectedParent) AND isset($SelectedComponent) AND $InputError != 1) {


			$sql = "UPDATE bom SET workcentreadded='" . $_POST['WorkCentreAdded'] . "',
						loccode='" . $_POST['LocCode'] . "',
						effectiveafter='" . $EffectiveAfterSQL . "',
						effectiveto='" . $EffectiveToSQL . "',
						quantity= " . $_POST['Quantity'] . ",
						autoissue=" . $_POST['AutoIssue'] . "
					WHERE bom.parent='" . $SelectedParent . "'
					AND bom.component='" . $SelectedComponent . "'";

			$ErrMsg =  _('Could not update this BOM component because');
			$DbgMsg =  _('The SQL used to update the component was');

			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			$msg = _('Details for') . ' - ' . $SelectedComponent . ' ' . _('have been updated') . '.';
			UpdateCost($db, $SelectedComponent);

		} elseIf ($InputError !=1 AND ! isset($SelectedComponent) AND isset($SelectedParent)) {

		/*Selected component is null cos no item selected on first time round so must be				adding a record must be Submitting new entries in the new component form */

		//need to check not recursive BOM component of itself!

			If (!CheckForRecursiveBOM ($SelectedParent, $_POST['Component'], $db)) {

				/*Now check to see that the component is not already on the BOM */
				$sql = "SELECT component
						FROM bom
					WHERE parent='$SelectedParent'
					AND component='" . $_POST['Component'] . "'
					AND workcentreadded='" . $_POST['WorkCentreAdded'] . "'
					AND loccode='" . $_POST['LocCode'] . "'" ;

				$ErrMsg =  _('An error occurred in checking the component is not already on the BOM');
				$DbgMsg =  _('The SQL that was used to check the component was not already on the BOM and that failed in the process was');

				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

				if (DB_num_rows($result)==0) {

					$sql = "INSERT INTO bom (parent,
								component,
								workcentreadded,
								loccode,
								quantity,
								effectiveafter,
								effectiveto,
								autoissue)
							VALUES ('$SelectedParent',
								'" . $_POST['Component'] . "',
								'" . $_POST['WorkCentreAdded'] . "',
								'" . $_POST['LocCode'] . "',
								" . $_POST['Quantity'] . ",
								'" . $EffectiveAfterSQL . "',
								'" . $EffectiveToSQL . "',
								" . $_POST['AutoIssue'] . ")";

					$ErrMsg = _('Could not insert the BOM component because');
					$DbgMsg = _('The SQL used to insert the component was');

					$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

					$msg = _('A new component part') . ' ' . $_POST['Component'] . ' ' . _('has been added to the bill of material for part') . ' - ' . $SelectedParent . '.';


				} else {

				/*The component must already be on the BOM */

					prnMsg( _('The component') . ' ' . $_POST['Component'] . ' ' . _('is already recorded as a component of') . ' ' . $SelectedParent . '.' . '<BR>' . _('Whilst the quantity of the component required can be modified it is inappropriate for a component to appear more than once in a bill of material'),'error');
				}


			} //end of if its not a recursive BOM

		} //end of if no input errors

		prnMsg($msg,'success');

	} elseif (isset($_GET['delete']) AND isset($SelectedComponent) AND isset($SelectedParent)) {

	//the link to delete a selected record was clicked instead of the Submit button

		$sql="DELETE FROM bom WHERE parent='$SelectedParent' AND component='$SelectedComponent'";

		$ErrMsg = _('Could not delete this BOM components because');
		$DbgMsg = _('The SQL used to delete the BOM was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg(_('The component part') . ' - ' . $SelectedComponent . ' - ' . _('has been deleted from this BOM'),'success');
		// Now reselect

	} elseif (isset($SelectedParent) 
		AND !isset($SelectedComponent) 
		AND ! isset($_POST['submit'])) {

	/* It could still be the second time the page has been run and a record has been selected	for modification - SelectedParent will exist because it was sent with the new call. If		its the first time the page has been displayed with no parameters then none of the above		are true and the list of components will be displayed with links to delete or edit each.		These will call the same page again and allow update/input or deletion of the records*/
		//DisplayBOMItems($SelectedParent, $db);

	} //BOM editing/insertion ifs


	if(isset($_GET['ReSelect'])) {
		$SelectedParent = $_GET['ReSelect'];
	}

	//DisplayBOMItems($SelectedParent, $db);
	$sql = "SELECT stockmaster.description,
			stockmaster.mbflag
		FROM stockmaster
		WHERE stockmaster.stockid='" . $SelectedParent . "'";

	$ErrMsg = _('Could not retrieve the description of the parent part because');
	$DbgMsg = _('The SQL used to retrieve description of the parent part was');
	$result=DB_query($sql,$db,$ErrMsg,$DbgMsg);

	$myrow=DB_fetch_row($result);

	$ParentMBflag = $myrow[1];

	switch ($ParentMBflag){
		case 'A':
			$MBdesc = _('Assembly');
			break;
		case 'B':
			$MBdesc = _('Purchased');
			break;
		case 'M':
			$MBdesc = _('Manufactured');
			break;
		case 'K':
			$MBdesc = _('Kit Set');
			break;
	}

	echo "<BR><FONT COLOR=BLUE SIZE=3><B> $SelectedParent - " . $myrow[0] . ' ('. $MBdesc. ') </FONT></B>';

	echo '<BR><A HREF=' . $_SERVER['PHP_SELF'] . '?' . SID . '>' . _('Select a Different BOM') . '</A></CENTER>';

	if (isset($SelectedParent)) {
		echo "<Center><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . "Select=$SelectedParent'>" . _('Review Components') . '</a></Center>';
	}
?>
<?php
	// Display Manufatured Parent Items
	$sql = "SELECT bom.parent, 
			stockmaster.description, 
			stockmaster.mbflag
		FROM bom, stockmaster
		WHERE bom.component='".$SelectedParent."'
		AND stockmaster.stockid=bom.parent
		AND stockmaster.mbflag='M'";

	$ErrMsg = _('Could not retrieve the description of the parent part because');
	$DbgMsg = _('The SQL used to retrieve description of the parent part was');
	$result=DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$ix = 0;
	$reqnl = 0;
	if( DB_num_rows($result) > 0 ) {
	 echo '<CENTER>'._('Manufactured parent items').' : ';
	 while ($myrow = DB_fetch_array($result)){
	 	   echo (($ix)?', ':'').'<A href="'.$_SERVER['PHP_SELF'] . '?' . SID . 'Select='.$myrow['parent'].'">'.
			$myrow['description'].'&nbsp;('.$myrow['parent'].')</A>';
			$ix++;
	 } //end while loop
	 echo '</CENTER>';
	 $reqnl = $ix;
	}
	// Display Assembly Parent Items
	$sql = "SELECT bom.parent, stockmaster.description, stockmaster.mbflag
		FROM bom, stockmaster
		WHERE bom.component='".$SelectedParent."'
		AND stockmaster.stockid=bom.parent
		AND stockmaster.mbflag='A'";

	$ErrMsg = _('Could not retrieve the description of the parent part because');
	$DbgMsg = _('The SQL used to retrieve description of the parent part was');
	$result=DB_query($sql,$db,$ErrMsg,$DbgMsg);
	if( DB_num_rows($result) > 0 ) {
		echo (($reqnl)?'<BR>':'').'<CENTER>'._('Assembly parent items').' : ';
	 	$ix = 0;
	 	while ($myrow = DB_fetch_array($result)){
	 	   echo (($ix)?', ':'').'<A href="'.$_SERVER['PHP_SELF'] . '?' . SID . 'Select='.$myrow['parent'].'">'.
			$myrow['description'].'&nbsp;('.$myrow['parent'].')</A>';
			$ix++;
	 	} //end while loop
	 	echo '</CENTER>';
	}
	// Display Kit Sets
	$sql = "SELECT bom.parent, stockmaster.description, stockmaster.mbflag
		FROM bom, stockmaster
		WHERE bom.component='".$SelectedParent."'
		AND stockmaster.stockid=bom.parent
		AND stockmaster.mbflag='K'";

	$ErrMsg = _('Could not retrieve the description of the parent part because');
	$DbgMsg = _('The SQL used to retrieve description of the parent part was');
	$result=DB_query($sql,$db,$ErrMsg,$DbgMsg);
	if( DB_num_rows($result) > 0 ) {
		echo (($reqnl)?'<BR>':'').'<CENTER>'._('Kit sets').' : ';
	 	$ix = 0;
	 	while ($myrow = DB_fetch_array($result)){
	 	   echo (($ix)?', ':'').'<A href="'.$_SERVER['PHP_SELF'] . '?' . SID . 'Select='.$myrow['parent'].'">'.
			$myrow['description'].'&nbsp;('.$myrow['parent'].')</A>';
			$ix++;
	 	} //end while loop
	 	echo '</CENTER>';
	}

?>
	<CENTER><table border=1>

<?php // *** POPAD&T
	$BOMTree = array();
	//BOMTree is a 2 dimensional array with three elements for each item in the array - Level, Parent, Component
	//display children populates the BOM_Tree from the selected parent
	$i =0;
	display_children($SelectedParent, 1, $BOMTree);

	$TableHeader =  '<tr>
			<td class="tableheader">' . _('Level') . '</td>
			<td class="tableheader">' . _('Code') . '</td>
			<td class="tableheader">' . _('Description') . '</td>
			<td class="tableheader">' . _('Location') . '</td>
			<td class="tableheader">' . _('Work Centre') . '</td>
			<td class="tableheader">' . _('Quantity') . '</td>
			<td class="tableheader">' . _('Effective After') . '</td>
			<td class="tableheader">' . _('Effective To') . '</td>
			<td class="tableheader">' . _('Auto Issue') . '</td>
			<td class="tableheader">' . _('Qty On Hand') . '</td>
			</tr>';
	echo $TableHeader;
	if(count($BOMTree) == 0) {
		echo '<tr bgcolor="#EEEEEE"><td colspan="8">'._('No materials found.').'</td></tr>';
	} else {
		$UltimateParent = $SelectedParent;
		$k = 0;
		$RowCounter = 1;

		foreach($BOMTree as $BOMItem){
			$Level = $BOMItem['Level'];
			$Parent = $BOMItem['Parent'];
			$Component = $BOMItem['Component'];
			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			}else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k++;
			}
			DisplayBOMItems($UltimateParent, $Parent, $Component, $Level, $db);
			$RowCounter++;
			if ($RowCounter==20){
				echo $TableHeader;
				$RowCounter=0;
			}
		}
	}
	// *** end POPAD&T
?>
	</table></CENTER>

	<?php

	if (! isset($_GET['delete'])) {

		echo '<FORM METHOD="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Select=' . $SelectedParent .'">';

		if (isset($SelectedComponent)) {
		//editing a selected component from the link to the line item

			$sql = "SELECT loccode,
					effectiveafter,
					effectiveto,
					workcentreadded,
					quantity,
					autoissue
				FROM bom
				WHERE parent='$SelectedParent'
				AND component='$SelectedComponent'";

			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);

			$_POST['LocCode'] = $myrow['loccode'];
			$_POST['EffectiveAfter'] = ConvertSQLDate($myrow['effectiveafter']);
			$_POST['EffectiveTo'] = ConvertSQLDate($myrow['effectiveto']);
			$_POST['WorkCentreAdded']  = $myrow['workcentreadded'];
			$_POST['Quantity'] = $myrow['quantity'];
			$_POST['AutoIssue'] = $myrow['autoissue'];

			prnMsg(_('Edit the details of the selected component in the fields below') . '. <BR>' . _('Click on the Enter Information button to update the component details'),'info');
			echo "<INPUT TYPE=HIDDEN NAME='SelectedParent' VALUE='$SelectedParent'>";
			echo "<INPUT TYPE=HIDDEN NAME='SelectedComponent' VALUE='$SelectedComponent'>";
			echo '<CENTER><TABLE><TR><TD>' . _('Component') . ':</TD><TD><B>' . $SelectedComponent . '</B></TD></TR>';

		} else { //end of if $SelectedComponent

			echo "<INPUT TYPE=HIDDEN NAME='SelectedParent' VALUE='$SelectedParent'>";
			/* echo "Enter the details of a new component in the fields below. <BR>Click on 'Enter Information' to add the new component, once all fields are completed.";
			*/
			echo '<CENTER><TABLE><TR><TD>' . _('Component code') . ':</TD><TD>';
			echo "<SELECT name='Component'>";


			if ($ParentMBflag=='A'){ /*Its an assembly */
				$sql = "SELECT stockmaster.stockid,
						stockmaster.description
					FROM stockmaster
					WHERE stockmaster.mbflag !='D'
					AND stockmaster.mbflag !='K'
					AND stockmaster.mbflag !='A'
					AND stockmaster.controlled = 0
					AND stockmaster.stockid != '$SelectedParent'
					ORDER BY stockmaster.stockid";

			} else { /*Its either a normal manufac item or a kitset - controlled items ok */
				$sql = "SELECT stockmaster.stockid,
						stockmaster.description
					FROM stockmaster
					WHERE stockmaster.mbflag !='D'
					AND stockmaster.mbflag !='K'
					AND stockmaster.mbflag !='A'
					AND stockmaster.stockid != '$SelectedParent'
					ORDER BY stockmaster.stockid";
			}

			$ErrMsg = _('Could not retrieve the list of potential components because');
			$DbgMsg = _('The SQL used to retrieve the list of potential components part was');
			$result = DB_query($sql,$db,$ErrMsg, $DbgMsg);


			while ($myrow = DB_fetch_array($result)) {
				echo "<OPTION VALUE=".$myrow['stockid'].'>' . str_pad($myrow['stockid'],21, '_', STR_PAD_RIGHT) . $myrow['description'];
			} //end while loop

			echo '</SELECT></TD></TR>';
		}
		?>

		<TR><TD><?php echo _('Location') . ':'; ?></TD>
		<TD>
		<SELECT name="LocCode">

		<?php

		DB_free_result($result);
		$sql = 'SELECT locationname, loccode FROM locations';
		$result = DB_query($sql,$db);

		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['loccode']==$_POST['LocCode']) {
				echo "<OPTION SELECTED VALUE='";
			} else {
				echo "<OPTION VALUE='";
			}
			echo $myrow['loccode'] . "'>" . $myrow['locationname'];

		} //end while loop

		DB_free_result($result);

		?>
		</SELECT>
		</TD></TR>

		<TR><TD><?php echo _('Work Centre Added') . ':'; ?></TD>
		<TD>
		<SELECT name="WorkCentreAdded">

		<?php

		$sql = 'SELECT code, description FROM workcentres';
		$result = DB_query($sql,$db);

		if (DB_num_rows($result)==0){
			prnMsg( _('There are no work centres set up yet') . '. ' . _('Please use the link below to set up work centres'),'warn');
			echo "<BR><A HREF='$rootpath/WorkCentres.php?" . SID . "'>" . _('Work Centre Maintenance') . '</A>';
			includes('includes/footer.inc');
			exit;
		}

		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['code']==$_POST['WorkCentreAdded']) {
				echo "<OPTION SELECTED VALUE='";
			} else {
				echo "<OPTION VALUE='";
			}
			echo $myrow['code'] . "'>" . $myrow['description'];
		} //end while loop

		DB_free_result($result);
		?>

		</SELECT>
		</TD></TR>

		<TR><TD><?php echo _('Quantity') . ':'; ?></TD>
		<TD>
		<INPUT TYPE="Text" name="Quantity" SIZE=10 MAXLENGTH=8 VALUE=
		<?php
		if ($_POST['Quantity']){
			echo $_POST['Quantity'];
		} else {
			echo 1;
		}?>>

		</TD></TR>

		<?php
		if (!isset($_POST['EffectiveTo']) OR $_POST['EffectiveTo']=='') {
			$_POST['EffectiveTo'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d'),(Date('y')+30)));
		}
		if (!isset($_POST['EffectiveAfter']) OR $_POST['EffectiveAfter']=='') {
			$_POST['EffectiveAfter'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d')-1,Date('y')));
		}

		?>

		<TR><TD><?php echo _('Effective After') . ' (' . $_SESSION['DefaultDateFormat'] . '):'; ?></TD>
		<TD>
		<INPUT TYPE="Text" name="EffectiveAfter" SIZE=11 MAXLENGTH=11 VALUE="<?php echo $_POST['EffectiveAfter']; ?>">
		</TD></TR>
		<TR><TD><?php echo _('Effective To') . ' (' . $_SESSION['DefaultDateFormat'] . '):'; ?></TD>
		<TD>
		<INPUT TYPE="Text" name="EffectiveTo" SIZE=11 MAXLENGTH=11 VALUE="<?php echo $_POST['EffectiveTo']; ?>">
		</TD></TR>
		<?php
		if ($ParentMBflag=='M'){
			echo '<TR><TD>' . _('Auto Issue this Component to Work Orders') . ':</TD>
				<TD>
				<SELECT name="AutoIssue">';

			if (!isset($_POST['AutoIssue'])){
				$_POST['AutoIssue'] = $_SESSION['AutoIssue'];
			}
			if ($_POST['AutoIssue']==0) {
				echo '<OPTION SELECTED VALUE=0>' . _('No');
				echo '<OPTION VALUE=1>' . _('Yes');
			} else {
				echo '<OPTION SELECTED VALUE=1>' . _('Yes');
				echo '<OPTION VALUE=0>' . _('No');
			}


			echo '</SELECT></TD></TR>';
		} else {
			echo '<INPUT TYPE=HIDDEN NAME="AutoIssue" VALUE=0>';
		}
		?>
		</TABLE>

		<CENTER><input type="Submit" name="Submit" value="<?php echo _('Enter Information'); ?>">

		</FORM>

		<?php
	} //end if record deleted no point displaying form to add record

	// end of BOM maintenance code - look at the parent selection form if not relevant
// ----------------------------------------------------------------------------------

} elseif (isset($_POST['Search'])){
	// Work around to auto select
	If ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$_POST['StockCode']='%';
	}
	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	If ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$msg=_('At least one stock description keyword or an extract of a stock code must be entered for the search');
	} else {
		If (strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces

			$i=0;
			$SearchString = '%';
			while (strpos($_POST['Keywords'], ' ', $i)) {
				$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
				$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
				$i=strpos($_POST['Keywords'],' ',$i) +1;
			}
			$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';


			$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					SUM(locstock.quantity) as totalonhand
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid = locstock.stockid
				AND stockmaster.description " . LIKE . " '$SearchString'
				AND (stockmaster.mbflag='M' OR stockmaster.mbflag='K' OR stockmaster.mbflag='A')
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";

		} elseif (strlen($_POST['StockCode'])>0){
			$sql = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag,
					sum(locstock.quantity) as totalonhand
				FROM stockmaster,
					locstock
				WHERE stockmaster.stockid = locstock.stockid
				AND stockmaster.stockid " . LIKE  . "'%" . $_POST['StockCode'] . "%'
				AND (stockmaster.mbflag='M'
					OR stockmaster.mbflag='K'
					OR stockmaster.mbflag='A')
				GROUP BY stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.mbflag
				ORDER BY stockmaster.stockid";

		}

		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$result = DB_query($sql,$db,$ErrMsg);

	} //one of keywords or StockCode was more than a zero length string
} //end of if search

if (!isset($SelectedParent)) {
?>

<FORM ACTION='<?php echo $_SERVER['PHP_SELF'] . '?' . SID; ?>' METHOD=POST>
<B><BR><?php echo $msg; ?></B>
<?php echo _('Select a manufactured part') . ' (' . _('or Assembly or Kit part') . ') ' . _('to maintain the bill of material for using the options below') . '.'; ?>
<BR><FONT SIZE=1><?php echo _('Parts must be defined in the stock item entry') . '/' . _('modification screen as manufactured') . ', ' . _('kits or assemblies to be available for construction of a bill of material'); ?></FONT>
<TABLE CELLPADDING=3 COLSPAN=4>
<TR>
<TD><FONT SIZE=1><?php echo _('Enter text extracts in the') . ' <B>' . _('description'); ?></B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25></TD>
<TD><FONT SIZE=3><B><?php echo _('OR'); ?></B></FONT></TD>
<TD><FONT SIZE=1><?php echo _('Enter extract of the') . ' <B>' . _('Stock Code'); ?></B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18></TD>
</TR>
</TABLE>
<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>">
</CENTER>


<?php

If (isset($result) AND !isset($SelectedParent)) {

	echo '<TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>';
	$TableHeader = '<TR><TD class=tableheader>' . _('Code') . '</TD>
				<TD class=tableheader>' . _('Description') . '</TD>
				<TD class=tableheader>' . _('On Hand') . '</TD>
				<TD class=tableheader>' . _('Units') . '</TD>
			</TR>';

	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter
	while ($myrow=DB_fetch_array($result)) {
		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k++;
		}
		if ($myrow['mbflag']=='A' OR $myrow['mbflag']=='K'){
			$StockOnHand = 'N/A';
		} else {
			$StockOnHand = number_format($myrow['totalonhand'],2);
		}
		printf("<td><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</td>
		        <td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td></tr>",
			$myrow['stockid'],
			$myrow['description'],
			$StockOnHand,
			$myrow['units']
		);

		$j++;
		If ($j == 12){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</TABLE>';

}
//end if results to show

?>

</FORM>

<?php } //end StockID already selected

include('includes/footer.inc');
?>
