<?php

include('includes/session.php');
$Title = _('Kapal-Laut DataBase Random Mess Fixer');
include('includes/header.php');
include('includes/KLDefines.php');
include('includes/KLPrices.php');
include('includes/KLBoards.php');
include('includes/KLGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

// FixGltransAmountZeroDueToCompensation($RootPath);

include('includes/footer.php');


function FixGltransAmountZeroDueToCompensation($RootPath) {
	$SQL = "SELECT counterindex,
				type,
				typeno,
				chequeno,
				trandate,
				periodno,
				account,
				narrative,
				amount,
				posted,
				jobref
			FROM gltrans
			WHERE `periodno` >= 190
				AND ABS(amount) < 0.1
				AND (account = '111517000AD'
					OR account = '111518000AD'
					OR account = '111518900AD'
					OR account = '111519000AD'
					OR account = '111516000AD'
					OR account = '510010000AD')
				AND trandate >= '2025-02-25'
				AND counterindex != '12380515'
				ORDER BY `counterindex` ASC";

	$Result = DB_query($SQL);

	if (DB_num_rows($Result) != 0){
		$TableTitleText = _('GLTrans with amount 0 due to wrong retail compensation setup');
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		echo '<thead>
				<tr>
					<th class="SortedColumn">' . _('#') . '</th>
					<th class="SortedColumn">' . _('counterindex') . '</th>
					<th class="SortedColumn">' . _('type') . '</th>
					<th class="SortedColumn">' . _('typeno') . '</th>
					<th class="SortedColumn">' . _('trandate') . '</th>
					<th class="SortedColumn">' . _('periodno') . '</th>
					<th class="SortedColumn">' . _('account') . '</th>
					<th class="SortedColumn">' . _('narrative') . '</th>
					<th class="SortedColumn">' . _('amount') . '</th>
					<th class="SortedColumn">' . _('item code') . '</th>
					<th class="SortedColumn">' . _('standard cost') . '</th>
					<th class="SortedColumn">' . _('qty') . '</th>
					<th class="SortedColumn">' . _('new amount') . '</th>
					<th class="SortedColumn">' . _('new narrative') . '</th>
				</tr>
				</thead>
				<tbody>';

		$i = 1;
		while ($MyRow = DB_fetch_array($Result)) {

			$ItemCode = '';
			$Quantity = '';
			$StandardCost = 0;
			$NewAmount = '';
			$NewNarrative = '';
			$Quantity = 0;

			// Parse the narrative to extract item code and quantity
			if (strpos($MyRow['narrative'], ' x ') !== false && strpos($MyRow['narrative'], ' @ ') !== false) {
				$ItemCode = trim(substr($MyRow['narrative'], 0, strpos($MyRow['narrative'], ' x ')));
				$MiddlePart = substr($MyRow['narrative'], strpos($MyRow['narrative'], ' x ') + 3);
				$Quantity = trim(substr($MiddlePart, 0, strpos($MiddlePart, ' @ ')));
				$StandardCost = GetItemStandardCostFromCode($ItemCode);
				$NewAmount = $StandardCost * $Quantity;
				if ($MyRow['account'] == '111517000AD'
					|| $MyRow['account'] == '111518000AD'
					|| $MyRow['account'] == '111518900AD'
					|| $MyRow['account'] == '111516000AD'
					|| $MyRow['account'] == '111519000AD'){
					$NewAmount = -$NewAmount;
				}
				$NewNarrative = $ItemCode . ' x ' . $Quantity . ' @ ' . $StandardCost;

				$SQLUpdate = "UPDATE gltrans
							SET narrative = '" . $NewNarrative . "',
								amount = '" . $NewAmount . "'
							WHERE counterindex = '" . $MyRow['counterindex'] . "'";
				$Updated = DB_query($SQLUpdate);
			}

			echo '<tr class="striped_row">
					<td class="number">' . $i . '</td>
					<td class="number">' . $MyRow['counterindex'] . '</td>
					<td class="number">' . $MyRow['type'] . '</td>
					<td class="number">' . $MyRow['typeno'] . '</td>
					<td>' . $MyRow['trandate'] . '</td>
					<td class="number">' . $MyRow['periodno'] . '</td>
					<td>' . $MyRow['account'] . '</td>
					<td>' . $MyRow['narrative'] . '</td>
					<td class="number">' . $MyRow['amount'] . '</td>
					<td>' . $ItemCode . '</td>
					<td class="number">' . $StandardCost . '</td>
					<td class="number">' . $Quantity . '</td>
					<td class="number">' . $NewAmount . '</td>
					<td>' . $NewNarrative . '</td>
					</tr>';
			$i++;
		}
		echo '</tbody>
				</table>
				</div>';
	}
}

?>