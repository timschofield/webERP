<?php
/* $Revision: 1.4 $ */

$PageSecurity = 2;

If (isset($_POST['PrintPDF'])
	AND isset($_POST['FromCriteria'])
	AND strlen($_POST['FromCriteria'])>=1
	AND isset($_POST['ToCriteria'])
		 AND strlen($_POST['ToCriteria'])>=1){

	include('config.php');
	include('includes/ConnectDB.inc');
	include('includes/PDFStarter_ros.inc');

	$FontSize=10;
	$pdf->addinfo('Title', _('Price Listing Report') );
	$pdf->addinfo('Subject', _('Price List') );

	$PageNumber=1;
	$line_height=12;

      /*Now figure out the inventory data to report for the category range under review */
	if ($_POST['CustomerSpecials']=='Customer Special Prices Only'){

		if ($_SESSION['CustomerID']==''){
			include('includes/header.inc');
			echo '<BR>';
			prnMsg( _('The Customer must first be selected from the select customer link , re-run the price list once the customer has been selected') );
			include('includes/footer.inc');
			exit;
		}

		$SQL = "SELECT Name, SalesType From DebtorsMaster WHERE DebtorNo = '" . $_SESSION['CustomerID'] . "'";
		$CustNameResult = DB_query($SQL,$db);
		$CustNameRow = DB_fetch_row($CustNameResult);
		$CustomerName = $CustNameRow[0];
		$SalesType = $CustNameRow[1];

		$SQL = "SELECT Prices.TypeAbbrev,
			Prices.StockID,
			StockMaster.Description,
			Prices.CurrAbrev,
			Prices.Price,
			StockMaster.MaterialCost+StockMaster.LabourCost+StockMaster.OverheadCost AS StandardCost,
			StockMaster.CategoryID,
			StockCategory.CategoryDescription,
			Prices.DebtorNo,
			Prices.BranchCode,
			CustBranch.BrName
			FROM StockMaster, StockCategory, Prices LEFT JOIN CustBranch
			ON Prices.DebtorNo=CustBranch.DebtorNo
			AND Prices.BranchCode=CustBranch.BranchCode
			WHERE StockMaster.StockID=Prices.StockID
			AND StockMaster.CategoryID=StockCategory.CategoryID
			AND Prices.TypeAbbrev = '" . $SalesType . "'
			AND StockMaster.CategoryID >= '" . $_POST['FromCriteria'] . "'
			AND StockMaster.CategoryID <= '" . $_POST['ToCriteria'] . "'
			AND Prices.DebtorNo='" . $_SESSION['CustomerID'] . "'
			ORDER BY Prices.CurrAbrev,
				StockMaster.CategoryID,
				StockMaster.StockID";

	} else { /* the sales type list only */


		$SQL = "SELECT Sales_Type FROM SalesTypes WHERE TypeAbbrev='" . $_POST['SalesType'] . "'";
		$SalesTypeResult = DB_query($SQL,$db);
		$SalesTypeRow = DB_fetch_row($SalesTypeResult);
		$SalesTypeName = $SalesTypeRow[0];

		$SQL = "SELECT Prices.TypeAbbrev,
				Prices.StockID,
				StockMaster.Description,
				Prices.CurrAbrev,
				Prices.Price,
				StockMaster.MaterialCost+StockMaster.LabourCost+StockMaster.OverheadCost AS StandardCost,
				StockMaster.CategoryID,
				StockCategory.CategoryDescription
			FROM Prices,
				StockMaster,
				StockCategory
			WHERE StockMaster.StockID=Prices.StockID
			AND StockMaster.CategoryID=StockCategory.CategoryID
			AND StockMaster.CategoryID >= '" . $_POST['FromCriteria'] . "'
			AND StockMaster.CategoryID <= '" . $_POST['ToCriteria'] . "'
			AND Prices.TypeAbbrev='" . $_POST['SalesType'] . "'
			AND Prices.DebtorNo=''
			ORDER BY Prices.CurrAbrev,
				StockMaster.CategoryID,
				StockMaster.StockID";
	}
	$PricesResult = DB_query($SQL,$db,'','',false,false);

	if (DB_error_no($db) !=0) {
		$title = _('Price List - Problem Report....');
		include('includes/header.inc');
		prnMsg( _('The Price List could not be retrieved by the SQL because'). ' - ' . DB_error_msg($db), 'error');
		echo '<BR><A HREF="' .$rootpath .'/index.php?' . SID . '">'.  _('Back to the Menu'). '</A>';
		if ($debug==1){
			echo '<BR>'. $SQL;
		}
		include('includes/footer.inc');
		exit;
	}

	include('includes/PDFPriceListPageHeader.inc');
	$CurrCode ='';
	$Category = '';
	$CatTot_Val=0;
	While ($PriceList = DB_fetch_array($PricesResult,$db)){

		if ($CurrCode!=$PriceList["CurrAbrev"]){
			$FontSize=10;
			$YPos -=(2*$line_height);
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize, _('Prices in') . ' ' . $PriceList["CurrAbrev"]);
			$CurrCode = $PriceList["CurrAbrev"];
			$FontSize=8;
			$YPos -=$line_height;
		}

		if ($Category!=$PriceList["CategoryID"]){
			$FontSize=10;
			$YPos -=(2*$line_height);
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,300-$Left_Margin,$FontSize,$PriceList["CategoryID"] . " - " . $PriceList["CategoryDescription"]);
			$Category = $PriceList["CategoryID"];
			$CategoryName = $PriceList["CategoryDescription"];
			$FontSize=8;
			$YPos -=$line_height;
		}

		$YPos -=$line_height;


		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,60,$FontSize,$PriceList["StockID"]);					$LeftOvers = $pdf->addTextWrap(120,$YPos,260,$FontSize,$PriceList["Description"]);

		if ($_POST['CustomerSpecials']=='Customer Special Prices Only'){
			/*Need to show to which branch the price relates */
			if ($PriceList['BranchCode']!=""){
				$LeftOvers = $pdf->addTextWrap(320,$YPos,130,$FontSize,$PriceList['BrName'],"left");
			} else {
				$LeftOvers = $pdf->addTextWrap(320,$YPos,130,$FontSize,_('All'),"left");
			}

		}

		$DisplayUnitPrice = number_format($PriceList["Price"],2);

		if ($PriceList['Price']!=0){
			$DisplayGPPercent = (int)(($PriceList["Price"]-$PriceList["StandardCost"])*100/$PriceList["Price"]) . "%";
		} else {
			$DisplayGPPercent = 0;
		}


		$LeftOvers = $pdf->addTextWrap(440,$YPos,60,$FontSize,$DisplayUnitPrice, "right");

		if ($_POST['ShowGPPercentages']=="Yes"){
			$LeftOvers = $pdf->addTextWrap(530,$YPos,20,$FontSize,$DisplayGPPercent, "right");
		}

		if ($YPos < $Bottom_Margin + $line_height){
		   include("includes/PDFPriceListPageHeader.inc");
		}

	} /*end inventory valn while loop */

	$FontSize =10;
/*Print out the category totals */


	$pdfcode = $pdf->output();
	$len = strlen($pdfcode);

      if ($len<=20){
		$title = _('Print Price List Error');
		include('includes/header.inc');
		echo '<p>' . _('There were no price details to print out for the customer/category specified');
		echo '<BR><A HREF="'.$rootpath.'/index.php?' . SID . '">'. _('Back to the Menu').'</A>';
		include("includes/footer.inc");
		exit;
      } else {
		header('Content-type: application/pdf');
		header('Content-Length: ' . $len);
		header('Content-Disposition: inline; filename=PriceList.pdf');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');

		$pdf->Stream();

	}
} else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title= _('Price Listing');
	include('includes/header.inc');
	include('includes/SQL_CommonFunctions.inc');
	$CompanyRecord = ReadInCompanyRecord($db);


	if (strlen($_POST['FromCriteria'])<1 || strlen($_POST['ToCriteria'])<1) {

	/*if $FromCriteria is not set then show a form to allow input	*/

		echo '<FORM ACTION=' . $_SERVER['PHP_SELF'] . ' METHOD="POST"><CENTER><TABLE>';

		echo '<TR><TD>'. _('From Inventory Category Code') .':</FONT></TD><TD><SELECT name=FromCriteria>';

		$sql="SELECT CategoryID, CategoryDescription FROM StockCategory ORDER BY CategoryID";
		$CatResult= DB_query($sql,$db);
		While ($myrow = DB_fetch_array($CatResult)){
			echo "<OPTION VALUE='" . $myrow["CategoryID"] . "'>" . $myrow["CategoryID"] . " - " . $myrow["CategoryDescription"];
		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('To Inventory Category Code'). ':</TD><TD><SELECT name=ToCriteria>';

		/*Set the index for the categories result set back to 0 */
		DB_data_seek($CatResult,0);

		While ($myrow = DB_fetch_array($CatResult)){
			echo '<OPTION VALUE="' . $myrow['CategoryID'] . '">' . $myrow['CategoryID'] . ' - ' . $myrow['CategoryDescription'];
		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('For Sales Type/Price List').':</TD><TD><SELECT name="SalesType">';
		$sql = "SELECT Sales_Type, TypeAbbrev FROM SalesTypes";
		$SalesTypesResult=DB_query($sql,$db);

		while ($myrow=DB_fetch_array($SalesTypesResult)){
		          echo '<OPTION Value="' . $myrow['TypeAbbrev'] . '">' . $myrow['Sales_Type'];
		}
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('Show Gross Profit %') . ':</TD><TD><SELECT name="ShowGPPercentages">';
		echo '<OPTION SELECTED Value="No">'. _('Prices Only');
		echo '<OPTION Value="Yes">'. _('Show GP % too');
		echo '</SELECT></TD></TR>';

		echo '<TR><TD>' . _('Price Listing Type'). ':</TD><TD><SELECT name="CustomerSpecials">';
		echo '<OPTION  Value="Customer Special Prices Only">'. _('Customer Special Prices Only');
		echo '<OPTION SELECTED Value="Sales Type Prices">'. _('Default Sales Type Prices');
		echo '</SELECT></TD></TR>';

		echo '</TABLE><INPUT TYPE=Submit Name="PrintPDF" Value="'. _('Print PDF'). '"></CENTER>';
	}
	include('includes/footer.inc');

} /*end of else not PrintPDF */

?>
