<?php
/* $Revision: 1.7 $ */

$PageSecurity = 2; /*viewing possible with inquiries but not mods */

$UpdateSecurity =10;

include('includes/session.inc');
$title = _('Stock Cost Update');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['StockID'])){
	$StockID =$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$StockID =$_POST['StockID'];
}

if (isset($_POST['UpdateData'])){
 	$OldCost =$_POST['OldMaterialCost'] + $_POST['OldLabourCost'] + $_POST['OldOverheadCost'];
   	$NewCost =$_POST['MaterialCost'] + $_POST['LabourCost'] + $_POST['OverheadCost'];

	$result = DB_query("SELECT * FROM stockmaster WHERE stockid='$StockID'",$db);
	$myrow = DB_fetch_row($result);
	if (DB_num_rows($result)==0) {
		prnMsg (_('The entered item code does not exist'),'error',_('Non-existent Item'));
	} elseif ($OldCost != $NewCost){

		$Result = DB_query('BEGIN',$db);

		if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $_POST['QOH']!=0){

			$CostUpdateNo = GetNextTransNo(35, $db);
			$PeriodNo = GetPeriod(Date("d/m/Y"), $db);
			$StockGLCode = GetStockGLCode($StockID,$db);

			$ValueOfChange = $_POST['QOH'] * ($NewCost - $OldCost);

			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (35,
							" . $CostUpdateNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $StockGLCode['adjglact'] . ",
							'" . $StockID . ' ' . _('cost was') . ' ' . $OldCost . ' ' . _('changed to') . ' ' . $NewCost . ' x ' . _('Quantity on hand of') . ' ' . $_POST['QOH'] . "',
							" . (-$ValueOfChange) . ")";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL credit for the stock cost adjustment posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

			$SQL = "INSERT INTO gltrans (type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount)
						VALUES (35,
							" . $CostUpdateNo . ",
							'" . Date('Y-m-d') . "',
							" . $PeriodNo . ",
							" . $StockGLCode['stockact'] . ",
							'" . $StockID . ' ' . _('cost was') . ' ' . $OldCost . ' ' . _('changed to') .' ' . $NewCost . ' x ' . _('Quantity on hand of') . ' ' . $_POST['QOH'] . "',
							" . $ValueOfChange . ")";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL debit for stock cost adjustment posting could not be inserted because');
			$DbgMsg = _('The following SQL to insert the GLTrans record was used');
			$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}

		$SQL = "UPDATE stockmaster SET
					materialcost=" . $_POST['MaterialCost'] . ",
					labourcost=" . $_POST['LabourCost'] . ",
					overheadcost=" . $_POST['OverheadCost'] . ",
					lastcost=" . $OldCost . "
			WHERE stockid='" . $StockID . "'";

		$ErrMsg = _('The cost details for the stock item could not be updated because');
		$DbgMsg = _('The SQL that failed was');
		$Result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);

		$Result = DB_query('COMMIT',$db);

   	}
}

$ErrMsg = _('The cost details for the stock item could not be retrieved because');
$DbgMsg = _('The SQL that failed was');

$result = DB_query("SELECT description,
			units,
			lastcost,
			actualcost,
			materialcost,
			labourcost,
			overheadcost,
			mbflag,
			sum(quantity) as totalqoh
		FROM stockmaster INNER JOIN locstock
			ON stockmaster.stockid=locstock.stockid
		WHERE stockmaster.stockid='$StockID'
		GROUP BY description,
			units,
			lastcost,
			actualcost,
			materialcost,
			labourcost,
			overheadcost,
			mbflag",
		$db,$ErrMsg,$DbgMsg);


$myrow = DB_fetch_array($result);
echo "<BR><FONT COLOR=BLUE SIZE=3><B>" . $StockID . " - " . $myrow['description'] . '</B> - ' . _('Total Quantity On Hand') . ': ' . $myrow['totalqoh'] . " " . $myrow['units'] ."</FONT>";

echo "<FORM ACTION='" . $_SERVER['PHP_SELF'] . "?". SID ."' METHOD=POST>";
echo _('Stock Code') . ":<input type=text name='StockID' value='$StockID' 1 maxlength=20>";

echo " <INPUT TYPE=SUBMIT NAME='Show' VALUE='" . _('Show Cost Details') . "'><HR>";

if ($myrow['mbflag']=='D' 
	OR $myrow['mbflag']=='A' 
	OR $myrow['mbflag']=='K'){

   if ($myrow['mbflag']=='D'){
      	
	echo "<BR>$StockID " . _('is a dummy part');
   } else if ($myrow['mbflag']=='A'){
      
   	echo "<BR>$StockID " . _('is an assembly part');
   } else if ($myrow['mbflag']=='K'){
   
   	echo "<BR>$StockID " . _('is a kit set part');
   }
   prnMsg(_('Cost information cannot be modified for kits assemblies or dummy parts') . '. ' . _('Please select a different part'),'warn');
   include('includes/footer.inc');
   exit;
}

echo '<INPUT TYPE=HIDDEN NAME=OldMaterialCost VALUE=' . $myrow['materialcost'] .'>';
echo '<INPUT TYPE=HIDDEN NAME=OldLabourCost VALUE=' . $myrow['labourcost'] .'>';
echo '<INPUT TYPE=HIDDEN NAME=OldOverheadCost VALUE=' . $myrow['overheadcost'] .">";
echo '<INPUT TYPE=HIDDEN NAME=QOH VALUE=' . $myrow['totalqoh'] .'>';

echo '<CENTER><TABLE CELLPADDING=2 BORDER=2>';
echo '<TR><TD>' . _('Last Cost') .':</TD><TD ALIGN=RIGHT>' . number_format($myrow['lastcost'],2) . '</TD></TR>';
if (! in_array($UpdateSecurity,$_SESSION['AllowedPageSecurityTokens']) OR !isset($UpdateSecurity)){
	echo '<TR><TD>' . _('Cost') . ':</TD><TD ALIGN=RIGHT>' . number_format($myrow['materialcost']+$myrow['labourcost']+$myrow['overheadcost'],2) . '</TD></TR></TABLE>';
} else {
	echo '<TR><TD>' . _('Standard Material Cost Per Unit') .':</TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT NAME=MaterialCost VALUE=' . $myrow['materialcost'] . '></TD></TR>';

	if ($myrow['mbflag']=='M'){
		echo '<TR><TD>' . _('Standard Labour Cost Per Unit') . ':</TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT NAME=LabourCost VALUE=' . $myrow['labourcost'] . '></TD></TR>';
		echo '<TR><TD>' . _('Standard Overhead Cost Per Unit') . ':</TD><TD ALIGN=RIGHT><INPUT TYPE=TEXT NAME=OverheadCost VALUE=' . $myrow['overheadcost'] . '></TD></TR>';
	} else {
		echo '<INPUT TYPE=HIDDEN NAME=LabourCost VALUE=0>';
		echo '<INPUT TYPE=HIDDEN NAME=OverheadCost VALUE=0>';
	}

echo "</TABLE><INPUT TYPE=SUBMIT NAME='UpdateData' VALUE='" . _('Update') . "'><HR>";
}
echo "<A HREF='$rootpath/StockStatus.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Status') . '</A>';
echo "<BR><A HREF='$rootpath/StockMovements.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Movements') . '</A>';
echo "<BR><A HREF='$rootpath/StockUsage.php?" . SID . "&StockID=$StockID'>" . _('Show Stock Usage')  .'</A>';
echo "<BR><A HREF='$rootpath/SelectSalesOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Search Outstanding Sales Orders') . '</A>';
echo "<BR><A HREF='$rootpath/SelectCompletedOrder.php?" . SID . "&SelectedStockItem=$StockID'>" . _('Search Completed Sales Orders') . '</A>';

echo '</FORM>';
include('includes/footer.inc');
?>