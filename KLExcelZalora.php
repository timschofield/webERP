<?php

include('includes/session.inc');
$title = _('Orders Inquiry');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
include('DBComuns.php');

if (isset($_POST['submit'])) {
    submit($db, $_POST['Account'], $_POST['Distancia'], $_POST['Carga'], $_POST['Volum'], $_POST['RatiATR'], $_POST['RentTTHHFutura'], 
			$_POST['Rent24m'], $_POST['Rent12m'], $_POST['Rent06m'], $_POST['Tendencia'], $_POST['TipusOrdre'], $_POST['Currency'], $_POST['SLBreak36m'], $_POST['SLBreak12m'] );
} else {
    display($db);
}

//####_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT_SUBMIT####
function submit(&$db, $Account, $Distancia, $Carga, $Volum, $RatiATR, $RentTTHHFutura, $Rent24m, $Rent12m, $Rent06m, $Tendencia, $TipusOrdre, $Currency, $SLBreak36m, $SLBreak12m) {

	//initialise no input errors
	$InputError = 0;

	$WhereStatus 	= " AND tradingavailableorders.status = '00'";
	$WhereIPO	  	= " AND tickermaster.ipo = '0'";
	$WhereAccount 	= " AND tradingavailableorders.codeaccount = '". $Account ."'";
	$WhereDistancia 	= " AND tradingavailableorders.hotdistancia <= '". $Distancia ."'";
	if ($Carga != 0){
		$WhereCarga 	= " AND tradingavailableorders.cargacaccount >= '". $Carga ."'";
	}else{
		$WhereCarga = " ";
	}
	if ($Volum != 0){
		$WhereVolum 	= " AND tradingavailableorders.volummigdiari >= '". $Volum ."'";
	}else{
		$WhereVolum = " ";
	}
	if ($RatiATR != 0){
		$WhereRatiATR 	= " AND tradingavailableorders.ratiatr <= '". $RatiATR ."'";
	}else{
		$WhereRatiATR = " ";
	}
	if ($RentTTHHFutura != 0){
		$WhereRentTTHHFutura 	= " AND tthhmensual.rentabilitattthhf >= '". $RentTTHHFutura ."'";
	}else{
		$WhereRentTTHHFutura = " ";
	}
	if ($Rent24m != 0){
		$WhereRent24m 	= " AND tthhmensual.rentabilitat24 >= '". $Rent24m ."'";
	}else{
		$WhereRent24m = " ";
	}
	if ($Rent12m != 0){
		$WhereRent12m 	= " AND tthhmensual.rentabilitat12 >= '". $Rent12m ."'";
	}else{
		$WhereRent12m = " ";
	}
	if ($Rent06m != 0){
		$WhereRent06m 	= " AND tthhmensual.rentabilitat06 >= '". $Rent06m ."'";
	}else{
		$WhereRent06m = " ";
	}
	if ($SLBreak36m != 99){
		$WhereSLBreak36m 	= " AND tthhmensual.stopstrencats36 <= '". $SLBreak36m ."'";
	}else{
		$WhereSLBreak36m = " ";
	}
	if ($SLBreak12m != 99){
		$WhereSLBreak12m 	= " AND tthhmensual.stopstrencats12 <= '". $SLBreak12m ."'";
	}else{
		$WhereSLBreak12m = " ";
	}
	if ($Tendencia != 'All'){
		$WhereTendencia = " AND tthhmensual.tendencia = '". $Tendencia ."'";
	}else{
		$WhereTendencia = " ";
	}
	if ($TipusOrdre != 'All'){
		$WhereTipusOrdre = " AND tradingavailableorders.tipusentrada = '". $TipusOrdre ."'";
	}else{
		$WhereTipusOrdre = " ";
	}
	if ($Currency != 'All'){
		$WhereCurrency = " AND tickermaster.currency = '". $Currency ."'";
	}else{
		$WhereCurrency = " ";
	}
	
	$OrderBy		= " ORDER BY tradingavailableorders.hotdistancia";
	
	$sql = "SELECT tradingavailableorders.availableid,
				tradingavailableorders.tickersb,
				tickermaster.tickersaxobank,
				tickermaster.currency,
				tthhmensual.tendencia,
				tradingavailableorders.tipusentrada,
				tradingavailableorders.numtitols,
				tradingavailableorders.entrada,
				tradingavailableorders.stoploss,
				tradingavailableorders.volummigdiari,
				tradingavailableorders.cargacticker,
				tradingavailableorders.cargacaccount,
				tradingavailableorders.hotdistancia,
				tradingavailableorders.atr,
				tradingavailableorders.ratiatr,
				tthhmensual.rentabilitattthh,
				tthhmensual.rentabilitattthhf,
				tthhmensual.rentabilitat24,
				tthhmensual.rentabilitat12,
				tthhmensual.rentabilitat06,
				tthhmensual.stopstrencats36,
				tthhmensual.stopstrencats12,
				tickermaster.tickername,
				tickermaster.industria,
				tradingavailableorders.status
			FROM tradingavailableorders, tthhmensual, tickermaster
			WHERE tradingavailableorders.tickersb = tthhmensual.tickersb
				AND tradingavailableorders.tickersb = tickermaster.tickersb".
				$WhereStatus .
				$WhereIPO .
				$WhereAccount .
				$WhereDistancia .
				$WhereCarga .
				$WhereVolum .
				$WhereRatiATR .
				$WhereRentTTHHFutura .
				$WhereRent24m .
				$WhereRent12m .
				$WhereRent06m .
				$WhereSLBreak36m .
				$WhereSLBreak12m .
				$WhereTendencia .
				$WhereTipusOrdre .
				$WhereCurrency .
				$OrderBy
			;
	
//	echo "<br/>".$sql."<br/>";
	
	$ErrMsg = _('The SQL to find the available orders with the message');
	$result = DB_query($sql,$db,$ErrMsg);
	if (DB_num_rows($result) != 0){
		echo '<p class="page_title_text" align="center"><strong>' . "Available orders for account: " . $Account . '</strong></p>';
		echo '<div>';
		echo '<table class="selection">';
		$TableHeader = '<tr>
							<th colspan="7">' . _('Ticker') . '</th>
							<th colspan="6">' . _('Ordre TTHH-M') . '</th>
							<th colspan="4">' . _('% Rentabilitat') . '</th>
							<th colspan="2">' . _('SL Break') . '</th>
							<th colspan="2">' . _('ATR') . '</th>
						</tr>
						<tr>
							<th>' . _('SB') . '</th>
							<th>' . _('Y!') . '</th>
							<th>' . _('Name') . '</th>
							<th>' . _('Currency') . '</th>
							<th>' . _('Sector') . '</th>
							<th>' . _('Volume M') . '</th>
							<th>' . _('Trend') . '</th>
							<th>' . _('Tipus') . '</th>
							<th>' . _('#Acc') . '</th>
							<th>' . _('In') . '</th>
							<th>' . _('StopLoss') . '</th>
							<th>' . _('Carga A') . '</th>
							<th>' . _('% Distancia') . '</th>
							<th> ' . _('TTHH F') . ' </th>
							<th> ' . _('24m') . ' </th>
							<th> ' . _('12m') . ' </th>
							<th> ' . _('06m') . ' </th>
							<th> ' . _('36m') . ' </th>
							<th> ' . _('12m') . ' </th>
							<th> ' . _('RatiATR') . ' </th>
							<th> ' . _('ATR') . ' </th>
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
			$YahooLink = '<a href="http://finance.yahoo.com/q/pr?s=' . $myrow['tickersb'] . '+Profile">' . $myrow['tickersb'] . '</a>';
			$OrderLink = '<a href="' . $rootpath . 'OrderAvailableDetails.php?Id=' . $myrow['availableid'] . '">' . $myrow['tipusentrada'] . '</a>';
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td class="number">%s</td>
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
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>', 
					$myrow['tickersaxobank'], 
					$YahooLink, 
					$myrow['tickername'], 
					$myrow['currency'], 
					$myrow['industria'], 
					locale_number_format($myrow['volummigdiari'],1),
					$myrow['tendencia'], 
					$OrderLink, 
					locale_number_format($myrow['numtitols'],0),
					locale_number_format($myrow['entrada'],2),
					locale_number_format($myrow['stoploss'],2),
					locale_number_format($myrow['cargacaccount'],0),
					locale_number_format($myrow['hotdistancia'],1).'%',
					locale_number_format($myrow['rentabilitattthhf'],0).'%',
					locale_number_format($myrow['rentabilitat24'],0).'%',
					locale_number_format($myrow['rentabilitat12'],0).'%',
					locale_number_format($myrow['rentabilitat06'],0).'%',
					locale_number_format($myrow['stopstrencats36'],0),
					locale_number_format($myrow['stopstrencats12'],0),
					locale_number_format($myrow['ratiatr'],1).'%',
					locale_number_format($myrow['atr'],2)
					);
			$i++;
		}
		echo '</table>
				</div>';
	}
	
	show_estat_account($Account, $db);
	show_open_positions_account($Account, $db);
	
} // End of function submit()


function display(&$db)  //####DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_DISPLAY_#####
{
// Display form fields. This function is called the first time
// the page is called.

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
          <div>
			<br/>
			<br/>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table>';

	echo '<tr><td>' . _('For Trading Account') . ':</td>
				<td><select name="Account">';
	$AccountResult= DB_query(	"SELECT codeaccount 
								FROM tradingaccount 
								WHERE metode NOT IN ('SB-LT-01')
									AND codeuser = '" . $_SESSION['UserID'] . "'
								ORDER BY codeaccount",$db); // Hi ha mètodes que no busquem ordres metòdicxament :-(
	While ($myrow = DB_fetch_array($AccountResult)){
		echo '<option value="' . $myrow['codeaccount'] . '">' . $myrow['codeaccount']  . '</option>';
	}
	echo '</select></td></tr>';

	if (!isset($_POST['Distancia'])){
		$_POST['Distancia']= 10;
	}	
	echo '<tr>
			<td>' . _('Distancia Entrada (%)') . ':</td>
			<td><input type="text" class="number" name="Distancia" size="2" maxlength="2" value="'. locale_number_format($_POST['Distancia'],0) . '" /></td>
		</tr>';

	if (!isset($_POST['Carga'])){
		$_POST['Carga']= 0;
	}	
	echo '<tr>
			<td>' . _('Carga minima') . ':</td>
			<td><input type="text" class="number" name="Carga" size="9" maxlength="9" value="'. locale_number_format($_POST['Carga'],0) . '" /></td>
		</tr>';

	if (!isset($_POST['Volum'])){
		$_POST['Volum']= 0;
	}	
	echo '<tr>
			<td>' . _('Volum minim diari (MEUR)') . ':</td>
			<td><input type="text" class="number" name="Volum" size="9" maxlength="9" value="'. locale_number_format($_POST['Volum'],0) . '" /></td>
		</tr>';

	if (!isset($_POST['RatiATR'])){
		$_POST['RatiATR']= 0;
	}	
	echo '<tr>
			<td>' . _('Rati ATR maxim (%)') . ':</td>
			<td><input type="text" class="number" name="RatiATR" size="9" maxlength="9" value="'. locale_number_format($_POST['RatiATR'],0) . '" /></td>
		</tr>';

		if (!isset($_POST['RentTTHHFutura'])){
		$_POST['RentTTHHFutura']= 0;
	}	
	echo '<tr>
			<td>' . _('Rentabilitat TTHH Futura minima (%)') . ':</td>
			<td><input type="text" class="number" name="RentTTHHFutura" size="9" maxlength="9" value="'. locale_number_format($_POST['RentTTHHFutura'],0) . '" /></td>
		</tr>';

	if (!isset($_POST['Rent24m'])){
		$_POST['Rent24m']= 0;
	}	
	echo '<tr>
			<td>' . _('Rentabilitat 24 mesos minima (%)') . ':</td>
			<td><input type="text" class="number" name="Rent24m" size="3" maxlength="3" value="'. locale_number_format($_POST['Rent24m'],0) . '" /></td>
		</tr>';

	if (!isset($_POST['Rent12m'])){
		$_POST['Rent12m']= 0;
	}	
	echo '<tr>
			<td>' . _('Rentabilitat 12 mesos minima (%)') . ':</td>
			<td><input type="text" class="number" name="Rent12m" size="3" maxlength="3" value="'. locale_number_format($_POST['Rent12m'],0) . '" /></td>
		</tr>';

	if (!isset($_POST['Rent06m'])){
		$_POST['Rent06m']= 0;
	}	
	echo '<tr>
			<td>' . _('Rentabilitat 06 mesos minima (%)') . ':</td>
			<td><input type="text" class="number" name="Rent06m" size="3" maxlength="3" value="'. locale_number_format($_POST['Rent06m'],0) . '" /></td>
		</tr>';

	if (!isset($_POST['SLBreak36m'])){
		$_POST['SLBreak36m']= 99;
	}	
	echo '<tr>
			<td>' . _('Maxim SL trencats en 36 mesos') . ':</td>
			<td><input type="text" class="number" name="SLBreak36m" size="3" maxlength="3" value="'. locale_number_format($_POST['SLBreak36m'],0) . '" /></td>
		</tr>';

	if (!isset($_POST['SLBreak12m'])){
		$_POST['SLBreak12m']= 99;
	}	
	echo '<tr>
			<td>' . _('Maxim SL trencats en 12 mesos') . ':</td>
			<td><input type="text" class="number" name="SLBreak12m" size="3" maxlength="3" value="'. locale_number_format($_POST['SLBreak12m'],0) . '" /></td>
		</tr>';

	echo '<tr>
			<td>' . _('Tendencia') . ':</td>
			<td><select name="Tendencia">
				<option selected="selected" value="All">' . _('Ambdues') . '</option>
				<option value="A">' . _('Alcista') . '</option>
				<option value="B">' . _('Baixista') . '</option>
			</select></td>
		</tr>';

		echo '<tr>
		<td>' . _('Tipus Ordre Entrada') . ':</td>
		<td><select name="TipusOrdre">
			<option selected="selected" value="All">' . _('Ambdues') . '</option>
			<option value="RR">' . _('RR') . '</option>
			<option value="LT">' . _('LT') . '</option>
		</select></td>
	</tr>';

	echo '<tr><td>' . _('Moneda Ticker') . ':</td>
				<td><select name="Currency">';
	$CurrencyResult= DB_query("SELECT currency, currabrev FROM currencies ORDER BY currency",$db);
	echo '<option selected="selected" value="All">' . _('All')  . '</option>';
	While ($myrow = DB_fetch_array($CurrencyResult)){
		echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['currency']  . '</option>';
	}
	echo '</select></td></tr>';

  echo '<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr><td>&nbsp;</td></tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="' . _('Busca Ordres Disponibles') . '" /></td>
		</tr>
		</table>
	<br />';
   echo '</div>
         </form>';

} // End of function display()

include('includes/footer.inc');
?>