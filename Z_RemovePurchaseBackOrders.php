<?php

require(__DIR__ . '/includes/session.php');

$Title=__('Remove Purchase Order Back Orders');
$ViewTopic = 'SpecialUtilities';
$BookMark = basename(__FILE__, '.php');
include('includes/header.php');

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
		<div class="centre">' . __('This will alter all purchase orders where the quantity required is more than the quantity delivered - where some has been delivered already. The quantity ordered will be reduced to the same as the quantity already delivered - removing all back orders') . '<br /><input type="submit" name="RemovePOBackOrders" value="' . __('Remove Purchase Back Orders') .'" />
		</div></form>';

if (isset($_POST['RemovePOBackOrders'])){
	DB_query('UPDATE purchorderdetails
				SET quantityord=quantityrecd
				WHERE quantityrecd>0 AND quantityord > quantityrecd
				AND deliverydate < CURRENT_DATE();');
	prnMsg(__('Updated all purchase orders to remove back orders'),'success');
}

include('includes/footer.php');
