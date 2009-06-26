<?php
/* $Revision: 1.10 $ */

$PageSecurity = 11;

include('includes/session.inc');

$title = _('Discount Categories Maintenance');

include('includes/header.inc');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'<br>';

if (isset($_POST['stockID'])) {
	$_POST['StockID']=$_POST['stockID'];
} elseif (isset($_GET['StockID'])) {
	$_POST['StockID']=$_GET['StockID'];
	$_POST['chooseoption']=1;
	$_POST['selectchoice']=1;
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	$result = DB_query("SELECT stockid
				FROM stockmaster
				WHERE mbflag <>'K'
				AND mbflag<>'D'
				AND stockid='" . strtoupper($_POST['StockID']) . "'",$db);
	if (DB_num_rows($result)==0){
		$InputError = 1;
		prnMsg(_('The stock item entered must be set up as either a manufactured or purchased or assembly item'),'warn');
	}

	if ($InputError !=1) {

		$sql = "UPDATE stockmaster SET discountcategory='" . $_POST['DiscountCategory'] . "'
			WHERE stockid='" . strtoupper($_POST['StockID']) . "'";

		$result = DB_query($sql,$db, _('The discount category') . ' ' . $_POST['DiscountCategory'] . ' ' . _('record for') . ' ' . strtoupper($_POST['StockID']) . ' ' . _('could not be updated because'));

		prnMsg(_('The stock master has been updated with this discount category'),'success');
		unset($_POST['DiscountCategory']);
		unset($_POST['StockID']);
	}


} elseif (isset($_GET['Delete']) and $_GET['Delete']=='yes') {
/*the link to delete a selected record was clicked instead of the submit button */

	$sql="UPDATE stockmaster SET discountcategory='' WHERE stockid='" . trim(strtoupper($_GET['StockID'])) ."'";
	$result = DB_query($sql,$db);
	prnMsg( _('The stock master record has been updated to no discount category'),'success');
} elseif (isset($_POST['submitcategory'])) {
	$sql="UPDATE stockmaster 
		SET discountcategory='".$_POST['DiscountCategory']."' 
		WHERE categoryid='".$_POST['stockcategory']."'";
	$result=DB_query($sql, $db);
}

if (isset($_POST['selectchoice'])) {
	echo "<form name='update' method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';

	$sql = "SELECT DISTINCT discountcategory FROM stockmaster WHERE discountcategory <>''";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result) > 0) {
		echo '<table><tr><td>'. _('Discount Category Code') .': </td>';

		echo '<td><select name="DiscCat" onChange="ReloadForm(update.select)">';

		while ($myrow = DB_fetch_array($result)){
			if ($myrow['discountcategory']==$_POST['DiscCat']){
				echo "<option selected value='" . $myrow['discountcategory'] . "'>" . $myrow['discountcategory'];
			} else {
				echo "<option value='" . $myrow['discountcategory'] . "'>" . $myrow['discountcategory'];
			}
			echo '</option>';
		}

		echo '</select></td>';
		echo '<td><input type="submit" name="select" value="'._('Select').'"></td></tr></table><br>';
	}

	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<input type="hidden" name="chooseoption" value="'.$_POST['chooseoption'].'">';
	echo '<input type="hidden" name="selectchoice" value="'.$_POST['selectchoice'].'">';
	
	if (isset($_POST['chooseoption']) and $_POST['chooseoption']==1) {
		echo '<table><tr><td>'. _('Discount Category Code') .':</td><td>';
		
		if (isset($_POST['DiscCat'])) {
			echo "<input type='text' name='DiscountCategory' maxlength=2 size=2 value='" . $_POST['DiscCat'] . 
				"'></td><td>"._('OR')."</td><td></td><td>"._('OR')."</td></tr>";
		} else {
			echo "<input type='text' name='DiscountCategory' maxlength=2 size=2></td><td>".
				_('OR')."</td><td></td><td>"._('OR')."</td></tr>";		
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
		echo '<tr><td>'. _('Enter Stock Code') .":</td><td><input type='text' name='StockID' size=20 maxlength=20
			value='".$_POST['StockID']."'></td>
			<td>"._('Partial code').":</td><td><input type='text' name='PartID' size=10 maxlength=10 value='".$_POST['PartID']."'></td>
			<td>"._('Partial description').":</td><td><input type='text' name='PartDesc' size=10 value='".$_POST['PartDesc']."' maxlength=10></td>
			<td><input type='Submit' name='search' value='". _('Search') ."'></td></tr>";

		echo '</table>';

		echo "<br><div class='centre'><input type='Submit' name='submit' value='". _('Update Item') ."'></div>";
	
		if (isset($_POST['search'])) {
			if ($_POST['PartID']!='' and $_POST['PartDesc']=='')
				$sql='SELECT stockid, description FROM stockmaster WHERE stockid LIKE"%'.$_POST['PartID'].'%"';
			if ($_POST['PartID']=='' and $_POST['PartDesc']!='')
				$sql='SELECT stockid, description FROM stockmaster WHERE description LIKE"%'.$_POST['PartDesc'].'%"';
			if ($_POST['PartID']!='' and $_POST['PartDesc']!='')
				$sql='SELECT stockid, description FROM stockmaster WHERE stockid LIKE"%'.$_POST['PartID'].'%" and 
				 description LIKE"%'.$_POST['PartDesc'].'%"';
			$result=DB_query($sql,$db);
			if (!isset($_POST['stockID'])) {
				echo _('Select a part code').':<br>';
				while ($myrow=DB_fetch_array($result)) {
					echo '<input type="submit" name="stockID" value="'.$myrow['stockid'].'"><br>';
				}
			}
		}
	} else {
		echo '<table><tr><td>'._('Assign discount category').'</td>';
		echo '<td><input type="text" name="DiscountCategory" maxlength=2 size=2></td>';
		echo '<td>'._('to all items in stock category').'</td>';
		$sql = 'SELECT categoryid, 
				categorydescription 
				FROM stockcategory';
		$result = DB_query($sql, $db);
		echo '<td><select name="stockcategory">';
		while ($myrow=DB_fetch_array($result)) {
			echo '<option value="'.$myrow['categoryid'].'">'.$myrow['categorydescription'].'</option>';
		}
		echo '</select></td></tr></table>';
		echo "<br><div class='centre'><input type='Submit' name='submitcategory' value='". _('Update Items') ."'></div>";
	}
	echo '</form>';
	
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

		echo '<br><table border=1>';
		echo "<tr>
			<th>". _('Discount Category') ."</th>
			<th>". _('Item') .'</th></tr>';

		$k=0; //row colour counter

		while ($myrow = DB_fetch_array($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			$DeleteURL = $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=yes&StockID=' . $myrow['stockid'] . '&DiscountCategory=' . $myrow['discountcategory'];

			printf("<td>%s</td>
				<td>%s - %s</td>
				<td><a href='%s'>". _('Delete') .'</td>
				</tr>',
				$myrow['discountcategory'],
				$myrow['stockid'],
				$myrow['description'],
				$DeleteURL);

		}

		echo '</table>';

	} else { /* $_POST['DiscCat'] ==0 */

		echo '</div><br>';
		prnMsg( _('There are currently no discount categories defined') . '. ' . _('Enter a two character abbreviation for the discount category and the stock code to which this category will apply to. Discount rules can then be applied to this discount category'),'info');
	}
}

if (!isset($_POST['selectchoice'])) {
	echo "<form method='post' name='choose' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
	echo '<table>';
	echo '<tr><td>'._('Update discount category for').'</td>';
	echo '<td><select name="chooseoption" onChange="ReloadForm(choose.selectchoice)">';
	echo '<option value="1">'._('a single stock item').'</option>';
	echo '<option value="2">'._('a complete stock category').'</option>';
	echo '</select></td></tr></table><br>';
	echo '<div class="centre"><input type="submit" name="selectchoice" value="'._('Select').'"></div>';
}

include('includes/footer.inc');
?>