<?php

/* $Revision: 1.12 $ */

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Work Order Entry');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_REQUEST['WO']) AND $_REQUEST['WO']!=''){
	$_POST['WO'] = $_REQUEST['WO'];
    $EditingExisting = true;
} else {
    $_POST['WO'] = GetNextTransNo(30,$db);
    $InsWOResult = DB_query("INSERT INTO workorders (wo,
                                                     loccode,
                                                     requiredby,
                                                     startdate)
                                     VALUES (" . $_POST['WO'] . ",
                                            '" . DB_escape_string($_SESSION['UserStockLocation']) . "',
                                            '" . Date('Y-m-d') . "',
                                            '" . Date('Y-m-d'). "')",
                              $db);
}

if (isset($_GET['NewItem'])){
	$NewItem = $_GET['NewItem'];
}

if (!isset($_POST['StockLocation'])){
	if (isset($_SESSION['UserStockLocation'])){
		$_POST['StockLocation']=$_SESSION['UserStockLocation'];
	}
}

if (isset($_POST['Search'])){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'),'warn');
	}
	If (strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
		$_POST['Keywords'] = strtoupper($_POST['Keywords']);

		$i=0;
		$SearchString = '%';
		while (strpos($_POST['Keywords'], ' ', $i)) {
			$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
			$i=strpos($_POST['Keywords'],' ',$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster,
					stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.description " . LIKE . " '$SearchString'
					AND stockmaster.discontinued=0
					AND mbflag='M'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster, stockcategory
					WHERE  stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.discontinued=0
					AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND mbflag='M'
					ORDER BY stockmaster.stockid";
		}

	} elseif (strlen($_POST['StockCode'])>0){

		$_POST['StockCode'] = strtoupper($_POST['StockCode']);
		$SearchString = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
					AND stockmaster.discontinued=0
					AND mbflag='M'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND mbflag='M'
					ORDER BY stockmaster.stockid";
		}
	} else {
		if ($_POST['StockCat']=='All'){
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster, stockcategory
					WHERE  stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.discontinued=0
					AND mbflag='M'
					ORDER BY stockmaster.stockid";
		} else {
			$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units
					FROM stockmaster, stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='F' OR stockcategory.stocktype='D')
					AND stockmaster.discontinued=0
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					AND mbflag='M'
					ORDER BY stockmaster.stockid";
		  }
	}

	$SQL = $SQL . ' LIMIT ' . $_SESSION['DisplayRecordsMax'];

	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL used to get the part selection was');
	$SearchResult = DB_query($SQL,$db,$ErrMsg, $DbgMsg);

	if (DB_num_rows($SearchResult)==0 ){
		prnMsg (_('There are no products available meeting the criteria specified'),'info');

		if ($debug==1){
			prnMsg(_('The SQL statement used was') . ':<BR>' . $SQL,'info');
		}
	}
	if (DB_num_rows($SearchResult)==1){
		$myrow=DB_fetch_array($SearchResult);
		$NewItem = $myrow['stockid'];
		DB_data_seek($SearchResult,0);
	}

} //end of if search

if (isset($NewItem) AND isset($_POST['WO'])){
      $InputError=false;
	  $CheckItemResult = DB_query("SELECT mbflag,
						eoq
					FROM stockmaster
					WHERE stockid='" . $NewItem . "'",
					$db);
	  if (DB_num_rows($CheckItemResult)==1){
	  		$CheckItemRow = DB_fetch_array($CheckItemResult);
	  		$EOQ = $CheckItemRow['eoq'];
	  		if ($CheckItemRow['mbflag']!='M'){
	  			prnMsg(_('The item selected cannot be addded to a work order because it is not a manufactured item'),'warn');
	  			$InputError=true;
	  		}
	  } else {
	  		prnMsg(_('The item selected cannot be found in the database'),'error');
	  		$InputError = true;
	  }
	  $CheckItemResult = DB_query("SELECT stockid
					FROM woitems
					WHERE stockid='" . $NewItem . "'
					AND wo=" .$_POST['WO'],
					$db);
	  if(DB_num_rows($CheckItemResult)==1){
	  		prnMsg(_('This item is already on the work order and cannot be added again'),'warn');
	  		$InputError=true;
	  }


	  if ($InputError==false){
		$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
                                                        FROM stockmaster INNER JOIN bom
                                                        ON stockmaster.stockid=bom.component
                                                        WHERE bom.parent='" . DB_escape_string($NewItem) . "'
                                                        AND bom.loccode='" . DB_escape_string($_POST['StockLocation']) . "'",
                             $db);
        	$CostRow = DB_fetch_row($CostResult);
		if (is_null($CostRow[0]) OR $CostRow[0]==0){
				$Cost =0;
				prnMsg(_('The cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
		} else {
				$Cost = $CostRow[0];
		}
		if (!isset($EOQ) OR $EOQ==0){
			$EOQ=1;
		}
		$sql[] = "INSERT INTO woitems (wo,
	                             stockid,
	                             qtyreqd,
	                             stdcost)
	         VALUES ( " . $_POST['WO'] . ",
                         '" . DB_escape_string($NewItem) . "',
                         " . $EOQ . ",
                          " . $Cost . "
                          )";

		$sql[] = "INSERT INTO worequirements (wo,
                                            parentstockid,
                                            stockid,
                                            qtypu,
                                            stdcost,
                                            autoissue)
      	                 SELECT " . $_POST['WO'] . ",
        	                           bom.parent,
                                       bom.component,
                                       bom.quantity,
                                       materialcost+labourcost+overheadcost,
                                       autoissue
                         FROM bom INNER JOIN stockmaster
                         ON bom.component=stockmaster.stockid
                         WHERE parent='" . DB_escape_string($NewItem) . "'
                         AND loccode ='" . DB_escape_string($_POST['StockLocation']) . "'";

         //run the SQL from either of the above possibilites
         $ErrMsg = _('The work order item could not be added');
         foreach ($sql as $sql_stmt){
                 $result = DB_query($sql_stmt,$db,$ErrMsg);
         } //end for each $sql statement
         unset($NewItem);
      } //end if there were no input errors
} //adding a new item to the work order


if (isset($_POST['submit'])) { //The update button has been clicked

	$Input_Error = false; //hope for the best
     for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++){
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
		$QtyRecd=0;

		for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++){
				$QtyRecd+=$_POST['RecdQty'.$i];
		}

		if ($QtyRecd==0){ //can only change factory location if Qty Recd is 0
				$sql[] = "UPDATE workorders SET requiredby='" . $SQL_ReqDate . "',
												loccode='" . DB_escape_string($_POST['StockLocation']) . "'
			        	    WHERE wo=" . $_POST['WO'];
		} else {
				prnMsg(_('The factory where this work order is made can only be updated if the quantity received on all output items is 0'),'warn');
				$sql[] = "UPDATE workorders SET requiredby='" . $SQL_ReqDate . "'
							WHERE wo=" . $_POST['WO'];
		}

    	for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++){
    			if ($_POST['QtyRecd'.$i]>$_POST['OutputQty'.$i]){
    					$_POST['OutputQty'.$i]=$_POST['QtyRecd'.$i]; //OutputQty must be >= Qty already reced
    			}
    			if ($_POST['RecdQty'.$i]==0){ // can only change location cost if QtyRecd=0
	    				$CostResult = DB_query("SELECT SUM(materialcost+labourcost+overheadcost) AS cost
                                                        FROM stockmaster INNER JOIN bom
                                                        ON stockmaster.stockid=bom.component
                                                        WHERE bom.parent='" . DB_escape_string($_POST['OutputItem'.$i]) . "'
                                                        AND bom.loccode='" . DB_escape_string($_POST['StockLocation']) . "'",
    		                         $db);
        				$CostRow = DB_fetch_row($CostResult);
						if (is_null($CostRow[0])){
							$Cost =0;
							prnMsg(_('The cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
						} else {
							$Cost = $CostRow[0];
						}
						$sql[] = "UPDATE woitems SET qtyreqd =  ". DB_escape_string($_POST['OutputQty' . $i]) . ",
    			                                 nextlotsnref = '". DB_escape_string($_POST['NextLotSNRef'.$i]) ."',
    			                                 stdcost =" . $Cost . "
    			                  WHERE wo=" . $_POST['WO'] . "
                                  AND stockid='" . DB_escape_string($_POST['OutputItem'.$i]) . "'";
      			} else {
    			    	$sql[] = "UPDATE woitems SET qtyreqd =  ". DB_escape_string($_POST['OutputQty' . $i]) . ",
    			                                 nextlotsnref = '". DB_escape_string($_POST['NextLotSNRef'.$i]) ."'
    			                  WHERE wo=" . $_POST['WO'] . "
                                  AND stockid='" . DB_escape_string($_POST['OutputItem'.$i]) . "'";
                }
        }

		//run the SQL from either of the above possibilites
        $ErrMsg = _('The work order could not be added/updated');
        foreach ($sql as $sql_stmt){
        //	echo '<BR>' . $sql_stmt;
            $result = DB_query($sql_stmt,$db,$ErrMsg);

        }

	    prnMsg(_('The work order has been updated'),'success');

        for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++){
          	     unset($_POST['OutputItem'.$i]);
                 unset($_POST['OutputQty'.$i]);
                 unset($_POST['QtyRecd'.$i]);
                 unset($_POST['NetLotSNRef'.$i]);
        }
		echo "<BR><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "'>" . _('Enter a new work order') . "</A>";
		echo "<BR><A HREF='" . $rootpath . "/SelectWorkOrder.php?" . SID . "'>" . _('Select an existing work order') . "</A>";
		echo "<BR><BR>";
	}
} elseif (isset($_POST['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete=false; //always assume the best

	// can't delete it there are open work issues
	$HasTransResult = DB_query("SELECT * FROM stockmoves
                                    WHERE (stockmoves.type= 26 OR stockmoves.type=28)
                                          AND reference LIKE '%" . $_POST['WO'] . "%'",$db);
	if (DB_num_rows($HasTransResult)>0){
		prnMsg(_('This work order cannot be deleted because it has issues or receipts related to it'),'error');
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
		$sql="DELETE FROM workorders WHERE wo=" . $_POST['WO'];
    		$ErrMsg=_('The work order could not be deleted');
		$result = DB_query($sql,$db,$ErrMsg);
		prnMsg(_('The work order has been deleted'),'success');

		echo "<P><A HREF='" . $rootpath . "/SelectWorkOrder.php?" . SID . "'>" . _('Select an existing outstanding work order') . "</A>";
		unset($_POST['WO']);
		for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++){
          	     unset($_POST['OutputItem'.$i]);
                 unset($_POST['OutputQty'.$i]);
                 unset($_POST['QtyRecd'.$i]);
                 unset($_POST['NetLotSNRef'.$i]);
        }
        include('includes/footer.inc');
        exit;
    }
}

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . ">";

echo '<CENTER><TABLE>';


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
	$myrow = DB_fetch_array($WOResult);
	$_POST['StartDate'] = ConvertSQLDate($myrow['startdate']);
	$_POST['CostIssued'] = $myrow['costissued'];
	$_POST['Closed'] = $myrow['closed'];
	$_POST['RequiredBy'] = ConvertSQLDate($myrow['requiredby']);
	$_POST['StockLocation'] = $myrow['loccode'];
	$ErrMsg =_('Could not get the work order items');
	$WOItemsResult = DB_query('SELECT woitems.stockid,
										stockmaster.description,
										qtyreqd,
										qtyrecd,
										stdcost,
										nextlotsnref,
										controlled,
										serialised
								FROM woitems INNER JOIN stockmaster
								ON woitems.stockid=stockmaster.stockid
								WHERE wo=' .$_POST['WO'],$db,$ErrMsg);

	$NumberOfOutputs=DB_num_rows($WOItemsResult);
	$i=1;
	while ($WOItem=DB_fetch_array($WOItemsResult)){
				$_POST['OutputItem' . $i]=$WOItem['stockid'];
				$_POST['OutputItemDesc'.$i]=$WOItem['description'];
				$_POST['OutputQty' . $i]= $WOItem['qtyreqd'];
		  		$_POST['RecdQty' .$i] =$WOItem['qtyrecd'];
		  		$_POST['NextLotSNRef' .$i]=$WOItem['nextlotsnref'];
		  		$_POST['Controlled'.$i] =$WOItem['controlled'];
		  		$_POST['Serialised'.$i] =$WOItem['serialised'];
		  		$i++;
	}
}

echo "<input type=hidden name='WO' value=" .$_POST['WO'] . '>';
echo '<tr><td class="label">' . _('Work Order Reference') . ':</td><td>' . $_POST['WO'] . '</td></tr>';
echo '<tr><td class="label">' . _('Factory Location') .':</td>
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
	$_POST['StartDate'] = Date($_SESSION['DefaultDateFormat']);
}

echo '<input type="hidden" name="StartDate" value="' . $_POST['StartDate'] . '">';

echo '<tr><td class="label">' . _('Start Date') . ':</td><td>' . $_POST['StartDate'] . '</td></tr>';

if (!isset($_POST['RequiredBy'])){
	$_POST['RequiredBy'] = Date($_SESSION['DefaultDateFormat']);
}

echo '<tr><td class="label">' . _('Required By') . ':</td>
		  <td><input type="textbox" name="RequiredBy" size=12 maxlength=12 value="' . $_POST['RequiredBy'] . '"></td></tr>';

if (isset($WOResult)){
	echo '<tr><td class="label">' . _('Accumulated Costs') . ':</td>
			  <td>' . number_format($myrow['costissued'],2) . '</td></tr>';
}
echo '</table>
		<P><table>';
echo '<tr><td class="tableheader">' . _('Output Item') . '</td>
		  <td class="tableheader">' . _('Qty Required') . '</td>
		  <td class="tableheader">' . _('Qty Received') . '</td>
		  <td class="tableheader">' . _('Balance Remaining') . '</td>
		  <td class="tableheader">' . _('Next Lot/SN Ref') . '</td>
		  </tr>';


if (isset($NumberOfOutputs)){
	for ($i=1;$i<=$NumberOfOutputs;$i++){
		echo '<tr><td><input type="hidden" name="OutputItem' . $i . '" value="' . $_POST['OutputItem' .$i] . '">' . $_POST['OutputItem' . $i] . ' - ' . $_POST['OutputItemDesc' .$i] . '</td>
		  		<td><input type="textbox" name="OutputQty' . $i . '" value=' . $_POST['OutputQty' . $i] . ' size=10 maxlength=10></td>
		  		<td align="right"><input type="hidden" name="RecdQty' . $i . '" value=' . $_POST['RecdQty' .$i] . '>' . $_POST['RecdQty' .$i] .'</td>
		  		<td align="right">' . ($_POST['OutputQty' . $i] - $_POST['RecdQty' .$i]) . '</td>';
		if ($_POST['Controlled'.$i]==1){
			echo '<td><input type=textbox name="NextLotSNRef' .$i . '" value="' . $_POST['NextLotSNRef'.$i] . '"></td>';
		}
		echo '</tr>';
	}
	echo '<input type=hidden name="NumberOfOutputs" value=' . ($i -1).'>';
}


echo '</table>';

echo '<center>';
echo '<hr><input type=submit name="submit" value="' . _('Update') . '">';

echo '<BR><P><INPUT TYPE=SUBMIT NAME="delete" VALUE="' . _('Delete This Work Order') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">';

echo '<hr>';

$SQL="SELECT categoryid,
			categorydescription
		FROM stockcategory
		WHERE stocktype='F' OR stocktype='D'
		ORDER BY categorydescription";
	$result1 = DB_query($SQL,$db);

echo '<B>' . $msg . '</B><TABLE><TR><TD><FONT SIZE=2>' . _('Select a stock category') . ':</FONT><SELECT NAME="StockCat">';

if (!isset($_POST['StockCat'])){
	echo "<OPTION SELECTED VALUE='All'>" . _('All');
	$_POST['StockCat'] ='All';
} else {
	echo "<OPTION VALUE='All'>" . _('All');
}

while ($myrow1 = DB_fetch_array($result1)) {

	if ($_POST['StockCat']==$myrow1['categoryid']){
		echo '<OPTION SELECTED VALUE=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
	} else {
		echo '<OPTION VALUE='. $myrow1['categoryid'] . '>' . $myrow1['categorydescription'];
	}
}
?>

</SELECT>
<TD><FONT SIZE=2><?php echo _('Enter text extracts in the'); ?> <B><?php echo _('description'); ?></B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25 VALUE="<?php if (isset($_POST['Keywords'])) echo $_POST['Keywords']; ?>"></TD></TR>
<TR><TD></TD>
		<TD><FONT SIZE 3><B><?php echo _('OR'); ?> </B></FONT><FONT SIZE=2><?php echo _('Enter extract of the'); ?> <B><?php echo _('Stock Code'); ?></B>:</FONT></TD>
	    <TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18 VALUE="<?php if (isset($_POST['StockCode'])) echo $_POST['StockCode']; ?>"></TD>
		</TR>
		</TABLE>
		<CENTER><INPUT TYPE=SUBMIT NAME="Search" VALUE="<?php echo _('Search Now'); ?>">

<script language='JavaScript' type='text/javascript'>

   	document.forms[0].StockCode.select();
   	document.forms[0].StockCode.focus();

</script>

<?php
echo '</CENTER>';

if (isset($SearchResult)) {

	if (DB_num_rows($SearchResult)>1){

		echo '<CENTER><TABLE CELLPADDING=2 COLSPAN=7 BORDER=1>';
		$TableHeader = '<TR><TD class="tableheader">' . _('Code') . '</TD>
                   			<TD class="tableheader">' . _('Description') . '</TD>
                   			<TD class="tableheader">' . _('Units') . '</TD></TR>';
		echo $TableHeader;
		$j = 1;
		$k=0; //row colour counter
		$ItemCodes = array();
		for ($i=1;$i<=$NumberOfOutputs;$i++){
			$ItemCodes[] =$_POST['OutputItem'.$i];
		}

		while ($myrow=DB_fetch_array($SearchResult)) {

			if (!in_array($myrow['stockid'],$ItemCodes)){
				if (function_exists('imagecreatefrompng') ){
					$ImageSource = '<IMG SRC="GetStockImage.php?SID&automake=1&textcolor=FFFFFF&bgcolor=CCCCCC&StockID=' . urlencode($myrow['stockid']). '&text=&width=64&height=64">';
				} else {
					if(file_exists($_SERVER['DOCUMENT_ROOT'] . $rootpath. '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg')) {
						$ImageSource = '<IMG SRC="' .$_SERVER['DOCUMENT_ROOT'] . $rootpath . '/' . $_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.jpg">';
					} else {
						$ImageSource = _('No Image');
					}
				}

				if ($k==1){
					echo '<tr bgcolor="#CCCCCC">';
					$k=0;
				} else {
					echo '<tr bgcolor="#EEEEEE">';
					$k=1;
				}

				printf("<TD><FONT SIZE=1>%s</FONT></TD>
						<TD><FONT SIZE=1>%s</FONT></TD>
						<TD><FONT SIZE=1>%s</FONT></TD>
						<TD>%s</TD>
						<TD><FONT SIZE=1><A HREF='%s'>"
						. _('Add to Work Order') . '</A></FONT></TD>
						</TR>',
						$myrow['stockid'],
						$myrow['description'],
						$myrow['units'],
						$ImageSource,
						$_SERVER['PHP_SELF'] . '?' . SID . 'WO=' . $_POST['WO'] . '&NewItem=' . $myrow['stockid']);

				$j++;
				If ($j == 25){
					$j=1;
					echo $TableHeader;
				} //end of page full new headings if
			} //end if not already on work order
		}//end of while loop
	} //end if more than 1 row to show
	echo '</TABLE>';

}#end if SearchResults to show



echo '</FORM>';

include('includes/footer.inc');

?>