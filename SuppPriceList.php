<?php

/* $Id$*/

$PageSecurity = 2;
include('includes/session.inc');

if (isset($_GET['SelectedSupplier'])) {
	$_POST['supplierid']=$_GET['SelectedSupplier'];
}

if (isset($_POST['PrintPDF'])) {

	include('includes/PDFStarter.php');
    
	$FontSize=9;
	$pdf->addinfo('Title',_('Supplier Price List'));
	$pdf->addinfo('Subject',_('Price List of goods from a Supplier'));

	$PageNumber=1;
	$line_height=12;
	
	//get supplier 
	$sqlsup = "SELECT suppname FROM suppliers where supplierid='" . $_POST['supplier'] . "'";
	$resultsup = db_query($sqlsup,$db,$ErrMsg);
	$RowSup = db_fetch_row($resultsup);
	$Supp=$RowSup['0'];
		
	//get category
	if ($_POST['category']!="all"){
		$sqlcat="SELECT categorydescription FROM `stockcategory` where categoryid ='" . $_POST['category'] . "'";
		$resultcat = DB_query($sqlcat,$db);
		$RowCat = db_fetch_row($resultcat);
		$Categoryname=$RowCat['0'];
	} else {
		$Categoryname="ALL";
	}
	
	//get currency
	$sqlcur="SELECT currcode FROM `suppliers` where supplierid='" . $_POST['supplier'] .  "'";
	$resultcur = db_query($sqlcur,$db,$ErrMsg);
	$RowCur = db_fetch_row($resultcur);
	$Currency=$RowCur['0'];
		
	//get date price
	if ($_POST['price']=="all"){
		$DatePrice=_('All Price');
	} else {
		$DatePrice=_('Current Price');
	}
	
	//price and category = all
	if (($_POST['price']=="all")and($_POST['category']=="all")){
		$sql = "SELECT 	purchdata.stockid,
					stockmaster.description,
					purchdata.price,
					(purchdata.effectivefrom)as dateprice,
					purchdata.supplierdescription 
			FROM purchdata,stockmaster
			WHERE supplierno='" . $_POST['supplier'] . "'
				AND stockmaster.stockid=purchdata.stockid
			ORDER BY stockid ASC ,dateprice DESC";
	} else {
	//category=all and price != all
		if (($_POST['price']!="all")and($_POST['category']=="all")){	
		
			$sql = "SELECT purchdata.stockid, 
						stockmaster.description,
						purchdata.price, 
						(SELECT purchdata.effectivefrom 
						 FROM purchdata 
						 WHERE purchdata.stockid = stockmaster.stockid 
						 ORDER BY effectivefrom DESC  
						 LIMIT 0,1) AS dateprice,
						purchdata.supplierdescription
			FROM purchdata, stockmaster
			WHERE supplierno = '" . $_POST['supplier'] . "'
			AND stockmaster.stockid = purchdata.stockid
			GROUP BY stockid
			ORDER BY stockid ASC , dateprice DESC";
		} else {		
			//price = all category !=all
			if (($_POST['price']=="all")and($_POST['category']!="all")){
		
				$sql = "SELECT 	purchdata.stockid,
					stockmaster.description,
					purchdata.price,
					(purchdata.effectivefrom)as dateprice,
					purchdata.supplierdescription 
				FROM purchdata,stockmaster
				WHERE supplierno='" . $_POST['supplier'] . "'
					AND stockmaster.stockid=purchdata.stockid
					AND stockmaster.categoryid='" . $_POST['category'] .  "'
				ORDER BY stockid ASC ,dateprice DESC";
			} else {
			//price != all category !=all
				$sql = "SELECT 	purchdata.stockid,
					stockmaster.description,
					purchdata.price,
					(SELECT purchdata.effectivefrom 
					FROM purchdata 
					WHERE purchdata.stockid = stockmaster.stockid 
					ORDER BY effectivefrom DESC  
					LIMIT 0,1) AS dateprice,
					purchdata.supplierdescription 
				FROM purchdata,stockmaster
				WHERE supplierno='" . $_POST['supplier'] . "'
					AND stockmaster.stockid=purchdata.stockid
					AND stockmaster.categoryid='" . $_POST['category'] .  "'
				GROUP BY stockid
				ORDER BY stockid ASC ,dateprice DESC";
			}
		}
	}
	$result = DB_query($sql,$db,'','',false,true);

	if (DB_error_no($db) !=0) {
		$title = _('Price List') . ' - ' . _('Problem Report');
		include('includes/header.inc');
		prnMsg( _('The Price List could not be retrieved by the SQL because') . ' '  . DB_error_msg($db),'error');
		echo "<br><a href='" .$rootpath .'/index.php?' . SID . "'>" . _('Back to the menu') . '</a>';
		if ($debug==1){
			echo "<br>$sql";
		}
		include('includes/footer.inc');
		exit;
	}

	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
	            $Page_Width,$Right_Margin,$Supp,$Categoryname,$Currency,$DatePrice);

	$FontSize=8;

	while ($myrow = DB_fetch_array($result,$db)){
		$YPos -=$line_height;
		// Parameters for addTextWrap are defined in /includes/class.pdf.php
		// 1) X position 2) Y position 3) Width
		// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
		// and False to set to transparent

		$date=$myrow[3];
		list($year,$month,$day ) = split('[/.-]', $myrow[3]);
		$dateprice="$day/$month/$year";

		//if item has more than 1 price, write only price, date and supplier code for the old ones
		if ($code==$myrow[0]){

			$pdf->addTextWrap(350,$YPos,50,$FontSize,number_format($myrow[2],2),'right');
			$pdf->addTextWrap(430,$YPos,50,$FontSize,$dateprice,'left');
			$pdf->addTextWrap(510,$YPos,40,$FontSize,$myrow[4],'left');
			$code=$myrow[0];
		} else {			
			$code=$myrow[0];			
			$pdf->addTextWrap(50,$YPos,90,$FontSize,$myrow[0],'left');
			$pdf->addTextWrap(145,$YPos,215,$FontSize,$myrow[1],'left');
			$pdf->addTextWrap(350,$YPos,50,$FontSize,number_format($myrow[2],2),'right');
			$pdf->addTextWrap(430,$YPos,50,$FontSize,$dateprice,'left');
			$pdf->addTextWrap(510,$YPos,40,$FontSize,$myrow[4],'left');
		}	


		if ($YPos < $Bottom_Margin + $line_height){				

			PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
			            $Right_Margin,$Supp,$Categoryname,$Currency,$DatePrice);
		}


	} /*end while loop  */

	
	if ($YPos < $Bottom_Margin + $line_height){
	       PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin,$Supp,$Categoryname,$Currency,$DatePrice);
	}


	$pdfcode = $pdf->output();
	
	header('Content-type: application/pdf');
	header("Content-Length: " . $len);
	header('Content-Disposition: inline; filename=Supplier Price List.pdf');
	header('Expires: 0');
	header('Cache-Control: private, post-check=0, pre-check=0');
	header('Pragma: public');	
	$pdf->Output('SuppPriceList.pdf', 'I');


	
} else { /*The option to print PDF was not hit so display form */

	$title=_('Supplier Price List');
	include('includes/header.inc');
	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Purchase') . '" alt="">' . ' ' . _('Supplier Price List') . '';
	echo '<div class="page_help_text">' . _('View the Price List from supplier') . '</div><br>';

	echo '<br/><form action=' . $_SERVER['PHP_SELF'] . " method='post'><table>";
	
	$sql = "SELECT supplierid,suppname FROM `suppliers`";
	$result = DB_query($sql,$db);
	echo '<table>';
	echo '<tr><td>' . _('Supplier') . ':</td><td><select name="supplier"> ';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['supplierid']) and ($myrow['supplierid'] == $_POST['supplierid'])) {
			 echo '<option selected Value="' . $myrow['supplierid'] . '">' . $myrow['supplierid'].' - '.$myrow['suppname'];
		} else {
			 echo '<option Value="' . $myrow['supplierid'] . '">' . $myrow['supplierid'].' - '.$myrow['suppname'];
		}
	} 
	echo '</select></td></tr>';
	
	$sql='SELECT categoryid,categorydescription FROM `stockcategory`';
	$result = DB_query($sql,$db);
	echo '<tr><td>' . _('Category') . ':</td><td><select name="category"> ';
		echo '<option Value="all">' ._('ALL').'';
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['categoryid']) and ($myrow['categoryid'] == $_POST['categoryid'])) {
			 echo '<option selected Value="' . $myrow['categoryid'] . '">' . $myrow['categoryid']-$myrow['categorydescription'];
		} else {
			 echo '<option Value="' . $myrow['categoryid'] . '">' .$myrow['categoryid'].' - '. $myrow['categorydescription'];
		}
	} 
	echo '</select></td></tr>';
	
	echo '<tr><td>' . _('Price List') . ':</td><td><select name="price"> ';
	echo '<option Value="all">' ._('All Prices').'';
	echo '<option Value="current">' ._('Only Current Price').'';
	echo '</select></td></tr>';
	
			
	echo "</table><br/><div class='centre'><input type=submit name='PrintPDF' value='" . _('Print PDF') . "'></div>";

	include('includes/footer.inc');

} /*end of else not PrintPDF */



function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
                     $Page_Width,$Right_Margin,$Supp,$Categoryname,$Currency,$DatePrice) {


	/*PDF page header for Supplier price list */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$line_height=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;
	$YPos -=(3*$line_height);
	
	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize+2,$_SESSION['CompanyRecord']['coyname']);
	$YPos -=$line_height;
	
	$pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,_('Supplier Price List for').' '.$DatePrice);
	
	$pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos,160,$FontSize,_('Printed') . ': ' . 
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Supplier').'   ');
	$pdf->addTextWrap(95,$YPos,150,$FontSize,_(': ').$Supp);
	
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Category').' ');

	$pdf->addTextWrap(95,$YPos,150,$FontSize,_(': ').$Categoryname);
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Currency').'  ');
	$pdf->addTextWrap(95,$YPos,50,$FontSize,_(': ').$Currency);
	$YPos -=(2*$line_height);	
	/*set up the headings */
	
	
	
	$pdf->addTextWrap(50,$YPos,100,$FontSize,_('Code'), 'left');
	$pdf->addTextWrap(145,$YPos,200,$FontSize,_('Description'), 'left');
	$pdf->addTextWrap(370,$YPos,60,$FontSize,_('Price'), 'left');
	$pdf->addTextWrap(390,$YPos,80,$FontSize,_('Date Price'), 'right');
	$pdf->addTextWrap(470,$YPos,80,$FontSize,_('Supp Code'), 'right');
	
	
	$FontSize=8;
	$PageNumber++;
} // End of PrintHeader() function
?>