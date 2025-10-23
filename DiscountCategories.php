<?php

require(__DIR__ . '/includes/session.php');

$Title = __('Discount Categories Maintenance');
$ViewTopic = "SalesOrders";
$BookMark = "DiscountMatrix";
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . __('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

if (isset($_POST['stockID'])) {
	$_POST['StockID']=$_POST['stockID'];
} elseif (isset($_GET['StockID'])) {
	$_POST['StockID']=$_GET['StockID'];
	$_POST['ChooseOption']=1;
	$_POST['SelectChoice']=1;
}

if (isset($_POST['submit']) and !isset($_POST['SubmitCategory'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$Result = DB_query("SELECT stockid
						FROM stockmaster
						WHERE mbflag <>'K'
						AND mbflag<>'D'
						AND stockid='" . mb_strtoupper($_POST['StockID']) . "'");
	if (DB_num_rows($Result)==0){
		$InputError = 1;
		prnMsg(__('The stock item entered must be set up as either a manufactured or purchased or assembly item'),'warn');
	}

	if ($InputError !=1) {

		$SQL = "UPDATE stockmaster SET discountcategory='" . $_POST['DiscountCategory'] . "'
				WHERE stockid='" . mb_strtoupper($_POST['StockID']) . "'";

		$Result = DB_query($SQL, __('The discount category') . ' ' . $_POST['DiscountCategory'] . ' ' . __('record for') . ' ' . mb_strtoupper($_POST['StockID']) . ' ' . __('could not be updated because'));

		prnMsg(__('The stock master has been updated with this discount category'),'success');
		unset($_POST['DiscountCategory']);
		unset($_POST['StockID']);
	}


} elseif (isset($_GET['Delete']) and $_GET['Delete']=='yes') {
/*the link to delete a selected record was clicked instead of the submit button */

	$SQL="UPDATE stockmaster SET discountcategory='' WHERE stockid='" . trim(mb_strtoupper($_GET['StockID'])) ."'";
	$Result = DB_query($SQL);
	prnMsg( __('The stock master record has been updated to no discount category'),'success');
	echo '<br />';
} elseif (isset($_POST['SubmitCategory'])) {
	$SQL = "SELECT stockid FROM stockmaster WHERE categoryid='".$_POST['stockcategory']."'";
	$ErrMsg = __('Failed to retrieve stock category data');
	$Result = DB_query($SQL, $ErrMsg);
	if(DB_num_rows($Result)>0){
		$SQL="UPDATE stockmaster
				SET discountcategory='".$_POST['DiscountCategory']."'
				WHERE categoryid='".$_POST['stockcategory']."'";
		$Result = DB_query($SQL);
	}else{
		prnMsg(__('There are no stock defined for this stock category, you must define stock for it first'),'error');
		include('includes/footer.php');
		exit();
	}
}

if (isset($_POST['SelectChoice'])) {
	echo '<form id="update" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$SQL = "SELECT DISTINCT discountcategory FROM stockmaster WHERE discountcategory <>''";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) > 0) {
		echo '<table class="selection"><tr><td>' .  __('Discount Category Code') .': </td>';

		echo '<td><select name="DiscCat" onchange="ReloadForm(update.select)">';

		while ($MyRow = DB_fetch_array($Result)){
			if ($MyRow['discountcategory']==$_POST['DiscCat']){
				echo '<option selected="selected" value="' . $MyRow['discountcategory'] . '">' . $MyRow['discountcategory']  . '</option>';
			} else {
				echo '<option value="' . $MyRow['discountcategory'] . '">' . $MyRow['discountcategory'] . '</option>';
			}
		}

		echo '</select></td>';
		echo '<td><input type="submit" name="select" value="'.__('Select').'" /></td>
			</tr>
			</table>
			<br />';
	}
	echo '</form>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="ChooseOption" value="'.$_POST['ChooseOption'].'" />';
	echo '<input type="hidden" name="SelectChoice" value="'.$_POST['SelectChoice'].'" />';

	if (isset($_POST['ChooseOption']) and $_POST['ChooseOption']==1) {
		echo '<fieldset>
				<legend>', __('Discount Category Details'), '</legend>
				<field>
					<td>' .  __('Discount Category Code') .':</td>
					<td>';

		if (isset($_POST['DiscCat'])) {
			echo '<input type="text" required="required" name="DiscountCategory" pattern="[0-9a-zA-Z_]*" title="' . __('Enter the discount category up to 2 alpha-numeric characters') . '" maxlength="2" size="2" value="' . $_POST['DiscCat'] .'" /></td>
				<td>' . __('OR') . '</td>
				<td></td>
				<td>' . __('OR') . '</td>
				</field>';
		} else {
			echo '<input type="text" name="DiscountCategory" required="required" name="DiscountCategory" pattern="[0-9a-zA-Z_]*" title="' . __('Enter the discount category up to 2 alpha-numeric characters') . '" maxlength="2" size="2" /></td>
				<td>' .__('OR') . '</td>
				<td></td>
				<td>' . __('OR') . '</td>
				</field>';
		}

		if (!isset($_POST['StockID'])) {
			$_POST['StockID']='';
		}
		if (!isset($_POST['PartID'])) {
			$_POST['PartID']='';
		}
		if (!isset($_POST['PartDesc'])) {
			$_POST['PartDesc']='';
		}
		echo '<field>
				<td>' .  __('Enter Stock Code') .':</td>
				<td><input type="text" name="StockID" name="DiscountCategory" pattern="[0-9a-zA-Z_]*" title="' . __('Enter the stock code of the item in this discount category up to 20 alpha-numeric characters') . '"  size="20" maxlength="20" value="' . $_POST['StockID'] . '" /></td>
				<td>' . __('Partial code') . ':</td>
				<td><input type="text" name="PartID" pattern="[0-9a-zA-Z_]*" title="' . __('Enter a portion of the item code only alpha-numeric characters') . '" size="10" maxlength="10" value="' . $_POST['PartID'] . '" /></td>
				<td>' . __('Partial description') . ':</td>
				<td><input type="text" name="PartDesc" size="10" value="' . $_POST['PartDesc'] .'" maxlength="10" /></td>
				<td><input type="submit" name="search" value="' . __('Search') .'" /></td>
			</field>';

		echo '</fieldset>';

		echo '<div class="centre"><input type="submit" name="submit" value="'. __('Update Item') .'" /></div>';

		if (isset($_POST['search'])) {
			if ($_POST['PartID']!='' and $_POST['PartDesc']=='')
				$SQL="SELECT stockid, description FROM stockmaster
						WHERE stockid " . LIKE  . " '%".$_POST['PartID']."%'";
			if ($_POST['PartID']=='' and $_POST['PartDesc']!='')
				$SQL="SELECT stockid, description FROM stockmaster
						WHERE description " . LIKE  . " '%".$_POST['PartDesc']."%'";
			if ($_POST['PartID']!='' and $_POST['PartDesc']!='')
				$SQL="SELECT stockid, description FROM stockmaster
						WHERE stockid " . LIKE  . " '%".$_POST['PartID']."%'
						AND description " . LIKE . " '%".$_POST['PartDesc']."%'";
			$Result = DB_query($SQL);
			if (!isset($_POST['stockID'])) {
				echo __('Select a part code').':<br />';
				while ($MyRow=DB_fetch_array($Result)) {
					echo '<input type="submit" name="stockID" value="'.$MyRow['stockid'].'" /><br />';
				}
			}
		}
	} else {
		echo '<fieldset>
				<legend>', __('Assign Discounts'), '</legend>
				<field>
					<label for="DiscountCategory">' . __('Assign discount category') . '</label>
					<input type="text" required="required" name="DiscountCategory" pattern="[0-9a-zA-Z_]*" title=""  maxlength="2" size="2" />
					<fieldhelp>' . __('Enter the discount category up to 2 alpha-numeric characters') . '</fieldhelp>
				</field>';
		echo '<field>
				<label for="stockcategory">' . __('to all items in stock category') . '</label>';
		$SQL = "SELECT categoryid,
				categorydescription
				FROM stockcategory";
		$Result = DB_query($SQL);
		echo '<select name="stockcategory">';
		while ($MyRow=DB_fetch_array($Result)) {
			echo '<option value="'.$MyRow['categoryid'].'">' . $MyRow['categorydescription'] . '</option>';
		}
		echo '</select>
			</field>
		</fieldset>';

		echo '<div class="centre">
				<input type="submit" name="SubmitCategory" value="'. __('Update Items') .'" />
			</div>';
	}
	echo '</form>';

	if (! isset($_POST['DiscCat'])){ /*set DiscCat to something to show results for first cat defined */

		$SQL = "SELECT DISTINCT discountcategory FROM stockmaster WHERE discountcategory <>''";
		$Result = DB_query($SQL);
		if (DB_num_rows($Result)>0){
			DB_data_seek($Result,0);
			$MyRow = DB_fetch_array($Result);
			$_POST['DiscCat'] = $MyRow['discountcategory'];
		} else {
			$_POST['DiscCat']='0';
		}
	}

	if ($_POST['DiscCat']!='0'){

		$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			discountcategory
		FROM stockmaster
		WHERE discountcategory='" . $_POST['DiscCat'] . "'
		ORDER BY stockmaster.stockid";

		$Result = DB_query($SQL);

		echo '<table class="selection">';
		echo '<tr>
				<th>' .  __('Discount Category')  . '</th>
				<th>' .  __('Item')  . '</th>
				<th></th>
			</tr>';

		while ($MyRow = DB_fetch_array($Result)) {
			$DeleteURL = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=yes&amp;StockID=' . $MyRow['stockid'] . '&amp;DiscountCategory=' . $MyRow['discountcategory'];

			echo '<tr class="striped_row">
					<td>', $MyRow['discountcategory'], '</td>
					<td>', $MyRow['stockid'], ' - ', $MyRow['description'], '</td>
					<td><a href="', $DeleteURL, '" onclick="return confirm(\'' . __('Are you sure you wish to delete this discount category?') . '\');">' .  __('Delete')  . '</a></td>
				</tr>';

		}

		echo '</table>';

	} else { /* $_POST['DiscCat'] ==0 */

		echo '</div><br />';
		prnMsg( __('There are currently no discount categories defined') . '. ' . __('Enter a two character abbreviation for the discount category and the stock code to which this category will apply to. Discount rules can then be applied to this discount category'),'info');
	}
}

if (!isset($_POST['SelectChoice'])) {
	echo '<form method="post" id="choose" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<fieldset>
			<legend>', __('Choose Stock Item Option'), '</legend>';

	echo '<field>
			<label for="ChooseOption">' . __('Update discount category for') . '</label>
			<select name="ChooseOption" onchange="ReloadForm(choose.SelectChoice)">
				<option value="1">' . __('a single stock item') . '</option>
				<option value="2">' . __('a complete stock category') . '</option>
			</select>
		</field>
		</fieldset>';

	echo '<div class="centre">
			<input type="submit" name="SelectChoice" value="'.__('Select').'" />
		</div>';
	echo '</form>';
}

include('includes/footer.php');
