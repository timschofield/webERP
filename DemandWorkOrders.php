<?php
// create work orders for all finished products with sales orders that exceed on hand and on work order quantities

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Demand Work Orders');

include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

$SQL = "SELECT stockmaster.stockid,
		stockmaster.description,
		SUM(locstock.quantity) AS qoh,
		stockmaster.units,
		stockmaster.eoq AS eoq
	FROM stockmaster,
		locstock
	WHERE stockmaster.stockid=locstock.stockid
	AND stockmaster.categoryid='F'
	GROUP BY stockmaster.stockid,
	stockmaster.description,
	stockmaster.units
	ORDER BY stockmaster.stockid";
$StockItemsResult = DB_query($SQL,$db,$ErrMsg,$DbgMsg);
echo '<table>';
echo '<tr><td class="label">Stock ID</td>';
echo '<td class="label">Work Order</td>';
echo '<td class="label">Description</td>';
echo '<td class="label">Quantity</td>';
echo '<td class="label">Picture</td>';
echo '<td class="label">Drawing</td></tr>';
while ($myrow=DB_fetch_array($StockItemsResult)) {
   $StockId 	= $myrow['stockid'];
   $StockQOH 	= $myrow['qoh']; 
   $StockEOQ	= $myrow['eoq']; 
   $StockDescription	= $myrow['description'];
	$StockQOWO = 0;
   $sql3 = "SELECT SUM(woitems.qtyreqd-woitems.qtyrecd) AS qtywo
			FROM woitems INNER JOIN workorders
			ON woitems.wo=workorders.wo
			WHERE workorders.closed=0
			AND woitems.stockid='" . $StockId . "'";
	$ErrMsg = _('The quantity on work orders for this product cannot be retrieved because');
	$QOOResult = DB_query($sql3,$db,$ErrMsg);
   if (DB_num_rows($QOOResult)==1){
				$QOORow = DB_fetch_row($QOOResult);
				$StockQOWO +=  $QOORow[0];
	}
	$StockDemand =0;
		$DemResult = DB_query("SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS demand,
										salesorderdetails.itemdue,
										salesorders.deliverydate		
                 					FROM salesorderdetails INNER JOIN salesorders
                 					ON salesorders.orderno = salesorderdetails.orderno
                 					WHERE salesorderdetails.completed=0
		 							AND salesorders.quotation=0
                 					AND salesorderdetails.stkcode='" . $StockId . "' 
                 					AND salesorderdetails.qtyinvoiced < salesorderdetails.quantity
                 					GROUP BY salesorderdetails.stkcode",
                 			$db);

   $DemRow = DB_fetch_row($DemResult);
   $StockDemand = $DemRow[0];
   $DemandDate  = $DemRow[1];
   If(strlen(trim($DemandDate)) == 0)
   {
   	$DemandDate  = $DemRow[2];
   }
   If($StockQOH < 0)
   {
   	$StockQOH = 0;
   }
   If($StockQOH + $StockQOWO - $StockDemand < 0)
   {
   	$DemandQuantity = $StockDemand - ($StockQOH + $StockQOWO);
   	If($StockEOQ > 0)
   	{
   		If($StockEOQ > $DemandQuantity)
   		{
   			$DemandQuantity = $StockEOQ;
   		}
   		else
   		{
   			$EOQMultiple = Round(($DemandQuantity/$StockEOQ)+.49,0);
   			$DemandQuantity = $StockEOQ*$EOQMultiple;
   		}
   	}
    	$WO = GetNextTransNo(30,$db);
    	// echo $WO. "= WO<br>";
    	$InsWOResult = DB_query("INSERT INTO workorders (wo,
                                                     loccode,
                                                     requiredby,
                                                     startdate)
                                     VALUES (" . $WO . ",
                                            '" . $_SESSION['UserStockLocation'] . "',
                                            '" . $DemandDate . "',
                                            '" . Date('Y-m-d'). "')",
                                            $db);

		$sql = "INSERT INTO woitems (wo,
	                             stockid,
	                             qtyreqd,
	                             stdcost)
	         VALUES ( " . $WO . ",
                         '" . $StockId . "',
                         " . $DemandQuantity . ",
                          0)";
		$result = DB_query($sql,$db,$ErrMsg);
		$sql2   = "INSERT INTO worequirements (wo,
                                            parentstockid,
                                            stockid,
                                            qtypu,
                                            stdcost,
                                            autoissue)
      	                 SELECT " . $WO . ",
        	                           bom.parent,
                                       bom.component,
                                       bom.quantity,
                                       (materialcost+labourcost+overheadcost)*bom.quantity,
                                       autoissue
                         FROM bom INNER JOIN stockmaster
                         ON bom.component=stockmaster.stockid
                         WHERE parent='" . $StockId . "'
                         AND loccode ='" . $_POST['StockLocation'] . "'";

		$result = DB_query($sql2,$db,$ErrMsg);
   	echo '<tr><td>' . $StockId  . '</td>';
   	echo '<td>' . $WO . '</td>';
   	echo '<td>' .$StockDescription . '</td>';
		echo '<td class="label">' . $DemandQuantity .'</td>';
		$PictureToDisplay = '/srv/www/htdocs/batavg/webERP2/companies/' . $_SESSION['DatabaseName'] . '/part_pics/' . $StockId . '.jpg' ;
		If(file_exists ( $PictureToDisplay))
	   {
			echo '<td class="label">OK</td>';
		}
		else
		{
			echo '<td class="label">Need Picture</td>';
		}
		$DrawingPath = "/srv/www/htdocs/batavg/" . $rootpath . "/companies/" . $_SESSION['DatabaseName'] . '/Drawings';

		$FileToDisplay = '';
		If($handle = opendir($DrawingPath))
	   {
	   	While(false !== ($file = readdir($handle)))
	   	{
	   	   If(strpos($file,$StockId))
	   	   {
	   	      $FileToDisplay = Trim($file);
	   	      break;
	   	   }
	   	   If(strlen(trim($FileToDisplay)) > 0)
	   	   {
	   	      break;
	   	   }
	   	}
	   }
	   If(file_exists ( $DrawingPath . "/" . $FileToDisplay) and strlen(trim($FileToDisplay)) > 0)
	   {
			echo '<td class="label">OK</td>';
		}
		else
		{
			echo '<td class="label">Need Drawing</td>';
		}
		echo '</tr>';
   }
}
echo '</table>';
echo '<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>';
include('includes/footer.inc');

?>