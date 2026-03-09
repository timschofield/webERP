<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Move To 50% Discount -> Step 02');
include(__DIR__ . '/includes/header.php');

include(__DIR__ . '/includes/KLBoards.php');
include(__DIR__ . '/includes/KLGeneralFunctions.php');
include(__DIR__ . '/includes/KLDefines.php');
include(__DIR__ . '/includes/KLPrices.php');
include(__DIR__ . '/includes/KLUIGeneralFunctions.php');

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				stockmaster.categoryid,
				(SELECT sum(quantity)
					FROM locstock,locations
					WHERE locstock.stockid = stockmaster.stockid
					AND locstock.loccode = locations.loccode
					AND locations.typeloc IN " . LIST_PHYSICAL_SHOPS_BY_TYPE . ") AS qohpos,
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
					AND locations.typeloc NOT IN " . LIST_PHYSICAL_SHOPS_BY_TYPE . "
					AND locations.loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.pendingqty) 
						FROM loctransfers,locations
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc = locations.loccode
						AND locations.typeloc IN " . LIST_PHYSICAL_SHOPS_BY_TYPE . ") AS intransitfromshops,
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
				klmovetodiscount50.countermovediscount,
				klmovetodiscount50.startprocessdate,
				klmovetodiscount50.discountcategory
			FROM stockmaster, klmovetodiscount50					
			WHERE stockmaster.stockid = klmovetodiscount50.stockid
				AND klmovetodiscount50.endprocessdate = '1000-01-01'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Items ready to be moved to 50% Discount in Kantor');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<thead><tr>
							<th>' . __('#') . '</th>
							<th>' . __('Code') . '</th>
							<th>' . __('Description') . '</th>
							<th>' . __('Start Date') . '</th>
							<th>' . __('QOH Shops') . '</th>
							<th>' . __('Transit From Kantor') . '</th>
							<th>' . __('Transit To Kantor') . '</th>
							<th>' . __('QOH Kantor') . '</th>
							<th>' . __('QOH Others') . '</th>
							<th>' . __('QOH Total') . '</th>
							<th>' . __('Discount') . '</th>
							<th>' . __('Labels') . '</th>
						</tr></thead><tbody>';
		echo $TableHeader;
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			if ((($MyRow['qohkantor'] + $MyRow['qohotherlocs']) == $MyRow['qohtotal'])
				AND ($MyRow['intransitfromkantor'] == 0)
				AND ($MyRow['intransitfromconsignment'] == 0)
				AND ($MyRow['intransitfromshops'] == 0)
				){
				if (ItemInList($MyRow['categoryid'], LIST_STOCK_CATEGORIES_DISCOUNT_50)){
					// already changed the category, so now it's time to see if labels have been printed and finish the process
					$NewDiscountCategory = 'Done';
					$NewLabelsPrinted = '<a href="' . $RootPath . '/KLChangeToDiscount.php?Item=' . $MyRow['stockid'] . '&Discount='. $MyRow['discountcategory'] . '&Category='. $MyRow['categoryid'] . '&Action=Finish">' . 'Printed' . '</a>';
				} else {
					// the category is still the old one. We still need to change it!
					// if we have ONLY stock in kantor (or in locations not needing procedure) and NO transit, all the QOH is at kantor
					// We can apply the new discount category
					$NewDiscountCategory = '<a href="' . $RootPath . '/KLChangeToDiscount.php?Item=' . $MyRow['stockid'] . '&Discount='. $MyRow['discountcategory'] . '&Category='. $MyRow['categoryid'] . '&Action=Change">' . 'Move to 50%' . '</a>';
					$NewLabelsPrinted = '';
				}
			} else {
				$NewDiscountCategory = '';
				$NewLabelsPrinted = '';
			}
			echo '<tr class="striped_row">
					<td class="number">' . locale_number_format($i,0) . '</td>
					<td>' . $CodeLink . '</td>
					<td>' . $MyRow['description'] . '</td>
					<td>' . ConvertSQLDate($MyRow['startprocessdate']) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohpos']-$MyRow['intransitfromshops'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['intransitfromkantor'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['intransitfromshops']+$MyRow['intransitfromconsignment'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohkantor']-$MyRow['intransitfromkantor'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohotherlocs'],0) . '</td>
					<td class="number">' . locale_number_format_zero_blank($MyRow['qohtotal'],0) . '</td>
					<td class="number">' . $NewDiscountCategory . '</td>
					<td>' . $NewLabelsPrinted . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody></table>
				</div>';
	} else {
		prnMsg("No items to be moved to 50% discount at the moment", "success");
	}

include(__DIR__ . '/includes/footer.php');
