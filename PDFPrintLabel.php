<?php
/* $Id: PDFPriceLabels.php 5228 2012-04-06 02:48:00Z vvs2012 $*/

include('includes/session.inc');

$PtsPerMM = 2.83465; //pdf points per mm


if ((isset($_POST['ShowLabels']) OR isset($_POST['SelectAll']))
	AND isset($_POST['FromCriteria'])
	AND mb_strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND mb_strlen($_POST['ToCriteria'])>=1){

	$title = _('Print Labels');
	include('includes/header.inc');
		
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
		prnMsg( _('The Price Labels could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br /><a href="' .$rootpath .'/index.php">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			prnMsg(_('For debugging purposes the SQL used was:') . $SQL,'error');
		}
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($LabelsResult)==0){
		prnMsg(_('There were no price labels to print out for the category specified'),'warn');
		echo '<br /><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">'. _('Back').'</a>';
		include('includes/footer.inc');
		exit;
	}

	
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
			<tr>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Price') . '</th>
				<th>' . _('Print') . ' ?</th>
			</tr>
			<tr><th colspan="4"><input type="submit" name="SelectAll" value="' . _('Select All Labels') . '" /><input type="checkbox" name="CheckAll" ';
	if (isset($_POST['CheckAll'])){
		echo 'checked="checked" ';
	}
	echo 'onchange="ReloadForm(SelectAll)" /></td>
		</tr>';
	
	$i=0;
	while ($LabelRow = DB_fetch_array($LabelsResult)){
		echo '<tr>
				<td>' . $LabelRow['stockid'] . '</td>
				<td>' . $LabelRow['description'] . '</td>
				<td class="number">' . locale_number_format($LabelRow['price'],$LabelRow['decimalplaces']) . '</td>
				<td>';
		if (isset($_POST['SelectAll']) AND isset($_POST['CheckAll'])) {
			echo '<input type="checkbox" checked="checked" name="PrintLabel' . $i .'" />';
		} else {
			echo '<input type="checkbox" name="PrintLabel' . $i .'" />';
		}
		echo '</td>
			</tr>';
		echo '<input type="hidden" name="StockID' . $i . '" value="' . $LabelRow['stockid'] . '" />
			<input type="hidden" name="Description' . $i . '" value="' . $LabelRow['description'] . '" />
			<input type="hidden" name="Barcode' . $i . '" value="' . $LabelRow['barcode'] . '" />
			<input type="hidden" name="Price' . $i . '" value="' . locale_number_format($LabelRow['price'],$LabelRow['decimalplaces']) . '" />';
		$i++;
	}
	$i--;
	echo '</table>
		<input type="hidden" name="NoOfLabels" value="' . $i . '" />
		<input type="hidden" name="LabelID" value="' . $_POST['LabelID'] . '" />
		<input type="hidden" name="FromCriteria" value="' . $_POST['FromCriteria'] . '" />
		<input type="hidden" name="ToCriteria" value="' . $_POST['ToCriteria'] . '" />
		<input type="hidden" name="SalesType" value="' . $_POST['SalesType'] . '" /> 
		<input type="hidden" name="Currency" value="' . $_POST['Currency'] . '" />
		<input type="hidden" name="EffectiveDate" value="' . $_POST['EffectiveDate'] . '" /> 
		<br />
		<div class="centre">
			
			<input type="submit" name="PrintLabels" value="'. _('Print Labels'). '" />
		</div>
		<br />
			<div class="centre">
				<a href="'. $rootpath . '/Labels.php">' . _('Label Template Maintenance'). '</a>
			</div>
		</form>';
	exit;
}
if (isset($_POST['PrintLabels']) 
	AND isset($_POST['NoOfLabels'])
	AND $_POST['NoOfLabels']>0){ 
	$NoOfLabels = 0;
	for ($i=0;$i < $_POST['NoOfLabels'];$i++){
		if (isset($_POST['PrintLabel'.$i])){
			$NoOfLabels++;
		}
	}
	if ($NoOfLabels ==0){
		prnMsg(_('There are no labels selected to print'),'info');
	}
}
if (isset($_POST['PrintLabels']) AND $NoOfLabels>0) {

	$result = DB_query("SELECT 	description,
								pagewidth*" . $PtsPerMM . " as page_width,
								pageheight*" . $PtsPerMM . " as page_height,
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
	
	$PaperSize = 'Custom'; // so PDF starter wont default the DocumentPaper
	$DocumentPaper = array($LabelDimensions['page_width'],$LabelDimensions['page_height']); 
	include('includes/PDFStarter.php');
	$Top_Margin = $LabelDimensions['label_topmargin'];
	$Left_Margin = $LabelDimensions['label_leftmargin'];
	$Page_Height = $LabelDimensions['page_height'];
	$Page_Width = $LabelDimensions['page_width'];
	$Right_Margin =0;
	$Bottom_Margin =0;

	$pdf->addInfo('Title', $LabelDimensions['description'] . ' ' . _('Price Labels') );
	$pdf->addInfo('Subject', $LabelDimensions['description'] . ' ' . _('Price Labels') );
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	
	$style = array(
	    'position' => '',
	    'align' => 'C',
	    'stretch' => false,
	    'fitwidth' => true,
	    'cellfitalign' => '',
	    'border' => true,
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
	
	for ($i=0;$i < $_POST['NoOfLabels'];$i++){
		if (isset($_POST['PrintLabel'.$i])){
			$NoOfLabels--;
			foreach ($LabelFields as $Field){
				//print_r($Field);

				if ($Field['FieldValue']== 'price'){
					$Value = $_POST['Price' . $i];
				} elseif ($Field['FieldValue']== 'stockid'){
					$Value = $_POST['StockID' . $i];
				} elseif ($Field['FieldValue']== 'description'){
					$Value = $_POST['Description' . $i];
				} elseif ($Field['FieldValue']== 'barcode'){
					$Value = $_POST['Barcode' . $i];
				}
				if ($Field['FieldValue'] == 'price'){ //need to format for the number of decimal places
					$LeftOvers = $pdf->addTextWrap($XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos'],$LabelDimensions['label_width']-$Field['HPos'],$Field['FontSize'],$_POST['Price' . $i],'center');
				} elseif($Field['Barcode']==1) {

					/* write1DBarcode($code, $type, $x='', $y='', $w='', $h='', $xres='', $style='', $align='') 
					 * Note that the YPos for this function is based on the opposite origin for the Y axis i.e from the bottom not from the top!
					 */
					//$BarcodeFileName = $_SERVER['DOCUMENT_ROOT'] . $rootpath . '/' . $_SESSION['reports_dir'] .'/barcode_'  . $i . '.jpg';
					//Barcode39(str_replace('_', $Value),$BarcodeFileName, $LabelDimensions['label_width']-$Field['HPos']-$Left_Margin,$Field['FontSize']);
					//$pdf->addJpegFromFile($BarcodeFileName, $XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos']);
					//$pdf->Image('@' . , $XPos+$Field['HPos'], $Page_Height-($YPos+$LabelDimensions['label_height']-$Field['VPos']), $LabelDimensions['label_width']-$Field['HPos']-$Left_Margin, $Field['FontSize']); 
					
					$pdf->write1DBarcode(str_replace('_','',$Value), 'C39E',$XPos+$Field['HPos'],$Page_Height - $YPos+$LabelDimensions['label_height']-$Field['VPos']-$Field['FontSize'],$LabelDimensions['label_width']-$Field['HPos'], 40, 1, $style, 'N');
				} else {
					$LeftOvers = $pdf->addTextWrap($XPos+$Field['HPos'],$YPos-$LabelDimensions['label_height']+$Field['VPos'],$LabelDimensions['label_width']-$Field['HPos']-20,$Field['FontSize'],$Value);
				}
			} // end loop through label fields
			if ($NoOfLabels>0) {
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
			}//end if there is another label to print 
		} //this label is set to print
	} //loop through labels selected to print

	
	$FileName=$_SESSION['DatabaseName']. '_' . _('Price_Labels') . '_' . date('Y-m-d').'.pdf';
	ob_clean();
	$pdf->OutputI($FileName);
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
					<input type="submit" name="ShowLabels" value="'. _('Show Labels'). '" />
				</div>
				<br />
				<div class="centre">
					<a href="'. $rootpath . '/Labels.php">' . _('Label Template Maintenance'). '</a>
				</div>
				</form>';
				
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

function Barcode39 ($barcode, $FileName='',$width=160, $height=80, $text='') {

	/* Generate a Code 3 of 9 barcode */

	$im = ImageCreate ($width, $height)	
	or die ("Cannot Initialize new GD image stream");
	$White = ImageColorAllocate ($im, 255, 255, 255);
	$Black = ImageColorAllocate ($im, 0, 0, 0);
	//ImageColorTransparent ($im, $White);
	ImageInterLace ($im, 1);

	$NarrowRatio = 20;
	$WideRatio = 55;
	$QuietRatio = 35;

	$nChars = (strlen($barcode)+2) * ((6 * $NarrowRatio) + (3 * $WideRatio) + ($QuietRatio));
	$Pixels = $width / $nChars;
	$NarrowBar = (int)(20 * $Pixels);
	$WideBar = (int)(55 * $Pixels);
	$QuietBar = (int)(35 * $Pixels);

	$ActualWidth = (($NarrowBar * 6) + ($WideBar*3) + $QuietBar) * (strlen ($barcode)+2);

	if (($NarrowBar == 0) || ($NarrowBar == $WideBar) || ($NarrowBar == $QuietBar) || ($WideBar == 0) || ($WideBar == $QuietBar) || ($QuietBar == 0)) {
			ImageString ($im, 1, 0, 0, "Image is too small!", $Black);
			ImageJPEG ($im, $FileName, 100);
			exit;
	}

	$CurrentBarX = (int)(($width - $ActualWidth) / 2);
	$Color = $White;
	$BarcodeFull = "*".strtoupper ($barcode)."*";
	settype ($BarcodeFull, "string");

	$FontNum = 3;
	$FontHeight = ImageFontHeight ($FontNum);
	$FontWidth = ImageFontWidth ($FontNum);
	if ($text != 0) {
			$CenterLoc = (int)(($width-1) / 2) - (int)(($FontWidth * strlen($BarcodeFull)) / 2);
			ImageString ($im, $FontNum, $CenterLoc, $height-$FontHeight, "$BarcodeFull", $Black);
	} else {
		$FontHeight=-2;
	}


	for ($i=0; $i<strlen($BarcodeFull); $i++) {
			$StripeCode = Code39 ($BarcodeFull[$i]);

			for ($n=0; $n < 9; $n++)  {
					if ($Color == $White){
					   $Color = $Black;
					} else {
						 $Color = $White;
					}

					switch ($StripeCode[$n]) {
							case '0':
									ImageFilledRectangle ($im, $CurrentBarX, 0, $CurrentBarX+$NarrowBar, $height-1-$FontHeight-2, $Color);
									$CurrentBarX += $NarrowBar;
									break;

							case '1':
									ImageFilledRectangle ($im, $CurrentBarX, 0, $CurrentBarX+$WideBar, $height-1-$FontHeight-2, $Color);
									$CurrentBarX += $WideBar;
									break;
					}
			}

			$Color = $White;
			ImageFilledRectangle ($im, $CurrentBarX, 0, $CurrentBarX+$QuietBar, $height-1-$FontHeight-2, $Color);
			$CurrentBarX += $QuietBar;
	} //end loop around each character in barcode string

	imagejpeg ($im, $FileName, 100);
}//end Barcode39

//-----------------------------------------------------------------------------
// Returns the Code 3 of 9 value for a given ASCII character
//-----------------------------------------------------------------------------
function Code39 ($Asc) {
        switch ($Asc)  {
                case ' ':
                        return "011000100";
                case '$':
                        return "010101000";
                case '%':
                        return "000101010";
                case '*':
                        return "010010100"; // * Start/Stop
                case '+':
                        return "010001010";
                case '|':
                        return "010000101";
                case '.':
                        return "110000100";
                case '/':
                        return "010100010";
				case '-':
						return "010000101";
                case '0':
                        return "000110100"; 
                case '1':
                        return "100100001"; 
                case '2':
                        return "001100001"; 
                case '3':
                        return "101100000"; 
                case '4':
                        return "000110001"; 
                case '5':
                        return "100110000"; 
                case '6':
                        return "001110000"; 
                case '7':
                        return "000100101"; 
                case '8':
                        return "100100100"; 
                case '9':
                        return "001100100"; 
                case 'A':
                        return "100001001"; 
                case 'B':
                        return "001001001"; 
                case 'C':
                        return "101001000";
                case 'D':
                        return "000011001";
                case 'E':
                        return "100011000";
                case 'F':
                        return "001011000";
                case 'G':
                        return "000001101";
                case 'H':
                        return "100001100";
                case 'I':
                        return "001001100";
                case 'J':
                        return "000011100";
                case 'K':
                        return "100000011";
                case 'L':
                        return "001000011";
                case 'M':
                        return "101000010";
                case 'N':
                        return "000010011";
                case 'O':
                        return "100010010";
                case 'P':
                        return "001010010";
                case 'Q':
                        return "000000111";
                case 'R':
                        return "100000110";
                case 'S':
                        return "001000110";
                case 'T':
                        return "000010110";
                case 'U':
                        return "110000001";
                case 'V':
                        return "011000001";
                case 'W':
                        return "111000000";
                case 'X':
                        return "010010001";
                case 'Y':
                        return "110010000";
                case 'Z':
                        return "011010000";
                default:
                        return "011000100"; 
        }
}
?>