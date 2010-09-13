<?php
/* $Revision: 1.14 $ */

$PageSecurity = 2;
include('includes/session.inc');

If (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
	AND strlen($_POST['ToCriteria'])>=1){

	include('includes/PDFStarter.php');

	$FontSize=10;
	$pdf->addinfo('Title', _('Price Listing Report') );
	$pdf->addinfo('Subject', _('Price List') );

	$PageNumber=1;
	$line_height=12;

	/*Now figure out the inventory data to report for the category range under review */
	if ($_POST['CustomerSpecials']==_('Customer Special Prices Only')){

		if ($_SESSION['CustomerID']==''){
			$title = _('Special price List - No Customer Selected');
			include('includes/header.inc');
			echo '<br>';
			prnMsg( _('The customer must first be selected from the select customer link') . '. ' . _('Re-run the price list once the customer has been selected') );
			echo '<br><br><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Back') . '</a>';
			include('includes/footer.inc');
			exit;
		}
		if (!Is_Date($_POST['EffectiveDate'])){
			$title = _('Special price List - No Customer Selected');
			include('includes/header.inc');
			prnMsg(_('The effective date must be entered in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
			echo '<br><br><a href="' . $_SERVER['PHP_SELF'] . '?' . SID . '">' . _('Back') . '</a>';
			include('includes/footer.inc');
			exit;
		}

		$SQL = "SELECT debtorsmaster.name,
				debtorsmaster.salestype
				FROM debtorsmaster
				WHERE debtorno = '" . $_SESSION['CustomerID'] . "'";
		$CustNameResult = DB_query($SQL,$db);
		$CustNameRow = DB_fetch_row($CustNameResult);
		$CustomerName = $CustNameRow[0];
		$SalesType = $CustNameRow[1];

		$SQL = "SELECT prices.typeabbrev,
						prices.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						prices.currabrev,
						prices.startdate,
						prices.enddate,
						prices.price,
						stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
						stockmaster.categoryid,
						stockcategory.categorydescription,
						prices.debtorno,
						prices.branchcode,
						custbranch.brname
						FROM stockmaster,
							stockcategory,
							prices LEFT JOIN custbranch
						ON prices.debtorno=custbranch.debtorno
						AND prices.branchcode=custbranch.branchcode
						WHERE stockmaster.stockid=prices.stockid
						AND stockmaster.categoryid=stockcategory.categoryid
						AND prices.typeabbrev = '" . $SalesType . "'
						AND stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "'
						AND stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "'
						AND prices.debtorno='" . $_SESSION['CustomerID'] . "'
						AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
						AND (prices.enddate='0000-00-00' OR prices.enddate>'" . FormatDateForSQL($_POST['EffectiveDate']) . "')
						ORDER BY prices.currabrev,
							stockmaster.categoryid,
							stockmaster.stockid,
							prices.startdate";

	} else { /* the sales type list only */

		$SQL = "SELECT sales_type FROM salestypes WHERE typeabbrev='" . $_POST['SalesType'] . "'";
		$SalesTypeResult = DB_query($SQL,$db);
		$SalesTypeRow = DB_fetch_row($SalesTypeResult);
		$SalesTypeName = $SalesTypeRow[0];

		$SQL = "SELECT prices.typeabbrev,
				prices.stockid,
				prices.startdate,
				prices.enddate,
				stockmaster.description,
				stockmaster.longdescription,
				prices.currabrev,
				prices.price,
				stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost as standardcost,
				stockmaster.categoryid,
				stockcategory.categorydescription
			FROM prices,
				stockmaster,
				stockcategory
			WHERE stockmaster.stockid=prices.stockid
			AND stockmaster.categoryid=stockcategory.categoryid
			AND stockmaster.categoryid >= '" . $_POST['FromCriteria'] . "'
			AND stockmaster.categoryid <= '" . $_POST['ToCriteria'] . "'
			AND prices.typeabbrev='" . $_POST['SalesType'] . "'
			AND prices.startdate<='" . FormatDateForSQL($_POST['EffectiveDate']) . "'
			AND (prices.enddate='0000-00-00' OR prices.enddate>'" . FormatDateForSQL($_POST['EffectiveDate']) . "')
			AND prices.debtorno=''
			ORDER BY prices.currabrev,
				stockmaster.categoryid,
				stockmaster.stockid,
				prices.startdate";
	}
	$PricesResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Price List') . ' - ' . _('Problem Report....');
		include('includes/header.inc');
		prnMsg( _('The Price List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<br><a href="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the menu'). '</a>';
		if ($debug==1){
			prnMsg(_('For debugging purposes the SQL used was:') . $SQL,'error');
		}
		include('includes/footer.inc');
		exit;
	}
	if (DB_num_rows($PricesResult)==0){
		$title = _('Print Price List Error');
		include('includes/header.inc');
		prnMsg(_('There were no price details to print out for the customer or category specified'),'warn');
		echo '<br><a href="'.$_SERVER['PHP_SELF'] .'?' . SID . '">'. _('Back').'</a>';
		include('includes/footer.inc');
		exit;
	}

	PageHeader();

	$CurrCode ='';
	$Category = '';
	$CatTot_Val=0;
	$Pos=$Page_Height-$Top_Margin-$YPos+20;

	While ($PriceList = DB_fetch_array($PricesResult,$db)){

		if ($CurrCode != $PriceList['currabrev']){
			$FontSize=10;
			$YPos -= 2*$line_height;
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300-$Left_Margin,$FontSize, $PriceList['currabrev'] . ' ' . _('Prices'));
			$CurrCode = $PriceList['currabrev'];
			$FontSize = 8;
		}

		if ($Category!=$PriceList['categoryid']){
			$FontSize=10;
			$YPos -= 2*$line_height;
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300-$Left_Margin,$FontSize,$PriceList['categoryid'] . ' - ' . $PriceList['categorydescription']);
			$Category = $PriceList['categoryid'];
			$CategoryName = $PriceList['categorydescription'];
			$FontSize=8;
		}

		$YPos -=$line_height;
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,80,$FontSize,$PriceList['stockid']);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+80,$YPos,47,$FontSize,ConvertSQLDate($PriceList['startdate']));
		if ($PriceList['enddate']!='0000-00-00'){
			$DisplayEndDate = ConvertSQLDate($PriceList['enddate']);
		} else {
			$DisplayEndDate = _('No End Date');
		}
		$LeftOvers = $pdf->addTextWrap($Left_Margin+80+47,$YPos,47,$FontSize,$DisplayEndDate);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+80+47+47,$YPos,130,$FontSize,$PriceList['description']);
		$DisplayUnitPrice = number_format($PriceList['price'],2);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+80+47+47+130,$YPos,60,$FontSize,$DisplayUnitPrice, 'right');

		if ($PriceList['price']!=0){
			$DisplayGPPercent = (int)(($PriceList['price']-$PriceList['standardcost'])*100/$PriceList['price']) . '%';
		} else {
			$DisplayGPPercent = 0;
		}

		if ($_POST['ShowGPPercentages']=='Yes'){
			$LeftOvers = $pdf->addTextWrap($Left_Margin+80+47+47+130+65,$YPos,20,$FontSize,$DisplayGPPercent, 'right');
		}

		if ($_POST['CustomerSpecials']=='Customer Special Prices Only'){
			/*Need to show to which branch the price relates */
			if ($PriceList['branchcode']!=''){
				$LeftOvers = $pdf->addTextWrap($Left_Margin+80+47+47+130+65+25,$YPos,60,$FontSize,$PriceList['brname'],'left');
			} else {
				$LeftOvers = $pdf->addTextWrap($Left_Margin+80+47+47+130+65+25,$YPos,60,$FontSize,_('All'),'left');
			}

		} else If ($_POST['CustomerSpecials']=='Full Description'){

			if(file_exists($_SESSION['part_pics_dir'] . '/' .$PriceList['stockid'].'.jpg') ) {
				$img = imagecreatefromjpeg($_SESSION['part_pics_dir'] . '/' .$PriceList['stockid'].'.jpg');
				$width = imagesx( $img );
				$height = imagesy( $img );
				if($width>$height){
					$LeftOvers = $pdf->Image($_SESSION['part_pics_dir'] . '/'.$PriceList['stockid'].'.jpg',120,$Page_Height-$Top_Margin-$YPos+20,50,0);
				}else{
					$LeftOvers = $pdf->Image($_SESSION['part_pics_dir'] . '/'.$PriceList['stockid'].'.jpg',120,$Page_Height-$Top_Margin-$YPos+20,0,40);
				}
			}/*end checked file exist*/

			$Split = explode("\r\n", $PriceList['longdescription']);
			$FontSize2=6;
			if ($YPos < ($Bottom_Margin + (count($Split)*$line_height))){
		 	  PageHeader();
			}
			for ($i=0; $i<=count($Split); $i++)	{
				if(count($Split)==1){
					$YPos -=(1*$line_height);
					$LeftOvers = $pdf->addTextWrap(140,$YPos,300,$FontSize2,$Split[$i]);
					$YPos -=(1*$line_height);
					$LeftOvers = $pdf->addTextWrap(140,$YPos,300,$FontSize2,'');
				}elseif(count($Split)==2){
					$YPos -=(1*$line_height);
					$LeftOvers = $pdf->addTextWrap(140,$YPos,300,$FontSize2,$Split[$i]);
				}elseif(count($Split)>=3){
					$YPos -=(1*$line_height);
					$LeftOvers = $pdf->addTextWrap(140,$YPos,300,$FontSize2,$Split[$i]);
				}
			}/*end for*/
		}/*end if full descriptions*/

		if ($YPos < $Bottom_Margin + $line_height){
		   PageHeader();
		}

	} /*end inventory valn while loop */

	$FontSize =10;
/*Print out the category totals */

	$FileName=$_SESSION['DatabaseName']. '_' . _('Price_List') . '_' . date('Y-m-d').'.pdf';
	ob_clean();
	$pdf->OutputD($FileName);
	$pdf->__destruct();

} else { /*The option to print PDF was not hit */

	$title= _('Price Listing');
	include('includes/header.inc');

	echo '<p class="page_title_text"><img src="' . $rootpath . '/css/' . $theme . '/images/customer.png" title="' .
		_('Price List') . '" alt="">' . ' ' . _('Print a price list');

	if (!isset($_POST['FromCriteria']) or !isset($_POST['ToCriteria'])) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<form action=' . $_SERVER['PHP_SELF'] . ' method="POST"><table class=selection>';

		echo '<tr><td>'. _('From Inventory Category Code') .':</font></td><td><select name=FromCriteria>';

		$sql='SELECT categoryid, categorydescription FROM stockcategory ORDER BY categoryid';
		$CatResult= DB_query($sql,$db);
		While ($myrow = DB_fetch_array($CatResult)){
			echo "<option VALUE='" . $myrow['categoryid'] . "'>" . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
		}
		echo '</select></td></tr>';

		echo '<tr><td>' . _('To Inventory Category Code'). ':</td><td><select name=ToCriteria>';

		/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		While ($myrow = DB_fetch_array($CatResult)){
			echo '<option VALUE="' . $myrow['categoryid'] . '">' . $myrow['categoryid'] . ' - ' . $myrow['categorydescription'];
		}
		echo '</select></td></tr>';

		echo '<tr><td>' . _('For Sales Type/Price List').':</td><td><select name="SalesType">';
		$sql = 'SELECT sales_type, typeabbrev FROM salestypes';
		$SalesTypesResult=DB_query($sql,$db);

		while ($myrow=DB_fetch_array($SalesTypesResult)){
		          echo '<option Value="' . $myrow['typeabbrev'] . '">' . $myrow['sales_type'];
		}
		echo '</select></td></tr>';

		echo '<tr><td>' . _('Show Gross Profit %') . ':</td><td><select name="ShowGPPercentages">';
		echo '<option selected Value="No">'. _('Prices Only');
		echo '<option Value="Yes">'. _('Show GP % too');
		echo '</select></td></tr>';

		echo '<tr><td>' . _('Price Listing Type'). ':</td><td><select name="CustomerSpecials">';
		echo '<option selected Value="Sales Type Prices">'. _('Default Sales Type Prices');
		echo '<option Value="Customer Special Prices Only">'. _('Customer Special Prices Only');
		echo '<option Value="Full Description">'. _('Full Description');
		echo '</select></td></tr>';

		echo '<tr><td>' . _('Effective As At') . ':</td><td><input type="text" size=11 class="date"	alt="' . $_SESSION['DefaultDateFormat'] . '" name="EffectiveDate" value="' . Date($_SESSION['DefaultDateFormat']) . '">';

		echo '</table><br><div class="centre"><input type=Submit Name="PrintPDF" Value="'. _('Print PDF'). '"></div>';
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

function PageHeader () {
	global $pdf;
	global $PageNumber;
	global $YPos;
	global $Xpos;
	global $line_height;
	global $Page_Height;
	global $Top_Margin;
	global $Page_Width;
	global $Right_Margin;
	global $Left_Margin;
	global $Bottom_Margin;
	global $FontSize;
	global $SalesTypeName;
	global $CustomerName;

	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize=10;
	$YPos= $Page_Height-$Top_Margin;

	$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-140,$YPos,140,$FontSize, _('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. _('Page'). ' ' . $PageNumber);

	$YPos -=$line_height;
	//Note, this is ok for multilang as this is the value of a Select, text in option is different
	if ($_POST['CustomerSpecials']==_('Customer Special Prices Only')){
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize, $CustomerName . ' ' . _('Prices for Categories').' ' . $_POST['FromCriteria'] . ' - ' . $_POST['ToCriteria'] . ' ' . _('Effective As At') . ' ' . $_POST['EffectiveDate']);
	} else {
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,550,$FontSize, $SalesTypeName . ' ' ._('Prices For Categories') . ' ' . $_POST['FromCriteria'] . ' - ' . $_POST['ToCriteria'] . ' ' . _('Effective As At') . ' ' . $_POST['EffectiveDate'] );
	}

	$YPos -=(2*$line_height);
	/*Draw a rectangle to put the headings in     */

	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
	$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
	$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

	/*set up the headings */
	$Xpos = $Left_Margin+1;

	$LeftOvers = $pdf->addTextWrap($Xpos,$YPos,60,$FontSize, _('Category') . '/' . _('Item'), 'left');
	$LeftOvers = $pdf->addTextWrap($Xpos+70,$YPos,60,$FontSize, _('Effective Date Range'), 'left');

	if ($_POST['CustomerSpecials']==_('Customer Special Prices Only')){
		$LeftOvers = $pdf->addTextWrap($Left_Margin+80+47+47+130+65+25,$YPos,60,$FontSize, _('Branch'), 'centre');
	}

	$LeftOvers = $pdf->addTextWrap($Left_Margin+80+47+47+130+20,$YPos,60,$FontSize, _('Price') , 'centre');

	if ($_POST['ShowGPPercentages']=='Yes'){
		$LeftOvers = $pdf->addTextWrap($Left_Margin+80+47+47+130+65,$YPos,20,$FontSize, _('GP') .'%', 'centre');
	}

	$FontSize=8;
	$YPos -= (1.5 * $line_height);

	$PageNumber++;
}

?>
