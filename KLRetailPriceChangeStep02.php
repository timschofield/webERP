<?php

include('includes/session.php');

$Title = __('KL Change of Retail Price -> Step 02');
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
				AND klchangeprice.endprocessdate = '1000-01-01'";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Items ready to change Retail Price in KL kantor');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
              <thead>';
		$TableHeader = '<tr>
							<th>' . __('#') . '</th>
							<th>' . __('Code') . '</th>
							<th>' . __('Description') . '</th>
							<th>' . __('Start Date') . '</th>
							<th>' . __('QOH KL Shops') . '</th>
							<th>' . __('Transit From Kantor') . '</th>
							<th>' . __('Transit To Kantor') . '</th>
							<th>' . __('QOH Kantor') . '</th>
							<th>' . __('QOH Others') . '</th>
							<th>' . __('QOH Total') . '</th>
							<th>' . __('New Retail Price') . '</th>
							<th>' . __('Labels') . '</th>
						</tr>';
		echo $TableHeader;
		echo '</thead><tbody>';
		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">';
			$CodeLink = '<a href="' . $RootPath . '/StockStatus.php?StockID=' . $MyRow['stockid'] . '">' . $MyRow['stockid'] . '</a>';
			if ((($MyRow['qohkantor'] + $MyRow['qohotherlocs']) == $MyRow['qohtotal'])
				AND ($MyRow['intransitfromkantor'] == 0)
				AND ($MyRow['intransitfromconsignment'] == 0)
				AND ($MyRow['intransitfromshops'] == 0)
				){
				if ($MyRow['pricechanged']==1){
					// already changed the price, so now it's time to see if labels have been printed and finish the process
					$NewPriceLink = 'Done';
					$NewLabelsPrinted = '<a href="' . $RootPath . '/KLChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $MyRow['newretailprice'] .  '&Action=Finish">' . 'Printed' . '</a>';
				}else{
					// the category is still the old one. We still need to change it!
					// if we have ONLY stock in kantor (or in locations not needing procedure) and NO transit, all the QOH is at kantor
					// We can apply the new discount category
					$NewPriceLink = '<a href="' . $RootPath . '/KLChangeRetailPrice.php?Item=' . $MyRow['stockid'] . '&NewPrice='. $MyRow['newretailprice'] .  '&Action=Change">' . 'Change to ' . locale_number_format($MyRow['newretailprice'],0) . '</a>';
					$NewLabelsPrinted = '';
				}
			}else{
				$NewPriceLink = '';
				$NewLabelsPrinted = '';
			}	
			echo '<td class="number">'.locale_number_format($i,0).'</td>
					<td>'.$CodeLink.'</td>
					<td>'.$MyRow['description'].'</td>
					<td>'.ConvertSQLDate($MyRow['startprocessdate']).'</td>
					<td class="number">'.locale_number_format_zero_blank($MyRow['qohpos']-$MyRow['intransitfromshops'],0).'</td>
					<td class="number">'.locale_number_format_zero_blank($MyRow['intransitfromkantor'],0).'</td>
					<td class="number">'.locale_number_format_zero_blank($MyRow['intransitfromshops']+$MyRow['intransitfromconsignment'],0).'</td>
					<td class="number">'.locale_number_format_zero_blank($MyRow['qohkantor']-$MyRow['intransitfromkantor'],0).'</td>
					<td class="number">'.locale_number_format_zero_blank($MyRow['qohotherlocs'],0).'</td>
					<td class="number">'.locale_number_format_zero_blank($MyRow['qohtotal'],0).'</td>
					<td class="number">'.$NewPriceLink.'</td>
					<td>'.$NewLabelsPrinted.'</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
			</table>
			</div>';
	}else{
		prnMsg("No items in process of price change at the moment", "success");
	}

include('includes/footer.php');
