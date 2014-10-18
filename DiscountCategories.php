<?php

/* $Id: DiscountCategories.php 6484 2013-12-09 02:00:55Z exsonqu $*/

include('includes/session.inc');

$Title = _('Discount Categories Maintenance');
/* webERP manual links before header.inc */
$ViewTopic= "SalesOrders";
$BookMark = "DiscountMatrix";
include('includes/header.inc');
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p><br />';

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

	$result = DB_query("SELECT stockid
						FROM stockmaster
						WHERE mbflag <>'K'
						AND mbflag<>'D'
						AND stockid='" . mb_strtoupper($_POST['StockID']) . "'",$db);
	if (DB_num_rows($result)==0){
		$InputError = 1;
		prnMsg(_('The stock item entered must be set up as either a manufactured or purchased or assembly item'),'warn');
	}

	if ($InputError !=1) {

		$sql = "UPDATE stockmaster SET discountcategory='" . $_POST['DiscountCategory'] . "'
				WHERE stockid='" . mb_strtoupper($_POST['StockID']) . "'";

		$result = DB_query($sql,$db, _('The discount category') . ' ' . $_POST['DiscountCategory'] . ' ' . _('record for') . ' ' . mb_strtoupper($_POST['StockID']) . ' ' . _('could not be updated because'));

		prnMsg(_('The stock master has been updated with this discount category'),'success');
		unset($_POST['DiscountCategory']);
		unset($_POST['StockID']);
	}


} elseif (isset($_GET['Delete']) and $_GET['Delete']=='yes') {
/*the link to delete a selected record was clicked instead of the submit button */

	$sql="UPDATE stockmaster SET discountcategory='' WHERE stockid='" . trim(mb_strtoupper($_GET['StockID'])) ."'";
	$result = DB_query($sql,$db);
	prnMsg( _('The stock master record has been updated to no discount category'),'success');
	echo '<br />';
} elseif (isset($_POST['SubmitCategory'])) {
	$sql = "SELECT stockid FROM stockmaster WHERE categoryid='".$_POST['stockcategory']."'";
	$ErrMsg = _('Failed to retrieve stock category data');
	$result = DB_query($sql,$db,$ErrMsg);
	if(DB_num_rows($result)>0){
		$sql="UPDATE stockmaster
				SET discountcategory='".$_POST['DiscountCategory']."'
				WHERE categoryid='".$_POST['stockcategory']."'";
		$result=DB_query($sql, $db);
	}else{
		prnMsg(_('There are no stock defined for this stock category, you must define stock for it first'),'error');
		include('includes/footer.inc');
		exit;
	}
}

if (isset($_POST['SelectChoice'])) {
	echo '<form id="update" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$sql = "SELECT DISTINCT discountcategory FROM stockmaster WHERE discountcategory <>''";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result) > 0) {
		echo '<table class="selection"><tr><td>' .  _('Discount Category Code') .': </td>';

		echo '<td><select name="DiscCat" onchange="ReloadForm(update.select)">';

		while ($myrow = DB_fetch_array($result)){
			if ($myrow['discountcategory']==$_POST['DiscCat']){
				echo '<option selected="selected" value="' . $myrow['discountcategory'] . '">' . $myrow['discountcategory']  . '</option>';
			} else {
				echo '<option value="' . $myrow['discountcategory'] . '">' . $myrow['discountcategory'] . '</option>';
			}
		}

		echo '</select></td>';
		echo '<td><input type="submit" name="select" value="'._('Select').'" /></td>
			</tr>
			</table>
			<br />';
	}
    echo '</div>
          </form>';

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="ChooseOption" value="'.$_POST['ChooseOption'].'" />';
	echo '<input type="hidden" name="SelectChoice" value="'.$_POST['SelectChoice'].'" />';

	if (isset($_POST['ChooseOption']) and $_POST['ChooseOption']==1) {
		echo '<table class="selection">
				<tr>
					<td>' .  _('Discount Category Code') .':</td>
					<td>';

		if (isset($_POST['DiscCat'])) {
			echo '<input type="text" required="required" name="DiscountCategory" pattern="[0-9a-zA-Z_]*" title="' . _('Enter the discount category up to 2 alpha-numeric characters') . '" maxlength="2" size="2" value="' . $_POST['DiscCat'] .'" /></td>
				<td>' . _('OR') . '</td>
				<td></td>
				<td>' . _('OR') . '</td>
				</tr>';
		} else {
			echo '<input type="text" name="DiscountCategory" required="required" name="DiscountCategory" pattern="[0-9a-zA-Z_]*" title="' . _('Enter the discount category up to 2 alpha-numeric characters') . '" maxlength="2" size="2" /></td>
				<td>' ._('OR') . '</td>
				<td></td>
				<td>' . _('OR') . '</td>
				</tr>';
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
		echo '<tr>
				<td>' .  _('Enter Stock Code') .':</td>
				<td><input type="text" name="StockID" name="DiscountCategory" pattern="[0-9a-zA-Z_]*" title="' . _('Enter the stock code of the item in this discount category up to 20 alpha-numeric characters') . '"  size="20" maxlength="20" value="' . $_POST['StockID'] . '" /></td>
				<td>' . _('Partial code') . ':</td>
				<td><input type="text" name="PartID" pattern="[0-9a-zA-Z_]*" title="' . _('Enter a portion of the item code only alpha-numeric characters') . '" size="10" maxlength="10" value="' . $_POST['PartID'] . '" /></td>
				<td>' . _('Partial description') . ':</td>
				<td><input type="text" name="PartDesc" size="10" value="' . $_POST['PartDesc'] .'" maxlength="10" /></td>
				<td><input type="submit" name="search" value="' . _('Search') .'" /></td>
			</tr>';

		echo '</table>';

		echo '<br /><div class="centre"><input type="submit" name="submit" value="'. _('Update Item') .'" /></div>';

		if (isset($_POST['search'])) {
			if ($_POST['PartID']!='' and $_POST['PartDesc']=='')
				$sql="SELECT stockid, description FROM stockmaster
						WHERE stockid " . LIKE  . " '%".$_POST['PartID']."%'";
			if ($_POST['PartID']=='' and $_POST['PartDesc']!='')
				$sql="SELECT stockid, description FROM stockmaster
						WHERE description " . LIKE  . " '%".$_POST['PartDesc']."%'";
			if ($_POST['PartID']!='' and $_POST['PartDesc']!='')
				$sql="SELECT stockid, description FROM stockmaster
						WHERE stockid " . LIKE  . " '%".$_POST['PartID']."%'
						AND description " . LIKE . " '%".$_POST['PartDesc']."%'";
			$result=DB_query($sql,$db);
			if (!isset($_POST['stockID'])) {
				echo _('Select a part code').':<br />';
				while ($myrow=DB_fetch_array($result)) {
					echo '<input type="submit" name="stockID" value="'.$myrow['stockid'].'" /><br />';
				}
			}
		}
	} else {
		echo '<table class="selection">
				<tr>
				<td>' . _('Assign discount category') . '</td>';
		echo '<td><input type="text" required="required" name="DiscountCategory" pattern="[0-9a-zA-Z_]*" title="' . _('Enter the discount category up to 2 alpha-numeric characters') . '"  maxlength="2" size="2" /></td>';
		echo '<td>' . _('to all items in stock category') . '</td>';
		$sql = "SELECT categoryid,
				categorydescription
				FROM stockcategory";
		$result = DB_query($sql, $db);
		echo '<td><select name="stockcategory">';
		while ($myrow=DB_fetch_array($result)) {
			echo '<option value="'.$myrow['categoryid'].'">' . $myrow['categorydescription'] . '</option>';
		}
		echo '</select></td></tr></table>';
		echo '<br /><div class="centre"><input type="submit" name="SubmitCategory" value="'. _('Update Items') .'" /></div>';
	}
	echo '</div>
          </form>';

	if (! isset($_POST['DiscCat'])){ /*set DiscCat to something to show results for first cat defined */

		$sql = "SELECT DISTINCT discountcategory FROM stockmaster WHERE discountcategory <>''";
		$result = DB_query($sql, $db);
		if (DB_num_rows($result)>0){
			DB_data_seek($result,0);
			$myrow = DB_fetch_array($result);
			$_POST['DiscCat'] = $myrow['discountcategory'];
		} else {
			$_POST['DiscCat']='0';
		}
	}

	if ($_POST['DiscCat']!='0'){

		$sql = "SELECT stockmaster.stockid,
			stockmaster.description,
			discountcategory
		FROM stockmaster
		WHERE discountcategory='" . $_POST['DiscCat'] . "'
		ORDER BY stockmaster.stockid";

		$result = DB_query($sql,$db);

		echo '<br /><table class="selection">';
		echo '<tr>
			<th>' .  _('Discount Category')  . '</th>
			<th>' .  _('Item')  . '</th></tr>';

		$k=0; //row colour counter

		while ($myrow = DB_fetch_array($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			$DeleteURL = htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=yes&amp;StockID=' . $myrow['stockid'] . '&amp;DiscountCategory=' . $myrow['discountcategory'];

			printf('<td>%s</td>
					<td>%s - %s</td>
					<td><a href="%s" onclick="return confirm(\'' . _('Are you sure you wish to delete this discount category?') . '\');">' .  _('Delete')  . '</a></td>
					</tr>',
					$myrow['discountcategory'],
					$myrow['stockid'],
					$myrow['description'],
					$DeleteURL);

		}

		echo '</table>';

	} else { /* $_POST['DiscCat'] ==0 */

		echo '</div><br />';
		prnMsg( _('There are currently no discount categories defined') . '. ' . _('Enter a two character abbreviation for the discount category and the stock code to which this category will apply to. Discount rules can then be applied to this discount category'),'info');
	}
}

if (!isset($_POST['SelectChoice'])) {
	echo '<form method="post" id="choose" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">';
	echo '<tr>
			<td>' . _('Update discount category for') . '</td>
			<td><select name="ChooseOption" onchange="ReloadForm(choose.SelectChoice)">
				<option value="1">' . _('a single stock item') . '</option>
				<option value="2">' . _('a complete stock category') . '</option>
				</select></td>
		</tr>
		</table>
		<br />';
	echo '<div class="centre"><input type="submit" name="SelectChoice" value="'._('Select').'" /></div>';
    echo '</div>
          </form>';
}

include('includes/footer.inc');
?>
