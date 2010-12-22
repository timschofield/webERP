<?php
/* $Revision: 1.2 $ */

//$PageSecurity = 10;

$Version_adds= "1.2";

include('includes/session.inc');
require_once('includes/DefineLabelClass.php');

$MsgErr=null;
$DecimalPlaces=2;
$pdf= null;

	$AllLabels =				 //!< The variable $AllLabels is the global variable that contains the list
		getXMLFile(LABELS_FILE); //!< of all the label objects defined until now. In case of a fresh
								 //!<  installation or an empty XML labels file it holds a NULL value.

// If there is no label templates, the user could select to set up a new one
if ($AllLabels==null) {
	echo '<br/><br/>';
	abortMsg( _("There isn't any label template to select for printing. Click") .
		' <a href="Labels.php"><b>' . _('HERE'). '</b></a> '. _('to set up a new one') );
}

/**
 *  The option print was selected, first it is checked if there are enough data
 *  to print the labels.
 */
if (isset($_POST['PrintPDF']) OR isset($_POST['PDFTest']) ) {
	if (!isset($_POST['QtyByItem']) OR (int)$_POST['QtyByItem']<1)
		$MsgErr = _('You must specify the number of labels per item required');
	else {
		if (count($_POST['StockID'])<1)
			$MsgErr = _('You must select the items to be printed');
		else {
			//
			$label = $AllLabels->getLabel($_POST['LabelID']);
			list($dimensions, $lines) = resizeLabel($label);
			$formatPage = getPageDimensions($dimensions);
			list($Page_Width,
				   $Page_Height,
				   $Top_Margin,
				   $Bottom_Margin,
				   $Left_Margin,
				   $Right_Margin) = $formatPage;

			// Do it!
			$PaperSize = 'User_special'; // Don't use any of the predefined sizes
			$DocumentPaper='LETTER';
			$DocumentOrientation='P';   // Correccion para la version trunk :(
			include('includes/PDFStarter.php');
			if ($Version>="3.12")
				$pdf->setPageFormat($formatPage);
			$ok = printLabels(
					$dimensions,
					$lines,
					intval($_POST['QtyByItem']),
					$_POST['Currency'],
					$_POST['SalesType'],
					$_POST['StockID']);

			if ($ok)
				exit(); // the print was success
			else	// Has been ocurred an error
				$MsgErr = _('There was an error. Consult your IT staff');
		}
	}
}

/**
 *  There is not activated option print, then show the window for capture the printing
 *  options.
 */

$title = _('Print Price Labels');
include('includes/header.inc');

if ($MsgErr!=null) {
	prnMsg($MsgErr,'warn');
}

showLabelOptions();

include('includes/footer.inc');
exit();

function showLabelOptions() {
	global $AllLabels, $DecimalPlaces, $rootpath, $theme;
	$txt = array(
		_('Label Sticker Printing'),
		_('Select label type'),
		_('Number of labels per item'),
		_('Price list'), _('Currency'),
		_('Category'), _('Update values')
	);
	if (!isset($_POST['LabelID']))
		$_POST['LabelID']=(string)$AllLabels->label[0]->id;
	$OptionLabels = selLabels($_POST['LabelID']);
	if (!isset($_POST['QtyByItem']))
		$_POST['QtyByItem']=1;
	if (!isset($_POST['SalesType']))
		$_POST['SalesType']=$_SESSION['DefaultPriceList'];
	$OptionSales = selSalesType($_POST['SalesType']);

	if (!isset($_POST['Currency'])){
		$_POST['Currency']=$_SESSION['CompanyRecord']['currencydefault'];
	}
	$DecimalPlaces=getDecimalPlaces($_POST['Currency']);

	$OptionCurrency = selCurrency($_POST['Currency']);
	if (!isset($_POST['Category']))
		$_POST['Category']='';
	$OptionsCategory = selCategory($_POST['Category']);

	$TableItems = tableItems($_POST['Category'], $okItems);

	$SendButton = '<br /><div class=centre><input type="submit" name="PrintPDF" value="'. _('Print labels') .'">&nbsp;&nbsp;&nbsp;
		<input type="submit" name="PDFTest" value="'. _('Print labels with borders') .'"></div>';
	$iTxt=0;

	echo '<script type="text/javascript">
	function setAll(all) {
		var x=document.getElementById("form1");
		for (var i=0;i<x.length;i++) {
			if (x.elements[i].id==\'item\');
				x.elements[i].checked=all.checked;
		}
	}
	</script>';

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' .$txt[$iTxt++].'</p>';
	echo '<form name ="form1" action="'.$_SERVER['PHP_SELF'].'" method="POST" id="form1">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class=selection>';
	echo '<tbody>';
	echo '<tr>
				<td class="number">'.$txt[$iTxt++].':</td>
				<td><select name="LabelID">'.
					$OptionLabels
					.'</select></td>
		</tr>';
	echo '<tr>
				<td class="number">'.$txt[$iTxt++].':</td>
				<td><input type="text" class=number name="QtyByItem" value="'.$_POST['QtyByItem'].'" size="2"
					maxlength="4"></td>
		</tr>';
	echo '<tr>
				<td class="number">'.$txt[$iTxt++].':</td>
				<td><select name="SalesType" onChange="ReloadForm(form1.refresh)">
					'.$OptionSales.'
					</select></td>
			</tr>';
	echo '<td class="number">'.$txt[$iTxt++].':</td>
				<td><select name="Currency" onChange="ReloadForm(form1.refresh)">
						'.$OptionCurrency.'
					</select></td>
			</tr>';
	echo '<tr>
				<td class="number">'.$txt[$iTxt++].':</td>
				<td><select name="Category" onChange="ReloadForm(form1.refresh)">
					'.$OptionsCategory.'
					</select> </td>
			</tr>';
	echo '<tr>
				<th colspan="2">
				<input type="submit" name="refresh" value="Refresh options">
				</th>';
	echo '<tr>
				<td colspan="2">
					'.$TableItems.'
				</td>
			</tr>';

	echo '</tbody>
		</table>
		'.$SendButton.'
	</form>';
}

function selLabels($type) {
	global $AllLabels;
	$list=array();

	foreach ($AllLabels->label as $label)
		$list[(string)$label->id] = (string)$label->description;

	return selectOptions($list, $type);
}

function selSalesType($type) {
	return selectTable("SELECT typeabbrev, sales_type FROM salestypes ORDER BY sales_type", $type);
}

function selCurrency($curr) {
	return selectTable("SELECT currabrev, currency FROM currencies", $curr);
}

function selCategory(&$categ) {
	return selectTable("SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription", $categ);
}

function selectTable($sql, &$currentKey) {
	global $db;
	$result = DB_query($sql, $db);
	while ($myrow=DB_fetch_row($result)) {
		if (empty($currentKey))
			$currentKey=$myrow[0];
		$list[$myrow[0]] = $myrow[1];
	}
	DB_free_result($result);
	return selectOptions($list, $currentKey);
}

function selectOptions($list, $currentKey) {
	$html='';
	foreach ($list as $key=>$value) {
		$xs = ($currentKey==$key) ? " selected":"";
		$html .= '
			<option value="'. $key .'"'. $xs .'>'. $value. '</option>';
	}
	return $html;
}

function tableItems($CategoryID, &$ok) {
	global $db, $DecimalPlaces;

	if (empty($CategoryID)) {
		$ok=false;
		return noneButton( _('Select a Category') );
	}
	$result = getStockItems($CategoryID, $_POST['Currency'], $_POST['SalesType']);
	if (!DB_num_rows($result)) {
		$ok=false;
		return noneButton( _('This category has no items to show') );
	}

	$txt=array(
		_('Code'), _('Description'), _('Price').'<br>('.$_POST['Currency'].')',
		_('All')
	);
	$ix=0;
	//  The table's header
	$html= '<table border="0" width="100%">
		<thead>
			<tr>
				<th>'.$txt[$ix++].'</th>
				<th>'.$txt[$ix++].'</th>
				<th>'.$txt[$ix++].'</th>
				<th colspan="2" align="center">'.$txt[$ix++].'
					<input type="checkbox" checked onclick="setAll(this);">
				</th>
			</tr>
		</thead>

		<tbody>';
	$ok=true;
	$odd=true;
	while ($myrow=DB_fetch_array($result)) {
		$price = number_format($myrow['price'],$DecimalPlaces);
		$oddEven=$odd?"Odd":"Even";
		$odd = !$odd;
		$html .= <<<ZZZ
			<tr class="{$oddEven}TableRows">
				<td>{$myrow['stockid']}</td>
				<td>{$myrow['description']}</td>
				<td class="number">{$price}</td>
				<td><div class="centre">
					<INPUT type="checkbox" checked name="StockID[{$myrow['stockid']}]" id="item">
					</div>
				</td>
				<td>&nbsp;&nbsp;&nbsp;</td>
			</tr>
ZZZ;
	}
	return $html . '
		</tbody>
		</table>
	</div>';
}

function noneButton($msg) {
	return '
		<div class="centre">
			<INPUT type="button" disabled name="None" value="'. $msg . '">
		</div>';
}

/**
 *  This access to item data includes its price.
 *  The routine works in two contexts: when only the category is given
 *  it looks for all the items
 */
function getStockItems($CategoryID, $CurrCode, $SalesType, $StockID=false) {
	global $db, $Today;
	if ($StockID!==false) {
		$WhereClause = "stockmaster.stockid='$StockID' LIMIT 1";
	} else {
		$WhereClause = "stockmaster.categoryid='$CategoryID' ORDER BY stockmaster.stockid";
	}

	$sql="SELECT stockmaster.stockid, 
							stockmaster.description, stockmaster.longdescription, stockmaster.barcode, prices.price 
				FROM stockmaster LEFT JOIN prices ON stockmaster.stockid=prices.stockid 
				AND prices.currabrev = '" . $CurrCode . "' 
				AND prices.typeabbrev= '" . $SalesType . "'
				AND prices.startdate >= '" . Date('Y-m-d') . "'
				AND (prices.enddate <= '" . Date('Y-m-d') . "' OR prices.enddate='0000-00-00') 
				AND prices.debtorno=''
				WHERE " . $WhereClause;

// if current prices are those with enddate = 0000-00-00 the following line was wrong			
//			"AND ('$Today' BETWEEN pr.startdate AND prices.enddate) " .
			
			
	return DB_query($sql, $db);
}

function getStockData($StockID, $Currency, $salesType) {
	$result = getStockItems(null, $Currency, $salesType, $StockID);
	return DB_fetch_array($result);
}

/**
 *  Change de scale of data given by points
 *  Returns two array for the dimension and lines of data
 */
function resizeLabel($label) {
	global $DimensionTags, $DataTags;

	//<* Values required from the beggining
	$scales=array('pt'=>1, 'in'=>72, 'mm'=>(float)72/25.4, 'cm'=>(float)72/2.54);

	$obj = $label->dimensions;
	$unit = (string)$obj->Unit;
	if ( array_key_exists($unit , $scales) )
		$factor = $scales[$unit];
	else
		abortMsg( _('Unit not defined in scale operation! Correct the template') );

	$dims = array();
	foreach ($DimensionTags as $iTag=>$tag) {
		if ($tag['type']=='n')   // it is a data numeric
			$dims[$iTag] = round(((float)$obj->$iTag)*$factor, 3);
		elseif ($tag['type']=='i')
			$dims[$iTag] = (int)$obj->$iTag;
	}

	$obj = $label->data;
	$line = array();
	$i=0;
	foreach ($obj->line as $labelLine) {
		$line[$i] = array();
		foreach ($DataTags as $iTag=>$tag) {
			if ($tag['type']=='n')   // it is a data numeric
				$line[$i][$iTag]= round(((float)$labelLine->$iTag)*$factor, 3);
			else
				$line[$i][$iTag]=(string)$labelLine->$iTag;  // field to use in printing data
		}
		$i++;
	}
	return array($dims, $line);
}

/**
 *  Returns the following data:
 *	  $Page_Width,
 *	  $Page_Height,
 *	  $Top_Margin,
 *	  $Bottom_Margin,
 *	  $Left_Margin,
 *	  $Right_Margin
 */
function getPageDimensions($dimensions) {
	$bm =(float)$dimensions['Sh'] - ( (float)$dimensions['Tm'] +
			(int)$dimensions['Rows']*(float)$dimensions['He']);
	$rm =(float)$dimensions['Sw'] - ( (float)$dimensions['Lm'] +
			(int)$dimensions['Cols']*(float)$dimensions['Wi']);
	return array(
		(float)$dimensions['Sw'],
		(float)$dimensions['Sh'],
		(float)$dimensions['Tm'], 0,
//		($bm>0?$bm:0),
		(float)$dimensions['Lm'], 0
//		($rm>0?$rm:0)
	);
}

function printLabels($dimensions, $lines, $qtyByItem, $Currency, $salesType, $StockIDList) {
	global $pdf, $DecimalPlaces, $Version;
	$row = $col = 0;

	$DecimalPlaces=getDecimalPlaces($Currency);

	foreach ($StockIDList as $StockID=>$on) {  // At least there is one item
		$itemData = getStockData($StockID, $Currency, $salesType);
		$num=$qtyByItem;
		while ($num-- > 0) {	// Print $num labels per item
			printStockid($itemData, $dimensions, $lines, $Currency, $row, $col);
			if (++$col>=$dimensions['Cols']) {
				$col=0;
				if (++$row>=$dimensions['Rows']) {
					$row=0;
					$pdf->newpage();
				}
			}
		}
	}
 /*   if ($row OR $col) // it seems to be unnecesary.
		$pdf->newpage();  */

	// now, emit the PDF file (if not errors!)
	if ($Version>="3.12") {
		$pdf->OutputD($_SESSION['DatabaseName'] . '_Labels_' . date('Y-m-d') . '.pdf');//UldisN
		$pdf->__destruct(); //UldisN
	} else {
		$pdfcode = $pdf->output();
		$len = strlen($pdfcode);

		if ($len<=20){
			return false;
		} else{
			header('Content-type: application/pdf');
			header('Content-Length: ' . $len);
			header('Content-Disposition: inline; filename=Labels.pdf');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');

			$pdf->Stream();
		}
	}
	return true;  // All fine!!
}

/*! \brief The heart of the program (perhaps the liver)
 *
 *  It shows the data label from the input $data as update data (id read only)
 *  if the third parameter is true or a fresh data label (new label). It is
 *  possible that the combination $data valid and $readonly false occurs when
 *  invalid data needs to be recaptured because an error in a new label capture.
 */
function printStockid($itemData, $labelDim, $dataParams, $Currency, $row, $col) {
	global $pdf, $DecimalPlaces;
//echo $row.':'.$col.'<br>';
	// Calculate the bottom left corner position
	$iX = $labelDim['Lm'] + $col * $labelDim['Cw'];
	$iY = $labelDim['Sh'] - ($labelDim['Tm'] + ($row+1) * $labelDim['Rh']);

	if (isset($_POST['PDFTest'])) {
		$pdf->line($iX, $iY+$labelDim['He'], $iX+$labelDim['Wi'], $iY+$labelDim['He']); // top
		$pdf->line($iX, $iY, $iX+$labelDim['Wi'], $iY); // bottom
		$pdf->line($iX, $iY, $iX, $iY+$labelDim['He']); // left
		$pdf->line($iX+$labelDim['Wi'], $iY, $iX+$labelDim['Wi'], $iY+$labelDim['He']);
	}
	// Now, for every data, write down the correspondig text
	$descrip= $ldescrip='';
	foreach ($dataParams as $line) {
		unset($resid);  // unlink the previous residue
		unset($txt);
		$adj='left';
		switch ($line['dat']) {
		case 'code':
			$txt = $itemData['stockid'];
			break;
		case 'name1':
			$txt = $itemData['description'];
			$resid = &$descrip;
			break;
		case 'name2':
			$txt = $descrip;
			unset($descrip);
			break;
		case 'lname1':
			$txt = $itemData['longdescription'];
			$resid = &$ldescrip;
			break;
		case 'lname2':
			$txt = $ldescrip;
			unset($ldescrip);
			break;
		case 'price':
			$txt = number_format($itemData['price'], $DecimalPlaces). ' '. $Currency;
//			$adj='left';
			break;
		case 'bcode': break;
		}
		$ix = $iX + $line['pos'];
		$iy = $iY + $line['row'];
		if (isset($txt)) {
			$resid = $pdf->addTextWrap($ix,$iy,$line['max'],$line['font'],$txt, $adj);
		}
	}
}

function getDecimalPlaces($Currency) {
	global $db;
	$sql="SELECT decimalplaces FROM currencies WHERE currabrev='$Currency'";
	$result = DB_query($sql, $db);
	if (!DB_num_rows($result))
		abortMsg(_('Couldnt get the currency data'));
	$myrow=DB_fetch_row($result);
	return $myrow[0];
}
?>
