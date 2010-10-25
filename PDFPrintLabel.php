<?php
/* $Revision: 1.2 $ */

$PageSecurity = 10;

$Version_adds= "1.2";

include('includes/session.inc');
require_once("includes/DefineLabelClass.php");

$msgErr=null;
$decimalplaces=2;
$today = date('Y-m-d');
$pdf= null;

	$allLabels =				 //!< The variable $allLabels is the global variable that contains the list
		getXMLFile(LABELS_FILE); //!< of all the label objects defined until now. In case of a fresh
								 //!<  installation or an empty XML labels file it holds a NULL value.

// If there is no label templates, the user could select to set up a new one
if ($allLabels==null) {
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
		$msgErr = _('You must specify the number of labels per item required');
	else {
		if (count($_POST['StockID'])<1)
			$msgErr = _('You must select the items to be printed');
		else {
			//
			$label = $allLabels->getLabel($_POST['labelID']);
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
				$msgErr = _('There were an error. Consult your IT staff');
		}
	}
}

/**
 *  There is not activated option print, then show the window for capture the printing
 *  options.
 */

$title = _('Print Price Labels');
include('includes/header.inc');

if ($msgErr!=null)
	prnMsg($msgErr,'warn');

showLabelOptions();

include('includes/footer.inc');
exit();

function showLabelOptions() {
	global $allLabels, $decimalplaces, $rootpath, $theme;
	$txt = array(
		_('Label Sticker Printing'),
		_('Select label type'),
		_('Number of labels per item'),
		_('Price list'), _('Currency'),
		_('Category'), _('Update values')
	);
	if (!isset($_POST['labelID']))
		$_POST['labelID']=(string)$allLabels->label[0]->id;
	$optionLabels = selLabels($_POST['labelID']);
	if (!isset($_POST['QtyByItem']))
		$_POST['QtyByItem']=1;
	if (!isset($_POST['SalesType']))
		$_POST['SalesType']=$_SESSION['DefaultPriceList'];
	$optionSales = selSalesType($_POST['SalesType']);

	if (!isset($_POST['Currency']))
		$_POST['Currency']=$_SESSION['CompanyRecord']['currencydefault'];
	$decimalplaces=getDecimalPlaces($_POST['Currency']);

	$optionCurrency = selCurrency($_POST['Currency']);
	if (!isset($_POST['Category']))
		$_POST['Category']='';
	$optionsCategory = selCategory($_POST['Category']);

	$tableItems = tableItems($_POST['Category'], $okItems);

	$sendButton = '<br /><div class=centre><input type="submit" name="PrintPDF" value="'. _('Print labels') .'">&nbsp;&nbsp;&nbsp;
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

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' .$txt[$iTxt++].'</p>';
	echo '<form name ="form1" action="'.$_SERVER['PHP_SELF'].'" method="POST" id="form1">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class=selection>';
	echo '<tbody>';
	echo '<tr>
				<td class="number">'.$txt[$iTxt++].':</td>
				<td><select name="labelID">'.
					$optionLabels
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
					'.$optionSales.'
					</select></td>
			</tr>';
	echo '<td class="number">'.$txt[$iTxt++].':</td>
				<td><select name="Currency" onChange="ReloadForm(form1.refresh)">
						'.$optionCurrency.'
					</select></td>
			</tr>';
	echo '<tr>
				<td class="number">'.$txt[$iTxt++].':</td>
				<td><select name="Category" onChange="ReloadForm(form1.refresh)">
					'.$optionsCategory.'
					</select> </td>
			</tr>';
	echo '<tr>
				<th colspan="2">
				<input type="submit" name="refresh" value="Refresh options">
				</th>';
	echo '<tr>
				<td colspan="2">
					'.$tableItems.'
				</td>
			</tr>';

	echo '</tbody>
		</table>
		'.$sendButton.'
	</form>';
}

function selLabels($type) {
	global $allLabels;
	$list=array();

	foreach ($allLabels->label as $label)
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

function tableItems($category, &$ok) {
	global $db, $decimalplaces;

	if (empty($category)) {
		$ok=false;
		return noneButton( _('Select a Category') );
	}
	$result = getStockItems($category, $_POST['Currency'], $_POST['SalesType']);
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
		$price = number_format($myrow['price'],$decimalplaces);
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
function getStockItems($category, $currAbrev, $typeSales, $stockID=false) {
	global $db, $today;
	if ($stockID!==false) {
		$wS= "sm.stockid='$stockID' LIMIT 1";
	} else {
		$wS= "sm.categoryid='$category' ORDER BY sm.stockid";
	}

	$sql="SELECT sm.stockid, sm.description, sm.longdescription, sm.barcode, pr.price ".
			"FROM stockmaster AS sm LEFT JOIN prices AS pr ON sm.stockid=pr.stockid ".
			"AND pr.currabrev = '$currAbrev' " .
			"AND pr.typeabbrev= '$typeSales' " .
			"AND ('$today' BETWEEN pr.startdate AND pr.enddate) " .
			"AND pr.debtorno='' " .
			"WHERE $wS";

	return DB_query($sql, $db);
}

function getStockData($stockID, $currency, $salesType) {
	$result = getStockItems(null, $currency, $salesType, $stockID);
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

function printLabels($dimensions, $lines, $qtyByItem, $currency, $salesType, $stockIDList) {
	global $pdf, $decimalplaces, $Version;
	$row = $col = 0;

	$decimalplaces=getDecimalPlaces($currency);

	foreach ($stockIDList as $stockID=>$on) {  // At least there is one item
		$itemData = getStockData($stockID, $currency, $salesType);
		$num=$qtyByItem;
		while ($num-- > 0) {	// Print $num labels per item
			printStockid($itemData, $dimensions, $lines, $currency, $row, $col);
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
function printStockid($itemData, $labelDim, $dataParams, $currency, $row, $col) {
	global $pdf, $decimalplaces;
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
			$txt = number_format($itemData['price'], $decimalplaces). ' '. $currency;
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

function getDecimalPlaces($currency) {
	global $db;
	$sql="SELECT decimalplaces FROM currencies WHERE currabrev='$currency'";
	$result = DB_query($sql, $db);
	if (!DB_num_rows($result))
		abortMsg(_('Couldnt get the currency data'));
	$myrow=DB_fetch_row($result);
	return $myrow[0];
}
?>
