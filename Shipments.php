<?php

$title = "Shipments";
$PageSecurity = 11;

/* Session started in header.inc for password checking and authorisation level check */
include("includes/DefineShiptClass.php");
include("includes/DateFunctions.inc");
include("includes/SQL_CommonFunctions.inc");
include("includes/session.inc");
include("includes/header.inc");


if ($_GET['NewShipment']=="Yes"){
	unset($_SESSION['Shipment']->LineItems);
	unset($_SESSION['Shipment']);
}

if (!isset($_SESSION['SupplierID']) AND !isset($_SESSION['Shipment'])){
	echo "<BR>To set up a shipment, the supplier must first be selected from the Select Supplier page<BR><A HREF='$rootpath/SelectSupplier.php?" . SID . "'>Select the supplier now</A>";
	include ("includes/footer.inc");
	exit;
}

if (isset($_GET['SelectedShipment'])){

	 if (isset($_SESSION['Shipment'])){
              unset ($_SESSION['Shipment']->LineItems);
              unset ($_SESSION['Shipment']);
       }

       $_SESSION['Shipment'] = new Shipment;

       $CompanyRecord = ReadInCompanyRecord($db);
       $_SESSION['Shipment']->GLLink = $CompanyRecord["GLLink_Stock"];

/*read in all the guff from the selected shipment into the Shipment Class variable - the class code is included in the main script before this script is included  */

       $ShipmentHeaderSQL = "SELECT Shipments.SupplierID, Suppliers.SuppName, Shipments.ETA, Suppliers.CurrCode, Vessel, VoyageRef, Shipments.Closed FROM Shipments, Suppliers WHERE Shipments.SupplierID = Suppliers.SupplierID AND Shipments.ShiptRef = " . $_GET['SelectedShipment'];

       $GetShiptHdrResult = DB_query($ShipmentHeaderSQL,$db);
       if (DB_error_no($db) !=0) {
              echo "<BR>The shipment cannot be retrieved because - " . DB_error_msg($db);
              if ($debug==1){
                 echo "<BR>The SQL statement that was used and failed was:<BR>$ShipmentHeaderSQL";
              }
       } elseif (DB_num_rows($GetShiptHdrResult)==1) {

              $myrow = DB_fetch_array($GetShiptHdrResult);

	      if ($myrow["Closed"]==1){
			echo "<BR>The selected shipment is already closed no modifications to shipment " . $_GET['SelectedShipment'] . " are possible.";
			include("includes/footer.inc");
			exit;
	      }
              $_SESSION['Shipment']->ShiptRef = $_GET['SelectedShipment'];
              $_SESSION['Shipment']->SupplierID = $myrow["SupplierID"];
              $_SESSION['Shipment']->SupplierName = $myrow["SuppName"];
              $_SESSION['Shipment']->CurrCode = $myrow["CurrCode"];
              $_SESSION['Shipment']->ETA = $myrow["ETA"];
              $_SESSION['Shipment']->Vessel = $myrow["Vessel"];
              $_SESSION['Shipment']->VoyageRef = $myrow["VoyageRef"];



/*now populate the shipment details records */

              $LineItemsSQL = "SELECT PODetailItem, PurchOrders.OrderNo, ItemCode, ItemDescription, DeliveryDate, GLCode, QtyInvoiced, UnitPrice, Units, QuantityOrd, QuantityRecd, StdCostUnit, MaterialCost+LabourCost+OverheadCost AS StdCost, IntoStockLocation FROM PurchOrderDetails INNER JOIN StockMaster ON PurchOrderDetails.ItemCode=StockMaster.StockID INNER JOIN PurchOrders ON PurchOrderDetails.OrderNo=PurchOrders.OrderNo WHERE PurchOrderDetails.ShiptRef=" . $_GET['SelectedShipment'];

              $LineItemsResult = db_query($LineItemsSQL,$db);

              if (DB_error_no($db) !=0) {
                     echo "<BR>The lines on the shipment cannot be retrieved because - " . DB_error_msg($db);
                     if ($debug==1){
                           echo "<BR>The SQL statement that was used to retrieve the shipment lines was:<BR>$LineItemsSQL";
                     }
              } elseif (db_num_rows($LineItemsResult) > 0) {

			while ($myrow=db_fetch_array($LineItemsResult)) {

				if ($myrow['StdCostUnit']==0){
					$StandardCost = $myrow['StdCost'];
				} else {
					$StandardCost =$myrow['StdCostUnit'];
				}

				$_SESSION['Shipment']->LineItems[$myrow['PODetailItem']] = new LineDetails($myrow['PODetailItem'], $myrow['OrderNo'],$myrow['ItemCode'], $myrow["ItemDescription"], $myrow["QtyInvoiced"], $myrow["UnitPrice"],$myrow['Units'],$myrow["DeliveryDate"], $myrow["QuantityOrd"], $myrow["QuantityRecd"], $StandardCost);
		   } /* line Shipment from shipment details */

		   DB_data_Seek($LineItemsResult,0);
		   $myrow=DB_fetch_array($LineItemsResult);
		   $_SESSION['Shipment']->StockLocation = $myrow['IntoStockLocation'];

              } //end of checks on returned data set
       }
} // end of reading in the existing shipment


if (!isset($_SESSION['Shipment'])){

	$_SESSION['Shipment'] = new Shipment;

	$sql = "SELECT SuppName, CurrCode FROM Suppliers WHERE SupplierID='" . $_SESSION['SupplierID'] . "'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);

	$_SESSION['Shipment']->SupplierID = $_SESSION['SupplierID'];
	$_SESSION['Shipment']->SupplierName = $myrow[0];
	$_SESSION['Shipment']->CurrCode = $myrow[1];

	$_SESSION['Shipment']->ShiptRef = GetNextTransNo (31, $db);

}


if ($_POST['Update']=="Update Shipment Details" AND !$_SESSION['Shipment']->Closed==1) {

	$_SESSION['Shipment']->Vessel = $_POST['Vessel'];
	$_SESSION['Shipment']->VoyageRef = $_POST['VoyageRef'];

	$InputError =0;

	if (count($_SESSION['Shipment']->LineItems)==0){
		echo "<BR>There are no line items on this shipment. Select some of the purchase order lines by clicking add on the available purchase order lines shown below.";
		$InputError=1;
	}
	if (!Is_Date($_POST['ETA'])){
		$InputError=1;
		echo "<BR>The date of expected arrival of the shipment must be entered in the format $DefaultDateFormat";
	} elseif (Date1GreaterThanDate2($_POST['ETA'],Date($DefaultDateFormat))==0){
		$InputError=1;
		echo "<BR>An expected arrival of the shipment must be a date after today.";
	} else {
		$_SESSION['Shipment']->ETA = FormatDateForSQL($_POST['ETA']);
	}

	if (strlen($_POST['Vessel'])<2){
		echo "<BR><B>Warning:</B> A reference to the vessel of more than 2 characters is expected.";
	}
	if (strlen($_POST['VoyageRef'])<2){
		echo "<BR><B>Warning:</B> A reference to the voyage (or HAWB in the case of air-freight) of more than 2 characters is expected.";
	}

/*The user hit the update the shipment button */
	if ($InputError ==0) {


		$sql = "SELECT ShiptRef FROM Shipments WHERE ShiptRef =" . $_SESSION['Shipment']->ShiptRef;

		$result = DB_query($sql,$db);

		if (DB_num_rows($result)==1){

			$sql = "UPDATE Shipments SET Vessel='" . $_SESSION['Shipment']->Vessel . "', VoyageRef='".  $_SESSION['Shipment']->VoyageRef . "', ETA='" .  $_SESSION['Shipment']->ETA . "' WHERE ShiptRef =" .  $_SESSION['Shipment']->ShiptRef;

		} else {

			$sql = "INSERT INTO Shipments (ShiptRef, Vessel, VoyageRef, ETA, SupplierID) VALUES (" . $_SESSION['Shipment']->ShiptRef . ", '" . $_SESSION['Shipment']->Vessel . "', VoyageRef='".  $_SESSION['Shipment']->VoyageRef . "', '" . $_SESSION['Shipment']->ETA . "', '" . $_SESSION['Shipment']->SupplierID . "')"  ;

		}
		/*now update or insert as necessary */
		$result = DB_query($sql,$db);

		/*now check that the delivery date of all PODetails are the same as the ETA as the shipment */
		foreach ($_SESSION['Shipment']->LineItems as $LnItm) {

			if (DateDiff(ConvertSQLDate($LnItm->DelDate),ConvertSQLDate($_SESSION['Shipment']->ETA),'d')!=0){

				$sql = "UPDATE PurchOrderDetails SET DeliveryDate ='" . $_SESSION['Shipment']->ETA . "' WHERE PODetailItem=" . $LnItm->PODetailItem;

				$result = DB_query($sql,$db);

				$_SESSION['Shipment']->LineItems[$LnItm->PODetailItem]->DelDate = $_SESSION['Shipment']->ETA;

			}
		}
   		echo "<BR>Updated the shipment record and delivery dates of order lines as necessary";
	}
}


if (isset($_GET['Delete']) AND ! $_SESSION['Shipment']->Closed==1){
	$_SESSION['Shipment']->remove_from_shipment($_GET['Delete'],$db);
}

if (isset($_GET['Add']) AND ! $_SESSION['Shipment']->Closed==1){

	$sql = "SELECT OrderNo, ItemCode, ItemDescription, UnitPrice, StdCostUnit, MaterialCost+LabourCost+OverheadCost AS StdCost, QuantityOrd, QuantityRecd, DeliveryDate, Units, QtyInvoiced FROM PurchOrderDetails INNER JOIN StockMaster ON PurchOrderDetails.ItemCode=StockMaster.StockID WHERE PODetailItem=" . $_GET['Add'];

	$result = DB_query($sql,$db);
	$myrow = DB_fetch_array($result);

/*The variable StdCostUnit gets set when the item is first received and stored for all future transactions with this purchase order line - subsequent changes to the standard cost will not therefore stuff up variances resulting from the line which may have several entries in GL for each delivery drop if it has already been set from a delivery then use it otherwise use the current system standard */

	if ($myrow['StdCostUnit']==0){
		$StandardCost = $myrow['StdCost'];
	}else {
		$StandardCost = $myrow['StdCostUnit'];
	}

	$_SESSION['Shipment']->add_to_shipment($_GET['Add'], $myrow['OrderNo'], $myrow['ItemCode'], $myrow['ItemDescription'], $myrow['QtyInvoiced'], $myrow['UnitPrice'], $myrow['Units'], $myrow['DeliveryDate'], $myrow['QuantityOrd'], $myrow['QuantityRecd'], $StandardCost, $db);

}

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?" . SID . "' METHOD=POST>";

echo "<CENTER><TABLE><TR><TD><B>Shipment: </TD><TD><B>" . $_SESSION['Shipment']->ShiptRef . "</B></TD><TD><B>From " . $_SESSION['Shipment']->SupplierName . "</B></TD></TR>";

echo "<TR><TD>Vessel: </TD><TD COLSPAN=3><INPUT TYPE=Text NAME='Vessel' MAXLENGTH=50 SIZE=50 VALUE='" . $_SESSION['Shipment']->Vessel . "'></TD><TD>Voyage Ref: </TD><TD><INPUT TYPE=Text NAME='VoyageRef' MAXLENGTH=20 SIZE=20 VALUE='" . $_SESSION['Shipment']->VoyageRef . "'></TD></TR>";

if (isset($_SESSION['Shipment']->ETA)){
	$ETA = ConvertSQLDate($_SESSION['Shipment']->ETA);
} else {
	$ETA ="";
}

echo "<TR><TD>Expected Arrival Date (ETA): </TD><TD><INPUT TYPE=Text NAME='ETA' MAXLENGTH=10 SIZE=10 VALUE=" . $ETA . "></TD><TD>Into ";

if (count($_SESSION['Shipment']->LineItems)>0){

   if (!isset($_SESSION['Shipment']->StockLocation)){

	$sql = "SELECT IntoStockLocation FROM PurchOrders INNER JOIN PurchOrderDetails ON PurchOrders.OrderNo=PurchOrderDetails.OrderNo AND PODetailItem = " . key($_SESSION['Shipment']->LineItems);

	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);

	$_SESSION['Shipment']->StockLocation = $myrow[0];
	$_POST['StockLocation']=$_SESSION['Shipment']->StockLocation;

   } else {

	$_POST['StockLocation']=$_SESSION['Shipment']->StockLocation;
   }
}


if (!isset($_SESSION['Shipment']->StockLocation)){

	echo "Stock Location: <SELECT name='StockLocation'>";

	$sql = "SELECT LocCode, LocationName FROM Locations";

	$resultStkLocs = DB_query($sql,$db);

	while ($myrow=DB_fetch_array($resultStkLocs)){

		if (isset($_POST['StockLocation'])){
			if ($myrow["LocCode"] == $_POST['StockLocation']){
				echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
			} else {
				echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
			}
		} elseif ($myrow["LocCode"]==$_SESSION['UserStockLocation']){
			echo "<OPTION SELECTED Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		} else {
			echo "<OPTION Value='" . $myrow["LocCode"] . "'>" . $myrow["LocationName"];
		}
	}

	if (!isset($_POST['StockLocation'])){
		$_POST['StockLocation'] = $_SESSION['UserStockLocation'];
	}

	echo "</SELECT>";

} else {
	$sql = "SELECT LocationName FROM Locations WHERE LocCode='" . $_SESSION['Shipment']->StockLocation . "'";
	$resultStkLocs = DB_query($sql,$db);
	$myrow=DB_fetch_array($resultStkLocs);
 	echo $myrow['LocationName'];
}


echo "</TD></TR></TABLE>";

/* Always display all shipment lines */

echo "<B><FONT COLOR=BLUE>Order Lines On This Shipment</FONT></B>";
echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>";

$TableHeader = "<TR><TD class='tableheader'>Order</TD><TD class='tableheader'>Item</TD><TD class='tableheader'>Quantity<BR>Ordered</TD><TD class='tableheader'>Units</TD><TD class='tableheader'>Quantity<BR>Received</TD><TD class='tableheader'>Quantity<BR>Invoiced</TD><TD class='tableheader'>" . $_SESSION['Shipment']->CurrCode ." Price</TD><TD class='tableheader'>Current<BR>Std Cost</TD></TR>";

echo  $TableHeader;

/*show the line items on the shipment with the quantity being received for modification */

$k=0; //row colour counter
$RowCounter =0;

if (count($_SESSION['Shipment']->LineItems)>0){

   foreach ($_SESSION['Shipment']->LineItems as $LnItm) {

	if ($RowCounter==15){
		echo $TableHeader;
		$RowCounter =0;
	}
	$RowCounter++;

	if ($k==1){
		echo "<tr bgcolor='#CCCCCC'>";
		$k=0;
	} else {
		echo "<tr bgcolor='#EEEEEE'>";
		$k=1;
	}


	echo "<TD>$LnItm->OrderNo</TD><TD>$LnItm->StockID - $LnItm->ItemDescription</TD><TD ALIGN=RIGHT>" . number_format($LnItm->QuantityOrd,2) . "</TD><TD>$LnItm->UOM</TD><TD ALIGN=RIGHT>" . number_format($LnItm->QuantityRecd,2) . "</TD><TD ALIGN=RIGHT>" . number_format($LnItm->QtyInvoiced,2) . "</TD><TD ALIGN=RIGHT>" . number_format($LnItm->UnitPrice,2) . "</TD><TD ALIGN=RIGHT>" . number_format($LnItm->StdCostUnit,2) . "</TD></TD><TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Delete=" . $LnItm->PODetailItem . "'>Delete</A></TD></TR>";

   }
}

echo "</TABLE>";

echo "<INPUT TYPE=SUBMIT NAME=Update Value='Update Shipment Details'><P>";

echo "<HR>";

$sql = "SELECT PODetailItem, PurchOrders.OrderNo, ItemCode, ItemDescription, UnitPrice, QuantityOrd, QuantityRecd, DeliveryDate, Units FROM PurchOrderDetails INNER JOIN PurchOrders ON PurchOrderDetails.OrderNo=PurchOrders.OrderNo INNER JOIN StockMaster ON PurchOrderDetails.ItemCode=StockMaster.StockID WHERE QtyInvoiced=0 AND PurchOrders.SupplierNo ='" . $_SESSION['Shipment']->SupplierID . "' AND ShiptRef=0 AND PurchOrders.IntoStockLocation='" . $_POST['StockLocation'] . "'";

$result = DB_query($sql,$db);

if (DB_num_rows($result)>0){

	echo "<B><FONT COLOR=BLUE>Possible Order Lines To Add To This Shipment</FONT></B>";
	echo "<TABLE CELLPADDING=2 COLSPAN=7 BORDER=0>";

	$TableHeader = "<TR><TD class='tableheader'>Order</TD><TD class='tableheader'>Item</TD><TD class='tableheader'>Quantity<BR>Ordered</TD><TD class='tableheader'>Units</TD><TD class='tableheader'>Quantity<BR>Received</TD><TD class='tableheader'>Delivery<BR>Date</TD></TR>";

	echo  $TableHeader;

	/*show the PO items that could be added to the shipment */

	$k=0; //row colour counter
	$RowCounter =0;

	while ($myrow=DB_fetch_array($result)){

		if ($RowCounter==15){
			echo $TableHeader;
			$RowCounter =0;
		}
		$RowCounter++;

		if ($k==1){
			echo "<tr bgcolor='#CCCCCC'>";
			$k=0;
		} else {
			echo "<tr bgcolor='#EEEEEE'>";
			$k=1;
		}

		echo "<TD>" . $myrow['OrderNo'] . "</TD><TD>" . $myrow['ItemCode'] . " - " . $myrow['ItemDescription'] . "</TD><TD ALIGN=RIGHT>" . number_format($myrow['QuantityOrd'],2) . "</TD><TD>" . $myrow['Units'] . "</TD><TD ALIGN=RIGHT>" . number_format($myrow['QuantityRecd'],2) . "</TD><TD ALIGN=RIGHT>" . ConvertSQLDate($myrow['DeliveryDate']) . "</TD><TD><A HREF='" . $_SERVER['PHP_SELF'] . "?" . SID . "Add=" . $myrow['PODetailItem'] . "'>Add</A></TD></TR>";

	}
	echo "</TABLE>";
}

echo "</form>";

include("includes/footer.inc");
?>
