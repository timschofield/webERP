<?php

/* $Revision: 1.8 $ */

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Work Order Entry');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_REQUEST['WO']) AND $_REQUEST['WO']!=''){
	$_POST['WO'] = $_REQUEST['WO'];
        $EditingExisting = true;
} else {
	$EditingExisting = false;
}

if (isset($_POST['submit'])) {

	$Input_Error = false; //hope for the best
        for ($i=0;$i<$_POST['NumberOfOutputs'];$i++){
        	if (!is_numeric($_POST['OutputQty'.$i])){
	        	prnMsg(_('The quantity entered must be numeric'),'error');
		        $Input_Error = true;
                } elseif ($_POST['OutputQty'.$i]<=0){
		         prnMsg(_('The quantity entered must be a positive number greater than zero'),'error');
		         $Input_Error = true;
                }
         }
        if (!Is_Date($_POST['RequiredBy'])){
	    prnMsg(_('The required by date entered is in an invalid format'),'error');
	    $Input_Error = true;
	}

	if ($Input_Error == false) {
		$SQL_ReqDate = FormatDateForSQL($_POST['RequiredBy']);

		if ($EditingExisting == false) {

    		        $sql[] = "INSERT INTO workorders (wo,
                                                        loccode,
                                                        requiredby,
                                                        startdate)
                                        VALUES (" . $_POST['WO'] . "',
                                                '" . DB_escape_string($_POST['StockLocation']) . "',
                                                " . DB_escape_string($_POST['Quantity']) . ",
                                                '" . $SQL_ReqDate . "',
                                                '" . Date('Y-m-d'). "')";

    			for ($i=0;$i<$_POST['NumberOfOutputs'];$i++){
    			      $CostResult = DB_query("SELECT SUM(materialcost+labourcost+overheadcost) AS cost
                                                        FROM stockmaster INNER JOIN bom
                                                        ON stockmaster.stockid=bom.component
                                                        WHERE bom.parent='" . $_POST['OutputItem'.$i] . "'
                                                        AND bom.loccode='" . $_POST['StockLocation'] . "'",
                                                        $db);
                              $CostRow = DB_fetch_row($CostResult);

    			      $sql[] = "INSERT INTO woitems (wo,
    			                                     stockid,
    			                                     qtyreqd,
    			                                     stdcost,
    			                                     nextlotsnref)
    			                        VALUES ( " . $_POST['WO'] . ",
                                                        '" . DB_escape_string($_POST['OutputItem' .$i]) . "',
                                                         " . $CostRow[0] . ",
                                                         " . DB_escape_string($_POST['OutputQty' . $i]) . ",
                                                        '" . DB_escape_string($_POST['OutputNextRef'.$i]) ."')";
                               $sql[] = "INSERT INTO worequirements (wo,
                                                                    parentstockid,
                                                                    stockid,
                                                                    qtypu
                                                                    stdcost)
                                                  SELECT " . $_POST['WO'] . ",
                                                        bom.parent,
                                                        bom.component,
                                                        bom.quantity,
                                                        materialcost+labourcost+overheadcost
                                                        FROM bom INNER JOIN stockmaster
                                                        ON bom.component=stockmaster.stockid
                                                        WHERE parent='" . DB_escape_string($_POST['OutputItem'.$i]) . "'
                                                        AND loccode ='" . DB_escape_string($_POST['StockLocation']) . "'";
                        }
    			$msg = _('The work order been added');
		} else {
			$sql[] = "UPDATE workorders SET requiredby='" . $SQL_ReqDate . "'
			                            WHERE wo=" . $_POST['WO'];
    			for ($i=0;$i<$_POST['NumberOfOutputs'];$i++){
    			      		      $sql[] = "UPDATE woitems SET qtyreqd =  ". DB_escape_string($_POST['OutputQty' . $i]) . ",
    			                                              nextlotsnref = '". DB_escape_string($_POST['OutputNextRef'.$i]) ."'
    			                                WHERE wo=" . $_POST['WO'] . "
                                                        AND stockid='" . DB_escape_string($_POST['OutputItem'.$i]) . "'";
                        }
			$msg = _('The work order has been updated');
		}

        	//run the SQL from either of the above possibilites
         	foreach ($sql as $sql_stmt){
                   $result = DB_query($sql_stmt,$db);
                   if (DB_error_no($db) !=0){
          		prnMsg(_('The work order could not be added/updated'),'error');
      		        if ($debug==1){
      			   prnMsg(_('The SQL statement that failed was') . "<BR>$sql_stmt",'error');
      		        }
    	            }
                }
	        echo "<CENTER><BR>$msg<BR>";
                       	for ($i=0;$i<$_POST['NumberOfOutputs'];$i++){
                       	     unset($_POST['OutputItem'.$i]);
                             unset($_POST['OutputQty'.$i]);
                       	}
		echo "<BR><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>" . _('Enter a new work order') . "</A>";
		echo "<BR><A HREF='" . $rootpath . "/OutstandingWorkOrders.php?" . SID . "'>" . _('Select an existing outstanding work order') . "</A>";
		echo "<BR><BR>";
		exit;
	}
} elseif (isset($_POST['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete=false; //always assume the best

	// can't delete it there are open work issues
	$HasTransResult = DB_query("SELECT * FROM stockmoves
                                    WHERE (stockmoves.type= 26 OR stockmoves.type=26)
                                          AND reference LIKE '" . $_POST['WO'] . "'",$db);
	if (DB_num_rows($HasTransResult)>0){
		prnMsg(_('This work order cannot be deleted because it has work issues or receipts related to it'),'error');
		$CancelDelete=true;
	}

	if ($CancelDelete==false) { //ie all tests proved ok to delete
		// delete the work order requirements
    		$sql="DELETE FROM worequirements WHERE wo=" . $_POST['WO'];
		$ErrMsg=_('The work order requirements could not be deleted');
    		$result = DB_query($sql,$db,$ErrMsg);
                //delete the items on the work order
		$sql = "DELETE FROM woitems WHERE wo=" . $_POST['WO'];
                $result = DB_query($sql,$db,$ErrMsg);
		// delete the actual work order
		$sql="DELETE FROM worksorders WHERE wo=" . $_POST['WO'];
    		$ErrMsg=_('The work order could not be deleted');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('The work order has been deleted'),'success');
		echo "<A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>" . _('Enter a new work order') . "</A>";
		echo "<A HREF='" . $rootpath . "OutstandingWorkOrders.php?" . SID . "'>" . _('Select an existing outstanding work order') . "</A>";
    	}
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";

echo '<CENTER><TABLE>';

if (!isset($_POST['FromStockLocation'])){
	if (isset($_SESSION['UserStockLocation'])){
		$_POST['FromStockLocation']=$_SESSION['UserStockLocation'];
	}
}

if ($EditingExisting == false) {
        $_POST['WO'] = GetNextTransNo(30,$db);
} else {
	$sql="SELECT workorders.loccode,
	             requiredby,
                     startdate,
                     costissued,
                     closed
                FROM workorders INNER JOIN locations
                ON workorders.loccode=locations.loccode
                WHERE workorders.wo=" . $_POST['WO'];

	$WOResult = DB_query($sql,$db);
	if (DB_num_rows($WOResult)==1){
		$myrow = DB_fetch_array($result);
		$_POST['StartDate'] = ConvertSQLDate($myrow['startdate']);
		$_POST['CostIssued'] = $myrow['costissued'];
		$_POST['Closed'] = $myrow['closed'];
		$_POST['RequiredBy'] = ConvertSQLDate($myrow['requiredby']);
		$_POST['StockLocation'] = $myrow['loccode'];
	}
}
echo "<input type=hidden name='WO' value=" .$_POST['WO'] . '>';
echo '<tr><td class="label">' . _('Work Order Reference') . ':</td><td>' . $_POST['WO'] . '</td></tr>';
echo '<tr><td class="label">' . _('Factory Location') .'</td>
	<td><select name="StockLocation">';
$LocResult = DB_query('SELECT loccode,locationname FROM locations',$db);
while ($LocRow = DB_fetch_array($LocResult)){
	if ($_POST['StockLocation']==$LocRow['loccode']){
		echo '<option selected value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'] . '</option>';
	} else {
		echo '<option value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'] . '</option>';
	}
}
echo '</select></td></tr>';
if (!isset($_POST['StartDate'])){
	$_POST['StartDate'] = Date($DefaultDateFormat);
}

echo '<input type="hidden" name="StartDate" value="' . $_POST['StartDate'] . '">';

echo '<tr><td class="label">' . _('Start Date') . ':</td><td>' . $_POST['StartDate'] . '</td></tr>';

if (!isset($_POST['RequiredBy'])){
	$_POST['RequiredBy'] = Date($DefaultDateFormat);
}

echo '<tr><td class="label">' . _('Required By') . ':</td><td><input type="textbox" name="RequiredBy" size=12 maxlength=12 value="' . $_POST['RequiredBy'] . '"></td></tr>';

if (DB_num_rows($WOResult)==1){
	echo '<tr><td class="label">' . _('Accumulated Costs') . ':</td><td>' . number_format($myrow['costissued'],2) . '</td></tr>';
}

echo '<tr><td class="tableheader">' . _('Output Item') . '</td>
		  <td class="tableheader">' . _('Qty Required') . '</td>
		  <td class="tableheader">' . _('Qty Received') . '</td>
		  <td class="tableheader">' . _('Balance Remaining') . '</td></tr>';
if (isset($_POST['NumberOfOutputs'])){
	for ($i=1;$i<$_POST['NumberOfOutputs'];$i++){
		echo '<tr><td align="right"><input type="textbox" name="OutputQty' . $i . ' value=' . $_POST['OutputQty' . $i] . '></td>
		  <td align="right"><input type="hidden name="RecQty"' . $i . ' value=' . $_POST['RecQty' .$i] . '>' . $_POST['RecQty' .$i] .'</td>
		  <td align="right">' . ($_POST['OutputQty' . $i] - $_POST['RecQty' .$i]) . '</td></tr>';
	}
	echo '<input type=hidden name="NumberOfOutputs" value=' . $i .'>';
}



echo '</table>';

echo '<center>';
echo '<input type=submit name="submit" value="' . _('Add/Update') . '">';

if ($EditingExisting == true) {
	echo '<BR><BR><TABLE><TR>';

	if ($_POST['AlreadyReleased']==false) {
		echo "<TD><INPUT TYPE=SUBMIT NAME='release' VALUE='" . _('Release This Work Order') . "'></TD>";
	}

	if ($_POST['Released']) {
		echo "<TD><INPUT TYPE=SUBMIT NAME='close' VALUE='" . _('Close This Work Order') . "'></TD>";
	}

	echo "<TD><INPUT TYPE=SUBMIT NAME='delete' VALUE='" . _('Delete This Work Order') . "'></TD>";

	echo '</TR></TABLE>';
}

if (($EditingExisting == true)){
	// display the WO requirements (ie the BOM)
	echo '<BR>';
	if ($_POST['AlreadyReleased']==false) {
	displayHeading2(_('BOM for item') . ': ' . $_POST['StockID']);
	displayBOM($_POST['StockID']);
	} else {
		echo '<table><tr><td>';
		displayHeading2(_('Work Order Requirements'));
		displayWORequirements($_POST['WO'], $_POST['Quantity']);
		echo '</td><td>';
		displayHeading2(_('Issues against this Work Order'));
		displayWOIssues($_POST['WO']);
		echo '</tr></table>';
	}
}

echo '</FORM>';

include('includes/footer.inc');

?>