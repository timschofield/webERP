<?php
/* $Id: PDFPriceLabels.php 5228 2012-04-06 02:48:00Z vvs2012 $*/

include('includes/session.inc');

$PtsPerMM = 2.83465; //pdf millimetres to points

if (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND mb_strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND mb_strlen($_POST['ToCriteria'])>=1){
	
	$result = DB_query("SELECT papersize,
								description,
								width*" . $PtsPerMM . " as label_width,
								height*" . $PtsPerMM . " as label_height,
								rowheight*" . $PtsPerMM . " as label_rowheight,
								columnwidth*" . $PtsPerMM . " as label_columnwidth,
								topmargin*" . $PtsPerMM . " as label_topmargin,
								leftmargin*" . $PtsPerMM . " as label_leftmargin
						FROM labels
						WHERE labelid='" . $_POST['LabelID'] . "'",
						$db);
	$LabelDimensions = DB_fetch_array($result);
	$PaperSize = $LabelDimensions['papersize'];
	include('includes/PDFStarter.php');
	$Top_Margin = $LabelDimensions['label_topmargin'];
	$Left_Margin = $LabelDimensions['label_leftmargin'];

	$pdf->addInfo('Title', $LabelDimensions['description'] . ' ' . _('Price Labels') );
	$pdf->addInfo('Subject', $LabelDimensions['description'] . ' ' . _('Price Labels') );

	$result = DB_query("SELECT fieldvalue,
								vpos,
								hpos,
								fontsize,
								barcode
						FROM labelfields
						WHERE labelid = '" . $_POST['LabelID'] . "'",
						$db);
	$LabelFields = array();
	$i=0;
	while ($LabelFieldRow = DB_fetch_array($result)){
		if ($LabelFieldRow['fieldvalue'] == 'itemcode'){
			$LabelFields[$i]['FieldValue'] = 'stockid';
		} elseif ($LabelFieldRow['fieldvalue'] == 'itemdescription'){
			$LabelFields[$i]['FieldValue'] = 'description';
		} else {
			$LabelFields[$i]['FieldValue'] = $LabelFieldRow['fieldvalue'];
		}
		$LabelFields[$i]['VPos'] = $LabelFieldRow['vpos']*$PtsPerMM;
		$LabelFields[$i]['HPos'] = $LabelFieldRow['hpos']*$PtsPerMM;
		$LabelFields[$i]['FontSize'] = $LabelFieldRow['fontsize'];
		$LabelFields[$i]['Barcode'] = $LabelFieldRow['barcode'];
		$i++;
	}
	
	$SQL = "SELECT prices.stockid,
					stockmaster.description,
					stockmaster.barcode,
					prices.price,
					currencies.decimalplaces
			FROM stockmaster INNER JOIN	stockcategory
   			     ON stockmaster.categoryid=stockcategory.categoryid
			INNER JOIN prices
				ON stockmaster.stockid=prices.stockid
			INNER JOIN currencies
				ON prices.currabrev=currencies.currabrev
			WHERE stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "'
			AND stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "'
			AND prices.typeabbrev='" . $_POST['SalesType'] . "' 
			AND prices.currabrev='" . $_POST['Currency'] . "'
			AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
			AND (prices.enddate='0000-00-00' OR prices.enddate>'" . FormatDateForSQL($_POST['EffectiveDate']) . "')
			AND prices.debtorno=''
			ORDER BY prices.currabrev,
				stockmaster.categoryid,
				stockmaster.stockid,
				prices.startdate";
	
	$LabelsResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Price Label') . ' - ' . _('Problem Report....');
		include('includes/header.inc');
		prnMsg( _('The Price Labels could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			prnMsg(_('For debugging purposes the SQL used was:') . $SQL,'error');
		}
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($LabelsResult)==0){
		$title = _('Print Price Labels Error');
		include('includes/header.inc');
		prnMsg(_('There were no price labels to print out for the category specified'),'warn');
		echo '<br /><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">'. _('Back').'</a>';
		include('includes/footer.inc');
		exit;
	}

	$style = array(
	    'position' => '',
	    'align' => 'C',
	    'stretch' => false,
	    'fitwidth' => true,
	    'cellfitalign' => '',
	    'border' => false,
	    'hpadding' => 'auto',
	    'vpadding' => 'auto',
	    'fgcolor' => array(0,0,0),
	    'bgcolor' => false, //array(255,255,255),
	    'text' => false,
	    'font' => 'helvetica',
	    'fontsize' => 8,
	    'stretchtext' => 4 );

	$PageNumber=1;
	//go down first then accross
	$YPos = $Page_Height - $Top_Margin; //top of current label
	$XPos = $Left_Margin; // left of current label

	while ($PriceLabels = DB_fetch_array($LabelsResult,$db)){
		foreach ($LabelFields as $Field){
			if ($Field['FieldValue'] == 'price'){ //need to format for the number of decimal places
				$LeftOvers = $pdf->addTextWrap($XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos'],$LabelDimensions['label_width']-$Field['HPos'],$Field['FontSize'],locale_number_format($PriceLabels['price'],$PriceLabels['decimalplaces']),'right');
			} elseif($Field['Barcode']==1) {
				
				/* write1DBarcode($code, $type, $x='', $y='', $w='', $h='', $xres='', $style='', $align='') 
				 * Note that the YPos for this function is based on the opposite origin for the Y axis i.e from the bottom not from the top!
				 */
				
				$pdf->write1DBarcode(str_replace('_','',$PriceLabels[$Field['FieldValue']]), 'C128',$XPos+$Field['HPos'],$Page_Height - $YPos+$LabelDimensions['label_height']-$Field['VPos']-$Field['FontSize'],$LabelDimensions['label_width']-$Field['HPos'], $Field['FontSize'], 0.4, $style, 'N');
			} else {
				$LeftOvers = $pdf->addTextWrap($XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos'],$LabelDimensions['label_width']-$Field['HPos'],$Field['FontSize'],$PriceLabels[$Field['FieldValue']]);
			}
		} // end loop through label fields
		//setup $YPos and $XPos for the next label
		if (($YPos - $LabelDimensions['label_rowheight']) < $LabelDimensions['label_height']){
			/* not enough space below the above label to print a new label
			 * so the above was the last label in the column 
			 * need to start either a new column or new page
			 */
			if (($Page_Width - $XPos - $LabelDimensions['label_columnwidth']) < $LabelDimensions['label_width']) {
				/* Not enough space to start a new column so we are into a new page
				 */
				$pdf->newPage();
				$PageNumber++;
				$YPos = $Page_Height - $Top_Margin; //top of next label
				$XPos = $Left_Margin; // left of next label
			} else {
				/* There is enough space for another column */
				$YPos = $Page_Height - $Top_Margin; //back to the top of next label column
				$XPos += $LabelDimensions['label_columnwidth']; // left of next label
			}
		} else {
			/* There is space below to print a label
			 */
			$YPos -= $LabelDimensions['label_rowheight']; //Top of next label
		} 
	}


	$FileName=$_SESSION['DatabaseName']. '_' . _('Price_Labels') . '_' . date('Y-m-d').'.pdf';
	ob_clean();
	$pdf->OutputD($FileName);
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$title= _('Price Labels');
	include('includes/header.inc');

	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' . _('Price Labels') . '" alt="" />
         ' . ' ' . _('Print Price Labels') . '</p>';

	if (!isset($_POST['FromCriteria']) OR !isset($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table class="selection">';
		echo '<tr>
				<td>' . _('Label to print') . ':</td>
				<td><select name="LabelID">';
		
		$LabelResult = DB_query("SELECT labelid, description FROM labels",$db);
		while ($LabelRow = DB_fetch_array($LabelResult)){
			echo '<option value="' . $LabelRow['labelid'] . '">' . $LabelRow['description'] . '</option>';
		}
		echo '</select></td>
			</tr>
			<tr>
				<td>'. _('From Inventory Category Code') .':</td>
				<td><select name="FromCriteria">';

		$CatResult= DB_query("SELECT categoryid, categorydescription FROM stockcategory ORDER BY categoryid",$db);
		while ($myrow = DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'] . '</option>';
		}
		echo '</select></td></tr>';

		echo '<tr><td>' . _('To Inventory Category Code'). ':</td>
                  <td><select name="ToCriteria">';

		/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		While ($myrow = DB_fetch_array($CatResult)){
			echo '<option value="' . $myrow['categoryid'] . '">' . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'] . '</option>';
		}
		echo '</select></td></tr>';

		echo '<tr><td>' . _('For Sales Type/Price List').':</td>
                  <td><select name="SalesType">';
		$sql = "SELECT sales_type, typeabbrev FROM salestypes";
		$SalesTypesResult=DB_query($sql,$db);

		while ($myrow=DB_fetch_array($SalesTypesResult)){
			if ($_SESSION['DefaultPriceList']==$myrow['typeabbrev']){
				echo '<option selected="selected" value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
			} else {
				echo '<option value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
			}
		}
		echo '</select></td></tr>';

		echo '<tr><td>' . _('For Currency').':</td>
                  <td><select name="Currency">';
		$sql = "SELECT currabrev, country, currency FROM currencies";
		$CurrenciesResult=DB_query($sql,$db);

		while ($myrow=DB_fetch_array($CurrenciesResult)){
			if ($_SESSION['CompanyRecord']['currencydefault']==$myrow['currabrev']){
				echo '<option selected="selected" value="' . $myrow['currabrev'] . '">' . $myrow['country'] . ' - ' .$myrow['currency'] . '</option>';
			} else {
				echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['country'] . ' - ' .$myrow['currency'] . '</option>';
			}
		}
		echo '</select></td></tr>';

		echo '<tr><td>' . _('Effective As At') . ':</td>';
        echo '<td><input type="text" size="11" class="date"	alt="' . $_SESSION['DefaultDateFormat'] . '" name="EffectiveDate" value="' . Date($_SESSION['DefaultDateFormat']) . '" />';
        echo '</td></tr>';

		echo '</table>
				<br />
				<div class="centre">
					<input type="submit" name="PrintPDF" value="'. _('Print PDF'). '" />
				</div>
			</form>';
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>
