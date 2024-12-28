<?php
/*  */

include('includes/session.php');

if (isset($_GET['SelectedSupplier'])) {
	$_POST['supplierid']=$_GET['SelectedSupplier'];
}

if (isset($_POST['PrintPDF']) OR isset($_POST['View'])) {

	include('includes/PDFStarter.php');

	$FontSize=9;
	$PDF->addInfo('Title',_('Supplier Price List'));
	$PDF->addInfo('Subject',_('Price List of goods from a Supplier'));

	$PageNumber=1;
	$LineHeight=12;

	//get supplier
	$SQLsup = "SELECT suppname,
					  currcode,
					  decimalplaces AS currdecimalplaces
				FROM suppliers INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
				WHERE supplierid='" . $_POST['supplier'] . "'";
	$Resultsup = DB_query($SQLsup);
	$RowSup = DB_fetch_array($Resultsup);
	$SupplierName=$RowSup['suppname'];
	$CurrCode =$RowSup['currcode'];
	$CurrDecimalPlaces=$RowSup['currdecimalplaces'];

	//get category
	if ($_POST['category']!='all'){
		$SQLcat="SELECT categorydescription
				FROM `stockcategory`
				WHERE categoryid ='" . $_POST['category'] . "'";

		$Resultcat = DB_query($SQLcat);
		$RowCat = DB_fetch_row($Resultcat);
		$Categoryname=$RowCat['0'];
	} else {
		$Categoryname='ALL';
	}


	//get date price
	if ($_POST['price']=='all'){
		$CurrentOrAllPrices=_('All Prices');
	} else {
		$CurrentOrAllPrices=_('Current Price');
	}

	//price and category = all
	if (($_POST['price']=='all') AND ($_POST['category']=='all')){
		$SQL = "SELECT 	purchdata.stockid,
					stockmaster.description,
					purchdata.price,
					purchdata.conversionfactor,
					(purchdata.effectivefrom)as dateprice,
					purchdata.supplierdescription,
					purchdata.suppliers_partno
				FROM purchdata,stockmaster
				WHERE supplierno='" . $_POST['supplier'] . "'
				AND stockmaster.stockid=purchdata.stockid
				ORDER BY stockid ASC ,dateprice DESC";
	} else {
	//category=all and price != all
		if (($_POST['price']!='all') AND ($_POST['category']=='all')){

			$SQL = "SELECT purchdata.stockid,
							stockmaster.description,
							(SELECT purchdata.price
							 FROM purchdata
							 WHERE purchdata.stockid = stockmaster.stockid
							 ORDER BY effectivefrom DESC
							 LIMIT 0,1) AS price,
							purchdata.conversionfactor,
							(SELECT purchdata.effectivefrom
							 FROM purchdata
							 WHERE purchdata.stockid = stockmaster.stockid
							 ORDER BY effectivefrom DESC
							 LIMIT 0,1) AS dateprice,
							purchdata.supplierdescription,
							purchdata.suppliers_partno
					FROM purchdata, stockmaster
					WHERE supplierno = '" . $_POST['supplier'] . "'
					AND stockmaster.stockid = purchdata.stockid
					GROUP BY stockid
					ORDER BY stockid ASC , dateprice DESC";
		} else {
			//price = all category !=all
			if (($_POST['price']=='all')and($_POST['category']!='all')){

				$SQL = "SELECT 	purchdata.stockid,
								stockmaster.description,
								purchdata.price,
								purchdata.conversionfactor,
								(purchdata.effectivefrom)as dateprice,
								purchdata.supplierdescription,
								purchdata.suppliers_partno
						FROM purchdata,stockmaster
						WHERE supplierno='" . $_POST['supplier'] . "'
						AND stockmaster.stockid=purchdata.stockid
						AND stockmaster.categoryid='" . $_POST['category'] .  "'
						ORDER BY stockid ASC ,dateprice DESC";
			} else {
			//price != all category !=all
				$SQL = "SELECT 	purchdata.stockid,
								stockmaster.description,
								(SELECT purchdata.price
								 FROM purchdata
								 WHERE purchdata.stockid = stockmaster.stockid
								 ORDER BY effectivefrom DESC
								 LIMIT 0,1) AS price,
								purchdata.conversionfactor,
								(SELECT purchdata.effectivefrom
								FROM purchdata
								WHERE purchdata.stockid = stockmaster.stockid
								ORDER BY effectivefrom DESC
								LIMIT 0,1) AS dateprice,
								purchdata.supplierdescription,
								purchdata.suppliers_partno
						FROM purchdata,stockmaster
						WHERE supplierno='" . $_POST['supplier'] . "'
						AND stockmaster.stockid=purchdata.stockid
						AND stockmaster.categoryid='" . $_POST['category'] .  "'
						GROUP BY stockid
						ORDER BY stockid ASC ,dateprice DESC";
			}
		}
	}
	$Result = DB_query($SQL,'','',false,true);

	if (DB_error_no() !=0) {
		$Title = _('Price List') . ' - ' . _('Problem Report');
		include('includes/header.php');
		prnMsg( _('The Price List could not be retrieved by the SQL because') . ' '  . DB_error_msg(),'error');
		echo '<a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		if ($Debug==1){
			echo '<br />' . $SQL;
		}
		include('includes/footer.php');
		exit;
	}

	if (DB_num_rows($Result)==0) {

		$Title = _('Supplier Price List') . '-' . _('Report');
		include('includes/header.php');
		prnMsg(_('There are no result so the PDF is empty'));
		include('includes/footer.php');
		exit;
	}
	if (!isset($_POST['View'])) {
	PrintHeader($PDF,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
				$Page_Width,$Right_Margin,$SupplierName,$Categoryname,$CurrCode,$CurrentOrAllPrices);

	$FontSize=8;
	$code='';
	while ($MyRow = DB_fetch_array($Result)){
		$YPos -=$LineHeight;

		$PriceDated=ConvertSQLDate($MyRow[4]);

		//if item has more than 1 price, write only price, date and supplier code for the old ones
		if ($code==$MyRow['stockid']){

			$PDF->addTextWrap(350,$YPos,50,$FontSize,locale_number_format($MyRow['price'],$CurrDecimalPlaces),'right');
			$PDF->addTextWrap(410,$YPos,50,$FontSize,$PriceDated,'left');
			$PDF->addTextWrap(470,$YPos,90,$FontSize,$MyRow['suppliers_partno'],'left');
			$code=$MyRow['stockid'];
		} else {
			$code=$MyRow['stockid'];
			$PDF->addTextWrap(30,$YPos,100,$FontSize,$MyRow['stockid'],'left');
			$PDF->addTextWrap(135,$YPos,160,$FontSize,$MyRow['description'],'left');
			$PDF->addTextWrap(300,$YPos,50,$FontSize,locale_number_format($MyRow['conversionfactor'],'Variable'),'right');
			$PDF->addTextWrap(350,$YPos,50,$FontSize,locale_number_format($MyRow['price'],$CurrDecimalPlaces),'right');
			$PDF->addTextWrap(410,$YPos,50,$FontSize,$PriceDated,'left');
			$PDF->addTextWrap(470,$YPos,90,$FontSize,$MyRow['suppliers_partno'],'left');
		}


		if ($YPos < $Bottom_Margin + $LineHeight){

			PrintHeader($PDF,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
						$Right_Margin,$SupplierName,$Categoryname,$CurrCode,$CurrentOrAllPrices);
		}


	} /*end while loop  */


	if ($YPos < $Bottom_Margin + $LineHeight){
		   PrintHeader($PDF,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
					   $Right_Margin,$SupplierName,$Categoryname,$CurrCode,$CurrentOrAllPrices);
	}


	$PDF->OutputD( $_SESSION['DatabaseName'] . '_SupplierPriceList_' . Date('Y-m-d') . '.pdf');
	} else {
		$Title = _('View supplier price');
		include('includes/header.inc');
		echo '<a href="'.htmlspecialchars($_SERVER['PHP_SELF'],'ENT_QUTOES','UTF-8').'">'._('return').'</a>';
		echo '<p class="page_title_text">'. _('Supplier Price List for').' : '.$CurrentOrAllPrices . '<br/>'
			._('Supplier').'   : '.$SupplierName.' <br/>'._('Category').' : '.$Categoryname.
			'</p>';

		echo '<table class="selection">
			<thead>
				<tr>
					<th class="SortedColumn">' . _('Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Conv Factor') . '</th>
				<th>' . _('Price') . '</th>
				<th class="SortedColumn">' . _('Date From') . '</th>
				<th>' . _('Supp Code') . '</th>
				</tr>
			</thead>
			<tbody>';

		while ($MyRow = DB_fetch_array($Result)){
			echo '<tr class="striped_row">
				<td>' . $MyRow['stockid'] . '</td>
				<td>' . $MyRow['description'] . '</td>
				<td>' . $MyRow['conversionfactor'] . '</td>
				<td>' . $MyRow['price'] . '</td>
				<td>' . ConvertSQLDate($MyRow['dateprice']) . '</td>
				<td>' . $MyRow['suppliers_partno'] . '</td>
				</tr>';

		}

		echo '</tbody></table>';
		include('includes/footer.inc');
	}

} else { /*The option to print PDF was not hit so display form */

	$Title=_('Supplier Price List');
	$ViewTopic = 'AccountsPayable';
	$BookMark = '';
	include('includes/header.php');
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Purchase') . '" alt="" />' . ' ' . _('Supplier Price List') . '
		</p>';
	echo '<div class="page_help_text">' . _('View the Price List from supplier') . '</div>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$SQL = "SELECT supplierid,suppname FROM `suppliers`";
	$Result = DB_query($SQL);
	echo '<fieldset>
			<legend>', _('Report Criteria'), '</legend>
			<field>
				<label for="supplier">' . _('Supplier') . ':</label>
				<select name="supplier"> ';
	while ($MyRow=DB_fetch_array($Result)){
		if (isset($_POST['supplierid']) and ($MyRow['supplierid'] == $_POST['supplierid'])) {
			 echo '<option selected="selected" value="' . $MyRow['supplierid'] . '">' . $MyRow['supplierid'].' - '.$MyRow['suppname'] . '</option>';
		} else {
			 echo '<option value="' . $MyRow['supplierid'] . '">' . $MyRow['supplierid'].' - '.$MyRow['suppname'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	$SQL="SELECT categoryid, categorydescription FROM stockcategory";
	$Result = DB_query($SQL);
	echo '<field>
			<label for="category">' . _('Category') . ':</label>
			<select name="category"> ';
		echo '<option value="all">' . _('ALL') . '</option>';
	while ($MyRow=DB_fetch_array($Result)){
		if (isset($_POST['categoryid']) and ($MyRow['categoryid'] == $_POST['categoryid'])) {
			 echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categoryid'] . ' - ' . $MyRow['categorydescription'] . '</option>';
		} else {
			 echo '<option value="' . $MyRow['categoryid'] . '">' .$MyRow['categoryid'].' - '. $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	echo '<field>
			<label for="price">' . _('Price List') . ':</label>
			<select name="price">
				<option value="all">' ._('All Prices') . '</option>
				<option value="current">' ._('Only Current Price') . '</option>
			</select>
		</field>';
	echo '</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>';

	echo '</form>';
	include('includes/footer.php');

} /*end of else not PrintPDF */



function PrintHeader(&$PDF,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
					 $Page_Width,$Right_Margin,$SupplierName,$Categoryname,$CurrCode,$CurrentOrAllPrices) {


	/*PDF page header for Supplier price list */
	if ($PageNumber>1){
		$PDF->newPage();
	}
	$LineHeight=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;
	$YPos -=(3*$LineHeight);

	$PDF->addTextWrap($Left_Margin,$YPos,300,$FontSize+2,$_SESSION['CompanyRecord']['coyname']);
	$YPos -=$LineHeight;

	$PDF->addTextWrap($Left_Margin,$YPos,150,$FontSize,_('Supplier Price List for').' '.$CurrentOrAllPrices);

	$PDF->addTextWrap($Page_Width-$Right_Margin-150,$YPos,160,$FontSize,_('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -= $LineHeight;
	$PDF->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Supplier').'   ');
	$PDF->addTextWrap(95,$YPos,150,$FontSize,': '.$SupplierName);

	$YPos -= $LineHeight;
	$PDF->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Category').' ');

	$PDF->addTextWrap(95,$YPos,150,$FontSize,': '.$Categoryname);
	$YPos -= $LineHeight;
	$PDF->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Currency').'  ');
	$PDF->addTextWrap(95,$YPos,50,$FontSize,': '.$CurrCode);
	$YPos -=(2*$LineHeight);
	/*set up the headings */

	$PDF->addTextWrap(30,$YPos,80,$FontSize,_('Code'), 'left');
	$PDF->addTextWrap(135,$YPos,80,$FontSize,_('Description'), 'left');
	$PDF->addTextWrap(300,$YPos,50,$FontSize,_('Conv Factor'), 'left');
	$PDF->addTextWrap(370,$YPos,50,$FontSize,_('Price'), 'left');
	$PDF->addTextWrap(410,$YPos,80,$FontSize,_('Date From'), 'left');
	$PDF->addTextWrap(470,$YPos,80,$FontSize,_('Supp Code'), 'left');

	$FontSize=8;
	$PageNumber++;
} // End of PrintHeader() function
?>
