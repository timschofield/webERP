<?php
define("VERSIONFILE", "1.01"); 
define("NUMBER_OF_TESTS", 28); 

include ('includes/session.inc');
$Title = _('Kapal-Laut Retail Customer Analysis '. VERSIONFILE);
include('includes/header.inc');
include('includes/KLCountriesForRetail.php');
include('includes/KLGeneralFunctions.php');

$begintime = time_start();

if ($_SESSION['UserID'] == "Ricard"){
	RetailCustomerAnalysisBySex(1, "ALL", $db);
	RetailCustomerAnalysisBySex(30, "ALL", $db);

	RetailCustomerAnalysisByCountry(1, "ALL", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "ALL", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(1, "'TOK66','TOKSE','TOKOB'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "'TOK66','TOKSE','TOKOB'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(1, "'TOKKS','TOKBW'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "'TOKKS','TOKBW'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(1, "'TOKJC'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "'TOKJC'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(1, "'TOKSA','TOKSS','TOKSU'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "'TOKSA','TOKSS','TOKSU'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(1, "'TOKUB','TOKPU','TOKMF'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "'TOKUB','TOKPU','TOKMF'", $CountriesForRetail, $db);

	RetailCustomerAnalysisByAge(30, "ALL", $db);
	
	EmailHarvested(1, "ALL", $db);
	EmailHarvested(30, "ALL", $db);
	EmailHarvested(1, "'TOK66','TOKSE','TOKOB'", $db);
	EmailHarvested(1, "'TOKKS','TOKBW'", $db);
	EmailHarvested(1, "'TOKJC'", $db);
	EmailHarvested(1, "'TOKSA','TOKSS','TOKSU'", $db);
	EmailHarvested(1, "'TOKUB','TOKPU','TOKMF'", $db);
	
}

if ($_SESSION['UserID'] == "Laia"
	OR $_SESSION['UserID'] == "Ike1"
	OR $_SESSION['UserID'] == "Juliette"){
	RetailCustomerAnalysisBySex(30, "ALL", $db);

	RetailCustomerAnalysisByCountry(30, "ALL", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "'TOK66','TOKSE','TOKOB'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "'TOKKS','TOKBW'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "'TOKJC'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "'TOKSA','TOKSS','TOKSU'", $CountriesForRetail, $db);
	RetailCustomerAnalysisByCountry(30, "'TOKUB','TOKPU','TOKMF'", $CountriesForRetail, $db);

	EmailHarvested(30, "ALL", $db);

}


prnMsg("Performed ". NUMBER_OF_TESTS . " Retail Customers Analysis",'success');
time_finish($begintime);

include ('includes/footer.inc');

/******************************************************************************************************/
/*                               FUNCTIONS ASSOCIATED                                                 */
/******************************************************************************************************/

function RetailCustomerAnalysisBySex($NumDays, $ListShops, $db){
	$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

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
	$result = DB_query($SQL, $db);
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
	$result = DB_query($SQL, $db);
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
	$result = DB_query($SQL, $db);
	$myrow = DB_fetch_array($result);
	$NumberMales = $myrow[0];

	if ($ListShops == 'ALL'){
		echo '<p class="page_title_text" align="center"><strong>' . _('Retail Customers By Sex during the last ') . locale_number_format($NumDays,0) . ' days</strong></p>';
	}else{
		echo '<p class="page_title_text" align="center"><strong>' . _('Retail Customers By Sex during the last ') . locale_number_format($NumDays,0) . ' days in shop ' . $ListShops . '</strong></p>';
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

function RetailCustomerAnalysisByCountry($NumDays, $ListShops, $CountriesForRetail, $db){
	$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

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
	$result = DB_query($SQL, $db);
	$myrow = DB_fetch_array($result);
	$NumberSales = $myrow[0];

	// Get the total of cases 
	$SQL = "SELECT COUNT(klretailcustomers.country)
			FROM klretailcustomers, salesorders
			WHERE klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.country != '0'".
				$WhereListShops;
	$result = DB_query($SQL, $db);
	$myrow = DB_fetch_array($result);
	$NumberCases = $myrow[0];
	
	// Get the result of customers per country 
	$SQL = "SELECT klretailcustomers.country, COUNT(klretailcustomers.country) AS numberofcustomers
			FROM klretailcustomers, salesorders
			WHERE klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.country != '0'".
				$WhereListShops ."
			GROUP BY klretailcustomers.country 
			ORDER BY COUNT(klretailcustomers.country) DESC,
				klretailcustomers.country ASC"	;
	$result = DB_query($SQL, $db);
	
	if (DB_num_rows($result) != 0){
		if ($ListShops == 'ALL'){
			echo '<p class="page_title_text" align="center"><strong>' . _('Retail Customers By Country during the last ') . locale_number_format($NumDays,0) . ' days</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Retail Customers By Country during the last ') . locale_number_format($NumDays,0) . ' days in shop ' . $ListShops . '</strong></p>';
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
				
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				$CountriesForRetail[$myrow['country']],
				locale_number_format($myrow['numberofcustomers'],0),
				locale_number_format(($myrow['numberofcustomers']/$NumberCases)*100,1).'%'
				);
		
		}
		echo '</table>
			</div>
			</form>';
	}
}

function EmailHarvested($NumDays, $ListShops, $db){
	$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

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
	$result = DB_query($SQL, $db);
	$myrow = DB_fetch_array($result);
	$NumberSales = $myrow[0];
	
	// Get the result of emails harvested 
	$SQL = "SELECT COUNT(*)
			FROM klretailcustomers, salesorders
			WHERE klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.email != ''".
				$WhereListShops;
	$result = DB_query($SQL, $db);
	$myrow = DB_fetch_array($result);
	$NumberEmails = $myrow[0];

	if ($ListShops == 'ALL'){
		echo '<p class="page_title_text" align="center"><strong>' . _('e-mail harvested during the last ') . locale_number_format($NumDays,0) . ' days</strong></p>';
	}else{
		echo '<p class="page_title_text" align="center"><strong>' . _('e-mail harvested during the last ') . locale_number_format($NumDays,0) . ' days in shop ' . $ListShops . '</strong></p>';
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
			locale_number_format($NumberEmails,0),
			locale_number_format(($NumberEmails/$NumberSales)*100,1).'%'
			);

	echo '</table>
		</div>
		</form>';
			
}


function RetailCustomerAnalysisByAge($NumDays, $ListShops, $CountriesForRetail, $db){
	$Yesterday  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1));
	$StartDate  = FormatDateForSQL(DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-$NumDays));

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
	$result = DB_query($SQL, $db);
	$myrow = DB_fetch_array($result);
	$NumberSales = $myrow[0];

	// Get the total of cases 
	$SQL = "SELECT COUNT(klretailcustomers.age)
			FROM klretailcustomers, salesorders
			WHERE klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age != 0".
				$WhereListShops;
	$result = DB_query($SQL, $db);
	$myrow = DB_fetch_array($result);
	$NumberCases = $myrow[0];
	
	// Get the result of customers per Age 
	$SQL = "SELECT klretailcustomers.age, COUNT(klretailcustomers.age) AS numberofcustomers
			FROM klretailcustomers, salesorders
			WHERE klretailcustomers.orderno = salesorders.orderno
				AND salesorders.orddate >= '". $StartDate . "'
				AND salesorders.orddate <= '". $Yesterday . "'
				AND klretailcustomers.age != 0".
				$WhereListShops ."
			GROUP BY klretailcustomers.age
			ORDER BY klretailcustomers.age ASC";

prnMsg($SQL);	
	$result = DB_query($SQL, $db);
prnMsg($result);	
	if (DB_num_rows($result) != 0){
prnMsg('IN!');	
		if ($ListShops == 'ALL'){
			echo '<p class="page_title_text" align="center"><strong>' . _('Retail Customers By Age during the last ') . locale_number_format($NumDays,0) . ' days</strong></p>';
		}else{
			echo '<p class="page_title_text" align="center"><strong>' . _('Retail Customers By Age during the last ') . locale_number_format($NumDays,0) . ' days in shop ' . $ListShops . '</strong></p>';
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
				
		while ($myrow = DB_fetch_array($result)) {
			$k = StartEvenOrOddRow($k);
			printf('<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>', 
				$myrow['age'],
				locale_number_format($myrow['numberofcustomers'],0),
				locale_number_format(($myrow['numberofcustomers']/$NumberCases)*100,1).'%'
				);
		}
		echo '</table>
			</div>
			</form>';
	}
}

?>