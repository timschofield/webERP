<?php
/* $Revision: 1.1 $ */
/* $Id$*/

// ReorderLevelLocation.php - Report of reorder level by category
$PageSecurity = 2;

include('includes/session.inc');

$title=_('Reorder Level Location Reporting');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="">' . ' ' . _('Inventory Reorder Level Location Report') . '';

if (isset($_POST['submit']) or isset($_POST['update'])) {

	if ($_POST['NumberOfDays']==""){
		header('Location: ReorderLevelLocation.php');
	}

	if($_POST['order']==1){
		$order="qtyinvoice DESC, locstock.stockid";
	}else{
		$order="locstock.stockid";
	}

	$sql="SELECT locstock.stockid, stockmaster.description,
				locstock.reorderlevel,
				(SELECT SUM(salesorderdetails.qtyinvoiced)
					FROM salesorderdetails,salesorders
					WHERE salesorderdetails.stkcode=locstock.stockid
						AND salesorderdetails.orderno = salesorders.orderno
						AND salesorders.fromstkloc =  '" . $_POST['StockLocation'] . "'
						AND salesorderdetails.ActualDispatchDate >= DATE_SUB(CURDATE(), INTERVAL ".$_POST['NumberOfDays']." DAY)
				)as qtyinvoice
				FROM locstock, stockmaster
				WHERE locstock.stockid = stockmaster.stockid
					AND stockmaster.categoryid = '" . $_POST['StockCat'] . "'
					AND locstock.loccode = '" . $_POST['StockLocation'] . "'
				ORDER BY '" . $order . "' ASC";

	$result = DB_query($sql,$db);

	$sqlloc="SELECT locationname
		   FROM locations
		   WHERE loccode='".$_POST['StockLocation']."'";

	$ResultLocation = DB_query($sqlloc,$db);
	$Location=DB_fetch_array($ResultLocation);

	echo'<p class="page_title_text" align="center"><strong>' . _('Location : ') . '' . $Location['0'] . ' </strong></p>';
	echo'<p class="page_title_text" align="center"><strong>' . _('Number Of Days Sales : ') . '' . $_POST['NumberOfDays'] . '' . _(' Days ') . ' </strong></p>';
	echo "<table>";
	echo '<tr><th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Total Invoiced').'<br>'._('At All Locations') . '</th>
						<th>' . _('Total Invoiced').'<br>'._('At Location') . '</th>
						<th>' . _('On Hand') .'<br>'._('At All Locations') . '</th>
						<th>' . _('On Hand') .'<br>' ._('At Location') . '</th>
						<th>' . _('Reorder Level') . '</th>
					<tr>';

	$k=0; //row colour counter
	echo'<form action="ReorderLevelLocation.php" method="POST" name="'._('update').'">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	while ($myrow=DB_fetch_array($result))	{

	//update database if update pressed
		if ($_POST['submit']=='Update'){

			$SQLUpdate="UPDATE locstock SET reorderlevel = '" . $_POST[$myrow['0']] . "'
						    WHERE `locstock`.`loccode` = '" . $_POST['StockLocation'] . "'
						    AND `locstock`.`stockid` = '" . $myrow['0'] . "'";

			$Resultup = DB_query($SQLUpdate,$db);

		}

		if (isset($_POST[$myrow['0']])){
			$reorder=$_POST[$myrow['0']];
		}else{
			$reorder=$myrow[2];
		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		//variable for update data

		echo'<input type="hidden" value=' . $_POST['order'] . ' name='. _('order').' />
				<input type="hidden" value=' . $_POST['StockLocation'] . ' name='. _('StockLocation').' />
				<input type="hidden" value=' . $_POST['StockCat'] . ' name='. _('StockCat').' />
				<input type="hidden" value=' . $_POST['NumberOfDays'] . ' name='. _('NumberOfDays').' />
				<input type="hidden" value='.$myrow['0'].' name='. _('id').' />';

		//get qtyinvoice all
		$sqlinv="SELECT sum(salesorderdetails.qtyinvoiced)as qtyinvoice
						FROM salesorderdetails,salesorders
						WHERE salesorderdetails.stkcode='".$myrow[0]."'
						AND salesorderdetails.orderno = salesorders.orderno
						AND salesorderdetails.ActualDispatchDate >= DATE_SUB(CURDATE(), INTERVAL ".$_POST['NumberOfDays']." DAY)
						";
		$ResultInv = DB_query($sqlinv,$db);
		$InvoiceAll=DB_fetch_array($ResultInv);


		if($InvoiceAll['0']==""){
			$QtyInvoiceAll="0";
		}else{
			$QtyInvoiceAll=$InvoiceAll['0'];
		}

		//get qty invoice
		if($myrow['qtyinvoice']==""){
			$QtyInvoice="0";
		}else{
			$QtyInvoice=$myrow['qtyinvoice'];
		}

		//get On Hand all
		//find the quantity onhand item
		$sqloh="SELECT   sum(quantity)as qty
						FROM     `locstock`
						WHERE     stockid='" . $myrow['0'] . "'";
		$oh = db_query($sqloh,$db);
		$ohRow = db_fetch_row($oh);
		$ohRow[0];


		//get On Hand in Location
		$sqlohin="SELECT SUM(quantity) AS qty
						FROM `locstock`
						WHERE stockid='" . $myrow['0'] . "'
						AND locstock.loccode = '" . $_POST['StockLocation'] . "'";
		$ohin = db_query($sqlohin,$db);
		$ohinRow = db_fetch_row($ohin);

		echo'   <td>'.$myrow['0'].'</td>
						<td>'.$myrow['1'].'</td>
						<td class="number">'.$QtyInvoiceAll.'</td>
						<td class="number">'.$QtyInvoice.'</td>
						<td class="number">'.$ohRow['0'].'</td>
						<td class="number">'.$ohinRow['0'].'</td>
						<td><input type="text" class="number" name="'.$myrow['0'].'" MAXLENGTH =3 size=4 value="'.
							$reorder.'"></td>
					</tr> ';

	} //end of looping
	echo'<tr>
			<td style="text-align:center" colspan=7><input type=submit name=submit value=' . _("Update") . '></td>
			 </tr></form>';


} else { /*The option to submit was not hit so display form */


	echo '<div class="page_help_text">' . _('Use this report to display the reorder levels for Inventory items in different categories.') . '</div><br>';

	echo '</br></br><form action=' . $_SERVER['PHP_SELF'] . " method='post'><table>";
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$sql = "SELECT loccode,
				   locationname
		    FROM locations";
	$resultStkLocs = DB_query($sql,$db);
	echo '<table class=selection>
				<tr>
					<td>' . _('Location') . ':</td>
					<td><select name="StockLocation"> ';

	while ($myrow=DB_fetch_array($resultStkLocs)){

		echo '<option Value="' . $myrow['loccode'] . '">' . $myrow['locationname'];

	}
	echo '</select></td></tr>';

	$SQL="SELECT categoryid, categorydescription
	      FROM stockcategory
		  ORDER BY categorydescription";

	$result1 = DB_query($SQL,$db);

	echo '<tr><td>' . _('Category') . ':</td>
			<td><select name="StockCat">';

	while ($myrow1 = DB_fetch_array($result1)) {
		echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'];
	}

	echo '</select></td></tr>';
	echo'<tr>
				<td>' . _('Number Of Days Sales') . ':</td>
				<td><input type="text" class="number" name="NumberOfDays" MAXLENGTH =3 size=4 value=0></td>';
	echo '<tr><td>' . _('Order By') . ':</td>
				<td><select name="order">';
	echo '<option value="1">'. _('Total Invoiced'). '';
	echo '<option value="2">'. _('Code'). '';

	echo '</select></td></tr>';
	echo "</table></br><p><div class='centre'><input type=submit name='submit' value='" . _('Submit') . "'></div></p>";

} /*end of else not submit */
include('includes/footer.inc');
?>