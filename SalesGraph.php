<?php

require(__DIR__ . '/includes/session.php');

$Title=__('Sales Report Graph');
$ViewTopic = 'ARInquiries';
$BookMark = 'SalesGraph';
include('includes/header.php');

$SelectADifferentPeriod ='';

if (isset($_POST['FromPeriod']) AND isset($_POST['ToPeriod'])){

	if ($_POST['FromPeriod'] > $_POST['ToPeriod']){
		prnMsg(__('The selected period from is actually after the period to! Please re-select the reporting period'),'error');
		$SelectADifferentPeriod =__('Select A Different Period');
	}
/*	There is no PHPlot reason to restrict the graph to 12 months...
	if ($_POST['ToPeriod'] - $_POST['FromPeriod'] >12){
		prnMsg(__('The selected period range is more than 12 months - only graphs for a period less than 12 months can be created'),'error');
		$SelectADifferentPeriod= __('Select A Different Period');
	}
*/	if ((!isset($_POST['ValueFrom']) OR $_POST['ValueFrom']=='' OR !isset($_POST['ValueTo']) OR $_POST['ValueTo']=='') AND $_POST['GraphOn'] !='All'){
		prnMsg(__('For graphs including either a customer or item range - the range must be specified. Please enter the value from and the value to for the range'),'error');
		$SelectADifferentPeriod= __('Select A Different Period');
	}
}

if ((! isset($_POST['FromPeriod']) OR ! isset($_POST['ToPeriod']))
	OR $SelectADifferentPeriod==__('Select A Different Period')){

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<fieldset>
			<legend>', __('Report Criteria'), '</legend>';

	ECHO '<field>
			<label for="ToPeriod">' . __('Select Period From') . ':</label>
			<select name="FromPeriod">';

	if (Date('m') > $_SESSION['YearEnd']){
		/*Dates in SQL format */
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')));
	} else {
		$DefaultFromDate = Date ('Y-m-d', Mktime(0,0,0,$_SESSION['YearEnd'] + 2,0,Date('Y')-1));
	}
	$SQL = "SELECT periodno, lastdate_in_period FROM periods ORDER BY periodno";
	$Periods = DB_query($SQL);

	while ($MyRow=DB_fetch_array($Periods)){
		if(isset($_POST['FromPeriod']) AND $_POST['FromPeriod']!=''){
			if( $_POST['FromPeriod']== $MyRow['periodno']){
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' .MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		} else {
			if($MyRow['lastdate_in_period']==$DefaultFromDate){
				echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
			}
		}
	}

	echo '</select>
		</field>';
	if (!isset($_POST['ToPeriod']) OR $_POST['ToPeriod']==''){
		$DefaultToPeriod = GetPeriod(DateAdd(ConvertSQLDate($DefaultFromDate),'m',11));
	} else {
		$DefaultToPeriod = $_POST['ToPeriod'];
	}

	echo '<field>
			<label for="ToPeriod">' . __('Select Period To')  . ':</label>
			<select name="ToPeriod">';

	DB_data_seek($Periods,0);

	while ($MyRow=DB_fetch_array($Periods)){

		if($MyRow['periodno']==$DefaultToPeriod){
			echo '<option selected="selected" value="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		} else {
			echo '<option value ="' . $MyRow['periodno'] . '">' . MonthAndYearFromSQLDate($MyRow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select>
		</field>';

	if (!isset($_POST['Period'])) {
		$_POST['Period'] = '';
	}

	echo '<field>
			<label for="Period">', '<b>' , __('OR') , ' </b>' , __('Select Period'), '</label>
			', ReportPeriodList($_POST['Period'], array('l', 't')), '
		</field>';

	$AreasResult = DB_query("SELECT areacode, areadescription FROM areas ORDER BY areadescription");

	if (!isset($_POST['SalesArea'])){
		$_POST['SalesArea']='';
	}
	echo '<field>
			<label for="SalesArea">' . __('For Sales Area/Region:')  . '</label>
			<select name="SalesArea">';
	if($_POST['SalesArea']=='All'){
		echo '<option selected="selected" value="All">' . __('All') . '</option>';
	} else {
		echo '<option value="All">' . __('All') . '</option>';
	}
	while ($MyRow=DB_fetch_array($AreasResult)){
		if($MyRow['areacode']==$_POST['SalesArea']){
			echo '<option selected="selected" value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['areacode'] . '">' . $MyRow['areadescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	$CategoriesResult = DB_query("SELECT categoryid, categorydescription FROM stockcategory ORDER BY categorydescription");

	if (!isset($_POST['CategoryID'])){
		$_POST['CategoryID']='';
	}
	echo '<field>
			<LABEL FOR="CategoryID">' . __('For Stock Category')  . ':</LABEL>
			<select name="CategoryID">';
	if($_POST['CategoryID']=='All'){
		echo '<option selected="selected" value="All">' . __('All') . '</option>';
	} else {
		echo '<option value="All">' . __('All') . '</option>';
	}
	while ($MyRow=DB_fetch_array($CategoriesResult)){
		if($MyRow['categoryid']==$_POST['CategoryID']){
			echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
		}
	}
	echo '</select>
		</field>';

	$SalesFolkResult = DB_query("SELECT salesmancode, salesmanname FROM salesman ORDER BY salesmanname");

	if (! isset($_POST['SalesmanCode'])){
 		$_POST['SalesmanCode'] = '';
	}

	echo '<field>
			<label for="SalesmanCode">' . __('For Salesperson:') . '</label>
			<select name="SalesmanCode">';

	if($_POST['SalesmanCode']=='All'){
		echo '<option selected="selected" value="All">' . __('All') . '</option>';
	} else {
		echo '<option value="All">' . __('All') . '</option>';
	}
	while ($MyRow=DB_fetch_array($SalesFolkResult)){
		if ($MyRow['salesmancode']== $_POST['SalesmanCode']){
			echo '<option selected="selected" value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
		} else {
			echo '<option value="' . $MyRow['salesmancode'] . '">' . $MyRow['salesmanname'] . '</option>';
		}
	}
	echo '</select>
			<fieldtext>' . $_POST['SalesmanCode'] . '</fieldtext>
		</field>';

	echo '<field>
			<label for="GraphType">' . __('Graph Type') . '</label>
			<select name="GraphType">
				<option value="bars">' . __('Bar Graph') . '</option>
				<option value="stackedbars">' . __('Stacked Bar Graph') . '</option>
				<option value="lines">' . __('Line Graph') . '</option>
				<option value="linepoints">' . __('Line Point Graph') . '</option>
				<option value="area">' . __('Area Graph') . '</option>
				<option value="points">' . __('Points Graph') . '</option>
				<option value="pie">' . __('Pie Graph') . '</option>
				<option value="thinbarline">' . __('Thin Bar Line Graph') . '</option>
				<option value="squared">' . __('Squared Graph') . '</option>
				<option value="stackedarea">' . __('Stacked Area Graph') . '</option>
			</select>
			</field>';

	if (!isset($_POST['ValueFrom'])){
		$_POST['ValueFrom']='';
	}
	if (!isset($_POST['ValueTo'])){
		$_POST['ValueTo']='';
	}
	echo '<field>
			<label for="GraphOn">' . __('Graph On:') . '</label>
			<fieldset>
				<div><input type="radio" id="All" name="GraphOn" value="All" checked="checked" /><label for="All">' . __('All') . '</label></div>
				<div><input type="radio" id="Customer" name="GraphOn" value="Customer" /><label for="Customer">' . __('Customer') . '</label></div>
				<div><input type="radio" id="StockID" name="GraphOn" value="StockID" /><label for="StockID">' . __('Item Code') . '</label></div>
			</fieldset>
		</field>';
	echo '<field>
			<label for="ValueFrom">' . __('From:') . '</label>
			<input type="text" name="ValueFrom" value="' . $_POST['ValueFrom'] . '" />
		</field>
		<field>
	 		<label for="ValueTo">' . __('To:') . '</label>
	 		<input type="text" name="ValueTo" value="' . $_POST['ValueTo'] . '" />
	 	</field>';

	echo '<field>
			<label for="GraphValue">' . __('Graph Value:') . '</label>
			<fieldset>
				<div><label>' . __('Net Sales Value') . '</label><input type="radio" name="GraphValue" value="Net" checked="checked" /></div>
				<div><label>' . __('Gross Profit') . '</label><input type="radio" name="GraphValue" value="GP" /></div>
				<div><label>' . __('Quantity') . '</label><input type="radio" name="GraphValue" value="Quantity" /></div>
			</fieldset>
		</field>';

	echo '</fieldset>
			<div class="centre"><input type="submit" name="ShowGraph" value="' . __('Show Sales Graph') .'" /></div>
		</form>';
	include('includes/footer.php');
} else {

	$graph = new Phplot\Phplot\phplot(950,450);
	$SelectClause ='';
	$WhereClause ='';
	$GraphTitle ='';
	if ($_POST['GraphValue']=='Net') {
		$GraphTitle = __('Sales Value');
		$SelectClause = 'amt - disc';
	} elseif ($_POST['GraphValue']=='GP'){
		$GraphTitle = __('Gross Profit');
		$SelectClause = '(amt - disc - cost)';
	} else {
		$GraphTitle = __('Unit Sales');
		$SelectClause = 'qty';
	}

	if ($_POST['Period'] != '') {
		$_POST['FromPeriod'] = ReportPeriod($_POST['Period'], 'From');
		$_POST['ToPeriod'] = ReportPeriod($_POST['Period'], 'To');
	}

	$SQL = "SELECT YEAR(`lastdate_in_period`) AS year, MONTHNAME(`lastdate_in_period`) AS month
			  FROM `periods`
			 WHERE `periodno`='" . $_POST['FromPeriod'] . "' OR periodno='" . $_POST['ToPeriod'] . "'";

	$Result = DB_query($SQL);

	$FromPeriod = DB_fetch_array($Result);
	$Starting = $FromPeriod['month'] . ' ' . $FromPeriod['year'];

	$ToPeriod = DB_fetch_array($Result);
	$Ending = $ToPeriod['month'] . ' ' . $ToPeriod['year'];

	$GraphTitle .= ' ' . __('From Period') . ' ' . $Starting . ' ' . __('to') . ' ' . $Ending . "\n\r";

	if ($_POST['SalesArea']=='All'){
		$GraphTitle .= ' ' . __('For All Sales Areas');
	} else {
		$Result = DB_query("SELECT areadescription FROM areas WHERE areacode='" . $_POST['SalesArea'] . "'");
		$MyRow = DB_fetch_row($Result);
		$GraphTitle .= ' ' . __('For') . ' ' . $MyRow[0];
		$WhereClause .= " area='" . $_POST['SalesArea'] . "' AND";
	}
	if ($_POST['CategoryID']=='All'){
		$GraphTitle .= ' ' . __('For All Stock Categories');
	} else {
		$Result = DB_query("SELECT categorydescription FROM stockcategory WHERE categoryid='" . $_POST['CategoryID'] . "'");
		$MyRow = DB_fetch_row($Result);
		$GraphTitle .= ' ' . __('For') . ' ' . $MyRow[0];
		$WhereClause .= " stkcategory='" . $_POST['CategoryID'] . "' AND";

	}
	if ($_POST['SalesmanCode']=='All'){
		$GraphTitle .= ' ' . __('For All Salespeople');
	} else {
		$Result = DB_query("SELECT salesmanname FROM salesman WHERE salesmancode='" . $_POST['SalesmanCode'] . "'");
		$MyRow = DB_fetch_row($Result);
		$GraphTitle .= ' ' . __('For Salesperson:') . ' ' . $MyRow[0];
		$WhereClause .= " salesperson='" . $_POST['SalesmanCode'] . "' AND";

	}
	if ($_POST['GraphOn']=='Customer'){
		$GraphTitle .= ' ' . __('For Customers from') . ' ' . $_POST['ValueFrom'] . ' ' . __('to') . ' ' . $_POST['ValueTo'];
		$WhereClause .= "  cust >='" . $_POST['ValueFrom'] . "' AND cust <='" . $_POST['ValueTo'] . "' AND";
	}
	if ($_POST['GraphOn']=='StockID'){
		$GraphTitle .= ' ' . __('For Items from') . ' ' . $_POST['ValueFrom'] . ' ' . __('to') . ' ' . $_POST['ValueTo'];
		$WhereClause .= "  stockid >='" . $_POST['ValueFrom'] . "' AND stockid <='" . $_POST['ValueTo'] . "' AND";
	}

	$WhereClause = "WHERE " . $WhereClause . " salesanalysis.periodno>='" . $_POST['FromPeriod'] . "' AND salesanalysis.periodno <= '" . $_POST['ToPeriod'] . "'";

	$SQL = "SELECT salesanalysis.periodno,
				periods.lastdate_in_period,
				SUM(CASE WHEN budgetoractual=1 THEN " . $SelectClause . " ELSE 0 END) AS sales,
				SUM(CASE WHEN  budgetoractual=0 THEN " . $SelectClause . " ELSE 0 END) AS budget
		FROM salesanalysis INNER JOIN periods ON salesanalysis.periodno=periods.periodno " . $WhereClause . "
		GROUP BY salesanalysis.periodno,
			periods.lastdate_in_period
		ORDER BY salesanalysis.periodno";

	$graph->SetTitle($GraphTitle);
	$graph->SetTitleColor('blue');
	$graph->SetOutputFile($_SESSION['reports_dir'] . '/salesgraph.png');
	$graph->SetXTitle(__('Month'));
	if ($_POST['GraphValue']=='Net'){
		$graph->SetYTitle(__('Sales Value'));
	} elseif ($_POST['GraphValue']=='GP'){
		$graph->SetYTitle(__('Gross Profit'));
	} else {
		$graph->SetYTitle(__('Quantity'));
	}
	$graph->SetXTickPos('none');
	$graph->SetXTickLabelPos('none');
	$graph->SetXLabelAngle(90);
	$graph->SetBackgroundColor('white');
	$graph->SetTitleColor('blue');
	$graph->SetFileFormat('png');
	$graph->SetPlotType($_POST['GraphType']);
	$graph->SetIsInline('1');
	$graph->SetShading(5);
	$graph->SetDrawYGrid(true);
	$graph->SetDataType('text-data');
	$graph->SetNumberFormat($DecimalPoint, $ThousandsSeparator);
	$graph->SetPrecisionY($_SESSION['CompanyRecord']['decimalplaces']);

	$SalesResult = DB_query($SQL);
	if (DB_error_no() !=0) {

		prnMsg(__('The sales graph data for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg(),'error');
		include('includes/footer.php');
		exit();
	}
	if (DB_num_rows($SalesResult)==0){
		prnMsg(__('There is not sales data for the criteria entered to graph'),'info');
		include('includes/footer.php');
		exit();
	}

	$GraphArray = array();
	$i = 0;
	while ($MyRow = DB_fetch_array($SalesResult)){
		$GraphArray[$i] = array(MonthAndYearFromSQLDate($MyRow['lastdate_in_period']),$MyRow['sales'],$MyRow['budget']);
		$i++;
	}

	$graph->SetDataValues($GraphArray);
	$graph->SetDataColors(
		array('grey','wheat'),  //Data Colors
		array('black')	//Border Colors
	);
	$graph->SetLegend(array(__('Actual'),__('Budget')));
	$graph->SetYDataLabelPos('plotin');

	//Draw it
	$graph->DrawGraph();
	echo '<table class="selection">
			<tr>
				<td><p><img class="graph" src="',$RootPath,'/', $_SESSION['reports_dir'], '/salesgraph.png" alt="Sales Report Graph"></img></p></td>
			</tr>
		  </table>';
	include('includes/footer.php');
}
