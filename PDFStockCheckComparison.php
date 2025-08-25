<?php
/* Creates a pdf comparing the quantites entered as counted at a given range of locations against the quantity stored as on hand as at the time a stock check was initiated. */

include('includes/session.php');

if (isset($_POST['PrintPDF']) AND isset($_POST['ReportOrClose'])){

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title', __('Check Comparison Report') );
	$pdf->addInfo('Subject', __('Inventory Check Comparison'). ' ' . Date($_SESSION['DefaultDateFormat']));
	$PageNumber=1;
	$LineHeight=15;


	include('includes/SQL_CommonFunctions.php');


/*First off do the Inventory Comparison file stuff */
	if ($_POST['ReportOrClose']=='ReportAndClose'){

		$SQL = "SELECT stockcheckfreeze.stockid,
						stockcheckfreeze.loccode,
						qoh,
						actualcost AS standardcost
				FROM stockmaster INNER JOIN stockcheckfreeze
				ON stockcheckfreeze.stockid=stockmaster.stockid
				ORDER BY stockcheckfreeze.loccode,
						stockcheckfreeze.stockid";

		$ErrMsg = __('The inventory check file could not be retrieved');
		$StockChecks = DB_query($SQL, $ErrMsg);

		$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']));
		$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
		$AdjustmentNumber = GetNextTransNo(17);

		while ($MyRow = DB_fetch_array($StockChecks)){

			$SQL = "SELECT SUM(stockcounts.qtycounted) AS totcounted,
					COUNT(stockcounts.stockid) AS noofcounts
					FROM stockcounts
					WHERE stockcounts.stockid='" . $MyRow['stockid'] . "'
					AND stockcounts.loccode='" . $MyRow['loccode'] . "'";

			$ErrMsg = __('The inventory counts file could not be retrieved');
			$StockCounts = DB_query($SQL, $ErrMsg);

			$StkCountResult = DB_query($SQL);
			$StkCountRow = DB_fetch_array($StkCountResult);

			$StockQtyDifference = $StkCountRow['totcounted'] - $MyRow['qoh'];

			if ($_POST['ZeroCounts']=='Leave' AND $StkCountRow['noofcounts']==0){
				$StockQtyDifference =0;
			}

			if ($StockQtyDifference !=0){ // only adjust stock if there is an adjustment to make!!

				DB_Txn_Begin();

				// Need to get the current location quantity will need it later for the stock movement
				$SQL="SELECT locstock.quantity
						FROM locstock
					WHERE locstock.stockid='" . $MyRow['stockid'] . "'
					AND loccode= '" . $MyRow['loccode'] . "'";

				$Result = DB_query($SQL);
				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}

				$SQL = "INSERT INTO stockmoves (stockid,
								type,
								transno,
								loccode,
								trandate,
								userid,
								prd,
								reference,
								qty,
								newqoh,
								standardcost)
						VALUES ('" . $MyRow['stockid'] . "',
							17,
							'" . $AdjustmentNumber . "',
							'" . $MyRow['loccode'] . "',
							'" . $SQLAdjustmentDate . "',
							'" . $_SESSION['UserID'] . "',
							'" . $PeriodNo . "',
							'" . __('Inventory Check') . "',
							'" . $StockQtyDifference . "',
							'" . ($QtyOnHandPrior + $StockQtyDifference) . "',
							'" . $MyRow['standardcost'] . "'
						)";

				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The stock movement record cannot be inserted because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				$SQL = "UPDATE locstock
						SET quantity = quantity + '" . $StockQtyDifference . "'
						WHERE stockid='" . $MyRow['stockid'] . "'
						AND loccode='" . $MyRow['loccode'] . "'";
				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The location stock record could not be updated because');
				$Result = DB_query($SQL, $ErrMsg, '', true);

				if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $MyRow['standardcost'] > 0){

					$StockGLCodes = GetStockGLCode($MyRow['stockid']);
					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction entries could not be added because');

					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									amount,
									narrative)
							VALUES (17,
								'" .$AdjustmentNumber . "',
								'" . $SQLAdjustmentDate . "',
								'" . $PeriodNo . "',
								'" .  $StockGLCodes['adjglact'] . "',
								'" . ($MyRow['standardcost'] * -($StockQtyDifference)) . "',
								'" . mb_substr($MyRow['stockid'] . " x " . $StockQtyDifference . " @ " . $MyRow['standardcost'] . " - " . __('Inventory Check'), 0, 200) . "')";
					$Result = DB_query($SQL, $ErrMsg, '', true);

					$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The general ledger transaction entries could not be added because');

					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									amount,
									narrative)
							VALUES (17,
								'" .$AdjustmentNumber . "',
								'" . $SQLAdjustmentDate . "',
								'" . $PeriodNo . "',
								'" .  $StockGLCodes['stockact'] . "',
								'" . $MyRow['standardcost'] * $StockQtyDifference . "',
                                '" . mb_substr($MyRow['stockid'] . " x " . $StockQtyDifference . " @ " . $MyRow['standardcost'] . " - " . __('Inventory Check'), 0, 200) . "')";
					$Result = DB_query($SQL, $ErrMsg, '', true);

				} //END INSERT GL TRANS
				$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('Unable to COMMIT transaction while adjusting stock in StockCheckAdjustmet report');
				DB_Txn_Commit();

			} // end if $StockQtyDifference !=0

		} // end loop round all the checked parts
	} // end user wanted to close the inventory check file and do the adjustments

	// now do the report
	$ErrMsg = __('The Inventory Comparison data could not be retrieved because');
	$SQL = "SELECT stockcheckfreeze.stockid,
					description,
					stockmaster.categoryid,
					stockcategory.categorydescription,
					stockcheckfreeze.loccode,
					locations.locationname,
					stockcheckfreeze.qoh,
					stockmaster.decimalplaces,
					bin
			FROM stockcheckfreeze INNER JOIN stockmaster
				ON stockcheckfreeze.stockid=stockmaster.stockid
			INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN locations
				ON stockcheckfreeze.loccode=locations.loccode
			INNER JOIN locstock
				ON stockcheckfreeze.loccode=locstock.loccode
				AND stockcheckfreeze.stockid=locstock.stockid
			ORDER BY stockcheckfreeze.loccode,
				stockmaster.categoryid,
				stockcheckfreeze.stockid";

	$CheckedItems = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($CheckedItems)==0){
		$Title= __('Inventory Comparison Comparison Report');
		include('includes/header.php');
		echo '<p>';
		prnMsg(__('There is no inventory check data to report on'), 'warn');
		echo '<p>' .  __('To start an inventory check first run the'). ' <a href="' . $RootPath . '/StockCheck.php">' .  __('inventory check sheets') . '</a> - '. __('and select the option to create new Inventory Comparison data file');
		include('includes/footer.php');
		exit();
	}
	$FirstRow = DB_fetch_array($CheckedItems);
	$LocationName = $FirstRow['locationname'];
	DB_data_seek($CheckedItems,0);

	include('includes/PDFStockComparisonPageHeader.php');

	$Location = '';
	$Category = '';

	while ($CheckItemRow = DB_fetch_array($CheckedItems)){

		if ($Location!=$CheckItemRow['loccode']){
			$FontSize=14;
			if ($Location!=''){ /*Then it is NOT the first time round */
				/*draw a line under the Location*/
				$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
				$YPos -=$LineHeight;
			}

			$pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$CheckItemRow['loccode'] . ' - ' . $CheckItemRow['locationname'], 'left');
			$Location = $CheckItemRow['loccode'];
			$YPos -=$LineHeight;
		}


		if ($Category!=$CheckItemRow['categoryid']){
			$FontSize=12;
			if ($Category!=''){ /*Then it is NOT the first time round */
				/*draw a line under the CATEGORY TOTAL*/
				$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
				$YPos -=$LineHeight;
			}

			$pdf->addTextWrap($Left_Margin+15,$YPos,260-$Left_Margin,$FontSize,$CheckItemRow['categoryid'] . ' - ' . $CheckItemRow['categorydescription'], 'left');
			$Category = $CheckItemRow['categoryid'];
			$YPos -=$LineHeight;
		}


		$SQL = "SELECT qtycounted,
						reference
				FROM stockcounts
				WHERE loccode ='" . $Location . "'
				AND stockid = '" . $CheckItemRow['stockid'] . "'";

		$ErrMsg = __('The inventory counts could not be retrieved');
		$Counts = DB_query($SQL, $ErrMsg);

		if ($CheckItemRow['qoh']!=0 OR DB_num_rows($Counts)>0) {
			$YPos -=$LineHeight;
			$FontSize=8;
			if (mb_strlen($CheckItemRow['bin'])>0){
				$pdf->addTextWrap($Left_Margin,$YPos,120,$FontSize,$CheckItemRow['stockid'] . ' - ' . __('Bin') . ':' . $CheckItemRow['bin'], 'left');
			} else {
				$pdf->addTextWrap($Left_Margin,$YPos,120,$FontSize,$CheckItemRow['stockid'], 'left');
			}
			$pdf->addTextWrap(135,$YPos,180,$FontSize,$CheckItemRow['description'], 'left');
			$pdf->addTextWrap(315,$YPos,60,$FontSize,locale_number_format($CheckItemRow['qoh'],$CheckItemRow['decimalplaces']), 'right');
		}

		if (DB_num_rows($Counts)==0 AND $CheckItemRow['qoh']!=0){
			$pdf->addTextWrap(380, $YPos,160, $FontSize, __('No counts entered'), 'left');
			if ($_POST['ZeroCounts']=='Adjust'){
				$pdf->addTextWrap(485, $YPos, 60, $FontSize, locale_number_format(-($CheckItemRow['qoh']),$CheckItemRow['decimalplaces']), 'right');
			}
		} elseif (DB_num_rows($Counts)>0) {
			$TotalCount =0;
			while ($CountRow=DB_fetch_array($Counts)){
				$pdf->addTextWrap(375, $YPos, 60, $FontSize, locale_number_format(($CountRow['qtycounted']),$CheckItemRow['decimalplaces']), 'right');
				$pdf->addTextWrap(440, $YPos, 100, $FontSize, $CountRow['reference'], 'left');
				$TotalCount += $CountRow['qtycounted'];
				$YPos -= $LineHeight;

				if ($YPos < $Bottom_Margin + $LineHeight){
		 			$PageNumber++;
		   			include('includes/PDFStockComparisonPageHeader.php');
				}
			} // end of loop printing count information
			$pdf->addTextWrap($Left_Margin, $YPos, 375-$Left_Margin, $FontSize, __('Total for') . ': ' . $CheckItemRow['stockid'], 'right');
			$pdf->addTextWrap(375, $YPos, 60, $FontSize, locale_number_format($TotalCount,$CheckItemRow['decimalplaces']), 'right');
			$pdf->addTextWrap(485, $YPos, 60, $FontSize, locale_number_format($TotalCount-$CheckItemRow['qoh'],$CheckItemRow['decimalplaces']), 'right');
		} //end of if there are counts to print

		$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

		if ($YPos < $Bottom_Margin + $LineHeight){
		   $PageNumber++;
		   include('includes/PDFStockComparisonPageHeader.php');
		}

	} /*end STOCK comparison while loop */

	$YPos -= (2*$LineHeight);

    $pdf->OutputD($_SESSION['DatabaseName'] . '_StockComparison_' . date('Y-m-d') . '.pdf');
    $pdf->__destruct();

	if ($_POST['ReportOrClose']=='ReportAndClose'){
		//need to print the report first before this but don't risk re-adjusting all the stock!!
		$SQL = "TRUNCATE TABLE stockcheckfreeze";
		$Result = DB_query($SQL);

		$SQL = "TRUNCATE TABLE stockcounts";
		$Result = DB_query($SQL);
	}

} else { /*The option to print PDF was not hit */

	$Title= __('Inventory Comparison Report');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' '
		. $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

    echo '<fieldset>
			<legend>', __('Stock Check Options'), '</legend>';
	echo '<field>
			<label for="ReportOrClose">' . __('Choose Option'). ':</label>
			<select name="ReportOrClose">';

	if ($_POST['ReportOrClose']=='ReportAndClose'){
		echo '<option selected="selected" value="ReportAndClose">' .  __('Report and Close the Inventory Comparison Processing Adjustments As Necessary') . '</option>';
		echo '<option value="ReportOnly">' .  __('Report The Inventory Comparison Differences Only - No Adjustments') . '</option>';
	} else {
		echo '<option selected="selected" value="ReportOnly">' . __('Report The Inventory Comparison Differences Only - No Adjustments') . '</option>';
		echo '<option value="ReportAndClose">' . __('Report and Close the Inventory Comparison Processing Adjustments As Necessary') . '</option>';
	}

	echo '</select>
		</field>';

	echo '<field>
			<label for="ZeroCounts">' .  __('Action for Zero Counts') . ':</label>
			<select name="ZeroCounts">';

	if ($_POST['ZeroCounts'] =='Adjust'){
		echo '<option selected="selected" value="Adjust">' .  __('Adjust System stock to Nil') . '</option>';
		echo '<option value="Leave">' . __('Do not Adjust System stock to Nil') . '</option>';
	} else {
		echo '<option value="Adjust">' .  __('Adjust System stock to Nil') . '</option>';
		echo '<option selected="selected" value="Leave">' . __('Do not Adjust System stock to Nil') . '</option>';
	}

    echo '</select>
		</field>';
	echo '</fieldset>
		<div class="centre"><input type="submit" name="PrintPDF" value="' . __('Print PDF'). '" /></div>';
    echo '</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */
