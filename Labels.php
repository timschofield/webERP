<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Label Templates');
$ViewTopic = 'Setup';
$BookMark = 'Labels.php';
include('includes/header.php');

//define PaperSize array sizes in mm
$PaperSize = array();
$PaperSize['A4']['PageHeight'] = 297;
$PaperSize['A4']['PageWidth'] = 210;
$PaperSize['A5']['PageHeight'] = 210;
$PaperSize['A5']['PageWidth'] = 148;
$PaperSize['A3']['PageHeight'] = 420;
$PaperSize['A3']['PageWidth'] = 297;
$PaperSize['Letter']['PageHeight'] = 279.4;
$PaperSize['Letter']['PageWidth'] = 215.9;
$PaperSize['Legal']['PageHeight'] = 355.6;
$PaperSize['Legal']['PageWidth'] = 215.9;

$LabelPaper['DPS01 *']['PageWidth'] = 210;
$LabelPaper['DPS01 *']['PageHeight']= 297;
$LabelPaper['DPS01 *']['Height'] 	= 297;
$LabelPaper['DPS01 *']['TopMargin'] = 0;
$LabelPaper['DPS01 *']['Width'] 	= 210;
$LabelPaper['DPS01 *']['LeftMargin']= 0;
$LabelPaper['DPS01 *']['RowHeight'] = 297;
$LabelPaper['DPS01 *']['ColumnWidth']= 210;
$LabelPaper['DPS01 *']['PageWidth'] = 210;
$LabelPaper['DPS01 *']['PageHeight']= 297;

/*
'DPS01 *',210,297,210,297,0,0,0,0);
'DPS02 *',210,297,210,149,0,0,0,0);
'DPS08 *',210,297,105,71,7,7,0,0);
'DPS10 *',210,297,105,59.6,0,0,0,0);
'DPS16 *',210,297,105,37,0,0,0,0);
'DPS24 *',210,297,70,36,0,0,0,0);
'DPS30 *',210,297,70,30,0,0,0,0);
'DPS04 *',210,297,105,149,0,0,0,0);
'J5101 *',210,297,38,69,0,0,0,0);
'J5102 *',210,297,63.5,38,0,0,0,0);
'J5103 *',210,297,38,135,0,0,0,0);
'L4730 *',210,297,17.8,10,0,0,0,0);
'L4743 *',210,297,99.1,42.3,0,0,0,0);
'L6008 *',210,297,25.4,10,0,0,0,0);
'L6009 *',210,297,45.7,21.2,0,0,0,0);
'L6011 *',210,297,63.5,29.6,0,0,0,0);
'L6012 *',210,297,96,50.8,0,0,0,0);
'L7102 *',210,297,192,39,0,0,0,0);
'L7159 *',210,297,63.5,33.9,0,0,0,0);
'L7160 *',210,297,63.5,38.1,0,0,0,0);
'L7161 *',210,297,63.5,46.6,0,0,0,0);
'L7162 *',210,297,99.1,34,0,0,0,0);
'L7163 *',210,297,99.1,38.1,0,0,0,0);
'L7164',210,297,63.5,72,0,0,0,0);
'L7165 *',210,297,99.1,67.7,0,0,0,0);
'L7166',210,297,99.1,93.1,0,0,0,0);
'L7167 *',210,297,199.6,289.1,0,0,0,0);
'L7168 *',210,297,199.6,143.5,0,0,0,0);
'L7169 *',210,297,99.1,139,0,0,0,0);
'L7170 *',210,297,134,11,0,0,0,0);
'L7171 *',210,297,200,60,0,0,0,0);
'L7172 *',210,297,100,30,0,0,0,0);
'L7173 *',210,297,99.1,57,0,0,0,0);
'L7409 *',210,297,57,15,0,0,0,0);
'L7644 *',210,297,133,29.6,0,0,0,0);
'L7651 *',210,297,38.1,21.2,0,0,0,0);
'L7654 *',210,297,45.7,25.4,0,0,0,0);
'L7664 *',210,297,71,70,0,0,0,0);
'L7665 *',210,297,72,21.15,0,0,0,0);
'L7666 *',210,297,70,52,0,0,0,0);
'L7668 *',210,297,59,51,0,0,0,0);
'L7670 *',210,297,65,65,0,0,0,0);
'L7671 *',210,297,76.2,46.4,0,0,0,0);
'L7674 *',210,297,145,17,0,0,0,0);
'L7701 *',210,297,192,62,0,0,0,0);
'EL1S',210,297,210,287,0,0,0,0);
'VSL3B',210,297,191,99.48,0,0,0,0);
'EL3 LL03NSE',210,297,210,99.48,0,0,0,0);
'SLSQ95',210,297,95,95,0,0,0,0);
'EL6 LL06NSE',210,297,105,99.48,0,0,0,0);
'VSL6',210,297,70,149,0,0,0,0);
'EL8 LL08NSE',210,297,105,74.2,0,0,0,0);
'EL8SB',210,297,72,99,0,0,0,0);
'EL10S',210,297,105,57,0,0,0,0);
'EL12S',210,297,105,48,0,0,0,0);
'EL14 LL14NSE',210,297,105,42.5,0,0,0,0);
'EL15S',210,297,70,50,0,0,0,0);
'SLSQ51',210,297,51,51,0,0,0,0);
'EL15',210,297,70,59.6,0,0,0,0);
'EL16S LL16SE',210,297,105,35,0,0,0,0);
'EL21S LL21SE',210,297,70,38,0,0,0,0);
'EL21',210,297,70,42.5,0,0,0,0);
'EL24LS',210,297,70,34,0,0,0,0);
'EL24S LL24SE',210,297,70,35,0,0,0,0);
'EL24 LL24NSE',210,297,70,37,0,0,0,0);
'EL27S',210,297,70,32,0,0,0,0);
'VSL33D',210,297,53,21,0,0,0,0);
'EL33S',210,297,70,25.4,0,0,0,0);
'SLSQ37',210,297,37,37,0,0,0,0);
'VSL36SB',210,297,90,12,0,0,0,0);
'LL36',210,297,48.9,29.6,0,0,0,0);
'VSL56SB',210,297,89,10,0,0,0,0);
'EL56',210,297,52.5,21.3,0,0,0,0);
'SLSQ25',210,297,25,25,0,0,0,0);

*/

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Label Template Maintenance')
	. '" alt="" />' . $Title . ' - ' . __('all measurements in mm.') .
	'</p>';

if (!function_exists('gd_info')) {
	prnMsg(__('The GD module for PHP is required to print barcode labels. Your PHP installation is not capable currently. You will most likely experience problems with this script until the GD module is enabled.'),'error');
}

if (isset($_POST['SelectedLabelID'])){
	$SelectedLabelID =$_POST['SelectedLabelID'];
	if (ctype_digit($_POST['NoOfFieldsDefined'])){ //Now Process any field updates

		for ($i=0;$i<=$_POST['NoOfFieldsDefined'];$i++){

			if (ctype_digit($_POST['VPos' . $i])
				AND ctype_digit($_POST['HPos' . $i])
				AND ctype_digit($_POST['FontSize' . $i])){ // if all entries are integers

				$Result = DB_query("UPDATE labelfields SET fieldvalue='" . $_POST['FieldName' . $i] . "',
														vpos='" . $_POST['VPos' . $i] . "',
														hpos='" . $_POST['HPos' . $i] . "',
														fontsize='" . $_POST['FontSize' . $i] . "',
														barcode='" . $_POST['Barcode' . $i] . "'
								WHERE labelfieldid='" . $_POST['LabelFieldID' . $i] . "'");
			} else {
				prnMsg(__('Entries for Vertical Position, Horizontal Position, and Font Size must be integers.'),'error');
			}
		}
	}
	if (ctype_digit($_POST['VPos'])
		AND ctype_digit($_POST['HPos'])
		AND ctype_digit($_POST['FontSize'])){

		//insert the new label field entered

		$Result = DB_query("INSERT INTO labelfields (labelid,
													fieldvalue,
													vpos,
													hpos,
													fontsize,
													barcode)
							VALUES ('" . $SelectedLabelID . "',
									'" . $_POST['FieldName'] . "',
									'" . $_POST['VPos'] . "',
									'" . $_POST['HPos'] . "',
									'" . $_POST['FontSize'] . "',
									'" . $_POST['Barcode'] . "')");
	}
} elseif(isset($_GET['SelectedLabelID'])){
	$SelectedLabelID =$_GET['SelectedLabelID'];
	if (isset($_GET['DeleteField'])){ //then process any deleted fields
		$Result = DB_query("DELETE FROM labelfields WHERE labelfieldid='" . $_GET['DeleteField'] . "'");
	}
}

if (isset($_POST['submit'])) {
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	if ( trim( $_POST['Description'] ) == '' ) {
		$InputError = 1;
		prnMsg( __('The label description may not be empty'), 'error');
	}
	$Message = '';

	if (isset($_POST['PaperSize']) AND $_POST['PaperSize']!='custom'){

		$_POST['PageWidth'] = $PaperSize[$_POST['PaperSize']]['PageWidth'];
		$_POST['PageHeight'] = $PaperSize[$_POST['PaperSize']]['PageHeight'];

	} elseif ($_POST['PaperSize']=='custom' AND !isset($_POST['PageWidth'])){

		$_POST['PageWidth'] = 0;
		$_POST['PageHeight'] = 0;
	}

	if (isset($SelectedLabelID)) {

		/*SelectedLabelID could also exist if submit had not been clicked this code
		would not run in this case cos submit is false of course  see the
		delete code below*/

		$SQL = "UPDATE labels SET 	description = '" . $_POST['Description'] . "',
									height = '" . $_POST['Height'] . "',
									topmargin = '". $_POST['TopMargin'] . "',
									width = '". $_POST['Width'] . "',
									leftmargin = '". $_POST['LeftMargin'] . "',
									rowheight =  '". $_POST['RowHeight'] . "',
									columnwidth = '". $_POST['ColumnWidth'] . "',
									pagewidth = '" . $_POST['PageWidth'] . "',
									pageheight = '" . $_POST['PageHeight'] . "'
				WHERE labelid = '" . $SelectedLabelID . "'";

		$ErrMsg = __('The update of this label template failed because');
		$Result = DB_query($SQL, $ErrMsg);

		$Message = __('The label template has been updated');
		prnMsg($Message, 'success');

	} elseif ($InputError !=1) {

	/*Selected label is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new label form */

		$SQL = "INSERT INTO labels (description,
									height,
									topmargin,
									width,
									leftmargin,
									rowheight,
									columnwidth,
									pagewidth,
									pageheight)
			VALUES ('" . $_POST['Description'] . "',
					'" . $_POST['Height'] . "',
					'" . $_POST['TopMargin'] . "',
					'" . $_POST['Width'] . "',
					'" . $_POST['LeftMargin'] . "',
					'" . $_POST['RowHeight'] . "',
					'" . $_POST['ColumnWidth'] . "',
					'" . $_POST['PageWidth'] . "',
					'" . $_POST['PageHeight'] . "')";

		$ErrMsg = __('The addition of this label failed because');
		$Result = DB_query($SQL, $ErrMsg);
		$Message = __('The new label template has been added to the database');
		prnMsg($Message, 'success');
	}

	if (isset($InputError) AND $InputError !=1) {
		unset($_POST['PaperSize']);
		unset($_POST['Description']);
		unset($_POST['Width']);
		unset($_POST['Height']);
		unset($_POST['TopMargin']);
		unset($_POST['LeftMargin']);
		unset($_POST['ColumnWidth']);
		unset($_POST['RowHeight']);
		unset($_POST['PageWidth']);
		unset($_POST['PageHeight']);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	/*Cascade deletes in labelfields */
	$Result = DB_query("DELETE FROM labelfields WHERE labelid= '" . $SelectedLabelID . "'");
	$Result = DB_query("DELETE FROM labels WHERE labelid= '" . $SelectedLabelID . "'");
	prnMsg(__('The selected label template has been deleted'),'success');
	unset ($SelectedLabelID);
}

if (!isset($SelectedLabelID)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedLabelID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of label templates will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$SQL = "SELECT labelid,
				description,
				pagewidth,
				pageheight,
				height,
				width,
				topmargin,
				leftmargin,
				rowheight,
				columnwidth
			FROM labels";

	$ErrMsg = __('CRITICAL ERROR') . '! ' . __('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . __('The defined label templates could not be retrieved because');
	$Result = DB_query($SQL, $ErrMsg);

	if (DB_num_rows($Result)>0){
		echo '<table class="selection">
				<tr>
					<th>' . __('Description') . '</th>
					<th>' . __('Rows x Cols') . '</th>
					<th>' . __('Page Width') . '</th>
					<th>' . __('Page Height') . '</th>
					<th>' . __('Height') . '</th>
					<th>' . __('Width') . '</th>
					<th>' . __('Row Height') . '</th>
					<th>' . __('Column Width') . '</th>
					<th colspan="2"></th>
				</tr>';

		while ($MyRow = DB_fetch_array($Result)) {

			if ($MyRow['rowheight']>0) {
				$NoOfRows = floor(($MyRow['pageheight']-$MyRow['topmargin'])/$MyRow['rowheight']);
			} else {
				$NoOfRows = 0;
			}
			if ($MyRow['columnwidth']>0) {
				$NoOfCols = floor(($MyRow['pagewidth']-$MyRow['leftmargin'])/$MyRow['columnwidth']);
			} else {
				$NoOfCols = 0;
			}

			foreach ($PaperSize as $PaperName=>$PaperType) {
				if ($PaperType['PageWidth'] == $MyRow['pagewidth'] AND $PaperType['PageHeight'] == $MyRow['pageheight']) {
					$Paper = $PaperName;
				}
			}
			if (isset($Paper)){
				echo'<tr class="striped_row">
						<td>', $MyRow['description'], '</td>
						<td>', $NoOfRows . ' x ' . $NoOfCols, '</td>
						<td colspan="2">', $Paper, '</td>
						<td class="number">', $MyRow['height'], '</td>
						<td class="number">', $MyRow['width'], '</td>
						<td class="number">', $MyRow['rowheight'], '</td>
						<td class="number">', $MyRow['columnwidth'], '</td>
						<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedLabelID=', $MyRow['labelid'], '">' . __('Edit') . '</a></td>
						<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedLabelID=', $MyRow['labelid'], '&delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this label?') . '\');">' . __('Delete') . '</a></td>
					</tr>';
			} else {
				echo '<tr class="striped_row">
						<td>', $MyRow['description'], '</td>
						<td>', $NoOfRows . ' x ' . $NoOfCols, '</td>
						<td class="number">', $MyRow['pagewidth'], '</td>
						<td class="number">', $MyRow['pageheight'], '</td>
						<td class="number">', $MyRow['height'], '</td>
						<td class="number">', $MyRow['width'], '</td>
						<td class="number">', $MyRow['rowheight'], '</td>
						<td class="number">', $MyRow['columnwidth'], '</td>
						<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedLabelID=', $MyRow['labelid'], '">' . __('Edit') . '</a></td>
						<td><a href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedLabelID=', $MyRow['labelid'], '&delete=yes" onclick="return confirm(\'' . __('Are you sure you wish to delete this label?') . '\');">' . __('Delete') . '</a></td>
					</tr>';
			}
		}
		//END WHILE LIST LOOP

		//end of ifs and buts!

		echo '</table>';
	} //end if there are label definitions to show
}

if (isset($SelectedLabelID)) {
	echo '<div class="centre">
			<a href="' .  htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . __('Review all defined label records') . '</a>
		</div>';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedLabelID)) {
	//editing an existing label

	$SQL = "SELECT pagewidth,
					pageheight,
					description,
					height,
					width,
					topmargin,
					leftmargin,
					rowheight,
					columnwidth
			FROM labels
			WHERE labelid='" . $SelectedLabelID . "'";

	$Result = DB_query($SQL);
	$MyRow = DB_fetch_array($Result);

	$_POST['PageWidth']	= $MyRow['pagewidth'];
	$_POST['PageHeight']	= $MyRow['pageheight'];
	$_POST['Description']	= $MyRow['description'];
	$_POST['Height']		= $MyRow['height'];
	$_POST['TopMargin']	= $MyRow['topmargin'];
	$_POST['Width'] 	= $MyRow['width'];
	$_POST['LeftMargin']	= $MyRow['leftmargin'];
	$_POST['RowHeight']	= $MyRow['rowheight'];
	$_POST['ColumnWidth']	= $MyRow['columnwidth'];

	foreach ($PaperSize as $PaperName=>$PaperType) {
		if ($PaperType['PageWidth'] == $MyRow['pagewidth'] AND $PaperType['PageHeight'] == $MyRow['pageheight']) {
			$_POST['PaperSize'] = $PaperName;
		}
	}

	echo '<input type="hidden" name="SelectedLabelID" value="' . $SelectedLabelID . '" />';

}  //end of if $SelectedLabelID only do the else when a new record is being entered


if (!isset($_POST['Description'])) {
	$_POST['Description']='';
}
echo '<fieldset class="2column">
		<legend>', __('Label Details'), '</legend>
		<fieldset class="column1">
			<img class="label" src="css/paramsLabel.png">
			</fieldset>
			<fieldset class="Column2">
				<field>
					<label for="Description">' . __('Label Description') . ':</label>
					<input type="text" name="Description" size="21" maxlength="20" value="' . $_POST['Description'] . '" />
				</field>
				<field>
					<label for="PaperSize">' . __('Label Paper Size') . ':</label>
					<select name="PaperSize" onchange="ReloadForm(submit)" >';

if (!isset($_POST['PaperSize'])){
	echo '<option selected="selected" value="custom">' . __('Custom Size') . '</option>';
} else {
	echo '<option value="custom">' . __('Custom Size') . '</option>';
}
foreach($PaperSize as $PaperType=>$PaperSizeElement) {
	if (isset($_POST['PaperSize']) AND $PaperType==$_POST['PaperSize']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $PaperType . '">' . $PaperType . '</option>';

} //end while loop

echo '</select>
	</field>';

if (!isset($_POST['PageHeight'])) {
	$_POST['PageHeight']=0;
}
if (!isset($_POST['PageWidth'])) {
	$_POST['PageWidth']=0;
}
if (!isset($_POST['Height'])) {
	$_POST['Height']=0;
}
if (!isset($_POST['TopMargin'])) {
	$_POST['TopMargin']=5;
}
if (!isset($_POST['Width'])) {
	$_POST['Width']=0;
}
if (!isset($_POST['LeftMargin'])) {
	$_POST['LeftMargin']=10;
}
if (!isset($_POST['RowHeight'])) {
	$_POST['RowHeight']=0;
}

if (!isset($_POST['ColumnWidth'])) {
	$_POST['ColumnWidth']=0;
}

if (!isset($_POST['PaperSize']) OR $_POST['PaperSize'] == 'Custom') {
	if (!isset($_POST['PageWidth'])){
		$_POST['PageWidth'] = 0;
		$_POST['PageHeight'] = 0;
	}
	echo '<field>
			<label for="PageWidth">' . __('Page Width') . '</label>
			<input type="text" size="4" maxlength="4" name="PageWidth" value="' . $_POST['PageWidth'] . '" />
		</field>
		<field>
			<label for="PageHeight">' . __('Page Height') . '</label>
			<input type="text" size="4" maxlength="4" name="PageHeight" value="' . $_POST['PageHeight'] . '" />
		</field>';
}
echo '<field>
		<label for="Height">' . __('Label Height') . ' - (He):</label>
		<input type="text" name="Height" size="4" maxlength="4" value="' . $_POST['Height'] . '" />
	</field>
	<field>
		<label for="Width">' . __('Label Width') . ' - (Wi):</label>
		<input type="text" name="Width" size="4" maxlength="4" value="' . $_POST['Width'] . '" />
	</field>
	<field>
		<label for="TopMargin">' . __('Top Margin') . ' - (Tm):</label>
		<input type="text" name="TopMargin" size="4" maxlength="4" value="' . $_POST['TopMargin'] . '" />
	</field>
	<field>
		<label for="LeftMargin">' . __('Left Margin') . ' - (Lm):</label>
		<input type="text" name="LeftMargin" size="4" maxlength="4" value="' . $_POST['LeftMargin'] . '" />
	</field>
	<field>
		<label for="RowHeight">' . __('Row Height') . ' - (Rh):</label>
		<input type="text" name="RowHeight" size="4" maxlength="4" value="' . $_POST['RowHeight'] . '" />
	</field>
	<field>
		<label for="ColumnWidth">' . __('Column Width') . ' - (Cw):</label>
		<input type="text" name="ColumnWidth" size="4" maxlength="4" value="' . $_POST['ColumnWidth'] . '" />
	</field>
	</fieldset>
	</td></field>
	</fieldset>';

if (isset($SelectedLabelID)) {
	//get the fields to show
	$SQL = "SELECT labelfieldid,
					labelid,
					fieldvalue,
					vpos,
					hpos,
					fontsize,
					barcode
			FROM labelfields
			WHERE labelid = '" . $SelectedLabelID . "'
			ORDER BY vpos DESC";
	$ErrMsg = __('Could not get the label fields because');
	$Result = DB_query($SQL, $ErrMsg);
	$i=0;
	echo '<table class="selection">
				<tr>
				<td><img src="css/labelsDim.png"></td>
				<td><table>
					<tr>
						<th>' . __('Field') . '</th>
						<th>' . __('Vertical') . '<br />' . __('Position')  . '<br />(VPos)</th>
						<th>' . __('Horizontal') . '<br />' . __('Position') . '<br />(HPos)</th>
						<th>' . __('Font Size') . '</th>
						<th>' . __('Bar-code') . '</th>
					</tr>';
	if (DB_num_rows($Result)>0){
		while ($MyRow = DB_fetch_array($Result)) {

			echo '<input type="hidden" name="LabelFieldID' . $i . '" value="' . $MyRow['labelfieldid'] . '" />
			<tr class="striped_row"><td><select name="FieldName' . $i . '" onchange="ReloadForm(submit)">';
			if ($MyRow['fieldvalue']=='itemcode'){
				echo '<option selected="selected" value="itemcode">' . __('Item Code') . '</option>';
			} else {
				echo '<option value="itemcode">' . __('Item Code') . '</option>';
			}
			if ($MyRow['fieldvalue']=='itemdescription'){
				echo '<option selected="selected" value="itemdescription">' . __('Item Description') . '</option>';
			} else {
				echo '<option value="itemdescription">' . __('Item Descrption') . '</option>';
			}
			if ($MyRow['fieldvalue']=='barcode'){
				echo '<option selected="selected" value="barcode">' . __('Item Barcode') . '</option>';
			} else {
				echo '<option value="barcode">' . __('Item Barcode') . '</option>';
			}
			if ($MyRow['fieldvalue']=='price'){
				echo '<option selected="selected" value="price">' . __('Price') . '</option>';
			} else {
				echo '<option value="price">' . __('Price') . '</option>';
			}
			if ($MyRow['fieldvalue']=='logo'){
				echo '<option selected="selected" value="logo">' . __('Company Logo') . '</option>';
			} else {
				echo '<option value="logo">' . __('Company Logo') . '</option>';
			}
			echo '</select></td>
				<td><input type="text" name="VPos' . $i . '" size="4" maxlength="4" value="' . $MyRow['vpos'] . '" /></td>
				<td><input type="text" name="HPos' . $i . '" size="4" maxlength="4" value="' . $MyRow['hpos'] . '" /></td>
				<td><input type="text" name="FontSize' . $i . '" size="4" maxlength="4" value="' . $MyRow['fontsize'] . '" /></td>
				<td><select name="Barcode' . $i . '" onchange="ReloadForm(submit)">';
			if ($MyRow['barcode']==0){
				echo '<option selected="selected" value="0">' . __('No') . '</option>
						<option value="1">' . __('Yes') . '</option>';
			} else {
				echo '<option selected="selected" value="1">' . __('Yes') . '</option>
						<option value="0">' . __('No') . '</option>';
			}
			echo '</select></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedLabelID=' . $SelectedLabelID . '&amp;DeleteField=' . $MyRow['labelfieldid'] .' onclick="return confirm(\'' . __('Are you sure you wish to delete this label field?') . '\');">' . __('Delete') . '</a></td>
				</tr>';
			$i++;
		}
		//END WHILE LIST LOOP
		$i--; //last increment needs to be wound back

	} //end if there are label definitions to show
	echo '<input type="hidden" name="NoOfFieldsDefined" value="' . $i . '" />';

	echo '<tr>
		<td><select name="FieldName">
			<option value="itemcode">' . __('Item Code') . '</option>
			<option value="itemdescription">' . __('Item Descrption') . '</option>
			<option value="barcode">' . __('Item Barcode') . '</option>
			<option value="price">' . __('Price') . '</option>
			<option value="logo">' . __('Company Logo') . '</option>
			</select></td>
		<td><input type="text" size="4" maxlength="4" name="VPos" /></td>
		<td><input type="text" size="4" maxlength="4" name="HPos" /></td>
		<td><input type="text" size="4" maxlength="4" name="FontSize" /></td>
		<td><select name="Barcode">
			<option value="1">' . __('Yes') . '</option>
			<option selected="selected" value="0">' . __('No') . '</option>
			</select></td>
		</tr>
		</table>
		</td>
		</tr>
		</table>
		<p />';
}

echo '<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . __('Enter Information') . '" />
		</div>
	<br />
		<div class="centre">
			<a href="' . $RootPath . '/PDFPrintLabel.php">' . __('Print Labels') . '</a>
		</div>
	</form>';

include('includes/footer.php');
