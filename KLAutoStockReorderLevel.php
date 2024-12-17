<?php

include('includes/session.php');
$Title = _('Automatic Setting of Stock Re-Order Level');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLGeneralFunctions.php');

if (isset($_GET['StockID'])){
	$StockID = trim(mb_strtoupper($_GET['StockID']));
} elseif (isset($_POST['StockID'])){
	$StockID = trim(mb_strtoupper($_POST['StockID']));
}else{
	$StockID = '';
}

if (isset($_GET['LocCode'])){
	$LocCode = trim(mb_strtoupper($_GET['LocCode']));
} elseif (isset($_POST['LocCode'])){
	$LocCode = trim(mb_strtoupper($_POST['LocCode']));
}else{
	$LocCode = '';
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

if (isset($_GET['AllShops'])){
	$AllShops = trim(mb_strtoupper($_GET['AllShops']));
} elseif (isset($_POST['AllShops'])){
	$AllShops = trim(mb_strtoupper($_POST['AllShops']));
}else{
	$AllShops = '';
}

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="" /><b>' . $Title. '</b>
	</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

$result = DB_query("SELECT description,
						categoryid,
						units 
					FROM stockmaster 
					WHERE stockid='" . $StockID . "'");
$myitem=DB_fetch_array($result);

echo '<table class="selection">';
echo '<tr>
		<th colspan="3"><h3><b>' . $StockID . ' - ' . $myitem['description'] . '</b>  (' . _('In Units of') . ' ' . $myitem['units'] . ')</h3></th>
	</tr>';

if ($LocCode != ''){
	// we want to distribute to a specific location
	$FilterLoc = " AND locations.loccode = '" . $LocCode . "' ";
}else{
	// we want to distribute to a group of locations
	if ($AllShops == "N"){
		// we only want to distribute between the locations with the flags allitemsXXXX == true (big shops) not to small ones with allitemsXXXX == false
		if (ItemInLIst($myitem['categoryid'], LIST_STOCK_CATEGORIES_TEST)){
			$FilterLoc = " AND locations.alltestitems = 1 ";
		}elseif (ItemInLIst($myitem['categoryid'], LIST_STOCK_CATEGORIES_STABLE)){
			$FilterLoc = " AND locations.allstableitems = 1 ";
		}elseif (ItemInLIst($myitem['categoryid'], LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING)){
			$FilterLoc = " AND locations.allnopoitems = 1 ";
		}elseif (ItemInLIst($myitem['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_20)){
			$FilterLoc = " AND locations.alldisc20items = 1 ";
		}elseif (ItemInLIst($myitem['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_50)){
			$FilterLoc = " AND locations.alldisc50items = 1 ";
		}elseif (ItemInLIst($myitem['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_80)){
			$FilterLoc = " AND locations.alldisc80items = 1 ";
		}else{
			$FilterLoc = "";
		}
	}else{
		// want to distribute to all the locations
		$FilterLoc = "";
	}
}

$sql = "SELECT locstock.loccode,
				locations.locationname,
				locations.typeloc,
				locstock.quantity,
				locstock.reorderlevel,
				stockmaster.decimalplaces
		FROM locstock 
		INNER JOIN locations
			ON locstock.loccode=locations.loccode
		INNER JOIN locationusers 
			ON locationusers.loccode=locstock.loccode 
				AND locationusers.userid='" .  $_SESSION['UserID'] . "' 
				AND locationusers.canupd=1
		INNER JOIN stockmaster
			ON locstock.stockid=stockmaster.stockid
		WHERE locstock.stockid = '" . $StockID . "'".
		$FilterLoc ."
		ORDER BY locations.locationname";

$ErrMsg = _('The stock held at each location cannot be retrieved because');
$DbgMsg = _('The SQL that failed was');
$LocStockResult = DB_query($sql, $ErrMsg, $DbgMsg);

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

	if(($myrow['typeloc'] == $TypeOfShop) 
		OR ($myrow['loccode'] == $LocCode)){
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
