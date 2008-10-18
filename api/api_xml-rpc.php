<?php

	include 'api_php.php';

	include '../xmlrpc/lib/xmlrpc.inc';
	include '../xmlrpc/lib/xmlrpcs.inc';


	$InsertCustomer_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertCustomer_doc = 'This function takes an associative array containing the details of a customer to
			to be inserted, where the keys of the array are the field names in the table debtorsmaster. ';

	function xmlrpc_InsertCustomer($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertCustomer(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertBranch_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertBranch_doc = 'This function takes an associative array containing the details of a branch to
			to be inserted, where the keys of the array are the field names in the table debtorsmaster. ';

	function xmlrpc_InsertBranch($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertBranch(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$ModifyCustomer_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$ModifyCustomer_doc = 'This function takes an associative array containing the details of a customer to
			to be updated, where the keys of the array are the field names in the table debtorsmaster. ';

	function xmlrpc_ModifyCustomer($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(ModifyCustomer(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$ModifyBranch_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$ModifyBranch_doc = 'This function takes an associative array containing the details of a branch to
			to be updated, where the keys of the array are the field names in the table debtorsmaster. ';

	function xmlrpc_ModifyBranch($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(ModifyBranch(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetCustomer_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetCustomer_doc = 'This function returns an associative array containing the details of the customer
			whose account number is passed to it.';

	function xmlrpc_GetCustomer($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCustomer($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$SearchCustomers_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$SearchCustomers_doc = 'This function returns an array containing the account numbers of those customers
			that meet the criteria given. Any field in debtorsmaster can be search on.';

	function xmlrpc_SearchCustomers($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(SearchCustomers($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval(),
				 			$xmlrpcmsg->getParam(3)->scalarval())));
	}

	$GetCurrencyList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetCurrencyList_doc = 'This function returns an array containing a list of all currencies setup on webERP';

	function xmlrpc_GetCurrencyList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCurrencyList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetCurrencyDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetCurrencyDetails_doc = 'This function returns an associative array containing the details of the currency
			 sent as a parameter';

	function xmlrpc_GetCurrencyDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCurrencyDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetSalesTypeList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetSalesTypeList_doc = 'This function returns an array containing a list of all sales types setup on webERP';

	function xmlrpc_GetSalesTypeList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetSalesTypeList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetSalesTypeDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetSalesTypeDetails_doc = 'This function returns an associative array containing the details of the sales type
			 sent as a parameter';

	function xmlrpc_GetSalesTypeDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetSalesTypeDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetHoldReasonList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetHoldReasonList_doc = 'This function returns an array containing a list of all hold reason codes setup on webERP';

	function xmlrpc_GetHoldReasonList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetHoldReasonList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetHoldReasonDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetHoldReasonDetails_doc = 'This function returns an associative array containing the details of the hold reason
			 sent as a parameter';

	function xmlrpc_GetHoldReasonDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetHoldReasonDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetPaymentTermsList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetPaymentTermsList_doc = 'This function returns an array containing a list of all payment terms setup on webERP';

	function xmlrpc_GetPaymentTermsList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetPaymentTermsList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetPaymentTermsDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetPaymentTermsDetails_doc = 'This function returns an associative array containing the details of the payment terms
			 sent as a parameter';

	function xmlrpc_GetPaymentTermsDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetPaymentTermsDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertStockItem_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertStockItem_doc = 'This function takes an associative array containing the details of a stock item to
			to be inserted, where the keys of the array are the field names in the table stockmaster. ';

	function xmlrpc_InsertStockItem($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertStockItem(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$ModifyStockItem_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$ModifyStockItem_doc = 'This function takes an associative array containing the details of a stock item to
			to be updated, where the keys of the array are the field names in the table stockmaster. ';

	function xmlrpc_ModifyStockItem($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(ModifyStockItem(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetStockItem_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetStockItem_doc = 'This function returns an associative array containing the details of the item
			whose stockid is passed to it.';

	function xmlrpc_GetStockItem($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetStockItem($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$SearchStockItems_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$SearchStockItems_doc = 'This function returns an array containing the account numbers of those items
			that meet the criteria given. Any field in stockmaster can be search on.';

	function xmlrpc_SearchStockItems($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(SearchStockItems($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval(),
				 			$xmlrpcmsg->getParam(3)->scalarval())));
	}

	$GetStockBalance_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetStockBalance_doc = 'This function returns the quantity of stock on hand a the location given';

	function xmlrpc_GetStockBalance($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetStockBalance($xmlrpcmsg->getParam(0)->scalarval(),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval(),
				 			$xmlrpcmsg->getParam(3)->scalarval())));
	}

	$InsertSalesInvoice_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertSalesInvoice_doc = 'This function inserts a sales invoice into webERP';

	function xmlrpc_InsertSalesInvoice($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertSalesInvoice(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertSalesCredit_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertSalesCredit_doc = 'This function inserts a sales credit note into webERP';

	function xmlrpc_InsertSalesCredit($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertSalesCedit(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertSalesOrderHeader_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertSalesOrderHeader_doc = 'This function inserts a sales order header into webERP';

	function xmlrpc_InsertSalesOrderHeader($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertSalesOrderHeader(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$ModifySalesOrderHeader_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$ModifySalesOrderHeader_doc = 'This function modifies a sales order header already in webERP';

	function xmlrpc_ModifySalesOrderHeader($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(ModifySalesOrderHeader(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertSalesOrderLine_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertSalesOrderLine_doc = 'This function inserts a sales order line into webERP';

	function xmlrpc_InsertSalesOrderLine($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertSalesOrderLine(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$ModifySalesOrderLine_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$ModifySalesOrderLine_doc = 'This function modifies a sales order line in webERP';

	function xmlrpc_ModifySalesOrderLine($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(ModifySalesOrderLine(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertGLAccount_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertGLAccount_doc = 'This function inserts a General ledger account code';

	function xmlrpc_InsertGLAccount($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertGLAccount(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$InsertGLAccountSection_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertGLAccountSection_doc = 'This function inserts a General ledger account section';

	function xmlrpc_InsertGLAccountSection($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertGLAccountSection(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}
	$InsertGLAccountGroup_sig = array(array($xmlrpcStruct, $xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$InsertGLAccountGroup_doc = 'This function inserts a General ledger account Group';

	function xmlrpc_InsertGLAccountGroup($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(InsertGLAccountGroup(php_xmlrpc_decode($xmlrpcmsg->getParam(0)),
				 $xmlrpcmsg->getParam(1)->scalarval(),
				 		$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetLocationList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetLocationList_doc = 'This function returns an array containing a list of all locations setup on webERP';

	function xmlrpc_GetLocationList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetLocationList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetLocationDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetLocationDetails_doc = 'This function returns an associative array containing the details of the Location
			 sent as a parameter';

	function xmlrpc_GetLocationDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetLocationDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetShipperList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetShipperList_doc = 'This function returns an array containing a list of all Shippers setup on webERP';

	function xmlrpc_GetShipperList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetShipperList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetShipperDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetShipperDetails_doc = 'This function returns an associative array containing the details of the Shipper
			 sent as a parameter';

	function xmlrpc_GetShipperDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetShipperDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetSalesAreasList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetSalesAreasList_doc = 'This function returns an array containing a list of all Sales areas setup on webERP';

	function xmlrpc_GetSalesAreasList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetSalesAreasList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetSalesAreaDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetSalesAreaDetails_doc = 'This function returns an associative array containing the details of the Sales area
			 sent as a parameter';

	function xmlrpc_GetSalesAreaDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetSalesAreaDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetSalesmanList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetSalesmanList_doc = 'This function returns an array containing a list of all Salesman codes setup on webERP';

	function xmlrpc_GetSalesmanList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetSalesmanList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetSalesmanDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetSalesmanDetails_doc = 'This function returns an associative array containing the details of the Salesman
			 sent as a parameter';

	function xmlrpc_GetSalesmanDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetSalesmanDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetTaxgroupList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetTaxgroupList_doc = 'This function returns an array containing a list of all Taxgroup codes setup on webERP';

	function xmlrpc_GetTaxgroupList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetTaxgroupList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetTaxgroupDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetTaxgroupDetails_doc = 'This function returns an associative array containing the details of the Taxgroup
			 sent as a parameter';

	function xmlrpc_GetTaxgroupDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetTaxgroupDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$GetCustomerTypeList_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString));
	$GetCustomerTypeList_doc = 'This function returns an array containing a list of all Customer Type ids setup on webERP';

	function xmlrpc_GetCustomerTypeList($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCustomerTypeList($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval())));
	}

	$GetCustomerTypeDetails_sig = array(array($xmlrpcStruct, $xmlrpcString, $xmlrpcString, $xmlrpcString));
	$GetCustomerTypeDetails_doc = 'This function returns an associative array containing the details of the Customer Type
			 sent as a parameter';

	function xmlrpc_GetCustomerTypeDetails($xmlrpcmsg) {
		return new xmlrpcresp(php_xmlrpc_encode(GetCustomerTypeDetails($xmlrpcmsg->getParam(0)->scalarval(),
			$xmlrpcmsg->getParam(1)->scalarval(),
				$xmlrpcmsg->getParam(2)->scalarval())));
	}

	$s = new xmlrpc_server( array(
		"weberp.xmlrpc_InsertCustomer" => array(
			"function" => "xmlrpc_InsertCustomer",
			"signature" => $InsertCustomer_sig,
			"docstring" => $InsertCustomer_doc),
		"weberp.xmlrpc_ModifyCustomer" => array(
			"function" => "xmlrpc_ModifyCustomer",
			"signature" => $ModifyCustomer_sig,
			"docstring" => $ModifyCustomer_doc),
		"weberp.xmlrpc_GetCustomer" => array(
			"function" => "xmlrpc_GetCustomer",
			"signature" => $GetCustomer_sig,
			"docstring" => $GetCustomer_doc),
		"weberp.xmlrpc_SearchCustomers" => array(
			"function" => "xmlrpc_SearchCustomers",
			"signature" => $SearchCustomers_sig,
			"docstring" => $SearchCustomers_doc),
		"weberp.xmlrpc_GetCurrencyList" => array(
			"function" => "xmlrpc_GetCurrencyList",
			"signature" => $GetCurrencyList_sig,
			"docstring" => $GetCurrencyList_doc),
		"weberp.xmlrpc_GetCurrencyDetails" => array(
			"function" => "xmlrpc_GetCurrencyDetails",
			"signature" => $GetCurrencyDetails_sig,
			"docstring" => $GetCurrencyDetails_doc),
		"weberp.xmlrpc_GetSalesTypeList" => array(
			"function" => "xmlrpc_GetSalesTypeList",
			"signature" => $GetSalesTypeList_sig,
			"docstring" => $GetSalesTypeList_doc),
		"weberp.xmlrpc_GetSalesTypeDetails" => array(
			"function" => "xmlrpc_GetSalesTypeDetails",
			"signature" => $GetSalesTypeDetails_sig,
			"docstring" => $GetSalesTypeDetails_doc),
		"weberp.xmlrpc_GetHoldReasonList" => array(
			"function" => "xmlrpc_GetHoldReasonList",
			"signature" => $GetHoldReasonList_sig,
			"docstring" => $GetHoldReasonList_doc),
		"weberp.xmlrpc_GetHoldReasonDetails" => array(
			"function" => "xmlrpc_GetHoldReasonDetails",
			"signature" => $GetHoldReasonDetails_sig,
			"docstring" => $GetHoldReasonDetails_doc),
		"weberp.xmlrpc_GetPaymentTermsList" => array(
			"function" => "xmlrpc_GetPaymentTermsList",
			"signature" => $GetPaymentTermsList_sig,
			"docstring" => $GetPaymentTermsList_doc),
		"weberp.xmlrpc_GetPaymentTermsDetails" => array(
			"function" => "xmlrpc_GetPaymentTermsDetails",
			"signature" => $GetPaymentTermsDetails_sig,
			"docstring" => $GetPaymentTermsDetails_doc),
		"weberp.xmlrpc_InsertStockItem" => array(
			"function" => "xmlrpc_InsertStockItem",
			"signature" => $InsertStockItem_sig,
			"docstring" => $InsertStockItem_doc),
		"weberp.xmlrpc_ModifyStockItem" => array(
			"function" => "xmlrpc_ModifyStockItem",
			"signature" => $ModifyStockItem_sig,
			"docstring" => $ModifyStockItem_doc),
		"weberp.xmlrpc_GetStockItem" => array(
			"function" => "xmlrpc_GetStockItem",
			"signature" => $GetStockItem_sig,
			"docstring" => $GetStockItem_doc),
		"weberp.xmlrpc_SearchStockItems" => array(
			"function" => "xmlrpc_SearchStockItems",
			"signature" => $SearchStockItems_sig,
			"docstring" => $SearchStockItems_doc),
		"weberp.xmlrpc_GetStockBalance" => array(
			"function" => "xmlrpc_GetStockBalance",
			"signature" => $GetStockBalance_sig,
			"docstring" => $GetStockBalance_doc),
		"weberp.xmlrpc_InsertSalesInvoice" => array(
			"function" => "xmlrpc_InsertSalesInvoice",
			"signature" => $InsertSalesInvoice_sig,
			"docstring" => $InsertSalesInvoice_doc),
		"weberp.xmlrpc_InsertSalesCredit" => array(
			"function" => "xmlrpc_InsertSalesCredit",
			"signature" => $InsertSalesCredit_sig,
			"docstring" => $InsertSalesCredit_doc),
		"weberp.xmlrpc_InsertBranch" => array(
			"function" => "xmlrpc_InsertBranch",
			"signature" => $InsertBranch_sig,
			"docstring" => $InsertBranch_doc),
		"weberp.xmlrpc_ModifyBranch" => array(
			"function" => "xmlrpc_ModifyBranch",
			"signature" => $ModifyBranch_sig,
			"docstring" => $ModifyBranch_doc),
		"weberp.xmlrpc_InsertSalesOrderHeader" => array(
			"function" => "xmlrpc_InsertSalesOrderHeader",
			"signature" => $InsertSalesOrderHeader_sig,
			"docstring" => $InsertSalesOrderHeader_doc),
		"weberp.xmlrpc_ModifySalesOrderHeader" => array(
			"function" => "xmlrpc_ModifySalesOrderHeader",
			"signature" => $ModifySalesOrderHeader_sig,
			"docstring" => $ModifySalesOrderHeader_doc),
		"weberp.xmlrpc_InsertSalesOrderLine" => array(
			"function" => "xmlrpc_InsertSalesOrderLine",
			"signature" => $InsertSalesOrderLine_sig,
			"docstring" => $InsertSalesOrderLine_doc),
		"weberp.xmlrpc_ModifySalesOrderLine" => array(
			"function" => "xmlrpc_ModifySalesOrderLine",
			"signature" => $ModifySalesOrderLine_sig,
			"docstring" => $ModifySalesOrderLine_doc),
		"weberp.xmlrpc_InsertGLAccount" => array(
			"function" => "xmlrpc_InsertGLAccount",
			"signature" => $InsertGLAccount_sig,
			"docstring" => $InsertGLAccount_doc),
		"weberp.xmlrpc_InsertGLAccountSection" => array(
			"function" => "xmlrpc_InsertGLAccountSection",
			"signature" => $InsertGLAccountSection_sig,
			"docstring" => $InsertGLAccountSection_doc),
		"weberp.xmlrpc_InsertGLAccountGroup" => array(
			"function" => "xmlrpc_InsertGLAccountGroup",
			"signature" => $InsertGLAccountGroup_sig,
			"docstring" => $InsertGLAccountGroup_doc),
		"weberp.xmlrpc_GetLocationList" => array(
			"function" => "xmlrpc_GetLocationList",
			"signature" => $GetLocationList_sig,
			"docstring" => $GetLocationList_doc),
		"weberp.xmlrpc_GetLocationDetails" => array(
			"function" => "xmlrpc_GetLocationDetails",
			"signature" => $GetLocationDetails_sig,
			"docstring" => $GetLocationDetails_doc),
		"weberp.xmlrpc_GetShipperList" => array(
			"function" => "xmlrpc_GetShipperList",
			"signature" => $GetShipperList_sig,
			"docstring" => $GetShipperList_doc),
		"weberp.xmlrpc_GetShipperDetails" => array(
			"function" => "xmlrpc_GetShipperDetails",
			"signature" => $GetShipperDetails_sig,
			"docstring" => $GetShipperDetails_doc),
		"weberp.xmlrpc_GetSalesAreasList" => array(
			"function" => "xmlrpc_GetSalesAreasList",
			"signature" => $GetSalesAreasList_sig,
			"docstring" => $GetSalesAreasList_doc),
		"weberp.xmlrpc_GetSalesAreaDetails" => array(
			"function" => "xmlrpc_GetSalesAreaDetails",
			"signature" => $GetSalesAreaDetails_sig,
			"docstring" => $GetSalesAreaDetails_doc),
		"weberp.xmlrpc_GetSalesmanList" => array(
			"function" => "xmlrpc_GetSalesmanList",
			"signature" => $GetSalesmanList_sig,
			"docstring" => $GetSalesmanList_doc),
		"weberp.xmlrpc_GetSalesmanDetails" => array(
			"function" => "xmlrpc_GetSalesmanDetails",
			"signature" => $GetSalesmanDetails_sig,
			"docstring" => $GetSalesmanDetails_doc),
		"weberp.xmlrpc_GetTaxgroupList" => array(
			"function" => "xmlrpc_GetTaxgroupList",
			"signature" => $GetTaxgroupList_sig,
			"docstring" => $GetTaxgroupList_doc),
		"weberp.xmlrpc_GetTaxgroupDetails" => array(
			"function" => "xmlrpc_GetTaxgroupDetails",
			"signature" => $GetTaxgroupDetails_sig,
			"docstring" => $GetTaxgroupDetails_doc),
		)
	);

?>