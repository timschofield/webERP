<?php
/* $Revision: 1.4 $ */

$PageSecurity = 11;

include('includes/session.inc');
$title = _('Search Shipments');
include('includes/header.inc');
include('includes/DateFunctions.inc');

if (isset($_GET['SelectedStockItem'])){
	$SelectedStockItem=$_GET['SelectedStockItem'];
} elseif (isset($_POST['SelectedStockItem'])){
	$SelectedStockItem=$_POST['SelectedStockItem'];
}

if (isset($_GET['ShiptRef'])){
	$ShiptRef=$_GET['ShiptRef'];
} elseif (isset($_POST['ShiptRef'])){
	$ShiptRef=$_POST['ShiptRef'];
}

if (isset($_GET['SelectedSupplier'])){
	$SelectedSupplier=$_GET['SelectedSupplier'];
} elseif (isset($_POST['SelectedSupplier'])){
	$SelectedSupplier=$_POST['SelectedSupplier'];
}

echo '<FORM ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '" METHOD=POST>';


If ($_POST['ResetPart']){
     unset($SelectedStockItem);
}

If (isset($ShiptRef) && $ShiptRef!="") {
	if (!is_numeric($ShiptRef)){
		  echo '<BR>';
		  prnMsg( _('The Shipment Number entered MUST be numeric') );
		  unset ($ShiptRef);
	} else {
		echo _('Shipment Number'). ' - '. $ShiptRef;
	}
} else {
	If ($SelectedSupplier) {
		echo _('For supplier'). ': '. $SelectedSupplier . ' ' . _('and'). ' ';
		echo '<input type=hidden name="SelectedSupplier" value="'. $SelectedSupplier. '">';
	}
	If ($SelectedStockItem) {
		 echo _('for the part'). ': ' . $SelectedStockItem . '.';
		echo '<input type=hidden name="SelectedStockItem" value="'. $SelectedStockItem. '">';
	}
}

if ($_POST['SearchParts']){

	If ($_POST['Keywords'] AND $_POST['StockCode']) {
		echo '<BR>';
		prnMsg( _('Stock description keywords have been used in preference to the Stock code extract entered'),'info');
	}
	$SQL = "SELECT StockMaster.StockID,
			Description,
			Sum(LocStock.Quantity) AS QOH,
			Units,
			Sum(PurchOrderDetails.QuantityOrd-PurchOrderDetails.QuantityRecd) AS QORD
		FROM StockMaster INNER JOIN LocStock
			ON StockMaster.StockID = LocStock.StockID
		INNER JOIN PurchOrderDetails
			ON StockMaster.StockID=PurchOrderDetails.ItemCode";

	If ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$i=0;
		$SearchString = '%';
		while (strpos($_POST['Keywords'], ' ', $i)) {
			$wrdlen=strpos($_POST['Keywords'],' ',$i) - $i;
			$SearchString=$SearchString . substr($_POST['Keywords'],$i,$wrdlen) . '%';
			$i=strpos($_POST['Keywords'],' ',$i) +1;
		}
		$SearchString = $SearchString. substr($_POST['Keywords'],$i).'%';

		$SQL .= " WHERE PurchOrderDetails.ShiptRef<>''
			AND PurchOrderDetails.ShiptRef<>0
			AND StockMaster.Description LIKE '$SearchString'
			AND CategoryID='" . $_POST['StockCat'] . "'";

	 } elseif ($_POST['StockCode']){

		$SQL .= " WHERE PurchOrderDetails.ShiptRef<>''
			AND PurchOrderDetails.ShiptRef<>0
			AND StockMaster.StockID like '%" . $_POST['StockCode'] . "%'
			AND CategoryID='" . $_POST['StockCat'];

	 } elseif (!$_POST['StockCode'] AND !$_POST['Keywords']) {
		$SQL .= " WHERE PurchOrderDetails.ShiptRef<>''
			AND PurchOrderDetails.ShiptRef<>0
			AND CategoryID='" . $_POST['StockCat'] . "'";

	 }
	$SQL .= "  GROUP BY StockMaster.StockID,
				Description,
				Units
		 	ORDER BY StockMaster.StockID";

	$ErrMsg = _('No Stock Items were returned from the database because'). ' - '. DB_error_msg($db);
	$StockItemsResult = DB_query($SQL,$db, $ErrMsg);

}


if ($ShiptRef=="" OR !isset($ShiptRef)){

	echo _('Shipment Number'). ': <INPUT type=text name="ShiptRef" MAXLENGTH =10 SIZE=10> '.
		_('Into Stock Location').' :<SELECT name="StockLocation"> ';
	$sql = "SELECT LocCode, LocationName FROM Locations";
	$resultStkLocs = DB_query($sql,$db);
	while ($myrow=DB_fetch_array($resultStkLocs)){
		if (isset($_POST['StockLocation'])){
			if ($myrow['LocCode'] == $_POST['StockLocation']){
			echo '<OPTION SELECTED Value="' . $myrow['LocCode'] . '">' . $myrow['LocationName'];
			} else {
			echo '<OPTION Value="' . $myrow['LocCode'] . '">' . $myrow['LocationName'];
			}
		} elseif ($myrow['LocCode']==$_SESSION['UserStockLocation']){
			$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
			echo '<OPTION SELECTED Value="' . $myrow['LocCode'] . '">' . $myrow['LocationName'];
		} else {
			echo '<OPTION Value="' . $myrow['LocCode'] . '">' . $myrow['LocationName'];
		}
	}

	echo '</SELECT>';
	echo ' <SELECT NAME="OpenOrClosed">';
	if ($_POST['OpenOrClosed']==1){
		echo '<OPTION SELECTED VALUE=1>'. _('Closed Shipments Only');
		echo '<OPTION VALUE=0>'. _('Open Shipments Only');
	} else {
		$_POST['OpenOrClosed']=0;
		echo '<OPTION VALUE=1>'. _('Closed Shipments Only');
		echo '<OPTION SELECTED VALUE=0>'. _('Open Shipments Only');
	}
	echo '</SELECT>';

	echo '<BR><CENTER><INPUT TYPE=SUBMIT NAME="SearchShipments" VALUE="'. _('Search Shipments'). '">';
}

$SQL="SELECT CategoryID, CategoryDescription FROM StockCategory WHERE StockType<>'D' ORDER BY CategoryDescription";
$result1 = DB_query($SQL,$db);

?>

<HR>
<FONT SIZE=1><?php echo _('To search for shipments for a specific part use the part selection facilities below');?></FONT>
<INPUT TYPE=SUBMIT NAME="SearchParts" VALUE="<?php echo _('Search Parts Now');?>">
<INPUT TYPE=SUBMIT NAME="ResetPart" VALUE="<?php echo _('Clear Part Selection');?>">
<TABLE>
<TR>
<TD><FONT SIZE=1><?php echo _('Select a stock category');?>:</FONT>
<SELECT NAME="StockCat">
<?php
while ($myrow1 = DB_fetch_array($result1)) {
	if ($myrow1['CategoryID']==$_POST['StockCat']){
		echo '<OPTION SELECTED VALUE="'. $myrow1['CategoryID'] . '">' . $myrow1['CategoryDescription'];
	} else {
		echo '<OPTION VALUE="'. $myrow1['CategoryID'] . '">' . $myrow1['CategoryDescription'];
	}
}
?>
</SELECT>
<TD><FONT SIZE=1><?php echo _('Enter text extracts in the');?> <B><?php echo _('description');?></B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="Keywords" SIZE=20 MAXLENGTH=25></TD></TR>
<TR><TD></TD>
<TD><FONT SIZE 3><B><?php echo _('OR');?> </B></FONT><FONT SIZE=1><?php echo _('Enter extract of the');?> <B><?php echo _('Stock Code');?></B>:</FONT></TD>
<TD><INPUT TYPE="Text" NAME="StockCode" SIZE=15 MAXLENGTH=18></TD>
</TR>
</TABLE>

<HR>

<?php

If (isset($StockItemsResult)) {

	echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=2>";
	$TableHeader = '<TR>
			<TD class="tableheader">'. _('Code').'</TD>
			<TD class="tableheader">'. _('Description').'</TD>
			<TD class="tableheader">'. _('On Hand').'</TD>
			<TD class="tableheader">'. _('Orders') . '<BR>' . _('Outstanding').'</TD>
			<TD class="tableheader">'. _('Units').'</TD>
			</TR>';

	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($StockItemsResult)) {

		if ($k==1){
			echo '<tr bgcolor="#CCCCCC">';
			$k=0;
		} else {
			echo '<tr bgcolor="#EEEEEE">';
			$k=1;
		}
/*
Code	 Description	On Hand		 Orders Ostdg     Units		 Code	Description 	 On Hand     Orders Ostdg	Units	 */
		printf('<td><INPUT TYPE=SUBMIT NAME="SelectedStockItem" VALUE="%s"</td>
			<td>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td ALIGN=RIGHT>%s</td>
			<td>%s</td></tr>',
			$myrow['StockID'], $myrow['Description'], $myrow['QOH'], $myrow['QORD'],$myrow['Units']);

		$j++;
		If ($j == 15){
			$j=1;
			echo $TableHeader;
		}
//end of page full new headings if
	}
//end of while loop

	echo '</TABLE>';

}
//end if stock search results to show
  else {

	//figure out the SQL required from the inputs available

	if (isset($ShiptRef) && $ShiptRef !="") {
		$SQL = "SELECT Shipments.ShiptRef, Vessel, VoyageRef, Suppliers.SuppName, Shipments.ETA, Closed
			FROM Shipments INNER JOIN Suppliers
				ON Shipments.SupplierID = Suppliers.SupplierID
			WHERE Shipments.ShiptRef=". $ShiptRef;
	} else {
		$SQL = "SELECT DISTINCT Shipments.ShiptRef, Vessel, VoyageRef, Suppliers.SuppName, Shipments.ETA, Shipments.Closed
			FROM Shipments INNER JOIN Suppliers
				ON Shipments.SupplierID = Suppliers.SupplierID
			INNER JOIN PurchOrderDetails
				ON PurchOrderDetails.ShiptRef=Shipments.ShiptRef
			INNER JOIN PurchOrders
				ON PurchOrderDetails.OrderNo=PurchOrders.OrderNo
			";

		if (isset($SelectedSupplier)) {

			if (isset($SelectedStockItem)) {
					$SQL .= " WHERE PurchOrderDetails.ItemCode='". $SelectedStockItem ."'
						AND Shipments.SupplierID='" . $SelectedSupplier ."'
						AND PurchOrders.IntoStockLocation = '". $_POST['StockLocation'] . "'
						AND Shipments.Closed=" . $_POST['OpenOrClosed'];
			} else {
				$SQL .= "WHERE Shipments.SupplierID='" . $SelectedSupplier ."'
					AND PurchOrders.IntoStockLocation = '". $_POST['StockLocation'] . "'
					AND Shipments.Closed=" . $_POST['OpenOrClosed'];
			}
		} else { //no supplier selected
			if (isset($SelectedStockItem)) {
				$SQL .= "WHERE PurchOrderDetails.ItemCode='". $SelectedStockItem ."'
					AND PurchOrders.IntoStockLocation = '". $_POST['StockLocation'] . "'
					AND Shipments.Closed=" . $_POST['OpenOrClosed'];
			} else {
				$SQL .= "WHERE PurchOrders.IntoStockLocation = '". $_POST['StockLocation'] . "'
					AND Shipments.Closed=" . $_POST['OpenOrClosed'];
			}

		} //end selected supplier
	} //end not order number selected

	$ErrMsg = _('No shipments were returned by the SQL because');
	$ShipmentsResult = DB_query($SQL,$db,$ErrMsg);


	if (DB_num_rows($ShipmentsResult)>0){
		/*show a table of the shipments returned by the SQL */

		echo '<TABLE CELLPADDING=2 COLSPAN=7 WIDTH=100%>';
		$TableHeader = '<TR>
				<TD class="tableheader">'. _('Shipment'). '</TD>
				<TD class="tableheader">'. _('Supplier'). '</TD>
				<TD class="tableheader">'. _('Vessel'). '</TD>
				<TD class="tableheader">'. _('Voyage'). '</TD>
				<TD class="tableheader">'. _('Expected Arrival'). '</TD>
				</TR>';

		echo $TableHeader;

		$j = 1;
		$k=0; //row colour counter
		while ($myrow=DB_fetch_array($ShipmentsResult)) {


			if ($k==1){ /*alternate bgcolour of row for highlighting */
				echo '<tr bgcolor="#CCCCCC">';
				$k=0;
			} else {
				echo '<tr bgcolor="#EEEEEE">';
				$k++;
			}

			$URL_Modify_Shipment = $rootpath . '/Shipments.php?' . SID . 'SelectedShipment=' . $myrow['ShiptRef'];
			$URL_View_Shipment = $rootpath . '/ShipmentCosting.php?' . SID . 'SelectedShipment=' . $myrow['ShiptRef'];

			$FormatedETA = ConvertSQLDate($myrow['ETA']);
			/* ShiptRef   Supplier  Vessel  Voyage  ETA */

			if ($myrow['Closed']==0){

				$URL_Close_Shipment = $URL_View_Shipment . '&Close=Yes';

				printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><A HREF="%s">'._('Costing').'</A></td>
					<td><A HREF="%s">'._('Modify').'</A></td>
					<td><A HREF="%s"><B>'._('Close').'</B></A></td>
					</tr>', $myrow['ShiptRef'], $myrow['SuppName'], $myrow['Vessel'], $myrow['Voyage'],
						$FormatedETA, $URL_View_Shipment, $URL_Modify_Shipment,$URL_Close_Shipment);

			} else {
				printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><A HREF="%s">'._('Costing').'</A></td>
					</tr>', $myrow['ShiptRef'], $myrow['SuppName'], $myrow['Vessel'], $myrow['Voyage'],
						$FormatedETA, $URL_View_Shipment);
			}
			$j++;
			If ($j == 15){
				$j=1;
				echo $TableHeader;
			}
		//end of page full new headings if
		}
		//end of while loop

		echo '</TABLE>';
	} // end if shipments to show
}

echo '</form>';
include('includes/footer.inc');
?>
