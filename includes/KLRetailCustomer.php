<?php

/******************************************************************************************************/
/*                               FUNCTIONS ASSOCIATED                                                 */
/******************************************************************************************************/

function RetailCustomerAnalysisBySex($NumDays, $ListShops){
	if ($NumDays == -1){
		// today only
		$Yesterday  = date('Y-m-d');
		$StartDate  = date('Y-m-d');
		$TableTitleText = __('Retail Customers By Sex during today ');
	}else{
		$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
		$TableTitleText = 'Retail Customers By Sex during the last ' . locale_number_format($NumDays,0) . ' days ' ;
	}

	if ($ListShops == 'ALL'){
		$WhereListShops = " ";
	}else{
		$WhereListShops = " AND salesorders.fromstkloc IN (" . $ListShops . ") ";
	}
	
	// Get the total of sales 
	$SQL = "SELECT COUNT(*)
			FROM salesorders
			WHERE salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'".
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberSales = $MyRow[0];
	
	// Get the result of F 
	$SQL = "SELECT COUNT(*)
			FROM klretailcustomers, salesorders
			WHERE klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.sex = 'F'".
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberFemales = $MyRow[0];

	// Get the result of M 
	$SQL = "SELECT COUNT(*)
			FROM klretailcustomers, salesorders
			WHERE klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.sex = 'M'".
				$WhereListShops;;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberMales = $MyRow[0];

	if ($ListShops != 'ALL'){
		$TableTitleText = $TableTitleText . ' in shop ' . $ListShops;
	}
	ShowTableTitle($TableTitleText);
	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . __('Value') . '</th>
					<th>' . __('Cases') . '</th>
					<th>' . '%' . '</th>
				</tr>
			</thead>
			<tbody>';
	echo '<tr class="striped_row">
			<td>' . 'Total Sales' . '</td>
			<td class="number">' . locale_number_format($NumberSales,0) . '</td>
			<td class="number">' . '' . '</td>
			</tr>';

	$NumberCases = $NumberFemales + $NumberMales;

	echo '<tr class="striped_row">
			<td>' . 'Total Cases' . '</td>
			<td class="number">' . locale_number_format($NumberCases,0) . '</td>
			<td class="number">' . locale_number_format(($NumberSales > 0) ? ($NumberCases/$NumberSales)*100 : 0,1) . '%' . '</td>
			</tr>';

	echo '<tr class="striped_row">
			<td>' . 'Females' . '</td>
			<td class="number">' . locale_number_format($NumberFemales,0) . '</td>
			<td class="number">' . locale_number_format(($NumberCases > 0) ? ($NumberFemales/$NumberCases)*100 : 0,1).'%' . '</td>
			</tr>';

	echo '<tr class="striped_row">
			<td>' . 'Males' . '</td>
			<td class="number">' . locale_number_format($NumberMales,0) . '</td>
			<td class="number">' . locale_number_format(($NumberCases > 0) ? ($NumberMales/$NumberCases)*100 : 0,1).'%' . '</td>
			</tr>';

	echo '</tbody>
		</table>
		</div>
		</form>';
			
}

function RetailCustomerAnalysisByCountry($NumDays, $TypeOfShops, $ShopArea, $MinimCustomersToShow, $CountriesForRetail){
	if ($NumDays == -1){
		// today only
		$Yesterday  = date('Y-m-d');
		$StartDate  = date('Y-m-d');
	}else{
		$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	}

	if ($TypeOfShops == 'ALL'){
		$WhereListShops = " ";
		$NameOfShops = "ALL";
	}elseif ($TypeOfShops == 'KAPAL-LAUT'){
		$WhereListShops = " AND locations.typeloc = 'SHOPKL' ";
		$NameOfShops = "KAPAL-LAUT";
	}elseif ($TypeOfShops == 'BLINK'){
		$WhereListShops = " AND locations.typeloc = 'SHOPBL' ";
		$NameOfShops = "BLINK";
	}elseif ($TypeOfShops == 'OUTLET'){
		$WhereListShops = " AND locations.typeloc = 'SHOPOU' ";
		$NameOfShops = "OUTLET";
	}

	if ($ShopArea == 'ALL'){
		$WhereShopArea = " ";
		$NameOfArea = "ALL Zones";
	}else{
		$WhereShopArea = " AND locations.zone ='" . $ShopArea . "' ";
		$NameOfArea = $ShopArea . " Zone";
	}
	
	// Get the total of sales 
	$SQL = "SELECT COUNT(*)
			FROM salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'".
				$WhereListShops . 
				$WhereShopArea;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberSales = $MyRow[0];

	// Get the total of cases 
	$SQL = "SELECT COUNT(klretailcustomers.country)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.country != '0'".
				$WhereListShops . 
				$WhereShopArea;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberCases = $MyRow[0];
	
	// Get the result of customers per country 
	$SQL = "SELECT klretailcustomers.country, COUNT(klretailcustomers.country) AS numberofcustomers
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.country != '0'".
				$WhereListShops . 
				$WhereShopArea . "
			GROUP BY klretailcustomers.country 
			ORDER BY COUNT(klretailcustomers.country) DESC,
				klretailcustomers.country ASC"	;
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		if ($NumDays == -1){
			$TableTitleText = __('Retail Customers By Country during today in ') . $NameOfShops . ' shops  for ' . $NameOfArea;
		} else {
			$TableTitleText = __('Retail Customers By Country during the last ') . locale_number_format($NumDays,0) . ' days in ' . $NameOfShops . ' shops  for ' . $NameOfArea;
		}
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">
				<thead>
					<tr>
						<th>' . __('Value') . '</th>
						<th>' . __('Cases') . '</th>
						<th>' . '%' . '</th>
					</tr>
				</thead>
				<tbody>';
		echo '<tr class="striped_row">
				<td>' . 'Total Sales' . '</td>
				<td class="number">' . locale_number_format($NumberSales,0) . '</td>
				<td class="number">' . '' . '</td>
				</tr>';

		echo '<tr class="striped_row">
				<td>' . 'Total Cases' . '</td>
				<td class="number">' . locale_number_format($NumberCases,0) . '</td>
				<td class="number">' . locale_number_format(($NumberSales > 0) ? ($NumberCases/$NumberSales)*100 : 0,1).'%' . '</td>
				</tr>';

		$TotalOtherCountries = 0;	
		while ($MyRow = DB_fetch_array($Result)) {
			if ($MyRow['numberofcustomers'] > $MinimCustomersToShow){
				echo '<tr class="striped_row">
					<td>' . $CountriesForRetail[$MyRow['country']] . '</td>
					<td class="number">' . locale_number_format($MyRow['numberofcustomers'],0) . '</td>
					<td class="number">' . locale_number_format(($NumberCases > 0) ? ($MyRow['numberofcustomers']/$NumberCases)*100 : 0,1).'%' . '</td>
					</tr>';
			}else{
				$TotalOtherCountries += $MyRow['numberofcustomers'];
			}
		
		}
		if($TotalOtherCountries > 0){
			echo '<tr class="striped_row">
				<td>' . 'Other Countries' . '</td>
				<td class="number">' . locale_number_format($TotalOtherCountries,0) . '</td>
				<td class="number">' . locale_number_format(($NumberCases > 0) ? ($TotalOtherCountries/$NumberCases)*100 : 0,1).'%' . '</td>
				</tr>';
		}
		echo '</tbody>
			</table>
			</div>
			</form>';
	}
}

function EmailHarvested($NumDays, $TypeOfShops){
	if ($NumDays == -1){
		// today only
		$Yesterday  = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
		$StartDate  = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
	}else{
		$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	}

	if ($TypeOfShops == 'ALL'){
		$WhereListShops = " ";
		$NameOfShops = "ALL";
	}elseif ($TypeOfShops == 'KAPAL-LAUT'){
		$WhereListShops = " AND locations.typeloc = 'SHOPKL' ";
		$NameOfShops = "KAPAL-LAUT";
	}elseif ($TypeOfShops == 'BLINK'){
		$WhereListShops = " AND locations.typeloc = 'SHOPBL' ";
		$NameOfShops = "BLINK";
	}elseif ($TypeOfShops == 'OUTLET'){
		$WhereListShops = " AND locations.typeloc = 'SHOPOU' ";
		$NameOfShops = "OUTLET";
	}
	
	// Get the total of sales 
	$SQL = "SELECT COUNT(*)
			FROM salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'".
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberSales = $MyRow[0];
	
	// Get the result of emails harvested 
	$SQL = "SELECT COUNT(*)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.email != ''".
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberEmails = $MyRow[0];

	$TableTitleText = __('e-mail harvested during the last ') . locale_number_format($NumDays,0) . ' days in ' . $NameOfShops . ' shops';
	ShowTableTitle($TableTitleText);
	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . __('Value') . '</th>
					<th>' . __('Cases') . '</th>
					<th>' . '%' . '</th>
				</tr>
			</thead>
			<tbody>';
	echo '<tr class="striped_row">
			<td>' . 'Total Sales' . '</td>
			<td class="number">' . locale_number_format($NumberSales,0) . '</td>
			<td class="number">' . '' . '</td>
			</tr>';

	echo '<tr class="striped_row">
			<td>' . 'Total Cases' . '</td>
			<td class="number">' . locale_number_format($NumberEmails,0) . '</td>
			<td class="number">' . locale_number_format(($NumberSales > 0) ? ($NumberEmails/$NumberSales)*100 : 0,1).'%' . '</td>
			</tr>';

	echo '</tbody>
		</table>
		</div>
		</form>';
			
}

function RetailCustomerAnalysisByAge($NumDays, $TypeOfShops){
	if ($NumDays == -1){
		// today only
		$Yesterday  = date('Y-m-d');
		$StartDate  = date('Y-m-d');
	}else{
		$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	}

	if ($TypeOfShops == 'ALL'){
		$WhereListShops = " ";
		$NameOfShops = "ALL";
	}elseif ($TypeOfShops == 'KAPAL-LAUT'){
		$WhereListShops = " AND locations.typeloc = 'SHOPKL' ";
		$NameOfShops = "KAPAL-LAUT";
	}elseif ($TypeOfShops == 'BLINK'){
		$WhereListShops = " AND locations.typeloc = 'SHOPBL' ";
		$NameOfShops = "BLINK";
	}elseif ($TypeOfShops == 'OUTLET'){
		$WhereListShops = " AND locations.typeloc = 'SHOPOU' ";
		$NameOfShops = "OUTLET";
	}
	
	// Get the total of sales 
	$SQL = "SELECT COUNT(*)
			FROM salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'".
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberSales = $MyRow[0];

	// Get the total of cases 
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age != 0".
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberCases = $MyRow[0];
	
	// Get the cases for AGE_STEP_01
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age != 0
				AND klretailcustomers.age <= ". AGE_STEP_01 .
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberCases01 = $MyRow[0];

	// Get the cases for AGE_STEP_02
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age > ". AGE_STEP_01 . "
				AND klretailcustomers.age <= ". AGE_STEP_02 .
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberCases02 = $MyRow[0];

	// Get the cases for AGE_STEP_03
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age > ". AGE_STEP_02 . "
				AND klretailcustomers.age <= ". AGE_STEP_03 .
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberCases03 = $MyRow[0];

	// Get the cases for AGE_STEP_04
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age > ". AGE_STEP_03 . "
				AND klretailcustomers.age <= ". AGE_STEP_04 .
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberCases04 = $MyRow[0];

	// Get the cases for AGE_STEP_05
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age > ". AGE_STEP_04 . "
				AND klretailcustomers.age <= ". AGE_STEP_05 .
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberCases05 = $MyRow[0];
	
	// Get the cases for AGE_STEP_06
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age > ". AGE_STEP_05 . "
				AND klretailcustomers.age <= ". AGE_STEP_06 .
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberCases06 = $MyRow[0];
	
	// Get the cases for AGE_STEP_07
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age > ". AGE_STEP_06 . "
				AND klretailcustomers.age <= ". AGE_STEP_07 .
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberCases07 = $MyRow[0];

	// Get the cases for over AGE_STEP_07
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age > ". AGE_STEP_07 .
				$WhereListShops;
	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);
	$NumberCases08 = $MyRow[0];

	if ($NumDays == -1){
		$TableTitleText = __('Retail Customers By Age during today in ') . $NameOfShops . ' shops';
	} else {
		$TableTitleText = __('Retail Customers By Age during the last ') . locale_number_format($NumDays,0) . ' days in ' . $NameOfShops . ' shops';
	}
	ShowTableTitle($TableTitleText);
	echo '<div>';
	echo '<table class="selection">
			<thead>
				<tr>
					<th>' . __('Value') . '</th>
					<th>' . __('Cases') . '</th>
					<th>' . '%' . '</th>
				</tr>
			</thead>
			<tbody>';
	$k = 0; //row colour counter
	$k = StartEvenOrOddRow($k);
	echo '<tr class="striped_row">
			<td>' . 'Total Sales' . '</td>
			<td class="number">' . locale_number_format($NumberSales,0) . '</td>
			<td class="number">' . '' . '</td>
			</tr>';

	$k = StartEvenOrOddRow($k);
	echo '<tr class="striped_row">
			<td>' . 'Total Cases' . '</td>
			<td class="number">' . locale_number_format($NumberCases,0) . '</td>
			<td class="number">' . locale_number_format(($NumberSales > 0) ? ($NumberCases/$NumberSales)*100 : 0,1).'%' . '</td>
			</tr>';
			
	$k = StartEvenOrOddRow($k);
	echo '<tr class="striped_row">
			<td>' . '-'. AGE_STEP_01 . '</td>
			<td class="number">' . locale_number_format($NumberCases01,0) . '</td>
			<td class="number">' . locale_number_format(($NumberCases > 0) ? ($NumberCases01/$NumberCases)*100 : 0,1).'%' . '</td>
			</tr>';

	$k = StartEvenOrOddRow($k);
	echo '<tr class="striped_row">
			<td>' . AGE_STEP_01 . '-'. AGE_STEP_02 . '</td>
			<td class="number">' . locale_number_format($NumberCases02,0) . '</td>
			<td class="number">' . locale_number_format(($NumberCases > 0) ? ($NumberCases02/$NumberCases)*100 : 0,1).'%' . '</td>
			</tr>';

	$k = StartEvenOrOddRow($k);
	echo '<tr class="striped_row">
			<td>' . AGE_STEP_02 . '-'. AGE_STEP_03 . '</td>
			<td class="number">' . locale_number_format($NumberCases03,0) . '</td>
			<td class="number">' . locale_number_format(($NumberCases > 0) ? ($NumberCases03/$NumberCases)*100 : 0,1).'%' . '</td>
			</tr>';

	$k = StartEvenOrOddRow($k);
	echo '<tr class="striped_row">
			<td>' . AGE_STEP_03 . '-'. AGE_STEP_04 . '</td>
			<td class="number">' . locale_number_format($NumberCases04,0) . '</td>
			<td class="number">' . locale_number_format(($NumberCases > 0) ? ($NumberCases04/$NumberCases)*100 : 0,1).'%' . '</td>
			</tr>';

	$k = StartEvenOrOddRow($k);
	echo '<tr class="striped_row">
			<td>' . AGE_STEP_04 . '-'. AGE_STEP_05 . '</td>
			<td class="number">' . locale_number_format($NumberCases05,0) . '</td>
			<td class="number">' . locale_number_format(($NumberCases > 0) ? ($NumberCases05/$NumberCases)*100 : 0,1).'%' . '</td>
			</tr>';

	$k = StartEvenOrOddRow($k);
	echo '<tr class="striped_row">
			<td>' . AGE_STEP_05 . '-'. AGE_STEP_06 . '</td>
			<td class="number">' . locale_number_format($NumberCases06,0) . '</td>
			<td class="number">' . locale_number_format(($NumberCases > 0) ? ($NumberCases06/$NumberCases)*100 : 0,1).'%' . '</td>
			</tr>';

	$k = StartEvenOrOddRow($k);
	echo '<tr class="striped_row">
			<td>' . AGE_STEP_06 . '-'. AGE_STEP_07 . '</td>
			<td class="number">' . locale_number_format($NumberCases07,0) . '</td>
			<td class="number">' . locale_number_format(($NumberCases > 0) ? ($NumberCases07/$NumberCases)*100 : 0,1).'%' . '</td>
			</tr>';

	$k = StartEvenOrOddRow($k);
	echo '<tr class="striped_row">
			<td>' . AGE_STEP_07 . '+' . '</td>
			<td class="number">' . locale_number_format($NumberCases08,0) . '</td>
			<td class="number">' . locale_number_format(($NumberCases > 0) ? ($NumberCases08/$NumberCases)*100 : 0,1).'%' . '</td>
			</tr>';

	echo '</tbody>
		</table>
		</div>
		</form>';
}

function RetailCustomerDataQualitySPG($SPG, $NumDays){
	if ($NumDays == -1){
		// today only
		$Yesterday  = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
		$StartDate  = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']));
	}else{
		$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
	}

	if ($SPG != "ALL"){
		$WhereSPG = " AND salesman.salesmancode = " . $SPG . " ";
	}else{
		$WhereSPG = " ";
	}
	$SQL = "SELECT salesorders.salesperson,
				salesman.salesmanname,
				(SELECT COUNT(*)
					FROM salesorders AS so2
					WHERE so2.orddate >= '". $StartDate ."'
						AND so2.orddate <= '". $Yesterday ."'
						AND so2.salesperson = salesorders.salesperson) AS totalorders,
				COUNT(klretailcustomers.orderno) AS harvested,
				SUM(CASE klretailcustomers.firstname WHEN '' then 0 else 1 END) AS firstnames, 
				SUM(CASE klretailcustomers.lastname WHEN '' then 0 else 1 END) AS lastnames, 
				SUM(CASE klretailcustomers.country WHEN '0' then 0 else 1 END) AS countries, 
				SUM(CASE klretailcustomers.date_of_birth WHEN '1000-01-01' then 0 else 1 END) AS date_of_births, 
				SUM(CASE klretailcustomers.email WHEN '' then 0 else 1 END) AS emails, 
				SUM(CASE klretailcustomers.sex WHEN '' then 0 else 1 END) AS sexs,
				(SELECT SUM(qtyinvoiced)
					FROM salesorderdetails, salesorders AS so2
					WHERE salesorderdetails.orderno = so2.orderno
						AND so2.orddate >= '". $StartDate ."'
						AND so2.orddate <= '". $Yesterday ."'
						AND so2.salesperson = salesorders.salesperson
						AND salesorderdetails.stkcode = 'ONLINE-VIP-PACK') AS onlinevipcards 
			FROM klretailcustomers, salesorders, salesman
			WHERE salesorders.orderno = klretailcustomers.orderno
				AND salesman.salesmancode = salesorders.salesperson " . 
				$WhereSPG . "
				AND salesorders.orddate >= '". $StartDate ."'
				AND salesorders.orddate <= '". $Yesterday ."'
			GROUP BY salesorders.salesperson
			ORDER BY salesorders.salesperson";
	$Result = DB_query($SQL);
	
	if (DB_num_rows($Result) != 0){
		$TableTitleText = __('Quality data Retail Customer by SPG during the last ') . locale_number_format($NumDays,0) . ' days';
		ShowTableTitle($TableTitleText);
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="SortedColumn">' . __('SPG') . '</th>
							<th class="SortedColumn">' . __('Name') . '</th>
							<th class="SortedColumn">' . __('# Sales') . '</th>
							<th class="SortedColumn">' . __('% Data') . '</th>
							<th class="SortedColumn">' . __('% First') . '</th>
							<th class="SortedColumn">' . __('% Last') . '</th>
							<th class="SortedColumn">' . __('% Country') . '</th>
							<th class="SortedColumn">' . __('% DOB') . '</th>
							<th class="SortedColumn">' . __('% Email') . '</th>
							<th class="SortedColumn">' . __('% Sex') . '</th>
							<th class="SortedColumn">' . __('% VIP-PACK') . '</th>
						</tr>';
		echo $TableHeader;

		while ($MyRow = DB_fetch_array($Result)) {
			echo '<tr class="striped_row">
				<td>' . $MyRow['salesperson'] . '</td>
				<td>' . $MyRow['salesmanname'] . '</td>
				<td class="number">' . locale_number_format($MyRow['totalorders'],0) . '</td>
				<td class="number">' . locale_number_format(($MyRow['totalorders'] > 0) ? ($MyRow['harvested']/$MyRow['totalorders'])*100 : 0,0).'%' . '</td>
				<td class="number">' . locale_number_format(($MyRow['harvested'] > 0) ? ($MyRow['firstnames']/$MyRow['harvested'])*100 : 0,0).'%' . '</td>
				<td class="number">' . locale_number_format(($MyRow['harvested'] > 0) ? ($MyRow['lastnames']/$MyRow['harvested'])*100 : 0,0).'%' . '</td>
				<td class="number">' . locale_number_format(($MyRow['harvested'] > 0) ? ($MyRow['countries']/$MyRow['harvested'])*100 : 0,0).'%' . '</td>
				<td class="number">' . locale_number_format(($MyRow['harvested'] > 0) ? ($MyRow['date_of_births']/$MyRow['harvested'])*100 : 0,0).'%' . '</td>
				<td class="number">' . locale_number_format(($MyRow['harvested'] > 0) ? ($MyRow['emails']/$MyRow['harvested'])*100 : 0,0).'%' . '</td>
				<td class="number">' . locale_number_format(($MyRow['harvested'] > 0) ? ($MyRow['sexs']/$MyRow['harvested'])*100 : 0,0).'%' . '</td>
				<td class="number">' . locale_number_format(($MyRow['totalorders'] > 0) ? ($MyRow['onlinevipcards']/$MyRow['totalorders'])*100 : 0,0).'%' . '</td>
				</tr>';
		}
		echo '</table>
			</div>
			</form>';
	}
}

