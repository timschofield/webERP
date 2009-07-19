<?php
/* $Revision: 1.15 $ */
$PageSecurity = 2;
include('includes/session.inc');

If (isset($_POST['PrintPDF']) AND isset($_POST['ReportOrClose'])){

	include('includes/PDFStarter.php');
	include('includes/SQL_CommonFunctions.inc');


/*First off do the Inventory Comparison file stuff */
	if ($_POST['ReportOrClose']=='ReportAndClose'){

		$sql = "SELECT stockcheckfreeze.stockid,
				stockcheckfreeze.loccode,
				qoh,
				materialcost+labourcost+overheadcost AS standardcost
			FROM stockmaster INNER JOIN stockcheckfreeze
				ON stockcheckfreeze.stockid=stockmaster.stockid
			ORDER BY stockcheckfreeze.loccode, stockcheckfreeze.stockid";

		$StockChecks = DB_query($sql, $db,'','',false,false);
		if (DB_error_no($db) !=0) {
			$title = _('Stock Freeze') . ' - ' . _('Problem Report') . '....';
			include('includes/header.inc');
			echo '<br>';
			prnMsg( _('The inventory check file could not be retrieved because'). ' - ' . DB_error_msg($db),'error');
			echo '<br><a href="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu').'</a>';
			if ($debug==1){
	      			echo '<br>' . $sql;
			}
			include('includes/footer.inc');
			exit;
		}

		$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
		$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
		$AdjustmentNumber = GetNextTransNo(17,$db);

		while ($myrow = DB_fetch_array($StockChecks)){

			$sql = "SELECT SUM(stockcounts.qtycounted) AS totcounted,
					COUNT(stockcounts.stockid) AS noofcounts
				FROM stockcounts
				WHERE stockcounts.stockid='" . $myrow['stockid'] . "'
				AND stockcounts.loccode='" . $myrow['loccode'] . "'";

			$StockCounts = DB_query($sql, $db);
			if (DB_error_no($db) !=0) {
				$title = _('Stock Count Comparison') . ' - ' . _('Problem Report') . '....';
				include('includes/header.inc');
				echo '<br>';
				prnMsg( _('The inventory counts file could not be retrieved because'). ' - ' . DB_error_msg($db). 'error');
				echo '<br><a href="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu').'</a>';
				if ($debug==1){
					echo '<br>'. $sql;
				}
				include('includes/footer.inc');
				exit;
			}

			$StkCountResult = DB_query($sql,$db);
			$StkCountRow = DB_fetch_array($StkCountResult);

			$StockQtyDifference = $StkCountRow['totcounted'] - $myrow['qoh'];

			if ($_POST['ZeroCounts']=='Leave' AND $StkCountRow['noofcounts']==0){
				$StockQtyDifference =0;
			}

			if ($StockQtyDifference !=0){ // only adjust stock if there is an adjustment to make!!

				$SQL = 'BEGIN';
				$Result = DB_query($SQL,$db);

				// Need to get the current location quantity will need it later for the stock movement
				$SQL="SELECT locstock.quantity
						FROM locstock
					WHERE locstock.stockid='" . $myrow['stockid'] . "'
					AND loccode= '" . $myrow['loccode'] . "'";

				$Result = DB_query($SQL, $db);
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
								prd,
								reference,
								qty,
								newqoh)
						VALUES ('" . $myrow['stockid'] . "',
							17,
							" . $AdjustmentNumber . ",
							'" . $myrow['loccode'] . "',
							'" . $SQLAdjustmentDate . "',
							" . $PeriodNo . ",
							'" . _('Inventory Check') . "',
							" . $StockQtyDifference . ",
							" . ($QtyOnHandPrior + $StockQtyDifference) . "
						)";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

				$SQL = "UPDATE locstock
						SET quantity = quantity + " . $StockQtyDifference . "
						WHERE stockid='" . $myrow['stockid'] . "'
						AND loccode='" . $myrow['loccode'] . "'";
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the stock record was used');
				$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

				if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $myrow['standardcost'] > 0){

					$StockGLCodes = GetStockGLCode($myrow['stockid'],$db);
					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
					$DbgMsg = _('The following SQL to insert the GL entries was used');

					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									amount,
									narrative)
							VALUES (17,
								" .$AdjustmentNumber . ",
								'" . $SQLAdjustmentDate . "',
								" . $PeriodNo . ",
								" .  $StockGLCodes['adjglact'] . ",
								" . $myrow['standardcost'] * -($StockQtyDifference) . ",
								'" . $myrow['stockid'] . " x " . $StockQtyDifference . " @ " . $myrow['standardcost'] . " - " . _('Inventory Check') . "')";
					$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The general ledger transaction entries could not be added because');
					$DbgMsg = _('The following SQL to insert the GL entries was used');

					$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									amount,
									narrative)
							VALUES (17,
								" .$AdjustmentNumber . ",
								'" . $SQLAdjustmentDate . "',
								" . $PeriodNo . ",
								" .  $StockGLCodes['stockact'] . ",
								" . $myrow['standardcost'] * $StockQtyDifference . ", '" . $myrow['stockid'] . " x " . $StockQtyDifference . " @ " . $myrow['standardcost'] . " - " . _('Inventory Check') . "')";
					$Result = DB_query($SQL,$db, $ErrMsg, $DbgMsg, true);

				} //END INSERT GL TRANS
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Unable to COMMIT transaction while adjusting stock in StockCheckAdjustmet report');
				$SQL = "COMMIT";
				$Result = DB_query($SQL,$db, $ErrMsg,'',true);

			} // end if $StockQtyDifference !=0

		} // end loop round all the checked parts
	} // end user wanted to close the inventory check file and do the adjustments

	// now do the report
	$ErrMsg = _('The Inventory Comparison data could not be retrieved because');
	$DbgMsg = _('The following SQL to retrieve the Inventory Comparison data was used');
	$sql = "SELECT stockcheckfreeze.stockid,
			description,
			stockmaster.categoryid,
			stockcategory.categorydescription,
			stockcheckfreeze.loccode,
			locations.locationname,
			stockcheckfreeze.qoh
			FROM stockcheckfreeze INNER JOIN stockmaster
				ON stockcheckfreeze.stockid=stockmaster.stockid
			INNER JOIN locations
				ON stockcheckfreeze.loccode=locations.loccode
			INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
			ORDER BY stockcheckfreeze.loccode,
				stockmaster.categoryid,
				stockcheckfreeze.stockid";

	$CheckedItems = DB_query($sql,$db, $ErrMsg, $DbgMsg);

	if (DB_num_rows($CheckedItems)==0){
		include('includes/header.inc');
		echo '<p>';
		prnMsg(_('There is no inventory check data to report on'), 'warn');
		echo '<p>'. _('To start an inventory check first run the'). ' <a href="' . $rootpath . '/StockCheck.php?' . SID . '">'. _('inventory check sheets') . '</a> - '. _('and select the option to create new Inventory Comparison data file');
		include('includes/footer.inc');
		exit;
	}

	$pdf->addinfo('Title', _('Check Comparison Report') );
	$pdf->addinfo('Subject', _('Inventory Check Comparison'). ' ' . Date($_SESSION['DefaultDateFormat']));


	$PageNumber=1;
	$line_height=15;

	include ('includes/PDFStockComparisonPageHeader.inc');

	$Location = '';
	$Category = '';

	While ($CheckItemRow = DB_fetch_array($CheckedItems,$db)){

		if ($Location!=$CheckItemRow['loccode']){
			$FontSize=14;
			if ($Location!=''){ /*Then it's NOT the first time round */
				/*draw a line under the Location*/
				$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
				$YPos -=$line_height;
			}

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$CheckItemRow['loccode'] . ' - ' . $CheckItemRow['locationname'], 'left');
			$Location = $CheckItemRow['loccode'];
			$YPos -=$line_height;
		}


		if ($Category!=$CheckItemRow['categoryid']){
			$FontSize=12;
			if ($Category!=''){ /*Then it's NOT the first time round */
				/*draw a line under the CATEGORY TOTAL*/
				$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
				$YPos -=$line_height;
			}

			$LeftOvers = $pdf->addTextWrap($Left_Margin+15,$YPos,260-$Left_Margin,$FontSize,$CheckItemRow['categoryid'] . ' - ' . $CheckItemRow['categorydescription'], 'left');
			$Category = $CheckItemRow['categoryid'];
			$YPos -=$line_height;
		}

		$YPos -=$line_height;
		$FontSize=8;

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,120,$FontSize,$CheckItemRow['stockid'], 'left');
		$LeftOvers = $pdf->addTextWrap(135,$YPos,180,$FontSize,$CheckItemRow['description'], 'left');
		$LeftOvers = $pdf->addTextWrap(315,$YPos,60,$FontSize,$CheckItemRow['qoh'], 'right');

		$SQL = "SELECT qtycounted, reference FROM stockcounts WHERE loccode ='" . $Location . "' AND stockid = '" . $CheckItemRow['stockid'] . "'";

		$Counts = DB_query($SQL,$db,'','',false,false);

		if (DB_error_no($db) !=0) {
	 		$title = _('Inventory Comparison') . ' - ' . _('Problem Report') . '.... ';
	  		include('includes/header.inc');
	   		echo '<br>';
			prnMsg( _('The inventory counts could not be retrieved by the SQL because').' - ' . DB_error_msg($db), 'error');
	   		echo '<br><a href="' .$rootpath .'/index.php?' . SID . '">'. _('Back to the menu'). '</a>';
	   		if ($debug==1){
	      			echo '<br>'. $SQL;
	   		}
	   		include('includes/footer.inc');
	   		exit;
		}


		if (DB_num_rows($Counts)==0){
			$LeftOvers = $pdf->addTextWrap(380, $YPos,160, $FontSize, _('No counts entered'), 'left');
			if ($_POST['ZeroCounts']=='Adjust'){
				$LeftOvers = $pdf->addTextWrap(485, $YPos, 60, $FontSize, -($CheckItemRow['qoh']), 'right');
			}
		} else {
			$TotalCount =0;
			while ($CountRow=DB_fetch_array($Counts,$db)){

				$LeftOvers = $pdf->addTextWrap(375, $YPos, 60, $FontSize, ($CountRow['qtycounted']), 'right');
				$LeftOvers = $pdf->addTextWrap(440, $YPos, 100, $FontSize, $CountRow['reference'], 'left');
				$TotalCount += $CountRow['qtycounted'];
				$YPos -= $line_height;

				if ($YPos < $Bottom_Margin + $line_height){
		 			$PageNumber++;
		   			include('includes/PDFStockComparisonPageHeader.inc');
				}
			} // end of loop printing count information
			$LeftOvers = $pdf->addTextWrap($LeftMargin, $YPos, 375-$LeftMargin, $FontSize, _('Total for') . ': ' . $CheckItemRow['stockid'], 'right');
			$LeftOvers = $pdf->addTextWrap(375, $YPos, 60, $FontSize, $TotalCount, 'right');
			$LeftOvers = $pdf->addTextWrap(485, $YPos, 60, $FontSize, $TotalCount-$CheckItemRow['qoh'], 'right');
		} //end of if there are counts to print

		$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

		if ($YPos < $Bottom_Margin + $line_height){
		   $PageNumber++;
		   include('includes/PDFStockComparisonPageHeader.inc');
		}

	} /*end STOCK comparison while loop */

	$YPos -= (2*$line_height);

 	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

      if ($len<=20){
		$title = _('Print Stock check comparison Error');
		include('includes/header.inc');
		echo '<p>';
		prnMsg( _('There were no Inventory Comparison sheets to print out'), 'error');
		echo '<br><a href="' . $rootpath . '/index.php?' . SID . '">' . _('Back to the menu') . '</a>';
		include('includes/footer.inc');
		exit;
      } else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=StockComparison.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();

	}

	if ($_POST['ReportOrClose']=='ReportAndClose'){
		//need to print the report first before this but don't risk re-adjusting all the stock!!
		$sql = 'TRUNCATE TABLE stockcheckfreeze';
		$result = DB_query($sql,$db);

		$sql = 'TRUNCATE TABLE stockcounts';
		$result = DB_query($sql,$db);
	}



} else { /*The option to print PDF was not hit */

	$title= _('Inventory Comparison Comparison Report');
	include('includes/header.inc');

	echo '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST"><table>';

	echo '<tr><td>' . _('Choose Option'). ':</font></td><td><select name="ReportOrClose">';

	if ($_POST['ReportOrClose']=='ReportAndClose'){
		echo '<option selected VALUE="ReportAndClose">'. _('Report and Close the Inventory Comparison Processing Adjustments As Necessary');
		echo '<option VALUE="ReportOnly">'. _('Report The Inventory Comparison Differences Only - No Adjustments');
	} else {
		echo '<option selected VALUE="ReportOnly">' . _('Report The Inventory Comparison Differences Only - No Adjustments');
		echo '<option VALUE="ReportAndClose">' . _('Report and Close the Inventory Comparison Processing Adjustments As Necessary');
	}

	echo '</select></td></tr>';


	echo '<tr><td>'. _('Action for Zero Counts') . ':</td><td><select name="ZeroCounts">';

	if ($_POST['ZeroCounts'] =='Adjust'){
		echo '<option selected VALUE="Adjust">'. _('Adjust System stock to Nil');
		echo '<option VALUE="Leave">' . _("Don't Adjust System stock to Nil");
	} else {
		echo '<option VALUE="Adjust">'. _('Adjust System stock to Nil');
		echo '<option selected VALUE="Leave">' . _("Don't Adjust System stock to Nil");
	}

	echo '</table><div class="centre"><input type=Submit Name="PrintPDF" Value="' . _('Print PDF'). '"></div>';

	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>