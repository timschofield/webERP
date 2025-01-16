<?php
/* $Id: SalesPeople.php 5785 2012-12-29 04:47:42Z daintree $*/

include('includes/session.php');
$Title = _('KL Change of Retail Price -> Step 02');
include('includes/header.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLPrices.php');
include('includes/KLUIGeneralFunctions.php');

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				(SELECT sum(quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = locations.loccode
					AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ") AS qohpos,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohconsignment,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_KANTOR_LOCATIONS . ") AS qohkantor,
				(SELECT sum(quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = locations.loccode
					AND locations.loccode NOT IN " . LIST_KANTOR_LOCATIONS . "
					AND locations.typeloc NOT IN " . LIST_BALI_SHOPS_BY_TYPE . "
					AND locations.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.pendingqty) 
						FROM loctransfers,locations
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc = locations.loccode
						AND locations.typeloc IN " . LIST_BALI_SHOPS_BY_TYPE . ") AS intransitfromshops,
				(SELECT SUM(loctransfers.pendingqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS intransitfromconsignment,
				(SELECT SUM(loctransfers.pendingqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_KANTOR_LOCATIONS . ") AS intransitfromkantor,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid) AS qohtotal,
				klchangeprice.counterpricechange,
				klchangeprice.startprocessdate,
				klchangeprice.pricechanged,
				klchangeprice.newretailprice
			FROM stockmaster, klchangeprice					
			WHERE stockmaster.stockid = klchangeprice.stockid
				AND klchangeprice.endprocessdate = '0000-00-00'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items ready to change Retail Price in KL kantor');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('#') . '</th>
							<th>' . _('Code') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('Start Date') . '</th>
							<th>' . _('QOH KL Shops') . '</th>
							<th>' . _('Transit From Kantor') . '</th>
							<th>' . _('Transit To Kantor') . '</th>
							<th>' . _('QOH Kantor') . '</th>
							<th>' . _('QOH Others') . '</th>
							<th>' . _('QOH Total') . '</th>
							<th>' . _('New Retail Price') . '</th>
							<th>' . _('Labels') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			if ((($MyRow['qohkantor'] + $MyRow['qohotherlocs']) == $MyRow['qohtotal'])
				AND ($MyRow['intransitfromkantor'] == 0)
				AND ($MyRow['intransitfromconsignment'] == 0)
				AND ($MyRow['intransitfromshops'] == 0)
				){
				if ($MyRow['pricechanged']==1){
					// already changed the price, so now it's time to see if labels have been printed and finish the process
					$NewPriceLink = locale_number_format($MyRow['newretailprice'],0);
					$NewLabelsPrinted = '<a href="' . $RootPath . '/KLChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $MyRow['newretailprice'] .  '&Action=Finish">' . _('Printed') . '</a>';
				}else{
					// the category is still the old one. We still need to change it!
					// if we have ONLY stock in kantor (or in locations not needing procedure) and NO transit, all the QOH is at kantor
					// We can apply the new discount category
					$NewPriceLink = '<a href="' . $RootPath . '/KLChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $MyRow['newretailprice'] .  '&Action=Change">' . locale_number_format($MyRow['newretailprice'],0) . '</a>';
					$NewLabelsPrinted = 'Not yet';
				}
			}else{
				$NewPriceLink = locale_number_format($MyRow['newretailprice'],0);
				$NewLabelsPrinted = 'Not yet';
			}
			printf('<td class="number">%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td>%s</td>
					</tr>', 
					locale_number_format($i,0),
					$CodeLink, 
					$MyRow['description'],
					ConvertSQLDate($MyRow['startprocessdate']),
					locale_number_format_zero_blank($MyRow['qohpos']-$MyRow['intransitfromshops'],0),
					locale_number_format_zero_blank($MyRow['intransitfromkantor'],0),
					locale_number_format_zero_blank($MyRow['intransitfromshops']+$MyRow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($MyRow['qohkantor']-$MyRow['intransitfromkantor'],0),
					locale_number_format_zero_blank($MyRow['qohotherlocs'],0),
					locale_number_format_zero_blank($MyRow['qohtotal'],0),
					$NewPriceLink,
					$NewLabelsPrinted
					);
			$i++;
		}
		echo '</table>
				</div>';
	}else{
		prnMsg("No items in process of price change at the moment", "success");
	}

include('includes/footer.php');
?>