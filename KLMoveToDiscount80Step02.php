<?php

include('includes/session.php');
$Title = _('Move To 80% Discount -> Step 02');
include('includes/header.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLPrices.php');
include('includes/KLUIGeneralFunctions.php');

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
				klmovetodiscount80.countermovediscount,
				klmovetodiscount80.startprocessdate,
				klmovetodiscount80.discountcategory
			FROM stockmaster, klmovetodiscount80					
			WHERE stockmaster.stockid = klmovetodiscount80.stockid
				AND klmovetodiscount80.endprocessdate = '1000-01-01'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('Items ready to be moved to 80% Discount in Kantor');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		echo '<thead>';
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
		echo '</thead>';
		echo '<tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			if ((($MyRow['qohkantor'] + $MyRow['qohotherlocs']) == $MyRow['qohtotal'])
				AND ($MyRow['intransitfromkantor'] == 0)
				AND ($MyRow['intransitfromconsignment'] == 0)
				AND ($MyRow['intransitfromshops'] == 0)
				){
				if (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_80)){
					// already changed the category, so now it's time to see if labels have been printed and finish the process
					$NewDiscountCategory = $MyRow['discountcategory'];
					$NewLabelsPrinted = '<a href="' . $RootPath . '/KLChangeToDiscount.php?Item=' . $MyRow['stockid'] . '&Discount='. $MyRow['discountcategory'] . '&Category='. $MyRow['categoryid'] . '&Action=Finish">' . ('Printed') . '</a>';
				}else{
					// the category is still the old one. We still need to change it!
					// if we have ONLY stock in kantor (or in locations not needing procedure) and NO transit, all the QOH is at kantor
					// We can apply the new discount category
					$NewDiscountCategory = '<a href="' . $RootPath . '/KLChangeToDiscount.php?Item=' . $MyRow['stockid'] . '&Discount='. $MyRow['discountcategory'] . '&Category='. $MyRow['categoryid'] . '&Action=Change">' . $MyRow['discountcategory'] . '</a>';
					$NewLabelsPrinted = 'Not yet';
				}
			}else{
				$NewDiscountCategory = $MyRow['discountcategory'];
				$NewLabelsPrinted = 'Not yet';
			}
			printf('<tr class="striped_row">
					<td class="number">%s</td>
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
					$NewDiscountCategory,
					$NewLabelsPrinted
					);
			$i++;
		}
		echo '</tbody>';
		echo '</table>';
		echo '</div>';
	}else{
		prnMsg("No items to be moved to 80% discount at the moment", "success");
	}

include('includes/footer.php');
?>