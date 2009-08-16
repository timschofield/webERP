<?php
/* $Revision: 1.3 $ */
// Add, Edit, Delete, and List MRP demand records. Table is mrpdemands.
// Have separate functions for each routine. Use pass-by-reference - (&$db,&$StockID) -
// to pass values of $db and $StockID to functions. - when just used $db as variable,
// got error: Catchable fatal error: Object of class mysqli could not be converted to string

$PageSecurity=9;

include('includes/session.inc');
$title = _('MRP Demands');
include('includes/header.inc');

if (isset($_POST['DemandID'])){
	$DemandID =$_POST['DemandID'];
} elseif (isset($_GET['DemandID'])){
	$DemandID =$_GET['DemandID'];
}
	
if (isset($_POST['StockID'])){
	$StockID =trim(strtoupper($_POST['StockID']));
} elseif (isset($_GET['StockID'])){
	$StockID =trim(strtoupper($_GET['StockID']));
}

if (isset($_POST['Search'])) {
    search($db,$StockID);
} elseif (isset($_POST['submit'])) {
    submit($db,$StockID,$DemandID);
} elseif (isset($_GET['delete'])) {
    delete($db,$DemandID,"");
} elseif (isset($_POST['deletesome'])) {
    delete($db,"",$_POST['MRPDemandtype']);
} elseif (isset($_GET['listall'])) {
    listall($db,"","");
} elseif (isset($_POST['listsome'])) {
    listall($db,$StockID,$_POST['MRPDemandtype']);
} else {
    display($db,$StockID,$DemandID);
}

function search(&$db,&$StockID) { //####SEARCH_SEARCH_SEARCH_SEARCH_SEARCH_SEARCH_SEARCH_#####

// Search by partial part number or description. Display the part number and description from
// the stockmaster so user can select one. If the user clicks on a part number
// MRPDemands.php is called again, and it goes to the display() routine. 

	// Work around to auto select
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$_POST['StockCode']='%';
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		$msg=_('Stock description keywords have been used in preference to the Stock code extract entered');
	}
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$msg=_('At least one stock description keyword or an extract of a stock code must be entered for the search');
	} else {
		if (strlen($_POST['Keywords'])>0) {
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
					stockmaster.description
				FROM stockmaster
				WHERE  stockmaster.description " . LIKE . " '$SearchString'
				ORDER BY stockmaster.stockid";

		} elseif (strlen($_POST['StockCode'])>0){
			$sql = "SELECT stockmaster.stockid,
					stockmaster.description
				FROM stockmaster
				WHERE  stockmaster.stockid " . LIKE  . "'%" . $_POST['StockCode'] . "%'			
				ORDER BY stockmaster.stockid";

		}

		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$result = DB_query($sql,$db,$ErrMsg);

	} //one of keywords or StockCode was more than a zero length string

// If the SELECT found records, display them
if (DB_num_rows($result) > 0) {
echo "<form action=" . $_SERVER['PHP_SELF'] . "?" . SID ." method=post>";
	echo '<table cellpadding=2 colspan=7 BORDER=1>';
	$TableHeader = '<tr><th>' . _('Code') . '</th>
				        <th>' . _('Description') . '</th>
			       </tr>';
	echo $TableHeader;

	$j = 1;
	$k = 0; //row colour counter
	while ($myrow=DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		$tabindex=$j+4;
		printf("<td><input tabindex='".$tabindex."' type=submit name='StockID' value='%s'</td>
		        <td>%s</td>
			</tr>",
			$myrow['stockid'],
			$myrow['description']
		);
		$j++;
	}  //end of while loop

	echo '</table>';
	echo '</form>';

} else {
prnMsg(_('No record found in search'),'error');
unset ($StockID);
display($db,$StockID,$DemandID);
}


} // End of function search()


function submit(&$db,&$StockID,&$DemandID)  //####SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
{
// In this section if hit submit button. Do edit checks. If all checks pass, see if record already
// exists for StockID/Duedate/MRPDemandtype combo; that means do an Update, otherwise, do INSERT.
//initialise no input errors assumed initially before we test
	// echo "<br/>Submit - DemandID = $DemandID<br/>";
	$FormatedDuedate = FormatDateForSQL($_POST['Duedate']);
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (!is_numeric($_POST['Quantity'])) {
		$InputError = 1;
		prnMsg(_('Quantity must be numeric'),'error');
	} 
	if ($_POST['Quantity'] <= 0) {
		$InputError = 1;
		prnMsg(_('Quantity must be greater than 0'),'error');
	}
	if (!is_Date($_POST['Duedate'])) {
		$InputError = 1;
		prnMsg(_('Invalid due date'),'error');
	}
	$sql = "SELECT * FROM mrpdemandtypes
		WHERE mrpdemandtype='" . $_POST['MRPDemandtype'] . "'";
	$result = DB_query($sql, $db);

    if (DB_num_rows($result) == 0){
		$InputError = 1;
		prnMsg(_('Invalid demand type'),'error');
	}
// Check if valid part number - Had done a Select Count(*), but that returned a 1 in DB_num_rows
// even if there was no record.
	$sql = "SELECT * FROM stockmaster
		WHERE stockid='$StockID'";
	$result = DB_query($sql, $db);

	if (DB_num_rows($result) == 0){
			$InputError = 1;
			prnMsg($StockID . ' ' . _('is not a valid item code'),'error');
			unset ($_POST['StockID']);
			unset($StockID);
	} 
// Check if part number/demand type/due date combination already exists 
	$sql = "SELECT * FROM mrpdemands
	          WHERE stockid='$StockID'
		  AND mrpdemandtype='" . $_POST['MRPDemandtype'] . "'" .
		 " AND duedate='$FormatedDuedate'
		   AND demandid <> '$DemandID'";
	$result = DB_query($sql, $db);

    if (DB_num_rows($result) > 0){
		$InputError = 1;
		prnMsg(_('Record alread exists for part number/demand type/date'),'error');
	}
	
    if ($InputError !=1){	
		$sql = "SELECT COUNT(*) FROM mrpdemands
			       WHERE demandid='$DemandID' 
			       GROUP BY demandid";
		$result = DB_query($sql, $db);
		$myrow = DB_fetch_row($result);
		
		if ($myrow[0]>0) {
			//If $myrow[0] > 0, it means this is an edit, so do an update
			$sql = "UPDATE mrpdemands SET quantity = '" . $_POST['Quantity'] . "',
							mrpdemandtype = '" . trim(strtoupper($_POST['MRPDemandtype'])) . "',
							duedate = '" . $FormatedDuedate . "'
					WHERE demandid = '" . $DemandID . "'";
			$msg = _("The MRP demand record has been updated for $StockID");
		} else {
	
	// If $myrow[0] from SELECT count(*) is zero, this is an entry of a new record
			$sql = "INSERT INTO mrpdemands (stockid,
							mrpdemandtype,
							quantity,
							duedate)
						VALUES ('" . $StockID . "',
							'" . trim(strtoupper($_POST['MRPDemandtype'])) . "',
							'" . $_POST['Quantity'] . "',
							'" . $FormatedDuedate . "')";
			$msg = _('A new MRP demand record has been added to the database for') . ' ' . $StockID;
		}
	

		$result = DB_query($sql,$db,_('The update/addition of the MRP demand record failed because'));
		prnMsg($msg,'success');
		unset ($_POST['MRPDemandtype']);
		unset ($_POST['Quantity']);
		unset ($_POST['StockID']);
		unset ($_POST['Duedate']);
		unset ($StockID);
		unset ($DemandID);
	} // End of else where DB_num_rows showed there was a valid stockmaster record
	
display($db,$StockID,$DemandID);
} // End of function submit()


function delete(&$db,$DemandID,$demandtype) { //####DELETE_DELETE_DELETE_DELETE_DELETE_DELETE_####

// If wanted to have a Confirm routine before did actually deletion, could check if
// deletion = "yes"; if it did, display link that redirects back to this page 
// like this - <a href=" ' . $_SERVER['PHP_SELF'] . '?&delete=confirm&StockID=' . "$StockID" . ' ">
// that sets delete=confirm. If delete=confirm, do actually deletion.
//  This deletes an individual record by DemandID if called from a listall that shows 
// edit/delete or deletes all of a particular demand type if press Delete Demand Type button.
    $where = " ";
    if ($demandtype) {
        $where = ' WHERE mrpdemandtype =' . "'"  .  $demandtype . "'";
    }
    if ($DemandID) {
        $where = ' WHERE demandid =' . "'"  .  $DemandID . "'";
    }
	$sql="DELETE FROM mrpdemands
	       $where";
	$result = DB_query($sql,$db);
	if ($DemandID) {
	    prnMsg(_("The MRP demand record for $StockID has been deleted"),'succes');
	} else {
	    prnMsg(_("All records for demand type $demandtype have been deleted"),'succes');
	}
unset ($DemandID);
unset ($StockID);
display($db,$stockID,$DemandID);

} // End of function delete()


function listall(&$db,$part,$demandtype)  {//####LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_LISTALL_####

// List all mrpdemands records, with anchors to Edit or Delete records if hit List All anchor
// Lists some in hit List Selection submit button, and uses part number if it is entered or
// demandtype

echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID .' method=post>';
    $where = " ";
    if ($demandtype) {
        $where = ' WHERE mrpdemandtype =' . "'"  .  $demandtype . "'";
    }
    if ($part) {
        $where = ' WHERE mrpdemands.stockid =' . "'"  .  $part . "'";
    }
    // If part is entered, it overrides demandtype
	$sql = 'SELECT mrpdemands.demandid,
	               mrpdemands.stockid,
	               mrpdemands.mrpdemandtype,
	               mrpdemands.quantity,
	               mrpdemands.duedate,
	               stockmaster.description
		FROM mrpdemands
		LEFT JOIN stockmaster on mrpdemands.stockid = stockmaster.stockid' .
		 $where	. ' ORDER BY mrpdemands.stockid, mrpdemands.duedate';
    //echo "<br/>sql is $sql<br/>";
	$ErrMsg = _('The SQL to find the parts selected failed with the message');
	$result = DB_query($sql,$db,$ErrMsg);
		
	echo "<table border=1>
		<tr BGCOLOR =#800000><th>" . _('Part Number') . "</th>
				<th>" . _('Description') . "</th>
				<th>" . _('Demand Type') . "</th>
				<th>" . _('Quantity') . "</th>
				<th>" . _('Due Date') . "</th>
		</tr></font>";
    $ctr = 0;
	while ($myrow = DB_fetch_row($result)) {
	    $displaydate = ConvertSQLDate($myrow[4]);
        $ctr++;
		printf("<tr><td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href=\"%s&DemandID=%s\">" . _('Edit') . "</td>
				<td><a href=\"%s&DemandID=%s&delete=yes\">" . _('Delete') ."</td>
				</tr>",
				$myrow[1],
				$myrow[5],
				$myrow[2],
				$myrow[3],
				$displaydate,
				$_SERVER['PHP_SELF'] . '?' . SID,
				$myrow[0], 
				$_SERVER['PHP_SELF'] . '?' . SID,
				$myrow[0]);
	}

	//END WHILE LIST LOOP
	echo '<tr><td>' . _('Number of Records') . "</td><td>$ctr</td></tr>";
	echo '</table>';
echo '</form><br/><br/><br/><br/>';
unset ($StockID);
display($db,$StockID,$DemandID);

} // End of function listall()


function display(&$db,&$StockID,&$DemandID) { //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####

// Display Seach fields at top and Entry form below that. This function is called the first time
// the page is called, and is also invoked at the end of all of the other functions.
// echo "<br/>DISPLAY - DemandID = $DemandID<br/>";
	echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method="post"><b><br></b>
</font><table cellpadding=3 colspan=4>
<tr>
		 <td><font size=1>' . _('Enter text extracts in the') . ' <b>' . _('description') . '</b>:</font></td>
		 <td><input tabindex="1" type="Text" name="Keywords" size="20" maxlength="25"></td>
		 <td><font size=3><b>' . _('OR') . '</b></font></td>
		 <td><font size=1>' . _('Enter extract of the') . ' <b>' . _('Stock Code') . '</b>:</font></td>
		 <td><input tabindex="2" type="Text" name="StockCode" size="15" maxlength="20"></td>
		 <td><font size=3><b>' . _('OR') . '</b></font></td>
		 <td><a href="'. $_SERVER['PHP_SELF'] . '?&listall=yes>"' . _('List All') .'</a></td>
	 </tr>
	 </table>
	 <div class="centre">
	  <input tabindex="3" type="submit" name="Search" value="' . _('Search Now') . '">
	 </div>';
	 echo '<br/><br/><hr>';
	 echo '<br/><br/></b1r><br/><br/>';
	if (isset($DemandID)) {
	//editing an existing MRP demand

	$sql = "SELECT demandid,
	               stockid,
	               mrpdemandtype,
	               quantity,
	               duedate
	    FROM mrpdemands
		WHERE demandid='$DemandID'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	if (DB_num_rows($result) > 0){
		$_POST['DemandID'] = $myrow['demandid'];
		$_POST['StockID'] = $myrow['stockid'];
		$_POST['MRPDemandtype'] = $myrow['mrpdemandtype'];
		$_POST['Quantity'] = $myrow['quantity'];
		$_POST['Duedate']  = ConvertSQLDate($myrow['duedate']);
	}

    echo '<input type="hidden" name="DemandID" value=' . $_POST['DemandID'] . '>';
	echo '<input type="hidden" name="StockID" value="' . $_POST['StockID'] . '">';
	echo '<table><tr><td>' ._('Part Number') . ':</td><td>' . $_POST['StockID'] . '</td></tr>';

} else { 
	if (!isset($_POST['StockID'])) {
		$_POST['StockID'] = '';
	}
	echo '<table><tr>
			<td>' . _('Part Number') . ':</td>
			<td><input type="Text" name="StockID" size=21 maxlength=20 value="' . $_POST['StockID'] . '"></td>
			</tr>';
}


if (!isset($_POST['Quantity'])) {
	$_POST['Quantity']=0;
}

if (!isset($_POST['Duedate'])) {
	$_POST['Duedate']=' ';
}

echo '<tr>  
<td>' . _('Quantity') . ":</td>
	    <td><input type='Text' name='Quantity' size=6 maxlength=6 value=" . $_POST['Quantity'] . '></td>
	</tr>	
<tr>
<td>' . _('Due Date') . ":</td>
	    <td><input type='Text' name='Duedate' size=12 maxlength=12 value=" . $_POST['Duedate'] . '></td>
	</tr>';
// Generate selections for Demand Type
echo '<tr>
 <td>' . _('Demand Type') . "</td>
 <td><select name='MRPDemandtype'>";
 $sql = 'SELECT mrpdemandtype,
  description
  FROM mrpdemandtypes';
$result = DB_query($sql,$db);
while ($myrow = DB_fetch_array($result)) {
	if (isset($_POST['MRPDemandtype']) and $myrow['mrpdemandtype']==$_POST['MRPDemandtype']) {
		echo '<option selected value="';
	} else {
		echo '<option value="';
	}
	echo $myrow['mrpdemandtype'] . '">' . $myrow['mrpdemandtype'] . ' - ' .$myrow['description'];
} //end while loop
echo '</select></td></tr><tr><td>&nbsp</td>&nbsp<td></td></tr><tr><td>&nbsp</td>&nbsp<td></td></tr>';

echo '<tr><td><input type="submit" name="submit" value="' . _('Enter Information') . '"></td>';
echo '<td><input type="submit" name="listsome" value="' . _('List Selection') . '">';
echo '<td><input type="Submit" name="deletesome" value="' . _('Delete Demand Type') . '"></tr>';
echo '</table><br/><br/><br/><br/>';
// If mrpdemand record exists, display option to delete it
if ((isset($DemandID)) AND (DB_num_rows($result) > 0)) {
	echo '<br/><br/><a href=" ' . $_SERVER['PHP_SELF'] . '?&delete=yes&DemandID=' . $DemandID . '">' . _('Or Delete Record') ;
}

echo '</form>';

} // End of function display()


include('includes/footer.inc');
?>