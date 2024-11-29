<?php

include('includes/session.php');
$Title = _('Move To 20% Discount -> Step 02');
include('includes/header.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLPrices.php');

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				stockmaster.categoryid,
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
				klmovetodiscount20.countermovediscount,
				klmovetodiscount20.startprocessdate,
				klmovetodiscount20.discountcategory
			FROM stockmaster, klmovetodiscount20					
			WHERE stockmaster.stockid = klmovetodiscount20.stockid
				AND klmovetodiscount20.endprocessdate = '0000-00-00'";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items ready to be moved to 20% Discount in Kantor') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('#') . '</th>
							<th>' . _('Code') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('Start Date') . '</th>
							<th>' . _('QOH Shops') . '</th>
							<th>' . _('Transit From Kantor') . '</th>
							<th>' . _('Transit To Kantor') . '</th>
							<th>' . _('QOH Kantor') . '</th>
							<th>' . _('QOH Others') . '</th>
							<th>' . _('QOH Total') . '</th>
							<th>' . _('Discount') . '</th>
							<th>' . _('Labels') . '</th>
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
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'] . '">' . $myrow['stockid'] . '</a>';
			if ((($myrow['qohkantor'] + $myrow['qohotherlocs']) == $myrow['qohtotal'])
				AND ($myrow['intransitfromkantor'] == 0)
				AND ($myrow['intransitfromconsignment'] == 0)
				AND ($myrow['intransitfromshops'] == 0)
				){
				if (ItemInList($myrow['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_20)){
					// already changed the category, so now it's time to see if labels have been printed and finish the process
					$NewDiscountCategory = $myrow['discountcategory'];
					$NewLabelsPrinted = '<a href="' . $RootPath . '/KLChangeToDiscount.php?Item=' . $myrow['stockid'] . '&Discount='. $myrow['discountcategory'] . '&Category='. $myrow['categoryid'] . '&Action=Finish">' . ('Printed') . '</a>';
				}else{
					// the category is still the old one. We still need to change it!
					// if we have ONLY stock in kantor (or in locations not needing procedure) and NO transit, all the QOH is at kantor
					// We can apply the new discount category
					$NewDiscountCategory = '<a href="' . $RootPath . '/KLChangeToDiscount.php?Item=' . $myrow['stockid'] . '&Discount='. $myrow['discountcategory'] . '&Category='. $myrow['categoryid'] . '&Action=Change">' . $myrow['discountcategory'] . '</a>';
					$NewLabelsPrinted = 'Not yet';
				}
			}else{
				$NewDiscountCategory = $myrow['discountcategory'];
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
					$myrow['description'],
					ConvertSQLDate($myrow['startprocessdate']),
					locale_number_format_zero_blank($myrow['qohpos']-$myrow['intransitfromshops'],0),
					locale_number_format_zero_blank($myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['intransitfromshops']+$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['qohkantor']-$myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['qohotherlocs'],0),
					locale_number_format_zero_blank($myrow['qohtotal'],0),
					$NewDiscountCategory,
					$NewLabelsPrinted
					);
			$i++;
		}
		echo '</table>
				</div>';
	}else{
		prnMsg("No items to be moved to 20% discount at the moment", "success");
	}

include('includes/footer.php');
?>