<?php

include('includes/session.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/KLDefines.php');

$Title = _('Export CSV File for Faktur Pajak');

// The default company to Invoice from (PTADU).
if(!isset($_POST['CompanyFrom'])) {
	$_POST['CompanyFrom']='PTADU';
}

// The default company to Invoice to (PTBB).
if(!isset($_POST['CompanyTo'])) {
	$_POST['CompanyTo']='PTBB';
}

// default date to invoice is until Yesterday
if (!isset($_POST['EndDate'])){
	$_POST['EndDate'] = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-1); 
}

// The default draft or Invoice should be draft.
if(!isset($_POST['NomorSeriFP'])) {
	$_POST['NomorSeriFP']='0000000000000';
}


if (isset($_POST['submit'])) {
	submit($Title, $_POST['CompanyFrom'], $_POST['CompanyTo'], $_POST['EndDate'], $_POST['NomorSeriFP'], $db);
} else {
	display($Title, $db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit($Title, $CompanyFrom, $CompanyTo, $EndDate, $NomorSeriFP, &$db) {

	$EndDate = FormatDateForSQL($EndDate);

	//initialise no input errors
	$InputError = FALSE;

	//first off validate inputs sensible

	if(!$InputError){
		$SQL = "SELECT klconsignment.stockid,
						stockmaster.description,
					SUM(klconsignment.qty) AS qty,
					ROUND(AVG(klconsignment.consignmentprice),0) AS price
				FROM klconsignment,stockmaster
				WHERE klconsignment.stockid = stockmaster.stockid
					AND companycode = '" . $CompanyFrom . "'
					AND partnercode = '" . $CompanyTo . "'
					AND fakturpajakdate = '0000-00-00'
					AND saledate <= '" . $EndDate . "'
				GROUP BY klconsignment.stockid
				ORDER BY klconsignment.stockid";

		$result = DB_query($SQL);
		if (DB_num_rows($result) != 0){
			// prepare CSV file
			header("Content-Type: text/csv");
			header("Content-Disposition: attachment; filename=FakturPajak-" . $CompanyFrom . "-". $NomorSeriFP . ".csv");
			$output = fopen("php://output", "w");
			$Separator = ",";
			$EOL = "\n";
			
			// Prepare Lines for products in the FP
			$i = 0;
			while ($myrow = DB_fetch_array($result)) {

				$LineType = 'OF';
				$KodeObjek = $myrow['stockid'];
				$Nama = $myrow['description'];
				$HargaSatuan = round($myrow['price'],0);
				$JumlahBarang = round($myrow['qty'],0);
				$HargaTotal = $HargaSatuan * $JumlahBarang;
				$Diskon = 0;
				$DPP = round($HargaTotal / ((100 + PPN_PERCENT) / 100),0);
				$PPN = $HargaTotal - $DPP;
				$TarifPPNBM = 0;
				$PPNBM = 0;
				
				$Line = $LineType . $Separator . 
						$KodeObjek . $Separator . 
						$Nama . $Separator . 
						$HargaSatuan . $Separator . 
						$JumlahBarang . $Separator . 
						$HargaTotal . $Separator . 
						$Diskon . $Separator . 
						$DPP . $Separator . 
						$PPN . $Separator . 
						$TarifPPNBM . $Separator . 
						$PPNBM . $EOL;

				fwrite($output, $Line);
				$i++;
			}
			fclose($output);
		}else{
			include('includes/header.php');
			prnMsg('No data to create a Faktur Pajak ');
			include('includes/footer.php');
		}
	}else{
		include('includes/header.php');
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $PageTitle . '" alt="" />' . ' ' . $PageTitle . 
			'</p>';
		prnMsg($InputErrorMessage, "warn");
		include('includes/footer.php');
	}
} // End of function submit()


function display($Title, &$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.
	include('includes/header.php');

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text">
			<img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '
		</p>';

	echo '<table class="selection">';

	include('includes/KLConsignmentParameterSelection.php');
	echo '<tr>
			<td>' . _('Nomor Seri Faktur Pajak') . ':' . '</td>
			<td><input type="text" name="NomorSeriFP" value="' . $_POST['NomorSeriFP'] . '" size="14" maxlength="13" /></td>
		</tr>';
	
	echo '<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . $Title . '" /></td>
		</tr>
		</table>
		<br />';
	echo '</div>
         </form>';
	include('includes/footer.php');

} // End of function display()

?>