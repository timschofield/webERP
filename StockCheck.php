<?php


include('includes/session.php');

if (isset($_POST['PrintPDF'])){

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Stock Count Sheets'));
	$pdf->addInfo('Subject',_('Stock Count Sheets'));
	$FontSize=10;
	$PageNumber=1;
	$LineHeight=30;

/*First off do the stock check file stuff */
	if ($_POST['MakeStkChkData']=='New'){
		$SQL = "TRUNCATE TABLE stockcheckfreeze";
		$Result = DB_query($SQL);
		$SQL = "INSERT INTO stockcheckfreeze (stockid,
										  loccode,
										  qoh,
										  stockcheckdate)
					   SELECT locstock.stockid,
							  locstock.loccode,
							  locstock.quantity,
							  '" . Date('Y-m-d') . "'
					   FROM locstock,
							stockmaster
					   WHERE locstock.stockid=stockmaster.stockid
					   AND locstock.loccode='" . $_POST['Location'] . "'
					   AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
					   AND stockmaster.mbflag!='A'
					   AND stockmaster.mbflag!='K'
					   AND stockmaster.mbflag!='D'";

		$Result = DB_query($SQL,'','',false,false);
		if (DB_error_no() !=0) {
			$Title = _('Stock Count Sheets - Problem Report');
			include('includes/header.php');
			prnMsg(_('The inventory quantities could not be added to the freeze file because') . ' ' . DB_error_msg(),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($Debug==1){
		  			echo '<br />' . $SQL;
			}
			include('includes/footer.php');
			exit();
		}
	}

	if ($_POST['MakeStkChkData']=='AddUpdate'){
		$SQL = "DELETE stockcheckfreeze
				FROM stockcheckfreeze
				INNER JOIN stockmaster ON stockcheckfreeze.stockid=stockmaster.stockid
				WHERE stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				AND stockcheckfreeze.loccode='" . $_POST['Location'] . "'";

		$Result = DB_query($SQL,'','',false,false);
		if (DB_error_no() !=0) {
			$Title = _('Stock Freeze') . ' - ' . _('Problem Report') . '.... ';
			include('includes/header.php');
			prnMsg(_('The old quantities could not be deleted from the freeze file because') . ' ' . DB_error_msg(),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($Debug==1){
		  			echo '<br />' . $SQL;
			}
			include('includes/footer.php');
			exit();
		}

		$SQL = "INSERT INTO stockcheckfreeze (stockid,
										  loccode,
										  qoh,
										  stockcheckdate)
				SELECT locstock.stockid,
					loccode ,
					locstock.quantity,
					'" . Date('Y-m-d') . "'
				FROM locstock INNER JOIN stockmaster
				ON locstock.stockid=stockmaster.stockid
				WHERE locstock.loccode='" . $_POST['Location'] . "'
				AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				AND stockmaster.mbflag!='A'
				AND stockmaster.mbflag!='K'
				AND stockmaster.mbflag!='G'
				AND stockmaster.mbflag!='D'";

		$Result = DB_query($SQL,'','',false,false);
		if (DB_error_no() !=0) {
			$Title = _('Stock Freeze - Problem Report');
			include('includes/header.php');
			prnMsg(_('The inventory quantities could not be added to the freeze file because') . ' ' . DB_error_msg(),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($Debug==1){
		  			echo '<br />' . $SQL;
			}
			include('includes/footer.php');
			exit();
		} else {
			$Title = _('Stock Check Freeze Update');
			include('includes/header.php');
			echo '<p><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Print Check Sheets') . '</a>';
			prnMsg( _('Added to the stock check file successfully'),'success');
			include('includes/footer.php');
			exit();
		}
	}


	$SQL = "SELECT stockmaster.categoryid,
				 stockcheckfreeze.stockid,
				 stockmaster.description,
				 stockmaster.decimalplaces,
				 stockcategory.categorydescription,
				 stockcheckfreeze.qoh
			 FROM stockcheckfreeze INNER JOIN stockmaster
			 ON stockcheckfreeze.stockid=stockmaster.stockid
			 INNER JOIN stockcategory
			 ON stockmaster.categoryid=stockcategory.categoryid
			 WHERE stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
			 AND (stockmaster.mbflag='B' OR mbflag='M')
			 AND stockcheckfreeze.loccode = '" . $_POST['Location'] . "'";
	if (isset($_POST['NonZerosOnly']) and $_POST['NonZerosOnly']==true){
		$SQL .= " AND stockcheckfreeze.qoh<>0";
	}

	$SQL .=  " ORDER BY stockmaster.categoryid, stockmaster.stockid";

	$InventoryResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		$Title = _('Stock Sheets') . ' - ' . _('Problem Report') . '.... ';
		include('includes/header.php');
		prnMsg( _('The inventory quantities could not be retrieved by the SQL because') . ' ' . DB_error_msg(),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug==1){
		  	echo '<br />' . $SQL;
		}
		include('includes/footer.php');
		exit();
	}
	if (DB_num_rows($InventoryResult) ==0) {
		$Title = _('Stock Count Sheets - Problem Report');
		include('includes/header.php');
		prnMsg(_('Before stock count sheets can be printed, a copy of the stock quantities needs to be taken - the stock check freeze. Make a stock check data file first'),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit();
	}

	include('includes/PDFStockCheckPageHeader.php');

	$Category = '';

	while ($InventoryCheckRow = DB_fetch_array($InventoryResult)){

		if ($Category!=$InventoryCheckRow['categoryid']){
			$FontSize=12;
			if ($Category!=''){ /*Then it's NOT the first time round */
				/*draw a line under the CATEGORY TOTAL*/
				$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
				$YPos -=(2*$LineHeight);
			}

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$InventoryCheckRow['categoryid'] . ' - ' . $InventoryCheckRow['categorydescription'], 'left');
			$Category = $InventoryCheckRow['categoryid'];
		}

		$FontSize=10;
		$YPos -=$LineHeight;

		if (isset($_POST['ShowInfo']) and $_POST['ShowInfo']==true){

			$DemandQty = GetDemand($InventoryCheckRow['stockid'], $_POST['Location']);

			$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize,locale_number_format($InventoryCheckRow['qoh'], $InventoryCheckRow['decimalplaces']), 'right');
			$LeftOvers = $pdf->addTextWrap(410,$YPos,60,$FontSize,locale_number_format($DemandQty,$InventoryCheckRow['decimalplaces']), 'right');
			$LeftOvers = $pdf->addTextWrap(470,$YPos,60,$FontSize,locale_number_format($InventoryCheckRow['qoh']-$DemandQty,$InventoryCheckRow['decimalplaces']), 'right');

		}

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,$InventoryCheckRow['stockid'], 'left');

		$LeftOvers = $pdf->addTextWrap(150,$YPos,200,$FontSize,$InventoryCheckRow['description'], 'left');


		$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

		if ($YPos < $Bottom_Margin + $LineHeight){
			$PageNumber++;
			include('includes/PDFStockCheckPageHeader.php');
		}

	} /*end STOCK SHEETS while loop */

	$pdf->OutputD($_SESSION['DatabaseName'] . '_Stock_Count_Sheets_' . Date('Y-m-d') .'.pdf');

} else { /*The option to print PDF was not hit */

	$Title=_('Stock Check Sheets');
	$ViewTopic = 'Inventory';
	$BookMark = '';
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="'. _('print') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', _('Select Items For Stock Check'), '</legend>
			<field>
				<label for="Categories">' . _('Select Inventory Categories') . ':</label>
				<select autofocus="autofocus" required="required" minlength="1" name="Categories[]" multiple="multiple">';
	$SQL = 'SELECT categoryid, categorydescription
			FROM stockcategory
			ORDER BY categorydescription';
	$CatResult = DB_query($SQL);
	while ($MyRow = DB_fetch_array($CatResult)) {
		if (isset($_POST['Categories']) AND in_array($MyRow['categoryid'], $_POST['Categories'])) {
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] .'</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="Location">' . _('For Inventory in Location') . ':</label>
			<select name="Location">';
	$SQL = "SELECT locations.loccode, locationname FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			ORDER BY locationname";
	$LocnResult=DB_query($SQL);

	while ($MyRow=DB_fetch_array($LocnResult)){
			  echo '<option value="' . $MyRow['loccode'] . '">' . $MyRow['locationname'] . '</option>';
		}
	echo '</select>
		</field>';

	echo '<field>
			<label for="MakeStkChkData">' . _('Action for Stock Check Freeze') . ':</label>
			<select name="MakeStkChkData">';

	if (!isset($_POST['MakeStkChkData'])){
		$_POST['MakeStkChkData'] = 'PrintOnly';
	}
	if ($_POST['MakeStkChkData'] =='New'){
		echo '<option selected="selected" value="New">' . _('Make new stock check data file') . '</option>';
	} else {
		echo '<option value="New">' . _('Make new stock check data file') . '</option>';
	}
	if ($_POST['MakeStkChkData'] =='AddUpdate'){
		echo '<option selected="selected" value="AddUpdate">' . _('Add/update existing stock check file') . '</option>';
	} else {
		echo '<option value="AddUpdate">' . _('Add/update existing stock check file') . '</option>';
	}
	if ($_POST['MakeStkChkData'] =='PrintOnly'){
		echo '<option selected="selected" value="PrintOnly">' . _('Print Stock Check Sheets Only') . '</option>';
	} else {
		echo '<option value="PrintOnly">' . _('Print Stock Check Sheets Only') . '</option>';
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="ShowInfo">' . _('Show system quantity on sheets') . ':</label>';

	if (isset($_POST['ShowInfo']) and $_POST['ShowInfo'] == false){
			echo '<input type="checkbox" name="ShowInfo" value="false" />';
	} else {
			echo '<input type="checkbox" name="ShowInfo" value="true" />';
	}
	echo '</field>';

	echo '<field>
			<label for="NonZerosOnly">' . _('Only print items with non zero quantities') . ':</label>';
	if (isset($_POST['NonZerosOnly']) and $_POST['NonZerosOnly'] == false){
			echo '<input type="checkbox" name="NonZerosOnly" value="false" />';
	} else {
			echo '<input type="checkbox" name="NonZerosOnly" value="true" />';
	}

	echo '</field>';

	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print and Process') . '" />
		</div>
	</form>';

	include('includes/footer.php');

} /*end of else not PrintPDF */
