<?php
/* $Revision: 1.6 $ */
$PageSecurity = 9;

include('includes/session.inc');

$title = _('Bill Of Materials Maintenance');

include('includes/header.inc');
include('includes/DateFunctions.inc');


function CheckForRecursiveBOM ($UltimateParent, $ComponentToCheck, $db) {

/* returns true ie 1 if the BOM contains the parent part as a component
ie the BOM is recursive otherwise false ie 0 */


	$sql = "SELECT Component FROM BOM WHERE Parent='$ComponentToCheck'";
	$ErrMsg = _('An error occurred in retrieving the components of the BOM during the check for recursion');
	$DbgMsg = _('The SQL that was used to retrieve the components of the BOM and that failed in the process was');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if ($result!=0) {
		while ($myrow=DB_fetch_row($result)){
			if ($myrow[0]==$UltimateParent){
				return 1;
			}

			if (CheckForRecursiveBOM($UltimateParent, $myrow[0],&$db)){
				return 1;
			}
		} //(while loop)
	} //end if $result is true

	return 0;

} //end of function CheckForRecursiveBOM

function DisplayBOMItems($SelectedParent, $db) {
		$sql = "SELECT BOM.Component,
				StockMaster.Description,
				Locations.LocationName,
				WorkCentres.Description,
				Quantity,
				EffectiveAfter,
				EffectiveTo
			FROM BOM,
				StockMaster,
				Locations,
				WorkCentres
			WHERE BOM.Component=StockMaster.StockID
			AND BOM.LocCode = Locations.LocCode
			AND BOM.WorkCentreAdded=WorkCentres.Code
			AND BOM.Parent='$SelectedParent'";

		$ErrMsg = _('Could not retrieve the BOM components because');
		$DbgMsg = _('The SQL used to retrieve the components was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		echo "<CENTER><table border=1>";

		$TableHeader =  '<tr BGCOLOR =#800000>
				<td class=tableheader>' . _('Code') . '</td>
				<td class=tableheader>' . _('Description') . '</td>
				<td class=tableheader>' . _('Location') . '</td>
				<td class=tableheader>' . _('Work Centre') . '</td>
				<td class=tableheader>' . _('Quantity') . '</td>
				<td class=tableheader>' . _('Effective After') . '</td>
				<td class=tableheader>' . _('Effective To') . '</td>
				</tr>';

		echo $TableHeader;
		$RowCounter =0;
		while ($myrow=DB_fetch_row($result)) {
			if ($k==1){
				echo "<tr bgcolor='#CCCCCC'>";
				$k=0;
			} else {
				echo "<tr bgcolor='#EEEEEE'>";
				$k++;
			}

			printf("<td>%s</td>
			        <td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href=\"%s&Select=%s&SelectedComponent=%s\">" . _('Edit') . "</a></td>
				 <td><a href=\"%s&Select=%s&SelectedComponent=%s&delete=1\">" . _('Delete') . "</a></td>
				 </tr>",
				$myrow[0],
				$myrow[1],
				$myrow[2],
				$myrow[3],
				$myrow[4],
				ConvertSQLDate($myrow[5]),
				ConvertSQLDate($myrow[6]),
				$_SERVER['PHP_SELF'] . '?' . SID,
				$SelectedParent,
				$myrow[0],
				$_SERVER['PHP_SELF'] . '?' . SID,
				$SelectedParent,
				$myrow[0]);

			$RowCounter++;
			if ($RowCounter==20){
				echo $TableHeader;
				$RowCounter=0;
			}
		} //END WHILE LIST LOOP
}

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
	$Select = NULL;


	$sql = "SELECT Description FROM StockMaster WHERE StockID='" . $SelectedParent . "'";

	$ErrMsg = _('Could not retrieve the description of the parent part because');
	$DbgMsg = _('The SQL used to retrieve description of the parent part was');
	$result=DB_query($sql,$db,$ErrMsg,$DbgMsg);

	$myrow=DB_fetch_row($result);

	echo "<BR><FONT COLOR=BLUE SIZE=3><B>$SelectedParent - " . $myrow[0] . '</FONT></B>';
	echo '<BR><A HREF=' . $_SERVER['PHP_SELF'] . '?' . SID . '>' . _('Select a Different BOM') . '</A></CENTER>';

	if (isset($SelectedParent)) {
		echo "<Center><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . "Select=$SelectedParent'>" . _('Review Components') . '</a></Center>';
	}

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
			prnMsg(_('The effective to date must be a date after the effective after date') . '<BR>' . _('The effective to date is') . ' ' . DateDiff($_POST['EffectiveTo'], $_POST['EffectiveAfter'], 'd') . ' ' . _('days before the effective after date') . '! ' . _('No updates have been performed') . '.<BR>' . _('Effective after was') . ': ' . $_POST['EffectiveAfter'] . ' ' . _('and effective after was') . ': ' . $_POST['EffectiveTo'],'error');
			includes('includes/footer.inc');
			exit;
		}

		$EffectiveAfterSQL = FormatDateForSQL($_POST['EffectiveAfter']);
		$EffectiveToSQL = FormatDateForSQL($_POST['EffectiveTo']);

		if (isset($SelectedParent) AND isset($SelectedComponent) AND $InputError != 1) {


			$sql = "UPDATE BOM SET WorkCentreAdded='" . $_POST['WorkCentreAdded'] . "',
						LocCode='" . $_POST['LocCode'] . "',
						EffectiveAfter='" . $EffectiveAfterSQL . "',
						EffectiveTo='" . $EffectiveToSQL . "',
						Quantity= " . $_POST['Quantity'] . "
					WHERE Parent='" . $SelectedParent . "'
					AND Component='" . $SelectedComponent . "'";

			$ErrMsg =  _('Could not update this BOM component because');
			$DbgMsg =  _('The SQL used to update the component was');

			$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
			$msg = _('Details for') . ' - ' . $SelectedComponent . ' ' . _('have been updated') . '.';

		} elseIf ($InputError !=1 AND ! isset($SelectedComponent) AND isset($SelectedParent)) {

		/*Selected component is null cos no item selected on first time round so must be				adding a record must be Submitting new entries in the new component form */

		//need to check not recursive BOM component of itself!

			If (!CheckForRecursiveBOM ($SelectedParent, $_POST['Component'], &$db)) {

				/*Now check to see that the component is not already on the BOM */
				$sql = "SELECT Component
						FROM BOM
					WHERE Parent='$SelectedParent'
					AND Component='" . $_POST['Component'] . "'
					AND WorkCentreAdded='" . $_POST['WorkCentreAdded'] . "'
					AND LocCode='" . $_POST['LocCode'] . "'" ;

				$ErrMsg =  _('An error occurred in checking the component is not already on the BOM');
				$DbgMsg =  _('The SQL that was used to check the component was not already on the BOM and that failed in the process was');

				$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

				if (DB_num_rows($result)==0) {

					$sql = "INSERT INTO BOM (Parent,
								Component,
								WorkCentreAdded,
								LocCode,
								Quantity,
								EffectiveAfter,
								EffectiveTo)
							VALUES ('$SelectedParent',
								'" . $_POST['Component'] . "',
								'" . $_POST['WorkCentreAdded'] . "',
								'" . $_POST['LocCode'] . "',
								" . $_POST['Quantity'] . ",
								'" . $EffectiveAfterSQL . "',
								'" . $EffectiveToSQL . "')";

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

		$sql="DELETE FROM BOM WHERE Parent='$SelectedParent' AND Component='$SelectedComponent'";

		$ErrMsg = _('Could not delete this BOM components because');
		$DbgMsg = _('The SQL used to delete the BOM was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg(_('The component part') . ' - ' . $SelectedComponent . ' - ' . _('has been deleted from this BOM'),'success');

	} elseif (isset($SelectedParent) AND !isset($SelectedComponent) AND ! isset($_POST['submit'])) {

	/* It could still be the second time the page has been run and a record has been selected	for modification - SelectedParent will exist because it was sent with the new call. If		its the first time the page has been displayed with no parameters then none of the above		are true and the list of components will be displayed with links to delete or edit each.		These will call the same page again and allow update/input or deletion of the records*/
		//DisplayBOMItems($SelectedParent, $db);

	} //BOM editing/insertion ifs

	DisplayBOMItems($SelectedParent, $db);

	?>

	</table></CENTER>
	<p>

	<P>

	<?php

	if (! isset($_GET['delete'])) {

		echo '<FORM METHOD="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Select=' . $SelectedParent .'">';

		if (isset($SelectedComponent)) {
		//editing a selected component from the link to the line item

			$sql = "SELECT LocCode,
					EffectiveAfter,
					EffectiveTo,
					WorkCentreAdded,
					Quantity
				FROM BOM
				WHERE Parent='$SelectedParent'
				AND Component='$SelectedComponent'";

			$result = DB_query($sql, $db);
			$myrow = DB_fetch_array($result);

			$_POST['LocCode'] = $myrow['LocCode'];
			$_POST['EffectiveAfter'] = ConvertSQLDate($myrow['EffectiveAfter']);
			$_POST['EffectiveTo'] = ConvertSQLDate($myrow['EffectiveTo']);
			$_POST['WorkCentreAdded']  = $myrow['WorkCentreAdded'];
			$_POST['Quantity'] = $myrow['Quantity'];

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

			$sql = "SELECT StockID, Description FROM StockMaster WHERE MBflag !='D' AND MBflag !='K' AND MBflag !='A' AND StockID != '$SelectedParent' ORDER BY StockID";


			$ErrMsg = _('Could not retrieve the list of potential components because');
			$DbgMsg = _('The SQL used to retrieve the list of potential components part was');
			$result = DB_query($sql,$db,$ErrMsg, $DbgMsg);


			while ($myrow = DB_fetch_array($result)) {
				echo "<OPTION VALUE=".$myrow["StockID"].'>' . str_pad($myrow['StockID'],21, '_', STR_PAD_RIGHT) . $myrow['Description'];
			} //end while loop

			echo '</SELECT></TD></TR>';
		}
		?>

		<TR><TD><?php echo _('Location') . ':'; ?></TD>
		<TD>
		<SELECT name="LocCode">

		<?php

		DB_free_result($result);
		$sql = 'SELECT LocationName, LocCode FROM Locations';
		$result = DB_query($sql,$db);

		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['LocCode']==$_POST['LocCode']) {
				echo "<OPTION SELECTED VALUE='";
			} else {
				echo "<OPTION VALUE='";
			}
			echo $myrow['LocCode'] . "'>" . $myrow['LocationName'];

		} //end while loop

		DB_free_result($result);

		?>
		</SELECT>
		</TD></TR>

		<TR><TD><?php echo _('Work Centre Added') . ':'; ?></TD>
		<TD>
		<SELECT name="WorkCentreAdded">

		<?php

		$sql = 'SELECT Code, Description FROM WorkCentres';
		$result = DB_query($sql,$db);

		if (DB_num_rows($result)==0){
			prnMsg( _('There are no work centres set up yet') . '. ' . _('Please use the link below to set up work centres'),'warn');
			echo "<BR><A HREF='$rootpath/WorkCentres.php?" . SID . "'>" . _('Work Centre Maintenance') . '</A>';
			includes('includes/footer.inc');
			exit;
		}

		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['Code']==$_POST['WorkCentreAdded']) {
				echo "<OPTION SELECTED VALUE='";
			} else {
				echo "<OPTION VALUE='";

		}
			echo $myrow['Code'] . "'>" . $myrow['Description'];

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
			$_POST['EffectiveTo'] = Date($DefaultDateFormat,Mktime(0,0,0,Date('m'),Date('d'),(Date('y')+30)));
		}
		if (!isset($_POST['EffectiveAfter']) OR $_POST['EffectiveAfter']=='') {
			$_POST['EffectiveAfter'] = Date($DefaultDateFormat,Mktime(0,0,0,Date('m'),Date('d')-1,Date('y')));
		}

		?>

		<TR><TD><?php echo _('Effective After') . ' (' . $DefaultDateFormat . '):'; ?></TD>
		<TD>
		<INPUT TYPE="Text" name="EffectiveAfter" SIZE=11 MAXLENGTH=11 VALUE="<?php echo $_POST['EffectiveAfter']; ?>">
		</TD></TR>
		<TR><TD><?php echo _('Effective To') . ' (' . $DefaultDateFormat . '):'; ?></TD>
		<TD>
		<INPUT TYPE="Text" name="EffectiveTo" SIZE=11 MAXLENGTH=11 VALUE="<?php echo $_POST['EffectiveTo']; ?>">
		</TD></TR>


		</TABLE>

		<CENTER><input type="Submit" name="Submit" value="<?php echo _('Enter Information'); ?>">

		</FORM>

		<?php
	} //end if record deleted no point displaying form to add record

	// end of BOM maintenance code - look at the parent selection form if not relevant
// ----------------------------------------------------------------------------------

} elseif (isset($_POST['Search'])){

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


			$sql = "SELECT StockMaster.StockID,
					StockMaster.Description,
					StockMaster.Units,
					StockMaster.MBflag,
					Sum(LocStock.Quantity) AS TotalOnHand
				FROM StockMaster,
					LocStock
				WHERE StockMaster.StockID = LocStock.StockID
				AND StockMaster.Description LIKE '$SearchString'
				AND (StockMaster.MBflag='M' OR StockMaster.MBflag='K' OR StockMaster.MBflag='A')
				GROUP BY StockMaster.StockID,
					StockMaster.Description,
					StockMaster.Units
				ORDER BY StockMaster.StockID";

		} elseif (strlen($_POST['StockCode'])>0){
			$sql = "SELECT StockMaster.StockID,
					StockMaster.Description,
					StockMaster.Units,
					StockMaster.MBflag,
					Sum(LocStock.Quantity) AS TotalOnHand
				FROM StockMaster,
					LocStock
				WHERE StockMaster.StockID = LocStock.StockID
				AND StockMaster.StockID like '%" . $_POST['StockCode'] . "%'
				AND (StockMaster.MBflag='M'
					OR StockMaster.MBflag='K'
					OR StockMaster.MBflag='A')
				GROUP BY StockMaster.StockID,
					StockMaster.Description,
					StockMaster.Units
				ORDER BY StockMaster.StockID";

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
		if ($myrow['MBflag']=='A' OR $myrow['MBflag']=='K'){
			$StockOnHand = 'N/A';
		} else {
			$StockOnHand = number_format($myrow['TotalOnHand'],2);
		}
		printf("<td><INPUT TYPE=SUBMIT NAME='Select' VALUE='%s'</td>
		        <td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td></tr>",
			$myrow['StockID'],
			$myrow['Description'],
			$StockOnHand,
			$myrow['Units']
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
