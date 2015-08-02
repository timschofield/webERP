<?php

include('includes/session.inc');
$Title = _('KL Move To 80% Discount -> Step 02');
include('includes/header.inc');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLDefines.php');
include('includes/KLPrices.php');

	$SQL = "SELECT stockmaster.stockid, 
				stockmaster.description,
				stockmaster.categoryid,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode LIKE 'TOK%') AS qohpos,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohconsignment,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode IN " . LIST_KANTOR_LOCATIONS . ") AS qohkantor,
				(SELECT sum(quantity)
					FROM locstock
					WHERE locstock.stockid = stockmaster.stockid
					AND loccode NOT IN " . LIST_KANTOR_LOCATIONS . "
					AND loccode NOT LIKE 'TOK%'
					AND loccode NOT IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS qohotherlocs,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc LIKE 'TOK%') AS intransitfromshops,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
						FROM loctransfers
						WHERE loctransfers.stockid = stockmaster.stockid
						AND loctransfers.shiploc IN " . LIST_CONSIGNMENT_LOCATIONS . ") AS intransitfromconsignment,
				(SELECT SUM(loctransfers.shipqty-loctransfers.recqty) 
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
				AND klmovetodiscount80.endprocessdate = '0000-00-00'";
	$result = DB_query($SQL);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Items ready to be moved to 80% Discount in kantor') . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('#') . '</th>
							<th>' . _('Code') . '</th>
							<th>' . _('Description') . '</th>
							<th>' . _('Start Date') . '</th>
							<th>' . _('QOH KL Shops') . '</th>
							<th>' . _('QOH Consignment') . '</th>
							<th>' . _('Transit From Kantor') . '</th>
							<th>' . _('Transit To Kantor') . '</th>
							<th>' . _('QOH Kantor') . '</th>
							<th>' . _('QOH Others') . '</th>
							<th>' . _('QOH Total') . '</th>
							<th>' . _('Discount') . '</th>
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
				// if we have ONLY stock in kantor (or in locations not needing procedure) and NO transit, all the QOH is at kantor
				// We can apply the new discount category
				$NewDiscountCategory = '<a href="' . $RootPath . '/KLChangeToDiscount80.php?Item=' . $myrow['stockid'] . '&Discount='. $myrow['discountcategory'] . '&Action=Change">' . $myrow['discountcategory'] . '</a>';
			}else{
				$NewDiscountCategory = $myrow['discountcategory'];
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
					<td class="number">%s</td>
					</tr>', 
					locale_number_format($myrow['countermovediscount'],0),
					$CodeLink, 
					$myrow['description'],
					ConvertSQLDate($myrow['startprocessdate']),
					locale_number_format_zero_blank($myrow['qohpos']-$myrow['intransitfromshops'],0),
					locale_number_format_zero_blank($myrow['qohconsignment']-$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['intransitfromshops']+$myrow['intransitfromconsignment'],0),
					locale_number_format_zero_blank($myrow['qohkantor']-$myrow['intransitfromkantor'],0),
					locale_number_format_zero_blank($myrow['qohotherlocs'],0),
					locale_number_format_zero_blank($myrow['qohtotal'],0),
					$NewDiscountCategory
					);
			$i++;
		}
		echo '</table>
				</div>';
	}else{
		prnMsg("No items to be moved to 80% Discount at the moment", "success");
	}

include('includes/footer.inc');
?>