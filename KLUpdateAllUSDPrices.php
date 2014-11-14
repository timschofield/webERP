<?php

include ('includes/session.inc');
$Title = _('Kapal-Laut. Update all USD prices');
include('includes/header.inc');
include('includes/KLDefines.php');
include('includes/KLPrices.php');

$Today = date('Y-m-d');
$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));


DB_Txn_Begin();

/* 1st: Set enddate for ALL current USD prices to yesterday */
$sql = "UPDATE prices 
		SET enddate='" . $Yesterday . "'
		WHERE currabrev ='USD'
		AND  (enddate > '" . $Yesterday . "' OR enddate = '0000-00-00') ";
$ErrMsg = _('Could not update the price because');
$result = DB_query($sql,$db,$ErrMsg);
prnMsg (_('Set end date for ALL current USD prices to yesterday'),'success');

/* Then, select all Retail prices in IDR active as these are the base to calculate all the other prices, including the USD prices */
$SQL = "SELECT stockid,
				price
		FROM prices	
		WHERE prices.typeabbrev = '" . RETAIL_PRICE_LIST . "'
			AND prices.currabrev = '". CURRENCY_CODE ."'
			AND prices.startdate <= '". $Today. "' 
			AND (prices.enddate >= '". $Today. "' OR prices.enddate = '0000-00-00')";

$result = DB_query($SQL, $db);

if (DB_num_rows($result) != 0){
	echo '<p class="page_title_text" align="center"><strong>' . _('Prices in USD to be updated at rate ') . locale_number_format(RATE_IDRUSD_FOR_RETAIL_WEBSTORE,0) . '</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('#') . '</th>
						<th>' . _('Code') . '</th>
						<th>' . _('Retail IDR') . '</th>
						<th>' . _('Retail USD') . '</th>
						<th>' . _('Wholesale 30% USD') . '</th>
						<th>' . _('Wholesale 40% USD') . '</th>
						<th>' . _('Wholesale 50% USD') . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$i = 1;
	while ($myrow = DB_fetch_array($result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}


		$CodeLink = '<a href="' . $RootPath . '/SelectProduct.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
		
		$RetailUSD      = round_basic_price($myrow['price'] / RATE_IDRUSD_FOR_RETAIL_WEBSTORE, 0.05);
		$Wholesale30USD = round_basic_price($myrow['price'] / RATE_IDRUSD_FOR_RETAIL_WEBSTORE * (0.70), 0.05);
		$Wholesale40USD = round_basic_price($myrow['price'] / RATE_IDRUSD_FOR_RETAIL_WEBSTORE * (0.60), 0.05);
		$Wholesale50USD = round_basic_price($myrow['price'] / RATE_IDRUSD_FOR_RETAIL_WEBSTORE * (0.50), 0.05);

		UpdatePriceItem($myrow['stockid'], 'RT', 'USD', $RetailUSD,      $Today, FALSE, $db);
		UpdatePriceItem($myrow['stockid'], 'W3', 'USD', $Wholesale30USD, $Today, FALSE, $db);
		UpdatePriceItem($myrow['stockid'], 'W4', 'USD', $Wholesale40USD, $Today, FALSE, $db);
		UpdatePriceItem($myrow['stockid'], 'W5', 'USD', $Wholesale50USD, $Today, FALSE, $db);;

		printf('<td class="number">%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				$i, 
				$CodeLink, 
				locale_number_format($myrow['price'],0),
				locale_number_format($RetailUSD,2),
				locale_number_format($Wholesale30USD,2),
				locale_number_format($Wholesale40USD,2),
				locale_number_format($Wholesale50USD,2)
				);
		$i++;
	}
	echo '</table>
			</div>';
}

DB_Txn_Commit();

include('includes/footer.inc');

?>