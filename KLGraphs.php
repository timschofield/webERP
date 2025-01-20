<?php

include('includes/session.php');
include('includes/phplot/phplot.php');
include('includes/UIGeneralFunctions.php');
include('includes/KLUIGeneralFunctions.php');

$Title=_('KPI Graph');

include('includes/header.php');

$ErrorInDates =FALSE;

$_POST['GraphType'] = 'lines';

if (!isset($_POST['FromDate'])){
	// 1 year from today
	$_POST['FromDate'] = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',-365);
}
if (!isset($_POST['ToDate'])){
	$_POST['ToDate'] = Date($_SESSION['DefaultDateFormat']);
}

if (!isset($_POST['Concept'])){
	$_POST['Concept']='';
}

if (isset($_POST['FromDate']) AND isset($_POST['ToDate'])){
	if (FormatDateForSQL($_POST['FromDate']) > FormatDateForSQL($_POST['ToDate'])){
		prnMsg(_('The selected date from is actually after the date to!'),'error');
		$ErrorInDates =TRUE;
	}
}

if (!isset($_POST['FromDate']) 
	OR !isset($_POST['ToDate'])
	OR !isset($_POST['Concept'])
	OR $_POST['Concept']==''
	OR $ErrorInDates){

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	echo '<fieldset>
			<legend>' . _('KPI Graph Parameters') . '</legend>';

	echo FieldToSelectOneDate('FromDate', $_POST['FromDate'], _('From'), '', '', '', true, false);
	echo FieldToSelectOneDate('ToDate', $_POST['ToDate'], _('To'), '', '', '', true, false);
	echo FieldToSelectOneKPIConcept('Concept', $_POST['Concept'], _('KPI Concept to Graph'), '', '', '', true, false);

	echo '</fieldset>';

	echo OneButtonCenteredForm('ShowGraph', _('Show KPI Graph'));
	
	echo '</div>
        </form>';
	include('includes/footer.php');

} else {

	$SQL = "SELECT date,
				value
			FROM klkpi 
			WHERE concept = '".$_POST['Concept']."'
				AND date >='" . FormatDateForSQL($_POST['FromDate']) . "' 
				AND date <= '" . FormatDateForSQL($_POST['ToDate']) . "'
			ORDER BY date ASC";

	$KPIResult = DB_query($SQL);
	if (DB_error_no() !=0) {

		prnMsg(_('The KPI graph data for the selected criteria could not be retrieved because') . ' - ' . DB_error_msg(),'error');
		prnMsg($SQL);
		include('includes/footer.php');
		exit;
	}
	if (DB_num_rows($KPIResult)==0){
		prnMsg(_('There is not KPI data for the criteria entered to graph'),'info');
		prnMsg($SQL);
		include('includes/footer.php');
		exit;
	}

	$GraphArray = array();
	$i = 0;
	$InitialDate = "";
	$FinalDate = "";
	while ($MyRow = DB_fetch_array($KPIResult)){
		if ($InitialDate == ""){
			// first row, we can get the frist date, in case we don't have the full range requested
			$InitialDate = $MyRow['date'];
		}
		$FinalDate = $MyRow['date'];
		$GraphArray[$i] = array($MyRow['date'],$MyRow['value']);
		$i++;
	}

	$GraphTitle = $_POST['Concept'] . ' ' . _('From') . ' ' . ConvertSQLDate($InitialDate) . ' ' . _('to') . ' ' . ConvertSQLDate($FinalDate) . "\n\r";

	$graph = new PHPlot(1200,600);
	$graph->SetTitleColor('blue');
	$graph->SetTitle($GraphTitle);
	$graph->SetOutputFile('companies/' .$_SESSION['DatabaseName'] .  '/reports/kpigraph.png');
	$graph->SetXTitle(_('Date'));
	$graph->SetYTitle(_('KPI Value'));
	$graph->SetXTickPos('none');
	$graph->SetXTickLabelPos('none');
//	$graph->SetXDataLabelPos('none'); do not draw the dates in X axis
	$graph->SetXLabelAngle(90);
	$graph->SetBackgroundColor('white');
	$graph->SetFileFormat('png');
	$graph->SetPlotType($_POST['GraphType']);
	$graph->SetIsInline(TRUE);
	$graph->SetShading(5);
	$graph->SetDrawYGrid(TRUE);
	$graph->SetDataType('text-data');
	$graph->SetNumberFormat($DecimalPoint, $ThousandsSeparator);
	$graph->SetPrecisionY($_SESSION['CompanyRecord']['decimalplaces']);
	$graph->SetYDataLabelPos('none');
	$graph->TuneYAutoRange(0, 0, 0);
	$graph->SetDataColors(
		array('blue'),  //Data Colors
		array('black')	//Border Colors
	);
	$graph->SetDataValues($GraphArray);

	//Draw it
	$graph->DrawGraph();
	echo '<table class="selection">
			<tr>
				<td><p><img src="companies/' .$_SESSION['DatabaseName'] .  '/reports/kpigraph.png" alt="KPI Graph"></img></p></td>
			</tr>
		  </table>';
	unset ($_POST['Concept']);
	include('includes/footer.php');
}
?>
