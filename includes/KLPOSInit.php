<?php

/////////////////////////////////////////////////////////////////////
//  Variable Init
/////////////////////////////////////////////////////////////////////

if (isset($_POST['ReturnDate'])){
	$_POST['ReturnDate'] = ConvertSQLDate($_POST['ReturnDate']);
}

if (!isset($_POST['AmountPaidCash'])){
	$_POST['AmountPaidCash'] = 0;
}
if (!isset($_POST['AmountPaidCCDanamon'])){
	$_POST['AmountPaidCCDanamon'] = 0;
}
if (!isset($_POST['AmountPaidCCBNI'])){
	$_POST['AmountPaidCCBNI'] = 0;
}
if (!isset($_POST['AmountPaidCCMandiri'])){
	$_POST['AmountPaidCCMandiri'] = 0;
}
if (!isset($_POST['AmountPaidCCBCA'])){
	$_POST['AmountPaidCCBCA'] = 0;
}
if (!isset($_POST['AmountPaidCCBRI'])){
	$_POST['AmountPaidCCBRI'] = 0;
}

if (!isset($_POST['AmountPaidAmexDanamon'])){
	$_POST['AmountPaidAmexDanamon'] = 0;
}
if (!isset($_POST['AmountPaidAmexBNI'])){
	$_POST['AmountPaidAmexBNI'] = 0;
}
if (!isset($_POST['AmountPaidAmexMandiri'])){
	$_POST['AmountPaidAmexMandiri'] = 0;
}
if (!isset($_POST['AmountPaidAmexBCA'])){
	$_POST['AmountPaidAmexBCA'] = 0;
}
if (!isset($_POST['AmountPaidAmexBRI'])){
	$_POST['AmountPaidAmexBRI'] = 0;
}

if (!isset($_POST['AmountPaidWeChat'])){
	$_POST['AmountPaidWeChat'] = 0;
}

if (!isset($_POST['AmountPaidQRISMandiri'])){
	$_POST['AmountPaidQRISMandiri'] = 0;
}
if (!isset($_POST['AmountPaidQRISBRI'])){
	$_POST['AmountPaidQRISBRI'] = 0;
}

if (!isset($_POST['AmountReturnedGoods'])){
	$_POST['AmountReturnedGoods'] = 0;
}
if (!isset($_POST['ReturnedGoodsOldInvoice'])){
	$_POST['ReturnedGoodsOldInvoice'] = '';
}
if (!isset($_POST['ReturnedGoodsItems'])){
	$_POST['ReturnedGoodsItems'] = '';
}
if (!isset($_POST['ReturnedGoodsReason'])){
	$_POST['ReturnedGoodsReason'] = 0;
}
if (!isset($_POST['ReturnDate'])){
	$_POST['ReturnDate'] = date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AmountVouchers'])){
	$_POST['AmountVouchers'] = 0;
}
if (!isset($_POST['VoucherCode'])){
	$_POST['VoucherCode'] = '';
}
if (!isset($_POST['Comments'])){
	$_POST['Comments'] = '';
}

if (!isset($_POST['PackagingBox01L'])){
	$_POST['PackagingBox01L'] = 0;
}
if (!isset($_POST['PackagingBox01M'])){
	$_POST['PackagingBox01M'] = 0;
}
if (!isset($_POST['PackagingBox01S'])){
	$_POST['PackagingBox01S'] = 0;
}
if (!isset($_POST['PackagingBox02L'])){
	$_POST['PackagingBox02L'] = 0;
}
if (!isset($_POST['PackagingBox02M'])){
	$_POST['PackagingBox02M'] = 0;
}
if (!isset($_POST['PackagingBox02S'])){
	$_POST['PackagingBox02S'] = 0;
}

if (!isset($_POST['ShoppingBag02S'])){
	$_POST['ShoppingBag02S'] = 0;
}
if (!isset($_POST['ShoppingBag02M'])){
	$_POST['ShoppingBag02M'] = 0;
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

if (!isset($_POST['PackagingPouchBag01L'])){
	$_POST['PackagingPouchBag01L'] = 0;
}
if (!isset($_POST['PackagingPouchBag01M'])){
	$_POST['PackagingPouchBag01M'] = 0;
}
if (!isset($_POST['PackagingPouchBag01S'])){
	$_POST['PackagingPouchBag01S'] = 0;
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

