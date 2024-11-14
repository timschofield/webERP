<?php

/******************************************************************************************************/
/*                               FUNCTIONS ASSOCIATED                                                 */
/******************************************************************************************************/

function RetailCustomerAnalysisBySex($NumDays, $ListShops){
	if ($NumDays == -1){
		// today only
		$Yesterday  = Date('Y-m-d');
		$StartDate  = Date('Y-m-d');
		$Title = _('Retail Customers By Sex during today ');
	}else{
		$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
		$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));
		$Title = 'Retail Customers By Sex during the last ' . locale_number_format($NumDays,0) . ' days ' ;
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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberSales = $myrow[0];
	
	// Get the result of F 
	$SQL = "SELECT COUNT(*)
			FROM klretailcustomers, salesorders
			WHERE klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.sex = 'F'".
				$WhereListShops;
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberFemales = $myrow[0];

	// Get the result of M 
	$SQL = "SELECT COUNT(*)
			FROM klretailcustomers, salesorders
			WHERE klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.sex = 'M'".
				$WhereListShops;;
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberMales = $myrow[0];

	if ($ListShops == 'ALL'){
		echo '<p class="page_title_text" align="center"><strong>' . $Title . '</strong></p>';
	}else{
		echo '<p class="page_title_text" align="center"><strong>' . $Title . ' in shop ' . $ListShops . '</strong></p>';
	}
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('Value') . '</th>
						<th>' . _('Cases') . '</th>
						<th>' . '%' . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Total Sales',
			locale_number_format($NumberSales,0),
			''
			);

	$k = StartEvenOrOddRow($k);
	$NumberCases = $NumberFemales + $NumberMales;
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Total Cases',
			locale_number_format($NumberCases,0),
			locale_number_format(($NumberCases/$NumberSales)*100,1).'%'
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Females',
			locale_number_format($NumberFemales,0),
			locale_number_format(($NumberFemales/$NumberCases)*100,1).'%'
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Males',
			locale_number_format($NumberMales,0),
			locale_number_format(($NumberMales/$NumberCases)*100,1).'%'
			);

	echo '</table>
		</div>
		</form>';
			
}

function RetailCustomerAnalysisByCountry($NumDays, $TypeOfShops, $ShopArea, $MinimCustomersToShow, $CountriesForRetail){
	if ($NumDays == -1){
		// today only
		$Yesterday  = Date('Y-m-d');
		$StartDate  = Date('Y-m-d');
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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberSales = $myrow[0];

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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberCases = $myrow[0];
	
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
	$result = DB_query($SQL);
	
	if (DB_num_rows($result) != 0){
		if ($NumDays == -1){
			echo '<p class="page_title_text" align="center"><strong>' . _('Retail Customers By Country during today in ') . $NameOfShops . ' shops  for ' . $NameOfArea . '</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Retail Customers By Country during the last ') . locale_number_format($NumDays,0) . ' days in ' . $NameOfShops . ' shops  for ' . $NameOfArea . '</strong></p>';
		}
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th>' . _('Value') . '</th>
							<th>' . _('Cases') . '</th>
							<th>' . '%' . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter
		$k = StartEvenOrOddRow($k);
		printf('<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Total Sales',
				locale_number_format($NumberSales,0),
				''
				);

		$k = StartEvenOrOddRow($k);
		printf('<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Total Cases',
				locale_number_format($NumberCases,0),
				locale_number_format(($NumberCases/$NumberSales)*100,1).'%'
				);

		$TotalOtherCountries = 0;	
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['numberofcustomers'] > $MinimCustomersToShow){
				$k = StartEvenOrOddRow($k);
				printf('<td>%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$CountriesForRetail[$myrow['country']],
					locale_number_format($myrow['numberofcustomers'],0),
					locale_number_format(($myrow['numberofcustomers']/$NumberCases)*100,1).'%'
					);
			}else{
				$TotalOtherCountries += $myrow['numberofcustomers'];
			}
		
		}
		if($TotalOtherCountries > 0){
			printf('<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				'Other Countries',
				locale_number_format($TotalOtherCountries,0),
				locale_number_format(($TotalOtherCountries/$NumberCases)*100,1).'%'
				);
		}
		echo '</table>
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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberSales = $myrow[0];
	
	// Get the result of emails harvested 
	$SQL = "SELECT COUNT(*)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.email != ''".
				$WhereListShops;
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberEmails = $myrow[0];

	echo '<p class="page_title_text" align="center"><strong>' . _('e-mail harvested during the last ') . locale_number_format($NumDays,0) . ' days in ' . $NameOfShops . ' shops</strong></p>';
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('Value') . '</th>
						<th>' . _('Cases') . '</th>
						<th>' . '%' . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Total Sales',
			locale_number_format($NumberSales,0),
			''
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Total Cases',
			locale_number_format($NumberEmails,0),
			locale_number_format(($NumberEmails/$NumberSales)*100,1).'%'
			);

	echo '</table>
		</div>
		</form>';
			
}

function RetailCustomerAnalysisByAge($NumDays, $TypeOfShops, $CountriesForRetail){
	if ($NumDays == -1){
		// today only
		$Yesterday  = Date('Y-m-d');
		$StartDate  = Date('Y-m-d');
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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberSales = $myrow[0];

	// Get the total of cases 
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age != 0".
				$WhereListShops;
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberCases = $myrow[0];
	
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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberCases01 = $myrow[0];

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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberCases02 = $myrow[0];

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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberCases03 = $myrow[0];

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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberCases04 = $myrow[0];

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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberCases05 = $myrow[0];
	
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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberCases06 = $myrow[0];
	
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
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberCases07 = $myrow[0];

	// Get the cases for over AGE_STEP_07
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders, locations
			WHERE salesorders.fromstkloc = locations.loccode
				AND klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age > ". AGE_STEP_07 .
				$WhereListShops;
	$result = DB_query($SQL);
	$myrow = DB_fetch_array($result);
	$NumberCases08 = $myrow[0];

	if ($NumDays == -1){
		echo '<p class="page_title_text" align="center"><strong>' . _('Retail Customers By Age during today in ') . $NameOfShops . ' shops</strong></p>';
	}else{
		echo '<p class="page_title_text" align="center"><strong>' . _('Retail Customers By Age during the last ') . locale_number_format($NumDays,0) . ' days in ' . $NameOfShops . ' shops</strong></p>';
	}
	echo '<div>';
	echo '<table class="selection">';
	$TableHeader = '<tr>
						<th>' . _('Value') . '</th>
						<th>' . _('Cases') . '</th>
						<th>' . '%' . '</th>
					</tr>';
	echo $TableHeader;
	$k = 0; //row colour counter
	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Total Sales',
			locale_number_format($NumberSales,0),
			''
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			'Total Cases',
			locale_number_format($NumberCases,0),
			locale_number_format(($NumberCases/$NumberSales)*100,1).'%'
			);
			
	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			'-'. AGE_STEP_01,
			locale_number_format($NumberCases01,0),
			locale_number_format(($NumberCases01/$NumberCases)*100,1).'%'
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			AGE_STEP_01 . '-'. AGE_STEP_02,
			locale_number_format($NumberCases02,0),
			locale_number_format(($NumberCases02/$NumberCases)*100,1).'%'
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			AGE_STEP_02 . '-'. AGE_STEP_03,
			locale_number_format($NumberCases03,0),
			locale_number_format(($NumberCases03/$NumberCases)*100,1).'%'
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			AGE_STEP_03 . '-'. AGE_STEP_04,
			locale_number_format($NumberCases04,0),
			locale_number_format(($NumberCases04/$NumberCases)*100,1).'%'
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			AGE_STEP_04 . '-'. AGE_STEP_05,
			locale_number_format($NumberCases05,0),
			locale_number_format(($NumberCases05/$NumberCases)*100,1).'%'
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			AGE_STEP_05 . '-'. AGE_STEP_06,
			locale_number_format($NumberCases06,0),
			locale_number_format(($NumberCases06/$NumberCases)*100,1).'%'
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			AGE_STEP_06 . '-'. AGE_STEP_07,
			locale_number_format($NumberCases07,0),
			locale_number_format(($NumberCases07/$NumberCases)*100,1).'%'
			);

	$k = StartEvenOrOddRow($k);
	printf('<td>%s</td>
			<td class="number">%s</td>
			<td class="number">%s</td>
			</tr>', 
			AGE_STEP_07 . '+',
			locale_number_format($NumberCases08,0),
			locale_number_format(($NumberCases08/$NumberCases)*100,1).'%'
			);

	echo '</table>
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
				SUM(CASE klretailcustomers.date_of_birth WHEN '0000-00-00' then 0 else 1 END) AS date_of_births, 
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
	$result = DB_query($SQL);
	
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . _('Quality data Retail Customer by SPG during the last ') . locale_number_format($NumDays,0) . ' days</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th class="ascending">' . _('SPG') . '</th>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('# Sales') . '</th>
							<th class="ascending">' . _('% Data') . '</th>
							<th class="ascending">' . _('% First') . '</th>
							<th class="ascending">' . _('% Last') . '</th>
							<th class="ascending">' . _('% Country') . '</th>
							<th class="ascending">' . _('% DOB') . '</th>
							<th class="ascending">' . _('% Email') . '</th>
							<th class="ascending">' . _('% Sex') . '</th>
							<th class="ascending">' . _('% VIP-PACK') . '</th>
						</tr>';
		echo $TableHeader;
		$k = 0; //row colour counter

		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				$myrow['salesperson'],
				$myrow['salesmanname'],
				locale_number_format($myrow['totalorders'],0),
				locale_number_format(($myrow['harvested']/$myrow['totalorders'])*100,0).'%',
				locale_number_format(($myrow['firstnames']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['lastnames']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['countries']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['date_of_births']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['emails']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['sexs']/$myrow['harvested'])*100,0).'%',
				locale_number_format(($myrow['onlinevipcards']/$myrow['totalorders'])*100,0).'%'
				);
		}
		echo '</table>
			</div>
			</form>';
	}
}

?>
