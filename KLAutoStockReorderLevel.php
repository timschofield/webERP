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

$Result = DB_query("SELECT description,
						categoryid,
						units 
					FROM stockmaster 
					WHERE stockid='" . $StockID . "'");
$MyItem=DB_fetch_array($Result);

echo '<table class="selection">
	<thead>
		<tr>
			<th colspan="3"><h3><b>' . $StockID . ' - ' . $MyItem['description'] . '</b>  (' . _('In Units of') . ' ' . $MyItem['units'] . ')</h3></th>
		</tr>
	</thead>
	<tbody>';

if ($LocCode != ''){
	// we want to distribute to a specific location
	$FilterLoc = " AND locations.loccode = '" . $LocCode . "' ";
}else{
	// we want to distribute to a group of locations
	if ($AllShops == "N"){
		// we only want to distribute between the locations with the flags allitemsXXXX == true (big shops) not to small ones with allitemsXXXX == false
		if (ItemInLIst($MyItem['categoryid'], LIST_STOCK_CATEGORIES_TEST)){
			$FilterLoc = " AND locations.alltestitems = 1 ";
		}elseif (ItemInLIst($MyItem['categoryid'], LIST_STOCK_CATEGORIES_STABLE)){
			$FilterLoc = " AND locations.allstableitems = 1 ";
		}elseif (ItemInLIst($MyItem['categoryid'], LIST_STOCK_CATEGORIES_NO_MORE_PURCHASING)){
			$FilterLoc = " AND locations.allnopoitems = 1 ";
		}elseif (ItemInLIst($MyItem['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_20)){
			$FilterLoc = " AND locations.alldisc20items = 1 ";
		}elseif (ItemInLIst($MyItem['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_50)){
			$FilterLoc = " AND locations.alldisc50items = 1 ";
		}elseif (ItemInLIst($MyItem['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_80)){
			$FilterLoc = " AND locations.alldisc80items = 1 ";
		}else{
			$FilterLoc = "";
		}
	}else{
		// want to distribute to all the locations
		$FilterLoc = "";
	}
}

$SQL = "SELECT locstock.loccode,
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
$LocStockResult = DB_query($SQL, $ErrMsg, $DbgMsg);

$TableHeader = '<tr>
					<th class="SortedColumn">' . _('Location') . '</th>
					<th class="SortedColumn">' . _('Quantity On Hand') . '</th>
					<th class="SortedColumn">' . _('Re-Order Level') . '</th>
				</tr>';

echo $TableHeader;
$k=0; //row colour counter

while ($MyRow=DB_fetch_array($LocStockResult)) {

	// update the RL if the location is the same as the type of shop or the location code
	if(($MyRow['typeloc'] == $TypeOfShop) 
		OR ($MyRow['loccode'] == $LocCode)){
		$SQL = "UPDATE locstock SET reorderlevel = '" . $RL . "'
			WHERE stockid = '" . $StockID . "'
			AND loccode = '"  . $MyRow['loccode'] ."'";

		$UpdateReorderLevel = DB_query($SQL);
		$NewRL = $RL;
	}else{
		$NewRL = $MyRow['reorderlevel'];
	}

	echo '<tr class="striped_row">
			<td>' . $MyRow['locationname'] . '</td>
			<td class="number">' . locale_number_format($MyRow['quantity'],$MyRow['decimalplaces']) . '</td>
			<td class="number">' . locale_number_format($NewRL,$MyRow['decimalplaces']) . '</td>
			</tr>';

}
//end of while loop


echo '</tbody></table></div></form>';
include('includes/footer.php');

