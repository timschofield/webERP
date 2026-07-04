<?php

/////////////////////////////////////////////////////////////////////
//  PACKAGING  / SHOPPING BAGS Table
/////////////////////////////////////////////////////////////////////

/* This full script should be simplified afterwards when only using NEW boxes*/

$_SESSION['UserStockLocation'] = $_SESSION['UserStockLocation'] ?? '';

$UseNewBoxes = 0;
if ($_SESSION['UserStockLocation'] != '') {
	$SQL = "SELECT usenewboxes
			FROM locations
			WHERE loccode = '" . $_SESSION['UserStockLocation'] . "'";
	$Result = DB_query($SQL);
	if ($MyRow = DB_fetch_array($Result)) {
		$UseNewBoxes = $MyRow['usenewboxes'];
	}
}


// If the shop is using KAPAL-LAUT packaging with OLD boxes, show it!
if (($_SESSION['TypeLoc'] == "SHOPKL") AND ($UseNewBoxes == 0)){

	if (!isset($_POST['PackagingBox01L'])){
		$_POST['PackagingBox01L'] = 0;
	}
	if (!isset($_POST['PackagingPouchBag01L'])){
		$_POST['PackagingPouchBag01L'] = 0;
	}
	if (!isset($_POST['PackagingBox01M'])){
		$_POST['PackagingBox01M'] = 0;
	}
	if (!isset($_POST['PackagingPouchBag01M'])){
		$_POST['PackagingPouchBag01M'] = 0;
	}
	if (!isset($_POST['PackagingBox01S'])){
		$_POST['PackagingBox01S'] = 0;
	}
	if (!isset($_POST['PackagingPouchBag01S'])){
		$_POST['PackagingPouchBag01S'] = 0;
	}
	if (!isset($_POST['ShoppingBag02S'])){
		$_POST['ShoppingBag02S'] = 0;
	}
	if (!isset($_POST['ShoppingBag02M'])){
		$_POST['ShoppingBag02M'] = 0;
	}
	echo '<table class="selection">
			<tr>
				<th colspan=8>' . __('Kapal-Laut Packaging & Shopping Bags included in this sale') . '</th>
			</tr>';
	
	echo '<tr>
		<td>' . __('KL Box Large') . ':</td>
		<td><input type="text" class="number" name="PackagingBox01L" maxlength="3" size="3" value="' . $_POST['PackagingBox01L'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('KL Pouch Bag Large') . ':</td>
		<td><input type="text" class="number" name="PackagingPouchBag01L" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01L'] . '" /></td>';
	echo '<td></td>';
	echo '<td></td>
		<td></td>
		</tr>';

	echo '<tr>
		<td>' . __('KL Box Medium') . ':</td>
		<td><input type="text" class="number" name="PackagingBox01M" maxlength="3" size="3" value="' . $_POST['PackagingBox01M'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('KL Pouch Bag Medium') . ':</td>
		<td><input type="text" class="number" name="PackagingPouchBag01M" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01M'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('KL Shopping Bag Medium') . ':</td>
		<td><input type="text" class="number" name="ShoppingBag02M" maxlength="3" size="3" value="' . $_POST['ShoppingBag02M'] . '" /></td>';
	echo '</tr>';
	
	echo '<tr>
		<td>' . __('KL Box Small') . ':</td>
		<td><input type="text" class="number" name="PackagingBox01S" maxlength="3" size="3" value="' . $_POST['PackagingBox01S'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('KL Pouch Bag Small') . ':</td>
		<td><input type="text" class="number" name="PackagingPouchBag01S" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01S'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('KL Shopping Bag Small') . ':</td>
		<td><input type="text" class="number" name="ShoppingBag02S" maxlength="3" size="3" value="' . $_POST['ShoppingBag02S'] . '" /></td>';
	echo '</tr>';

	echo '</table>';	//end of column/row/master table
}

// If the shop is using KAPAL-LAUT packaging with NEW boxes, show it!
if (($_SESSION['TypeLoc'] == "SHOPKL") AND ($UseNewBoxes == 1)){

	if (!isset($_POST['PackagingBox05L'])){
		$_POST['PackagingBox05L'] = 0;
	}
	if (!isset($_POST['PackagingPouchBag01L'])){
	$_POST['PackagingPouchBag01L'] = 0;
	}
	if (!isset($_POST['PackagingBox05M'])){
		$_POST['PackagingBox05M'] = 0;
	}
	if (!isset($_POST['PackagingPouchBag01M'])){
		$_POST['PackagingPouchBag01M'] = 0;
	}
	if (!isset($_POST['PackagingBox05S'])){
		$_POST['PackagingBox05S'] = 0;
	}
	if (!isset($_POST['PackagingPouchBag01S'])){
		$_POST['PackagingPouchBag01S'] = 0;
	}
	if (!isset($_POST['ShoppingBag02S'])){
		$_POST['ShoppingBag02S'] = 0;
	}
	if (!isset($_POST['ShoppingBag02M'])){
		$_POST['ShoppingBag02M'] = 0;
	}
	echo '<table class="selection">
			<tr>
				<th colspan=8>' . __('Kapal-Laut Packaging & Shopping Bags included in this sale') . '</th>
			</tr>';
	
	echo '<tr>
		<td>' . __('KL Box Large') . ':</td>
		<td><input type="text" class="number" name="PackagingBox05L" maxlength="3" size="3" value="' . $_POST['PackagingBox05L'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('KL Pouch Bag Large') . ':</td>
		<td><input type="text" class="number" name="PackagingPouchBag01L" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01L'] . '" /></td>';
	echo '<td></td>';
	echo '<td></td>
		<td></td>
		</tr>';

	echo '<tr>
		<td>' . __('KL Box Medium') . ':</td>
		<td><input type="text" class="number" name="PackagingBox05M" maxlength="3" size="3" value="' . $_POST['PackagingBox05M'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('KL Pouch Bag Medium') . ':</td>
		<td><input type="text" class="number" name="PackagingPouchBag01M" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01M'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('KL Shopping Bag Medium') . ':</td>
		<td><input type="text" class="number" name="ShoppingBag02M" maxlength="3" size="3" value="' . $_POST['ShoppingBag02M'] . '" /></td>';
	echo '</tr>';
	
	echo '<tr>
		<td>' . __('KL Box Small') . ':</td>
		<td><input type="text" class="number" name="PackagingBox05S" maxlength="3" size="3" value="' . $_POST['PackagingBox05S'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('KL Pouch Bag Small') . ':</td>
		<td><input type="text" class="number" name="PackagingPouchBag01S" maxlength="3" size="3" value="' . $_POST['PackagingPouchBag01S'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('KL Shopping Bag Small') . ':</td>
		<td><input type="text" class="number" name="ShoppingBag02S" maxlength="3" size="3" value="' . $_POST['ShoppingBag02S'] . '" /></td>';
	echo '</tr>';

	echo '</table>';	//end of column/row/master table
}



// If the shop is using BLINK packaging with OLD boxes, show it!
if (($_SESSION['TypeLoc'] == "SHOPBL") AND ($UseNewBoxes == 0)){

	if (!isset($_POST['PackagingBox02L'])){
		$_POST['PackagingBox02L'] = 0;
	}
	if (!isset($_POST['PackagingBox02M'])){
		$_POST['PackagingBox02M'] = 0;
	}
	if (!isset($_POST['PackagingBox02S'])){
		$_POST['PackagingBox02S'] = 0;
	}
	if (!isset($_POST['BlinkShoppingBag04L'])){
		$_POST['BlinkShoppingBag04L'] = 0;
	}
	if (!isset($_POST['BlinkShoppingBag04M'])){
		$_POST['BlinkShoppingBag04M'] = 0;
	}
	if (!isset($_POST['BlinkShoppingBag04S'])){
		$_POST['BlinkShoppingBag04S'] = 0;
	}
	if (!isset($_POST['BlinkPouchBag03L'])){
		$_POST['BlinkPouchBag03L'] = 0;
	}
	if (!isset($_POST['BlinkPouchBag03M'])){
		$_POST['BlinkPouchBag03M'] = 0;
	}
	if (!isset($_POST['BlinkPouchBag03S'])){
		$_POST['BlinkPouchBag03S'] = 0;
	}
	echo '<table class="selection">
			<tr>
				<th colspan=8>' . __('BLINK Packaging & Shopping Bags included in this sale') . '
				</th>
			</tr>';
	
	echo '<tr>
		<td>' . __('BLINK Box Large') . ':</td>
		<td><input type="text" class="number" name="PackagingBox02L" maxlength="3" size="3" value="' . $_POST['PackagingBox02L'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Pouch Bag Large') . ':</td>
		<td><input type="text" class="number" name="BlinkPouchBag03L" maxlength="3" size="3" value="' . $_POST['BlinkPouchBag03L'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Shopping Bag Large') . ':</td>
		<td><input type="text" class="number" name="BlinkShoppingBag04L" maxlength="3" size="3" value="' . $_POST['BlinkShoppingBag04L'] . '" /></td></tr>';
	echo '</tr>';

	echo '<tr>
		<td>' . __('BLINK Box Medium') . ':</td>
		<td><input type="text" class="number" name="PackagingBox02M" maxlength="3" size="3" value="' . $_POST['PackagingBox02M'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Pouch Bag Medium') . ':</td>
		<td><input type="text" class="number" name="BlinkPouchBag03M" maxlength="3" size="3" value="' . $_POST['BlinkPouchBag03M'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Shopping Bag Medium') . ':</td>
		<td><input type="text" class="number" name="BlinkShoppingBag04M" maxlength="3" size="3" value="' . $_POST['BlinkShoppingBag04M'] . '" /></td>';
	echo '</tr>';
	
	echo '<tr>
		<td>' . __('BLINK Box Small') . ':</td>
		<td><input type="text" class="number" name="PackagingBox02S" maxlength="3" size="3" value="' . $_POST['PackagingBox02S'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Pouch Bag Small') . ':</td>
		<td><input type="text" class="number" name="BlinkPouchBag03S" maxlength="3" size="3" value="' . $_POST['BlinkPouchBag03S'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Shopping Bag Small') . ':</td>
		<td><input type="text" class="number" name="BlinkShoppingBag04S" maxlength="3" size="3" value="' . $_POST['BlinkShoppingBag04S'] . '" /></td>';
	echo '</tr>';

	echo '</table>';	//end of column/row/master table
}

// If the shop is using BLINK packaging with NEW boxes, show it!
if (($_SESSION['TypeLoc'] == "SHOPBL") AND ($UseNewBoxes == 1)){

	if (!isset($_POST['PackagingBox04L'])){
		$_POST['PackagingBox04L'] = 0;
	}
	if (!isset($_POST['PackagingBox04M'])){
		$_POST['PackagingBox04M'] = 0;
	}
	if (!isset($_POST['PackagingBox04S'])){
		$_POST['PackagingBox04S'] = 0;
	}
	if (!isset($_POST['BlinkShoppingBag04L'])){
		$_POST['BlinkShoppingBag04L'] = 0;
	}
	if (!isset($_POST['BlinkShoppingBag04M'])){
		$_POST['BlinkShoppingBag04M'] = 0;
	}
	if (!isset($_POST['BlinkShoppingBag04S'])){
		$_POST['BlinkShoppingBag04S'] = 0;
	}
	if (!isset($_POST['BlinkPouchBag03L'])){
		$_POST['BlinkPouchBag03L'] = 0;
	}
	if (!isset($_POST['BlinkPouchBag03M'])){
		$_POST['BlinkPouchBag03M'] = 0;
	}
	if (!isset($_POST['BlinkPouchBag03S'])){
		$_POST['BlinkPouchBag03S'] = 0;
	}
	echo '<table class="selection">
			<tr>
				<th colspan=8>' . __('BLINK Packaging & Shopping Bags included in this sale') . '
				</th>
			</tr>';
	
	echo '<tr>
		<td>' . __('BLINK Box Large') . ':</td>
		<td><input type="text" class="number" name="PackagingBox04L" maxlength="3" size="3" value="' . $_POST['PackagingBox04L'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Pouch Bag Large') . ':</td>
		<td><input type="text" class="number" name="BlinkPouchBag03L" maxlength="3" size="3" value="' . $_POST['BlinkPouchBag03L'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Shopping Bag Large') . ':</td>
		<td><input type="text" class="number" name="BlinkShoppingBag04L" maxlength="3" size="3" value="' . $_POST['BlinkShoppingBag04L'] . '" /></td></tr>';
	echo '</tr>';

	echo '<tr>
		<td>' . __('BLINK Box Medium') . ':</td>
		<td><input type="text" class="number" name="PackagingBox04M" maxlength="3" size="3" value="' . $_POST['PackagingBox04M'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Pouch Bag Medium') . ':</td>
		<td><input type="text" class="number" name="BlinkPouchBag03M" maxlength="3" size="3" value="' . $_POST['BlinkPouchBag03M'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Shopping Bag Medium') . ':</td>
		<td><input type="text" class="number" name="BlinkShoppingBag04M" maxlength="3" size="3" value="' . $_POST['BlinkShoppingBag04M'] . '" /></td>';
	echo '</tr>';
	
	echo '<tr>
		<td>' . __('BLINK Box Small') . ':</td>
		<td><input type="text" class="number" name="PackagingBox04S" maxlength="3" size="3" value="' . $_POST['PackagingBox04S'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Pouch Bag Small') . ':</td>
		<td><input type="text" class="number" name="BlinkPouchBag03S" maxlength="3" size="3" value="' . $_POST['BlinkPouchBag03S'] . '" /></td>';
	echo '<td></td>';
	echo '<td>' . __('BLINK Shopping Bag Small') . ':</td>
		<td><input type="text" class="number" name="BlinkShoppingBag04S" maxlength="3" size="3" value="' . $_POST['BlinkShoppingBag04S'] . '" /></td>';
	echo '</tr>';

	echo '</table>';	//end of column/row/master table
}
