<?php

$PageSecurity = 2;
if (! isset($_POST['ReportOrClose'])){
	$title="Inventory Comparison Comparison Report";
}

If (isset($_POST['PrintPDF']) AND isset($_POST['ReportOrClose'])){

	include("config.php");
	include("includes/ConnectDB.inc");
	include("includes/PDFStarter_ros.inc");
	include("includes/SQL_CommonFunctions.inc");
	include("includes/DateFunctions.inc");

/*First off do the Inventory Comparison file stuff */
	if ($_POST['ReportOrClose']=='ReportAndClose'){

		$sql = "SELECT StockCheckFreeze.StockID,StockCheckFreeze.LocCode, QOH, MaterialCost+LabourCost+OverheadCost AS StandardCost FROM StockMaster INNER JOIN StockCheckFreeze ON StockCheckFreeze.StockID=StockMaster.StockID ORDER BY StockCheckFreeze.LocCode, StockCheckFreeze.StockID";

		$StockChecks = DB_query($sql, $db);
		if (DB_error_no($db) !=0) {
			$title = "Stock Freeze - Problem Report.... ";
			include("includes/header.inc");
			echo "<BR>The inventory check file could not be retreived because - " . DB_error_msg($db);
			echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>Back to the menu</A>";
			if ($debug==1){
	      			echo "<BR>$sql";
			}
			echo "</body</html>";
			include("includes/footer.inc");
			exit;
		}

		$PeriodNo = GetPeriod (Date($DefaultDateFormat), $db);
		$SQLAdjustmentDate = FormatDateForSQL(Date($DefaultDateFormat));
		$CompanyRecord = ReadInCompanyRecord($db);
		$AdjustmentNumber = GetNextTransNo(17,$db);

		while ($myrow = DB_fetch_array($StockChecks)){

			$sql = "SELECT SUM(QtyCounted) AS TotCounted, COUNT(StockID) AS NoOfCounts FROM StockCounts WHERE StockID='" . $myrow['StockID'] . "' AND LocCode='" . $myrow['LocCode'] . "'";

			$StockCounts = DB_query($sql, $db);
			if (DB_error_no($db) !=0) {
				$title = "Stock Count Comparison - Problem Report.... ";
				include("includes/header.inc");
				echo "<BR>The inventory counts file could not be retreived because - " . DB_error_msg($db);
				echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>Back to the menu</A>";
				if ($debug==1){
					echo "<BR>$sql";
				}
				echo "</body</html>";
				include("includes/footer.inc");
				exit;
			}

			$StkCountResult = DB_query($sql,$db);
			$StkCountRow = DB_fetch_array($StkCountResult);

			$StockQtyDifference = $StkCountRow["TotCounted"] - $myrow["QOH"];

			if ($_POST['ZeroCounts']=='Leave' AND $StkCountRow['NoOfCounts']==0){
				$StockQtyDifference =0;
			}

			if ($StockQtyDifference !=0){ // only adjust stock if there is an adjustment to make!!

				$SQL = "BEGIN";
				$Result = DB_query($SQL,$db);

				// Need to get the current location quantity will need it later for the stock movement
				$SQL="SELECT LocStock.Quantity FROM LocStock WHERE LocStock.StockID='" . $myrow['StockID'] . "' AND LocCode= '" . $myrow['LocCode'] . "'";
				$Result = DB_query($SQL, $db);
				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					// There must actually be some error this should never happen
					$QtyOnHandPrior = 0;
				}

				$SQL = "INSERT INTO StockMoves (StockID, Type, TransNo, LocCode, TranDate, Prd, Reference, Qty, NewQOH) VALUES ('" . $myrow['StockID'] . "', 17, " . $AdjustmentNumber . ", '" . $myrow['LocCode'] . "','" . $SQLAdjustmentDate . "'," . $PeriodNo . ", 'Inventory Check', " . $StockQtyDifference . ", " . ($QtyOnHandPrior + $StockQtyDifference) . ")";

				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The stock movement record cannot be inserted because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to insert the stock movement record was used:<BR>$SQL<BR>";
					}
					$SQL = "Rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}


				$SQL = "UPDATE LocStock SET Quantity = Quantity + " . $StockQtyDifference . " WHERE StockID='" . $myrow['StockID'] . "' AND LocCode='" . $myrow['LocCode'] . "'";
				$Result = DB_query($SQL,$db);
				if (DB_error_no($db) !=0){
					echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The location stock record could not be updated because: -<BR>" . DB_error_msg($db);
					if ($debug==1){
						echo "<BR>The following SQL to update the stock record was used:<BR>$SQL<BR>";
					}
					$SQL = "Rollback";
					$Result = DB_query($SQL,$db);
					exit;
				}

				if ($CompanyRecord["GLLink_Stock"]==1 AND $myrow['StandardCost'] > 0){

					$StockGLCodes = GetStockGLCode($myrow['StockID'],$db);

					$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Amount, Narrative) VALUES (17," .$AdjustmentNumber . ", '" . $SQLAdjustmentDate . "', " . $PeriodNo . ", " .  $StockGLCodes['AdjGLAct'] . ", " . $myrow['StandardCost'] * -($StockQtyDifference) . ", '" . $myrow['StockID'] . " x " . $StockQtyDifference . " @ " . $myrow['StandardCost'] . " - Inventory Check')";
					$Result = DB_query($SQL,$db);
					if (DB_error_no($db) !=0){
						echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction entries could not be added because: -<BR>" . DB_error_msg($db);
						if ($debug==1){
							echo "<BR>The following SQL to insert the GL entries was used:<BR>$SQL<BR>";
						}
						$SQL = "Rollback";
						$Result = DB_query($SQL,$db);
						exit;
					}

					$SQL = "INSERT INTO GLTrans (Type, TypeNo, TranDate, PeriodNo, Account, Amount, Narrative) VALUES (17," .$AdjustmentNumber . ", '" . $SQLAdjustmentDate . "', " . $PeriodNo . ", " .  $StockGLCodes['StockAct'] . ", " . $myrow['StandardCost'] * $StockQtyDifference . ", '" . $myrow['StockID'] . " x " . $StockQtyDifference . " @ " . $myrow['StandardCost'] . " - Inventory Check')";
					$Result = DB_query($SQL,$db);
					if (DB_error_no($db) !=0){
						echo "<BR>CRITICAL ERROR! NOTE DOWN THIS ERROR AND SEEK ASSISTANCE: The general ledger transaction entries could not be added because: -<BR>" . DB_error_msg($db);
						if ($debug==1){
							echo "<BR>The following SQL to insert the GL entries was used:<BR>$SQL<BR>";
						}
						$SQL = "Rollback";
						$Result = DB_query($SQL,$db);
						exit;
					}

				}


				$SQL = "Commit";
				$Result = DB_query($SQL,$db);

			} // end if $StockQtyDifference !=0

		} // end loop round all the checked parts
	} // end user wanted to close the inventory check file and do the adjustments

	// now do the report

	$sql = "SELECT StockCheckFreeze.StockID, Description, StockMaster.CategoryID, StockCategory.CategoryDescription, StockCheckFreeze.LocCode, Locations.LocationName, StockCheckFreeze.QOH FROM StockCheckFreeze INNER JOIN StockMaster ON StockCheckFreeze.StockID=StockMaster.StockID INNER JOIN Locations ON StockCheckFreeze.LocCode=Locations.LocCode INNER JOIN StockCategory ON StockMaster.CategoryID=StockCategory.CategoryID ORDER BY StockCheckFreeze.LocCode, StockMaster.CategoryID, StockCheckFreeze.StockID";

	$CheckedItems = DB_query($sql,$db);

	if (DB_error_no($db) !=0){
		include("includes/header.inc");
		echo "<BR>The Inventory Comparison data could not be retrieved because: -<BR>" . DB_error_msg($db);
		if ($debug==1){
			echo "<BR>The following SQL to retrieve the Inventory Comparison data was used:<BR>$SQL<BR>";
		}
		include("includes/footer.inc");
		exit;
	}

	if (DB_num_rows($CheckedItems)==0){
		include("includes/header.inc");
		echo "<P>There is no inventory check data to report on. To start an inventory check first run the <a href='" . $rootpath . "/StockCheck.php?" . SID . "'>inventory check sheets</A> - and select the option to create new Inventory Comparison data file";
		include("includes/footer.inc");
		exit;
	}

	$pdf->addinfo('Title',"Check Comparison Report");
	$pdf->addinfo('Subject',"Inventory Check Comparision  " . Date($DefaultDateFormat));


	$PageNumber=1;
	$line_height=15;

	include ("includes/PDFStockComparisonPageHeader.inc");

	$Location = "";
	$Category = "";

	While ($CheckItemRow = DB_fetch_array($CheckedItems,$db)){

		if ($Location!=$CheckItemRow["LocCode"]){
			$FontSize=14;
			if ($Location!=""){ /*Then it's NOT the first time round */
				/*draw a line under the Location*/
				$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
				$YPos -=$line_height;
			}

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$CheckItemRow["LocCode"] . " - " . $CheckItemRow["LocationName"], "left");
			$Location = $CheckItemRow["LocCode"];
			$YPos -=$line_height;
		}


		if ($Category!=$CheckItemRow["CategoryID"]){
			$FontSize=12;
			if ($Category!=""){ /*Then it's NOT the first time round */
				/*draw a line under the CATEGORY TOTAL*/
				$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
				$YPos -=$line_height;
			}

			$LeftOvers = $pdf->addTextWrap($Left_Margin+15,$YPos,260-$Left_Margin,$FontSize,$CheckItemRow["CategoryID"] . " - " . $CheckItemRow["CategoryDescription"], "left");
			$Category = $CheckItemRow["CategoryID"];
			$YPos -=$line_height;
		}

		$YPos -=$line_height;
		$FontSize=8;

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,120,$FontSize,$CheckItemRow["StockID"], "left");
		$LeftOvers = $pdf->addTextWrap(135,$YPos,180,$FontSize,$CheckItemRow["Description"], "left");
		$LeftOvers = $pdf->addTextWrap(315,$YPos,60,$FontSize,$CheckItemRow["QOH"], "right");


		$SQL = "SELECT QtyCounted, Reference FROM StockCounts WHERE LocCode ='" . $Location . "' AND StockID = '" . $CheckItemRow["StockID"] . "'";

		$Counts = DB_query($SQL,$db);

		if (DB_error_no($db) !=0) {
	 		$title = "Inventory Comparison - Problem Report.... ";
	  		include("includes/header.inc");
	   		echo "<BR>The inventory counts could not be retrieved by the SQL because - " . DB_error_msg($db);
	   		echo "<BR><A HREF='" .$rootpath ."/index.php?" . SID . "'>Back to the menu</A>";
	   		if ($debug==1){
	      			echo "<BR>$SQL";
	   		}
	   		include("includes/footer.inc");
	   		exit;
		}


		if (DB_num_rows($Counts)==0){
			$LeftOvers = $pdf->addTextWrap(380, $YPos,160, $FontSize, "No counts entered", "left");
			if ($_POST['ZeroCounts']=='Adjust'){
				$LeftOvers = $pdf->addTextWrap(485, $YPos, 60, $FontSize, -($CheckItemRow['QOH']), "right");
			}
		} else {
			$TotalCount =0;
			while ($CountRow=DB_fetch_array($Counts,$db)){

				$LeftOvers = $pdf->addTextWrap(375, $YPos, 60, $FontSize, ($CountRow['QtyCounted']), "right");
				$LeftOvers = $pdf->addTextWrap(440, $YPos, 100, $FontSize, $CountRow["Reference"], "left");
				$TotalCount += $CountRow['QtyCounted'];
				$YPos -= $line_height;

				if ($YPos < $Bottom_Margin + $line_height){
		 			$PageNumber++;
		   			include("includes/PDFStockComparisonPageHeader.inc");
				}
			} // end of loop printing count information
			$LeftOvers = $pdf->addTextWrap($LeftMargin, $YPos, 375-$LeftMargin, $FontSize, "Total for: " . $CheckItemRow['StockID'], "right");
			$LeftOvers = $pdf->addTextWrap(375, $YPos, 60, $FontSize, $TotalCount, "right");
			$LeftOvers = $pdf->addTextWrap(485, $YPos, 60, $FontSize, $TotalCount-$CheckItemRow['QOH'], "right");
		} //end of if there are counts to print

		$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

		if ($YPos < $Bottom_Margin + $line_height){
		   $PageNumber++;
		   include("includes/PDFStockComparisonPageHeader.inc");
		}

	} /*end STOCK comparison while loop */

	$YPos -= (2*$line_height);

 	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

      if ($len<=20){
		$title = "Print Stock check comparison Error";
		include("includes/header.inc");
		echo "<p>There were no Inventory Comparison sheets to print out";
		echo "<BR><A HREF='$rootpath/index.php?" . SID . "'>Back to the menu</A>";
		include("includes/footer.inc");
		exit;
      } else {
		header("Content-type: application/pdf");
		header("Content-Length: " . $len);
		header("Content-Disposition: inline; filename=StockComparison.pdf");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Pragma: public");

		$pdf->Stream();

	}

	if ($_POST['ReportOrClose']=='ReportAndClose'){
		//need to print the report first before this but don't risk re-adjusting all the stock!!
		$sql = "TRUNCATE TABLE StockCheckFreeze";
		$result = DB_query($sql,$db);

		$sql = "TRUNCATE TABLE StockCounts";
		$result = DB_query($sql,$db);
	}



} else { /*The option to print PDF was not hit */

	include("includes/session.inc");
	include("includes/header.inc");

	echo "<FORM ACTION=" . $_SERVER['PHP_SELF'] . " METHOD='POST'><CENTER><TABLE>";

	echo "<TR><TD>Choose Option:</FONT></TD><TD><SELECT name='ReportOrClose'>";

	if ($_POST['ReportOrClose']=="ReportAndClose"){
		echo "<OPTION SELECTED VALUE='ReportAndClose'>Report and Close the Inventory Comparison Processing Adjustments As Necessary";
		echo "<OPTION VALUE='ReportOnly'>Report The Inventory Comparison Differences Only - No Adjustments";
	} else {
		echo "<OPTION SELECTED VALUE='ReportOnly'>Report The Inventory Comparison Differences Only - No Adjustments";
		echo "<OPTION VALUE='ReportAndClose'>Report and Close the Inventory Comparison Processing Adjustments As Necessary";
	}

	echo "</SELECT></TD></TR>";


	echo "<TR><TD>Action for Zero Counts:</TD><TD><SELECT name='ZeroCounts'>";

	if ($_POST['ZeroCounts'] =="Adjust"){
		echo "<OPTION SELECTED VALUE='Adjust'>Adjust System stock to Nil";
		echo "<OPTION VALUE='Leave'>Don't Adjust System stock to Nil";
	} else {
		echo "<OPTION VALUE='Adjust'>Adjust System stock to Nil";
		echo "<OPTION SELECTED VALUE='Leave'>Don't Adjust System stock to Nil";
	}

	echo "</TABLE><INPUT TYPE=Submit Name='PrintPDF' Value='Print PDF'></CENTER>";

	echo "</body></html>";

} /*end of else not PrintPDF */

?>
