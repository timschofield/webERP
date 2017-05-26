<?php

/* $Id: StockReorderLevel.php 6941 2014-10-26 23:18:08Z daintree $*/

include('includes/session.php');
$Title = _('Automatic Setting of Stock Re-Order Level');
include('includes/header.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}else{
	$StockID = '';
}

if (isset($_GET['TypeOfShop'])){
	$TypeOfShop = trim(mb_strtoupper($_GET['TypeOfShop']));
} elseif (isset($_POST['TypeOfShop'])){
	$TypeOfShop = trim(mb_strtoupper($_POST['TypeOfShop']));
}else{
	$TypeOfShop = '';
}

if (isset($_GET['RL'])){
	$RL = trim(mb_strtoupper($_GET['RL']));
} elseif (isset($_POST['RL'])){
	$RL = trim(mb_strtoupper($_POST['RL']));
}else{
	$RL = 0;
}

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b>' . $Title. '</b>
	</p>';

$result = DB_query("SELECT description, 
							units 
					FROM stockmaster 
					WHERE stockid='" . $StockID . "'");
$myrow = DB_fetch_row($result);

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$sql = "SELECT locstock.loccode,
				locations.locationname,
				locations.typeloc,
				locstock.quantity,
				locstock.reorderlevel,
				stockmaster.decimalplaces
		FROM locstock 
		INNER JOIN locations
			ON locstock.loccode=locations.loccode
		INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
		INNER JOIN stockmaster
			ON locstock.stockid=stockmaster.stockid
		WHERE locstock.stockid = '" . $StockID . "'
		ORDER BY locations.locationname";

$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that failed was');

$LocStockResult = DB_query($sql, $ErrMsg, $DbgMsg);

echo '<table class="selection">';
echo '<tr>
		<th colspan="3"><h3><b>' . $StockID . ' - ' . $myrow[0] . '</b>  (' . _('In Units of') . ' ' . $myrow[1] . ')</h3></th>
	</tr>';

$TableHeader = '<tbody><tr>
					<th class="ascending">' . _('Location') . '</th>
					<th class="ascending">' . _('Quantity On Hand') . '</th>
					<th class="ascending">' . _('Re-Order Level') . '</th>
				</tr>';

echo $TableHeader;
$k=0; //row colour counter

while ($myrow=DB_fetch_array($LocStockResult)) {

	if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k=1;
	}

	if($myrow['typeloc'] == $TypeOfShop){
		$sql = "UPDATE locstock SET reorderlevel = '" . $RL . "'
			WHERE stockid = '" . $StockID . "'
			AND loccode = '"  . $myrow['loccode'] ."'";
		$UpdateReorderLevel = DB_query($sql);
		$NewRL = $RL;
	}else{
		$NewRL = $myrow['reorderlevel'];
	}
	
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			$myrow['locationname'], 
			locale_number_format($myrow['quantity'],$myrow['decimalplaces']),
			locale_number_format($NewRL,$myrow['decimalplaces'])
			);

}
//end of while loop


echo '</table></div>
    </div>
	</form>';
include('includes/footer.php');
?>
