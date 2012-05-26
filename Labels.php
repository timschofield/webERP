<?php

/* $Id: Labels.php 4950 2012-02-22 06:26:38Z daintree $*/

include('includes/session.inc');
$title = _('Label Templates');
include('includes/header.inc');

//define PaperSize array sizes in pdf points
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

echo '<p class="page_title_text">
		<img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Label Template Maintenance')
	. '" alt="" />' . $title. '
	</p>';

if (isset($_POST['SelectedLabelID'])){
	$SelectedLabelID =$_POST['SelectedLabelID'];
	if (ctype_digit($_POST['NoOfFieldsDefined'])){ //Now Process any field updates

		for ($i=0;$i<=$_POST['NoOfFieldsDefined'];$i++){

			if (ctype_digit($_POST['VPos' . $i])
				AND ctype_digit($_POST['HPos' . $i])
				AND ctype_digit($_POST['FontSize' . $i])){ // if all entries are integers

				$result =DB_query("UPDATE labelfields SET fieldvalue='" . $_POST['FieldName' . $i] . "',
														vpos='" . $_POST['VPos' . $i] . "',
														hpos='" . $_POST['HPos' . $i] . "',
														fontsize='" . $_POST['FontSize' . $i] . "',
														barcode='" . $_POST['Barcode' . $i] . "'
								WHERE labelfieldid='" . $_POST['LabelFieldID' . $i] . "'",
								$db);
			} else {
				prnMsg (_('Entries for Vertical Position, Horizonal Position, and Font Size must be integers.'),'error');
			}
		}
	}
	if (ctype_digit($_POST['VPos']) AND ctype_digit($_POST['HPos']) AND ctype_digit($_POST['FontSize'])){
		//insert the new label field entered
		$result = DB_query("INSERT INTO labelfields (labelid,
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
									'" . $_POST['Barcode'] . "')",
							$db);
	}
} elseif(isset($_GET['SelectedLabelID'])){
	$SelectedLabelID =$_GET['SelectedLabelID'];
	if (isset($_GET['DeleteField'])){ //then process any deleted fields
		$result = DB_query("DELETE FROM labelfields WHERE labelfieldid='" . $_GET['DeleteField'] . "'",$db);
	}
}

if (isset($_POST['submit'])) {
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */
	if ( trim( $_POST['Description'] ) == '' ) {
		$InputError = 1;
		prnMsg( _('The label description may not be empty'), 'error');
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

		$sql = "UPDATE labels SET 	description = '" . $_POST['Description'] . "',
									height = '" . $_POST['Height'] . "',
									topmargin = '". $_POST['TopMargin'] . "',
									width = '". $_POST['Width'] . "',
									leftmargin = '". $_POST['LeftMargin'] . "',
									rowheight =  '". $_POST['RowHeight'] . "',
									columnwidth = '". $_POST['ColumnWidth'] . "',
									pagewidth = '" . $_POST['PageWidth'] . "',
									pageheight = '" . $_POST['PageHeight'] . "'
				WHERE labelid = '" . $SelectedLabelID . "'";

		$ErrMsg = _('The update of this label template failed because');
		$result = DB_query($sql,$db,$ErrMsg);

		$Message = _('The label template has been updated');

	} elseif ($InputError !=1) {

	/*Selected label is null cos no item selected on first time round so must be adding a	record must be submitting new entries in the new label form */

		$sql = "INSERT INTO labels (description,
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

		$ErrMsg = _('The addition of this label failed because');
		$result = DB_query($sql,$db,$ErrMsg);
		$Message = _('The new label template has been added to the database');
	}
	//run the SQL from either of the above possibilites
	if (isset($InputError) AND $InputError !=1) {
		unset( $_POST['PaperSize']);
		unset( $_POST['Description']);
		unset( $_POST['Width']);
		unset( $_POST['Height']);
		unset( $_POST['TopMargin']);
		unset( $_POST['LeftMargin']);
		unset( $_POST['ColumnWidth']);
		unset( $_POST['RowHeight']);
		unset($_POST['PageWidth']);
		unset($_POST['PageHeight']);
	}

	prnMsg($Message);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	/*Cascade deletes in TaxAuthLevels */
	$result = DB_query("DELETE FROM labelfields WHERE labelid= '" . $SelectedLabelID . "'",$db);
	$result = DB_query("DELETE FROM labels WHERE labelid= '" . $SelectedLabelID . "'",$db);
	prnMsg(_('The selected label template has been deleted'),'success');
	unset ($SelectedLabelID);
}

if (!isset($SelectedLabelID)) {

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedLabelID will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true and the list of label templates will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT labelid,
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

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The defined label templates could not be retrieved because');
	$DbgMsg = _('The following SQL to retrieve the label templates was used');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	if (DB_num_rows($result)>0){
		echo '<table class="selection">
				<tr>
					<th>' . _('Description') . '</th>
					<th>' . _('Rows x Cols') . '</th>
					<th>' . _('Page Width') . '</th>
					<th>' . _('Page Height') . '</th>
					<th>' . _('Height') . '</th>
					<th>' . _('Width') . '</th>
					<th>' . _('Row Height') . '</th>
					<th>' . _('Column Width') . '</th>
				</tr>';
		$k=0;
		while ($myrow = DB_fetch_array($result)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			$NoOfRows = floor(($myrow['pageheight']-$myrow['topmargin'])/$myrow['rowheight']);
			$NoOfCols = floor(($myrow['pagewidth']-$myrow['leftmargin'])/$myrow['columnwidth']);

			foreach ($PaperSize as $PaperName=>$PaperType) {
				if ($PaperType['PageWidth'] == $myrow['pagewidth'] AND $PaperType['PageHeight'] == $myrow['pageheight']) {
					$Paper = $PaperName;
				}
			}
			if (isset($Paper)){
				printf('<td>%s</td>
						<td>%s</td>
						<td colspan="2">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td><a href="%sSelectedLabelID=%s">' . _('Edit') . '</a></td>
						<td><a href="%sSelectedLabelID=%s&delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this label?') . '\');">' . _('Delete') . '</a></td>
						</tr>',
						$myrow['description'],
						$NoOfRows . ' x ' . $NoOfCols,
						$Paper,
						$myrow['height'],
						$myrow['width'],
						$myrow['rowheight'],
						$myrow['columnwidth'],
						htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
						$myrow['labelid'],
						htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
						$myrow['labelid'],
						$rootpath . '/LabelFields.php?',
						$myrow['labelid']);
			} else {
				printf('<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td><a href="%sSelectedLabelID=%s">' . _('Edit') . '</a></td>
						<td><a href="%sSelectedLabelID=%s&delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this label?') . '\');">' . _('Delete') . '</a></td>
						</tr>',
						$myrow['description'],
						$NoOfRows . ' x ' . $NoOfCols,
						$myrow['pagewidth'],
						$myrow['pageheight'],
						$myrow['height'],
						$myrow['width'],
						$myrow['rowheight'],
						$myrow['columnwidth'],
						htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
						$myrow['labelid'],
						htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
						$myrow['labelid'],
						$rootpath . '/LabelFields.php?',
						$myrow['labelid']);
			}
		}
		//END WHILE LIST LOOP

		//end of ifs and buts!

		echo '</table><p>';
	} //end if there are label definitions to show
}

if (isset($SelectedLabelID)) {
	echo '<div class="centre">
			<a href="' .  htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">' . _('Review all defined label records') . '</a>
		</div>';
}

echo '<p><form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedLabelID)) {
	//editing an existing label

	$sql = "SELECT pagewidth,
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

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['PageWidth']	= $myrow['pagewidth'];
	$_POST['PageHeight']	= $myrow['pageheight'];
	$_POST['Description']	= $myrow['description'];
	$_POST['Height']		= $myrow['height'];
	$_POST['TopMargin']	= $myrow['topmargin'];
	$_POST['Width'] 	= $myrow['width'];
	$_POST['LeftMargin']	= $myrow['leftmargin'];
	$_POST['RowHeight']	= $myrow['rowheight'];
	$_POST['ColumnWidth']	= $myrow['columnwidth'];

	foreach ($PaperSize as $PaperName=>$PaperType) {
		if ($PaperType['PageWidth'] == $myrow['pagewidth'] AND $PaperType['PageHeight'] == $myrow['pageheight']) {
			$_POST['PaperSize'] = $PaperName;
		}
	}

	echo '<input type="hidden" name="SelectedLabelID" value="' . $SelectedLabelID . '" />';

}  //end of if $SelectedLabelID only do the else when a new record is being entered


if (!isset($_POST['Description'])) {
	$_POST['Description']='';
}
echo '<table class="selection">
		<tr>
			<td><img src="css/paramsLabel.png"></td>
			<td><table>
				<tr>
					<td>' . _('Label Description') . ':</td>
					<td><input type="text" name="Description" size="21" maxlength="20" value="' . $_POST['Description'] . '" /></td>
				</tr>
				<tr>
					<td>' . _('Label Paper Size') . ':</td>
					<td><select name="PaperSize" onchange="ReloadForm(submit)" >';

if (!isset($_POST['PaperSize'])){
	echo '<option selected="selected" value="custom">' . _('Custom Size') . '</option>';
} else {
	echo '<option value="custom">' . _('Custom Size') . '</option>';
}
foreach($PaperSize as $PaperType=>$PaperSizeElement) {
	if (isset($_POST['PaperSize']) AND $PaperType==$_POST['PaperSize']) {
		echo '<option selected="selected" value="';
	} else {
		echo '<option value="';
	}
	echo $PaperType . '">' . $PaperType . '</option>';

} //end while loop

echo '</select></td>
	</tr>';

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
	echo '<tr>
			<td>' . _('Page Width') . '</td>
			<td><input type="text" size="4" maxlength="4" name="PageWidth" value="' . $_POST['PageWidth'] . '" /></td>
		</tr>
		<tr>
			<td>' . _('Page Height') . '</td>
			<td><input type="text" size="4" maxlength="4" name="PageHeight" value="' . $_POST['PageHeight'] . '" /></td>
		</tr>';
}
echo '<tr>
		<td>' . _('Label Height') . ' - (He):</td>
		<td><input type="text" name="Height" size="4" maxlength="4" value="' . $_POST['Height'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Label Width') . ' - (Wi):</td>
		<td><input type="text" name="Width" size="4" maxlength="4" value="' . $_POST['Width'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Top Margin') . ' - (Tm):</td>
		<td><input type="text" name="TopMargin" size="4" maxlength="4" value="' . $_POST['TopMargin'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Left Margin') . ' - (Lm):</td>
		<td><input type="text" name="LeftMargin" size="4" maxlength="4" value="' . $_POST['LeftMargin'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Row Height') . ' - (Rh):</td>
		<td><input type="text" name="RowHeight" size="4" maxlength="4" value="' . $_POST['RowHeight'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Column Width') . ' - (Cw):</td>
		<td><input type="text" name="ColumnWidth" size="4" maxlength="4" value="' . $_POST['ColumnWidth'] . '" /></td>
	</tr>
	</table></td></tr>
	</td></tr>
	</table>';

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
	$ErrMsg = _('Could note get the label fields because');
	$result = DB_query($SQL,$db,$ErrMsg);
	$i=0;
	echo '<table class="selection">
				<tr>
				<td><img src="css/labelsDim.png"></td>
				<td><table>
					<tr>
						<th>' . _('Field') . '</th>
						<th>' . _('Vertical') . '<br />' . _('Position')  . '<br />(VPos)</th>
						<th>' . _('Horizonal') . '<br />' . _('Position') . '<br />(HPos)</th>
						<th>' . _('Font Size') . '</th>
						<th>' . _('Bar-code') . '</th>
					</tr>';
	if (DB_num_rows($result)>0){
		$k=0;
		while ($myrow = DB_fetch_array($result)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			echo '<input type="hidden" name="LabelFieldID' . $i . '" value="' . $myrow['labelfieldid'] . '" />
			<td><select name="FieldName' . $i . '" onchange="ReloadForm(submit)">';
			if ($myrow['fieldvalue']=='itemcode'){
				echo '<option selected="selected" value="itemcode">' . _('Item Code') . '</option>';
			} else {
				echo '<option value="itemcode">' . _('Item Code') . '</option>';
			}
			if ($myrow['fieldvalue']=='itemdescription'){
				echo '<option selected="selected" value="itemdescription">' . _('Item Description') . '</option>';
			} else {
				echo '<option value="itemdescription">' . _('Item Descrption') . '</option>';
			}
			if ($myrow['fieldvalue']=='barcode'){
				echo '<option selected="selected" value="barcode">' . _('Item Barcode') . '</option>';
			} else {
				echo '<option value="barcode">' . _('Item Barcode') . '</option>';
			}
			if ($myrow['fieldvalue']=='price'){
				echo '<option selected="selected" value="price">' . _('Price') . '</option>';
			} else {
				echo '<option value="price">' . _('Price') . '</option>';
			}
			echo '</select></td>
				<td><input type="text" name="VPos' . $i . '" size="4" maxlength="4" value="' . $myrow['vpos'] . '" /></td>
				<td><input type="text" name="HPos' . $i . '" size="4" maxlength="4" value="' . $myrow['hpos'] . '" /></td>
				<td><input type="text" name="FontSize' . $i . '" size="4" maxlength="4" value="' . $myrow['fontsize'] . '" /></td>
				<td><select name="Barcode' . $i . '" onchange="ReloadForm(submit)">';
			if ($myrow['barcode']==0){
				echo '<option selected="selected" value="0">' . _('No') . '</option>
						<option value="1">' . _('Yes') . '</option>';
			} else {
				echo '<option selected="selected" value="1">' . _('Yes') . '</option>
						<option value="0">' . _('No') . '</option>';
			}
			echo '</select></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedLabelID=' . $SelectedLabelID . '&amp;DeleteField=' . $myrow['labelfieldid'] .' onclick="return confirm(\'' . _('Are you sure you wish to delete this label field?') . '\');">' . _('Delete') . '</a></td>
				</tr>';
			$i++;
		}
		//END WHILE LIST LOOP
		$i--; //last increment needs to be wound back

	} //end if there are label definitions to show
	echo '<input type="hidden" name="NoOfFieldsDefined" value="' . $i . '" />';

	echo '<tr>
		<td><select name="FieldName">
			<option value="itemcode">' . _('Item Code') . '</option>
			<option value="itemdescription">' . _('Item Descrption') . '</option>
			<option value="barcode">' . _('Item Barcode') . '</option>
			<option value="price">' . _('Price') . '</option>
			</select></td>
		<td><input type="text" size="4" maxlength="4" name="VPos" /></td>
		<td><input type="text" size="4" maxlength="4" name="HPos" /></td>
		<td><input type="text" size="4" maxlength="4" name="FontSize" /></td>
		<td><select name="Barcode">
			<option value="1">' . _('Yes') . '</option>
			<option selected="selected" value="0">' . _('No') . '</option>
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
			<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		</div>
	<br />
		<div class="centre">
			<a href="' . $rootpath  . '/PDFPrintLabel.php">' . _('Print Labels') . '</a>
		</div>
	</form>';

include('includes/footer.inc');

?>