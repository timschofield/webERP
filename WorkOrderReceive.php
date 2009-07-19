<?php
/* $Revision: 1.21 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Receive Work Order');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

echo '<a href="'. $rootpath . '/SelectWorkOrder.php?' . SID . '">' . _('Back to Work Orders'). '</a><br>';
echo '<a href="'. $rootpath . '/WorkOrderCosting.php?' . SID . '&WO=' .  $_REQUEST['WO'] . '">' . _('Back to Costing'). '</a><br>';

echo '<form action="' . $_SERVER['PHP_SELF'] . '?' . SID . '" method=post>';

if (!isset($_REQUEST['WO']) OR !isset($_REQUEST['StockID'])) {
    /* This page can only be called with a purchase order number for invoicing*/
    echo '<div class="centre"><a href="' . $rootpath . '/SelectWorkOrder.php?' . SID . '">'.
        _('Select a work order to receive').'</a></div>';
    prnMsg(_('This page can only be opened if a work order has been selected. Please select a work order to receive first'),'info');
    include ('includes/footer.inc');
    exit;
} else {
    echo '<input type="hidden" name="WO" value=' .$_REQUEST['WO'] . '>';
    $_POST['WO']=$_REQUEST['WO'];
    echo '<input type="hidden" name="StockID" value=' .$_REQUEST['StockID'] . '>';
    $_POST['StockID']=$_REQUEST['StockID'];
}

if (isset($_POST['Process'])){ //user hit the process the work order receipts entered.

    $InputError = false; //ie assume no problems for a start - ever the optimist
    $ErrMsg = _('Could not retrieve the details of the selected work order item');
    $WOResult = DB_query("SELECT workorders.loccode,
                             locations.locationname,
                             workorders.requiredby,
                             workorders.startdate,
                             workorders.closed,
                             stockmaster.description,
                             stockmaster.controlled,
                             stockmaster.serialised,
                             stockmaster.decimalplaces,
                             stockmaster.units,
                             woitems.qtyreqd,
                             woitems.qtyrecd,
                             woitems.stdcost,
                             stockcategory.wipact,
                             stockcategory.stockact
							FROM workorders INNER JOIN locations
							ON workorders.loccode=locations.loccode
							INNER JOIN woitems
							ON workorders.wo=woitems.wo
							INNER JOIN stockmaster
							ON woitems.stockid=stockmaster.stockid
							INNER JOIN stockcategory
							ON stockmaster.categoryid=stockcategory.categoryid
							WHERE woitems.stockid='" . $_POST['StockID'] . "'
							AND workorders.wo=".$_POST['WO'],
							$db,
							$ErrMsg);

    if (DB_num_rows($WOResult)==0){
        prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
        include('includes/footer.inc');
        exit;
    }
    $WORow = DB_fetch_array($WOResult);

    $QuantityReceived = 0;

    if($WORow['controlled']==1){ //controlled
        if ($WORow['serialised']==1){ //serialised
            for ($i=0;$i<$_POST['CountOfInputs'];$i++){
                if ($_SESSION['DefineControlledOnWOEntry']==1){
					if (isset($_POST['CheckItem' . $i])){
						$QuantityReceived ++;
                	}
				} else { //not predefined serial numbers
					if (strlen($_POST['SerialNo' . $i])>0){
						$QuantityReceived ++;
					}
				}
            }
        } else { //controlled but not serialised - just lot/batch control
            for ($i=0;$i<15;$i++){
                if (strlen($_POST['BatchRef' . $i])>0){
                    $QuantityReceived += $_POST['Qty' .$i];
                }
            }
        } //end of lot/batch control
    } else { //not controlled - an easy one!
		if (!is_numeric($_POST['Qty'])){
			$InputError=true;
			prnMsg(_('The quantity entered is not numeric - a number is expected'),'error');
		} else {
        	$QuantityReceived = $_POST['Qty'];
		}
    }

    if ($QuantityReceived + $WORow['qtyrecd'] > $WORow['qtyreqd'] *(1+$_SESSION['OverReceiveProportion'])){
        prnMsg(_('The quantity received is greater than the quantity required even after allowing for the configured allowable over-receive proportion. If this is correct then the work order must be modified first.'),'error');
        $InputError=true;
    }

    if ($WORow['serialised']==1){
        /* serialised items form has a possible $_POST['CountOfInputs'] fields for entry of serial numbers - 12 rows x 5 per row
		 * if serial numbers are defined at the time of work order entry $_SESSION['DefineControlledOnWOEntry']==1 then possibly more 
		 * need to inspect $_POST['CountOfInputs']
		 */
        for($i=0;$i<$_POST['CountOfInputs'];$i++){
        //need to test if the serialised item exists first already
            if (trim($_POST['SerialNo' .$i]) != "" AND  ($_SESSION['DefineControlledOnWOEntry']==0
					OR ($_SESSION['DefineControlledOnWOEntry']==1 AND $_POST['CheckedItem'.$i]==true))){
                    $SQL = "SELECT COUNT(*) FROM stockserialitems
                            WHERE stockid='" . $_POST['StockID'] . "'
                            AND loccode = '" . $_POST['IntoLocation'] . "'
                            AND serialno = '" . $_POST['SerialNo' .$i] . "'";
                    $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a serial number for the stock item already exists because');
                    $DbgMsg =  _('The following SQL to test for an already existing serialised stock item was used');
                    $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                    $AlreadyExistsRow = DB_fetch_row($Result);

                    if ($AlreadyExistsRow[0]>0){
                        prnMsg(_('The serial number entered already exists. Duplicate serial numbers are prohibited. The duplicate item is:') . ' ' . $_POST['SerialNo'.$i] ,'error');
                        $InputError = true;
                    }
            }
        } //end loop throught the 60 fields for serial number entry
    }//end check on pre-existing serial numbered items


    if ($_SESSION['ProhibitNegativeStock']==1){
        /*Now look for autoissue components that would go negative */
                $SQL = "SELECT worequirements.stockid,
                               stockmaster.description,
                               locstock.quantity-(" . $QuantityReceived  . "*worequirements.qtypu) AS qtyleft
                          FROM worequirements
                          INNER JOIN stockmaster
                            ON worequirements.stockid=stockmaster.stockid
                          INNER JOIN locstock
                            ON worequirements.stockid=locstock.stockid
                          WHERE worequirements.wo=" . $_POST['WO'] . "
                          AND worequirements.parentstockid='" .$_POST['StockID'] . "'
                          AND locstock.loccode='" . $WORow['loccode'] . "'
                          AND stockmaster.mbflag <>'D'
                          AND worequirements.autoissue=1";

        $ErrMsg = _('Could not retrieve the component quantity left at the location once the component items are issued to the work order (for the purposes of checking that stock will not go negative) because');
        $Result = DB_query($SQL,$db,$ErrMsg);
        while ($NegRow = DB_fetch_array($Result)){
            if ($NegRow['qtyleft']<0){
                prnMsg(_('Receiving the selected quantity against this work order would result in negative stock for a component. The system parameters are set to prohibit negative stocks from occurring. This manufacturing receipt cannot be created until the stock on hand is corrected.'),'error',_('Component') . ' - ' .$NegRow['component'] . ' ' . $NegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
                $InputError = true;
            } // end if negative would result
        } //loop around the autoissue requirements for the work order
    }

    if ($InputError==false){
/************************ BEGIN SQL TRANSACTIONS ************************/

        $Result = DB_Txn_Begin($db);
        /*Now Get the next WOReceipt transaction type 26 - function in SQL_CommonFunctions*/
        $WOReceiptNo = GetNextTransNo(26, $db);

        $PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
        $SQLReceivedDate = FormatDateForSQL($_POST['ReceivedDate']);
        $StockGLCode = GetStockGLCode($_POST['StockID'],$db);

    //Recalculate the standard for the item if there were no items previously received against the work order
        if ($WORow['qtyrecd']==0){
            $CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
                                    FROM stockmaster INNER JOIN bom
                                    ON stockmaster.stockid=bom.component
                                    WHERE bom.parent='" . $_POST['StockID'] . "'
                                    AND bom.loccode='" . $WORow['loccode'] . "'",
                                    $db);
            $CostRow = DB_fetch_row($CostResult);
            if (is_null($CostRow[0]) OR $CostRow[0]==0){
                    $Cost =0;
            } else {
                    $Cost = $CostRow[0];
            }
            //Need to refresh the worequirments with the bom components now incase they changed
            $DelWORequirements = DB_query("DELETE FROM worequirements
                                            WHERE wo=" . $_POST['WO'] . "
                                            AND parentstockid='" . $_POST['StockID'] . "'",
                                            $db);

			//Recursively insert real component requirements
			WoRealRequirements($db, $_POST['WO'], $WORow['loccode'], $_POST['StockID']);

            //Need to check this against the current standard cost and do a cost update if necessary
            $sql = "SELECT materialcost+labourcost+overheadcost AS cost,
                          sum(quantity) AS totalqoh
                    FROM stockmaster INNER JOIN locstock
                        ON stockmaster.stockid=locstock.stockid
                    WHERE stockmaster.stockid='" . $_POST['StockID'] . "'
                    GROUP BY
                        materialcost,
                        labourcost,
                        overheadcost";
            $ItemResult = DB_query($sql,$db);
            $ItemCostRow = DB_fetch_array($ItemResult);

            if ($Cost != $ItemCostRow['cost']){ //the cost roll-up cost <> standard cost

                if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $ItemCostRow['totalqoh']!=0){

                    $CostUpdateNo = GetNextTransNo(35, $db);
                    $PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

                    $ValueOfChange = $ItemCostRow['totalqoh'] * ($Cost - $ItemCostRow['cost']);

                    $SQL = "INSERT INTO gltrans (type,
                                typeno,
                                trandate,
                                periodno,
                                account,
                                narrative,
                                amount)
                            VALUES (35,
                                " . $CostUpdateNo . ",
                                '" . Date('Y-m-d') . "',
                                " . $PeriodNo . ",
                                " . $StockGLCode['adjglact'] . ",
                                '" . _('Cost roll on release of WO') . ': ' . $_POST['WO'] . ' - ' . $_POST['StockID'] . ' ' . _('cost was') . ' ' . $ItemCostRow['cost'] . ' ' . _('changed to') . ' ' . $Cost . ' x ' . _('Quantity on hand of') . ' ' . $ItemCostRow['totalqoh'] . "',
                                " . (-$ValueOfChange) . ")";

                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL credit for the stock cost adjustment posting could not be inserted because');
                    $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

                    $SQL = "INSERT INTO gltrans (type,
                                typeno,
                                trandate,
                                periodno,
                                account,
                                narrative,
                                amount)
                            VALUES (35,
                                " . $CostUpdateNo . ",
                                '" . Date('Y-m-d') . "',
                                " . $PeriodNo . ",
                                " . $StockGLCode['stockact'] . ",
                                '" . _('Cost roll on release of WO') . ': ' . $_POST['WO'] . ' - ' . $_POST['StockID'] . ' ' . _('cost was') . ' ' . $ItemCostRow['cost'] . ' ' . _('changed to') . ' ' . $Cost . ' x ' . _('Quantity on hand of') . ' ' . $ItemCostRow['totalqoh'] . "',
                                " . $ValueOfChange . ")";

                    $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL debit for stock cost adjustment posting could not be inserted because');
                    $DbgMsg = _('The following SQL to insert the GLTrans record was used');
                    $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
                }

                $SQL = "UPDATE stockmaster SET
                            materialcost=" . $Cost . ",
                            labourcost=0,
                            overheadcost=0,
                            lastcost=" . $ItemCostRow['cost'] . "
                        WHERE stockid='" . $_POST['StockID'] . "'";

                $ErrMsg = _('The cost details for the stock item could not be updated because');
                $DbgMsg = _('The SQL that failed was');
                $Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
            } //cost as rolled up now <> current standard cost  so do adjustments
        }   //qty recd previously was 0 so need to check costs and do adjustments as required

        //Do the issues for autoissue components in the worequirements table
        $AutoIssueCompsResult = DB_query("SELECT worequirements.stockid,
                                                 qtypu,
                                                 materialcost+labourcost+overheadcost AS cost,
                                                 stockcategory.stockact,
                                                 stockcategory.stocktype
                                          FROM worequirements
                                          INNER JOIN stockmaster
                                          ON worequirements.stockid=stockmaster.stockid
                                          INNER JOIN stockcategory
                                          ON stockmaster.categoryid=stockcategory.categoryid
                                          WHERE wo=" . $_POST['WO'] . "
                                          AND parentstockid='" .$_POST['StockID'] . "'
                                          AND autoissue=1",
                                          $db);

        $WOIssueNo = GetNextTransNo(28,$db);
        while ($AutoIssueCompRow = DB_fetch_array($AutoIssueCompsResult)){

            //Note that only none-controlled items can be auto-issuers so don't worry about serial nos and batches of controlled ones
            /*Cost variances calculated overall on close of the work orders so NO need to check if cost of component has been updated subsequent to the release of the WO
            */
            if ($AutoIssueCompRow['stocktype']!='L'){
                //Need to get the previous locstock quantity for the component at the location where the WO manuafactured
                $CompQOHResult = DB_query("SELECT locstock.quantity
                                        FROM locstock
                                        WHERE locstock.stockid='" . $AutoIssueCompRow['stockid'] . "'
                                        AND loccode= '" . $WORow['loccode'] . "'",
                                        $db);
                if (DB_num_rows($CompQOHResult)==1){
                            $LocQtyRow = DB_fetch_row($CompQOHResult);
                            $NewQtyOnHand = $LocQtyRow[0] - ($AutoIssueCompRow['qtypu'] * $QuantityReceived);
                } else {
                            /*There must actually be some error this should never happen */
                            $NewQtyOnHand = 0;
                }

                $SQL = "UPDATE locstock
                            SET quantity = quantity - " . ($AutoIssueCompRow['qtypu'] * $QuantityReceived). "
                            WHERE locstock.stockid = '" . $AutoIssueCompRow['stockid'] . "'
                            AND loccode = '" . $WORow['loccode'] . "'";

                $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated by the issue of stock to the work order from an auto issue component because');
                $DbgMsg =  _('The following SQL to update the location stock record was used');
                $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
            } else {
                $NewQtyOnHand =0;
            }
            $SQL = "INSERT INTO stockmoves (stockid,
                                            type,
                                            transno,
                                            loccode,
                                            trandate,
                                            prd,
                                            reference,
                                            price,
                                            qty,
                                            standardcost,
                                            newqoh)
                        VALUES ('" . $AutoIssueCompRow['stockid'] . "',
                            28,
                            " . $WOIssueNo . ",
                            '" . $WORow['loccode'] . "',
                            '" . Date('Y-m-d') . "',
                            " . $PeriodNo . ",
                            '" . $_POST['WO'] . "',
                            " . $AutoIssueCompRow['cost'] . ",
                            " . -($AutoIssueCompRow['qtypu'] * $QuantityReceived) . ",
                            " . $AutoIssueCompRow['cost'] . ",
                            " . $NewQtyOnHand . ")";

            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement record could not be inserted for an auto-issue component because');
            $DbgMsg =  _('The following SQL to insert the stock movement records was used');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

            //Update the workorder record with the cost issued to the work order
            $SQL = "UPDATE workorders SET
                        costissued = costissued+" . ($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost']) ."
                    WHERE wo=" . $_POST['WO'];
            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not be update the work order cost for an auto-issue component because');
            $DbgMsg =  _('The following SQL to update the work order cost was used');
            $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

            if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND ($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost'])!=0){
            //if GL linked then do the GL entries to DR wip and CR stock

                $SQL = "INSERT INTO gltrans (type,
                                    typeno,
                                    trandate,
                                    periodno,
                                    account,
                                    narrative,
                                    amount)
                            VALUES (28,
                                " . $WOIssueNo . ",
                                '" . Date('Y-m-d') . "',
                                " . $PeriodNo . ",
                                " . $StockGLCode['wipact'] . ",
                                '" . $_POST['WO'] . ' - ' . $_POST['StockID'] . ' ' . _('Component') . ': ' . $AutoIssueCompRow['stockid'] . ' - ' . $QuantityReceived . ' x ' . $AutoIssueCompRow['qtypu'] . ' @ ' . number_format($AutoIssueCompRow['cost'],2) . "',
                                " . ($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost']) . ")";

                    $ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The WIP side of the work order issue GL posting could not be inserted because');
                    $DbgMsg =  _('The following SQL to insert the WO issue GLTrans record was used');
                    $Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

                $SQL = "INSERT INTO gltrans (type,
                                    typeno,
                                    trandate,
                                    periodno,
                                    account,
                                    narrative,
                                    amount)
                            VALUES (28,
                                " . $WOIssueNo . ",
                                '" . Date('Y-m-d') . "',
                                " . $PeriodNo . ",
                                " . $AutoIssueCompRow['stockact'] . ",
                                '" . $_POST['WO'] . ' - ' . $_POST['StockID'] . ' -> ' . $AutoIssueCompRow['stockid'] . ' - ' . $QuantityReceived . ' x ' . $AutoIssueCompRow['qtypu'] . ' @ ' . number_format($AutoIssueCompRow['cost'],2) . "',
                                " . -($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost']) . ")";

                    $ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the work order issue GL posting could not be inserted because');
                    $DbgMsg =  _('The following SQL to insert the WO issue GLTrans record was used');
                    $Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);
            }//end GL-stock linked

        } //end of auto-issue loop for all components set to auto-issue


        /* Need to get the current location quantity will need it later for the stock movement */
        $SQL = "SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $_POST['StockID'] . "'
				AND loccode= '" . $_POST['IntoLocation'] . "'";

        $Result = DB_query($SQL, $db);
        if (DB_num_rows($Result)==1){
            $LocQtyRow = DB_fetch_row($Result);
            $QtyOnHandPrior = $LocQtyRow[0];
        } else {
        /*There must actually be some error this should never happen */
            $QtyOnHandPrior = 0;
        }

        $SQL = "UPDATE locstock
                SET quantity = locstock.quantity + " . $QuantityReceived . "
                WHERE locstock.stockid = '" . $_POST['StockID'] . "'
                AND loccode = '" . $_POST['IntoLocation'] . "'";

        $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
        $DbgMsg =  _('The following SQL to update the location stock record was used');
        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

        $WOReceiptNo = GetNextTransNo(26,$db);
        /*Insert stock movements - with unit cost */

        $SQL = "INSERT INTO stockmoves (stockid,
                                        type,
                                        transno,
                                        loccode,
                                        trandate,
                                        price,
                                        prd,
                                        reference,
                                        qty,
                                        standardcost,
                                        newqoh)
                    VALUES ('" . $_POST['StockID'] . "',
                            26,
                            " . $WOReceiptNo . ",
                            '" . $_POST['IntoLocation'] . "',
                            '" . Date('Y-m-d') . "',
                            " . $WORow['stdcost'] . ",
                            " . $PeriodNo . ",
                            '" . $_POST['WO'] . "',
                            " . $QuantityReceived . ",
                            " . $WORow['stdcost'] . ",
                            " . ($QtyOnHandPrior + $QuantityReceived) . ")";

        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order receipt because');
        $DbgMsg =  _('The following SQL to insert the stock movement records was used');
        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

        /*Get the ID of the StockMove... */
        $StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
        /* Do the Controlled Item INSERTS HERE */

        if ($WORow['controlled'] ==1){
            //the form is different for serialised items and just batch/lot controlled items
            if ($WORow['serialised']==1){
                //serialised items form has a possible 60 fields for entry of serial numbers - 12 rows x 5 per row
                for($i=0;$i<$_POST['CountOfInputs'];$i++){
                /*  We need to add the StockSerialItem record and
                    The StockSerialMoves as well */
					if (trim($_POST['SerialNo' .$i]) != ""){
						if ($_SESSION['DefineControlledOnWOEntry']==0 OR 
							($_SESSION['DefineControlledOnWOEntry']==1 AND $_POST['CheckItem'.$i]==true)){
			
							$LastRef = trim($_POST['SerialNo' .$i]);
							//already checked to ensure there are no duplicate serial numbers entered
							if (isset($_POST['QualityText'.$i])){
								$QualityText = $_POST['QualityText'.$i];
							} else {
								$QualityText ='';
							}
							
							$SQL = "INSERT INTO stockserialitems (stockid,
																	loccode,
																	serialno,
																	quantity,
																	qualitytext)
											VALUES ('" . $_POST['StockID'] . "',
													'" . $_POST['IntoLocation'] . "',
													'" . DB_escape_string($_POST['SerialNo' . $i]) . "',
													1,
													'" . DB_escape_string($QualityText) . "')";
							$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
							$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

							/** end of handle stockserialitems records */

							/** now insert the serial stock movement **/
							$SQL = "INSERT INTO stockserialmoves (stockmoveno,
																	stockid,
																	serialno,
																	moveqty)
										VALUES (" . $StkMoveNo . ",
												'" . $_POST['StockID'] . "',
												'" . DB_escape_string($_POST['SerialNo' .$i]) . "',
												1)";
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
							$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
							$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
							
							if ($_SESSION['DefineControlledOnWOEntry']==1){
								//need to delete the item from woserialnos
								$SQL = "DELETE FROM	woserialnos 
											WHERE wo=" . $_POST['WO'] . "
											AND stockid='" . $_POST['StockID'] ."' 
											AND serialno='" . DB_escape_string($_POST['SerialNo'.$i]) . "'";
								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The predefined serial number record could not be deleted because');
								$DbgMsg = _('The following SQL to delete the predefined work order serial number record was used');
								$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);			
							}
						}//end prefined controlled items or not
					} //non blank SerialNo
                } //end for all of the potential serialised fields received
            } else { //the item is just batch/lot controlled not serialised
            /*the form for entry of batch controlled items is only 15 possible fields */
                for($i=0;$i<$_POST['CountOfItems'];$i++){
                /*  We need to add the StockSerialItem record and
                    The StockSerialMoves as well */
                //need to test if the batch/lot exists first already
                    if (trim($_POST['BatchRef' .$i]) != ""){
                        $LastRef = trim($_POST['BatchRef' .$i]);
                        $SQL = "SELECT COUNT(*) FROM stockserialitems
                                WHERE stockid='" . $_POST['StockID'] . "'
                                AND loccode = '" . $_POST['IntoLocation'] . "'
                                AND serialno = '" . $_POST['BatchRef' .$i] . "'";
                        $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a serial number for the stock item already exists because');
                        $DbgMsg =  _('The following SQL to test for an already existing serialised stock item was used');
                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
                        $AlreadyExistsRow = DB_fetch_row($Result);
						if (isset($_POST['QualityText'.$i])){
							$QualityText = $_POST['QualityText'.$i];
						} else {
							$QualityText ='';
						}	
                        if ($AlreadyExistsRow[0]>0){
                            $SQL = 'UPDATE stockserialitems SET quantity = quantity + ' . $_POST['Qty' . $i] . ",
																qualitytext = '" . DB_escape_string($QualityText) . "'
                                        WHERE stockid='" . $_POST['StockID'] . "'
                                        AND loccode = '" . $_POST['IntoLocation'] . "'
                                        AND serialno = '" . $POST['BatchRef' .$i] . "'";
                        } else {
                            $SQL = "INSERT INTO stockserialitems (stockid,
                                                                loccode,
                                                                serialno,
                                                                quantity,
																qualitytext)
                                        VALUES ('" . $_POST['StockID'] . "',
                                                '" . $_POST['IntoLocation'] . "',
                                                '" . DB_escape_string($_POST['BatchRef' . $i]) . "',
                                                " . $_POST['Qty'.$i] . ",
												'" . DB_escape_string($_POST['QualityText']) . "')";
                        }
                        $ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
                        $DbgMsg =  _('The following SQL to insert the serial stock item records was used');
                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);

                        /** end of handle stockserialitems records */

                        /** now insert the serial stock movement **/
                        $SQL = "INSERT INTO stockserialmoves (stockmoveno,
                                                        stockid,
                                                        serialno,
                                                        moveqty)
                                    VALUES (" . $StkMoveNo . ",
                                            '" . $_POST['StockID'] . "',
                                            '" . DB_escape_string($_POST['BatchRef'.$i])  . "',
                                            " . $_POST['Qty'.$i]  . ")";
                        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
                        $DbgMsg = _('The following SQL to insert the serial stock movement records was used');
                        $Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);
						
						if ($_SESSION['DefineControlledOnWOEntry']==1){
							//check how many of the batch/bundle/lot has been received
							$SQL = "SELECT sum(moveqty) FROM stockserialmoves 
										INNER JOIN stockmoves ON stockserialmoves.stockmoveno=stockmoves.stkmoveno
										WHERE stockmoves.type=26
										AND stockserialmoves.stockid='" . $_POST['StockID'] . "' 
										AND stockserialmoves.serialno='" . 	DB_escape_string($_POST['BatchRef'.$i]) . "'";
							
							$BatchTotQtyResult = DB_query($SQL,$db);
							$BatchTotQtyRow = DB_fetch_row($BatchTotQtyResult);
							if ($BatchTotQtyRow[0] >= $_POST['QtyReqd'.$i]){
								//need to delete the item from woserialnos
								$SQL = "DELETE FROM	woserialnos 
										WHERE wo=" . $_POST['WO'] . "
										AND stockid='" . $_POST['StockID'] ."' 
										AND serialno='" . DB_escape_string($_POST['BatchRef'.$i]) . "'";
								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The predefined batch/lot/bundle record could not be deleted because');
								$DbgMsg = _('The following SQL to delete the predefined work order batch/bundle/lot record was used');
								$Result = DB_query($SQL, $db, $ErrMsg, $DbgMsg, true);			
							}
						}
                    }//non blank BundleRef
                } //end for all of the potential batch/lot fields received
            } //end of the batch controlled stuff
        } //end if the woitem received here is a controlled item


        /* If GLLink_Stock then insert GLTrans to debit the GL Code  and credit GRN Suspense account at standard cost*/
        if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND ($WORow['stdcost']*$QuantityReceived)!=0){
        /*GL integration with stock is activated so need the GL journals to make it so */

        /*first the debit the finished stock of the item received from the WO
          the appropriate account was already retrieved into the $StockGLCode variable as the Processing code is kicked off
          it is retrieved from the stock category record of the item by a function in SQL_CommonFunctions.inc*/

            $SQL = "INSERT INTO gltrans (type,
                                    typeno,
                                    trandate,
                                    periodno,
                                    account,
                                    narrative,
                                    amount)
                            VALUES (26,
                                " . $WOReceiptNo . ",
                                '" . Date('Y-m-d') . "',
                                " . $PeriodNo . ",
                                " . $StockGLCode['stockact'] . ",
                                '" . $_POST['WO'] . " " . $_POST['StockID'] . " - " . $WORow['description'] . ' x ' . $QuantityReceived . " @ " . number_format($WORow['stdcost'],2) . "',
                                " . ($WORow['stdcost'] * $QuantityReceived) . ")";

            $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The receipt of work order finished stock GL posting could not be inserted because');
            $DbgMsg = _('The following SQL to insert the work order receipt of finished items GLTrans record was used');
            $Result = DB_query($SQL,$db,$ErrMsg, $DbgMsg, true);

        /*now the credit WIP entry*/
            $SQL = "INSERT INTO gltrans (type,
                                    typeno,
                                    trandate,
                                    periodno,
                                    account,
                                    narrative,
                                    amount)
                            VALUES (26,
                                " . $WOReceiptNo . ",
                                '" . Date('Y-m-d') . "',
                                " . $PeriodNo . ",
                                " . $StockGLCode['wipact'] . ",
                                '" . $_POST['WO'] . " " . $_POST['StockID'] . " - " . $WORow['description'] . ' x ' . $QuantityReceived . " @ " . number_format($WORow['stdcost'],2) . "',
                                " . -($WORow['stdcost'] * $QuantityReceived) . ")";

            $ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The WIP credit on receipt of finished items from a work order GL posting could not be inserted because');
            $DbgMsg =  _('The following SQL to insert the WIP GLTrans record was used');
            $Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg,true);

        } /* end of if GL and stock integrated and standard cost !=0 */


        //update the wo with the new qtyrecd
        $ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('Could not update the work order item record with the total quantity received because');
        $DbgMsg = _('The following SQL was used to update the work order');
        $UpdateWOResult =DB_query("UPDATE woitems
                                    SET qtyrecd=qtyrecd+" . $QuantityReceived . ",
                                        nextlotsnref='" . $LastRef . "'
                                    WHERE wo=" . $_POST['WO'] . "
                                    AND stockid='" . $_POST['StockID'] . "'",
                                    $db,$ErrMsg,$DbgMsg,true);


        $Result = DB_Txn_Commit($db);

        prnMsg(_('The receipt of') . ' ' . $QuantityReceived . ' ' . $WORow['units'] . ' ' . _('of')  . ' ' . $_POST['StockID'] . ' - ' . $WORow['description'] . ' ' . _('against work order') . ' '. $_POST['WO'] . ' ' . _('has been processed'),'info');
        echo "<a href='$rootpath/SelectWorkOrder.php?" . SID . "'>" . _('Select a different work order for receiving finished stock against'). '</a>';
        unset($_POST['WO']);
        unset($_POST['StockID']);
        unset($_POST['IntoLocation']);
        unset($_POST['Process']);
        for ($i=1;$i<$_POST['CountOfInputs'];$i++){
            unset($_POST['SerialNo'.$i]);
            unset($_POST['BatchRef'.$i]);
            unset($_POST['Qty'.$i]);
			unset($_POST['QualityText'.$i]);
			unset($_POST['QtyReqd'.$i]);
        }
        /*end of process work order goods received entry */
        include('includes/footer.inc');
        exit;
    } //end if there were not input errors reported - so the processing was allowed to continue
} //end of if the user hit the process button

/* Always display quantities received and recalc balance for all items on the order */

$ErrMsg = _('Could not retrieve the details of the selected work order item');

$WOResult = DB_query("SELECT workorders.loccode,
                             locations.locationname,
                             workorders.requiredby,
                             workorders.startdate,
                             workorders.closed,
                             stockmaster.description,
                             stockmaster.controlled,
                             stockmaster.serialised,
                             stockmaster.decimalplaces,
                             stockmaster.units,
                             woitems.qtyreqd,
                             woitems.qtyrecd,
                             woitems.stdcost,
                             woitems.nextlotsnref
                    FROM workorders INNER JOIN locations
                    ON workorders.loccode=locations.loccode
                    INNER JOIN woitems
                    ON workorders.wo=woitems.wo
                    INNER JOIN stockmaster
                    ON woitems.stockid=stockmaster.stockid
                    WHERE woitems.stockid='" . $_POST['StockID'] . "' and workorders.wo=".$_POST['WO'],
                    $db,
                    $ErrMsg);

if (DB_num_rows($WOResult)==0){
    prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
    include('includes/footer.inc');
    exit;
}
$WORow = DB_fetch_array($WOResult);

if ($WORow['closed']==1){
    prnMsg(_('The selected work order has been closed and variances calculated and posted. No more receipts of manufactured items can be received against this work order. You should make up a new work order to receive this item against.'),'info');
    include('includes/footer.inc');
    exit;
}

if (!isset($_POST['ReceivedDate'])){
    $_POST['ReceivedDate'] = Date($_SESSION['DefaultDateFormat']);
}
echo '<table cellpadding=2 border=0>
        <tr><td>' . _('Receive work order') . ':</td>
			<td>' . $_POST['WO'] .'</td><td>' . _('Item') . ':</td>
			<td>' . $_POST['StockID'] . ' - ' . $WORow['description'] . '</td></tr>
         <tr><td>' . _('Manufactured at') . ':</td>
		 	<td>' . $WORow['locationname'] . '</td>
			<td>' . _('Required By') . ':</td>
			<td>' . ConvertSQLDate($WORow['requiredby']) . '</td></tr>
         <tr><td>' . _('Quantity Ordered') . ':</td>
		 	<td align=right>' . number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td>
			<td colspan=2>' . $WORow['units'] . '</td></tr>
         <tr><td>' . _('Already Received') . ':</td>
		 	<td align=right>' . number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td>
			<td colspan=2>' . $WORow['units'] . '</td></tr>
         <tr><td>' . _('Date Received') . ':</td>
		 	<td>' . Date($_SESSION['DefaultDateFormat']) . '</td>
			<td>' . _('Received Into') . ':</td><td>
         <select name="IntoLocation">';


if (!isset($_POST['IntoLocation'])){
        $_POST['IntoLocation']=$WORow['loccode'];
}
$LocResult = DB_query('SELECT loccode, locationname FROM locations',$db);
while ($LocRow = DB_fetch_array($LocResult)){
    if ($_POST['IntoLocation'] ==$LocRow['loccode']){
        echo '<option selected value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'];
    } else {
        echo '<option value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'];
    }
}
echo '</select></td></tr>
    </table>';

//Now Setup the form for entering quantities received
echo '<table>';

if($WORow['controlled']==1){ //controlled
    $LotSNRefLength =strlen($WORow['nextlotsnref']);
    $EndOfTextPartPointer = 0;
    if (is_numeric($WORow['nextlotsnref'])){
		$LotSNRefNumeric =$WORow['nextlotsnref'];
		$StringBitOfLotSNRef ='';
	} else { //try to determine if the serial ref is an amalgamation of a text part and a numerical part and increment the numerical part only
		while (is_numeric(substr($WORow['nextlotsnref'],$LotSNRefLength-$EndOfTextPartPointer-1)) AND
            substr($WORow['nextlotsnref'],$LotSNRefLength-$EndOfTextPartPointer-1,1)!='-'){
        	$EndOfTextPartPointer++;
        	$LotSNRefNumeric = substr($WORow['nextlotsnref'],$LotSNRefLength-$EndOfTextPartPointer);
        	$StringBitOfLotSNRef = substr($WORow['nextlotsnref'],0,$LotSNRefLength-$EndOfTextPartPointer);
    	}
	}
	
	
    if ($WORow['serialised']==1){ //serialised
        echo '<tr><th colspan="5">' . _('Serial Numbers Received') . '</th></tr>';
        echo '<tr>';
		
		if ($_SESSION['DefineControlledOnWOEntry']==1){ //then potentially serial numbers already set up
			//retrieve the woserialnos
			$WOSNResult = DB_query("SELECT serialno, qualitytext 
									FROM woserialnos 
									WHERE wo=" . $_POST['WO'] . "
									AND stockid='" . $_POST['StockID'] . "'",$db);
			if (DB_num_rows($WOSNResult)==0){
				echo '<th colspan="5">' . _('No serial numbers defined yet') . '</th></tr>';
			} else {
				$i=0; //the SerialNo counter
				while ($WOSNRow = DB_fetch_row($WOSNResult)){
					if (($i/5 -intval($i/5))==0){
						echo '</tr><tr>';
					}				
					echo '<td><input type="checkbox" name="CheckItem' . $i . '">'. $WOSNRow[0] .'<input type="hidden" name="SerialNo' . $i . '" value="' . $WOSNRow[0] . '"><input type="hidden" name="QualityText' . $i . '" value="' . $WOSNRow[1] . '"></td>';
					$i++;
				}
			}
		} else { //serial numbers not yet defined need to enter them manually now
			for ($i=0;$i<60;$i++){
				if (($i/5 -intval($i/5))==0){
					echo '</tr><tr>';
				}
				echo '<td><input type="textbox" name="SerialNo' . $i . '" ';
				if ($i==0){
					echo 'value="' . $StringBitOfLotSNRef . ($LotSNRefNumeric + 1) . '"';
				}
				echo '"></td>';
				
			}
		}
		echo '</tr>';
		echo '<input type="hidden" name="CountOfInputs" value=' . $i . '>';
		echo '<tr><td align="center" colspan=5><input type=submit name="Process" value="' . _('Process Manufactured Items Received') . '"></td></tr>';
    } else { //controlled but not serialised - just lot/batch control
        echo '<tr><th colspan="2">' . _('Batch/Lots Received') . '</th></tr>';
		if ($_SESSION['DefineControlledOnWOEntry']==1){ //then potentially batches/lots already set up
			//retrieve them from woserialnos
			$WOSNResult = DB_query("SELECT serialno, quantity, qualitytext 
									FROM woserialnos 
									WHERE wo=" . $_POST['WO'] . "
									AND stockid='" . $_POST['StockID'] . "'",$db);
			if (DB_num_rows($WOSNResult)==0){
				echo '<th colspan="5">' . _('No batches/lots defined yet') . '</th></tr>';
			} else {
				$i=0; //the Batch counter
				while ($WOSNRow = DB_fetch_row($WOSNResult)){
					if (($i/5 -intval($i/5))==0){
						echo '</tr><tr>';
					}				
					echo '<td><input type="textbox" name="BatchRef' . $i . '" value="' . $WOSNRow[0] . '"></td>
						  <td><input type="textbox" class="number" name="Qty' . $i . '" onKeyPress="return restrictToNumbers(this, event)">
						  		<input type="hidden" name="QualityText' . $i . '" value="' . $WOSNRow[2] . '">
						  		<input type="hidden" name="QtyReqd' . $i . '" value="' . $WOSNRow[1] . '"></td></tr>';
					$i++;
				}
			}
		} else { // batches/lots yet to be set up enter them manually
			for ($i=0;$i<15;$i++){
				echo '<tr><td><input type="textbox" name="BatchRef' . $i .'" ';

				if ($i==0){
					echo 'value="' . $StringBitOfLotSNRef . ($LotSNRefNumeric + 1) . '"';
				}
				echo '></td>
						  <td><input type="textbox" class="number" name="Qty' . $i .'"></td></tr>';
			}
		}
		echo '<input type="hidden" name="CountOfInputs" value=' . $i . '>';
        echo '<tr><td align="center" colspan=2><input type=submit name="Process" value="' . _('Process Manufactured Items Received') . '"></td></tr>';
    } //end of lot/batch control
} else { //not controlled - an easy one!

    echo '<tr><td>' . _('Quantity Received') . ':</td>
              <td><input type="text" class="number" name="Qty" onKeyPress="return restrictToNumbers(this, event)"></tr>';
    echo '<tr><td align="center"><input type=submit name="Process" value="' . _('Process Manufactured Items Received') . '"></td></tr>';
}

echo '</table>';



echo '</form>';

include('includes/footer.inc');
?>