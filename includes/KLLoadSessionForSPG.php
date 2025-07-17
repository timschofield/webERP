<?php

if (($KL_SPGSeniorOrSupport OR $KL_SPGJunior)
	AND (!isset($_SESSION['cashsalecustomer']))){
	/*Get the default customer-branch combo from the user's default location record */
	$SQL = "SELECT 	locations.cashsalecustomer,
					locations.cashsalebranch,
					locations.locationname,
					locations.deladd1,
					locations.deladd2,
					locations.deladd3,
					locations.deladd4,
					locations.deladd5,
					locations.klposcashaccount,
					locations.klpostag,
					locations.taxprovinceid,
					locations.typeloc,
					debtorsmaster.name,
					debtorsmaster.salestype,
					debtorsmaster.currcode,
					debtorsmaster.customerpoline,
					klretailpartners.partnercode,
					klretailpartners.partnername,
					klretailpartners.partneraddress,
					klretailpartners.partnernpwp,
					klretailpartners.ppn,
					klretailpartners.areasalescreditcard,
					klretailpartners.areasalescash,
					klretailpartners.areasalescashothers,
					klretailpartners.cashsalesreported,
					klretailpartners.hppcompensation,
					klretailpartners.accountposreceivable,
					klretailpartners.accounthppcompensation,
					klretailpartners.accountbankdanamon,
					klretailpartners.accountbankbni,
					klretailpartners.accountbankmandiri,
					klretailpartners.accountbankbca,
					klretailpartners.accountcomissioncreditcard,
					klretailpartners.comissionccdanamon,
					klretailpartners.comissionamexdanamon,
					klretailpartners.comissionccbni,
					klretailpartners.comissionamexbni,
					klretailpartners.comissionccmandiri,
					klretailpartners.comissionccbca,
					klretailpartners.comissionamexbca,
					klretailpartners.percentconsignmentptadu,
					klretailpartners.accountconsignmentsalesptadu,
					klretailpartners.accountconsignmentcogspartner,
					klretailpartners.accountwechat,
					klretailpartners.comissionwechat,
					klretailpartners.accountcomissionwechat,
					klretailpartners.accountqris,
					klretailpartners.comissionqris,
					klretailpartners.accountcomissionqris,
					klretailpartners.counterinvoicea,
					klretailpartners.counterinvoiceb,
					klretailpartners.counterinvoicec,
					klretailpartners.settlementdelaydanamon,
					klretailpartners.settlementdelaybni,
					klretailpartners.settlementdelaybca,
					klretailpartners.settlementdelaymandiri,
					klretailpartners.settlementdelayqris,
					klretailpartners.settlementdelaywechat,
					custbranch.brname,
					custbranch.braddress1,
					custbranch.specialinstructions,
					custbranch.salesman,
					custbranch.taxgroupid,
					holdreasons.dissallowinvoices,
					salestypes.sales_type,
					paymentterms.terms
			 FROM locations,
					debtorsmaster,
					klretailpartners,
					holdreasons,
					salestypes,
					paymentterms,
					custbranch
			 WHERE debtorsmaster.salestype=salestypes.typeabbrev
				AND debtorsmaster.holdreason=holdreasons.reasoncode
				AND debtorsmaster.paymentterms=paymentterms.termsindicator
				AND debtorsmaster.debtorno = locations.cashsalecustomer
				AND locations.partnercode = klretailpartners.partnercode
				AND custbranch.debtorno = locations.cashsalecustomer
				AND custbranch.branchcode = locations.cashsalebranch
				AND loccode='" . $_SESSION['UserStockLocation'] ."'";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0) {
		prnMsg(_('Your SPG user account is not linked to any valid shop. Please contact Kantor IT inmediately.'),'error');
		include('includes/footer.php');
		exit;
	} else {
		$MyRow = DB_fetch_array($Result); //get the only row returned

		if ($MyRow['cashsalecustomer']=='' OR $MyRow['cashsalebranch']==''){
			prnMsg(_('To use this script it is first necessary to define a cash sales customer for the location that is your default location.'),'error');
			include('includes/footer.php');
			exit;
		}

		if ($MyRow['partnercode']=='NORETAIL'){
			prnMsg(_('To use this script it is first necessary to define a retail partner for the location that is your default location. '),'error');
			include('includes/footer.php');
			exit;
		}

		$_SESSION['ShopAddress1'] = $MyRow['deladd1'];
		$_SESSION['ShopAddress2'] = $MyRow['deladd2'];
		$_SESSION['ShopAddress3'] = $MyRow['deladd3'];
		$_SESSION['ShopAddress4'] = $MyRow['deladd4'];
		$_SESSION['ShopAddress5'] = $MyRow['deladd5'];
		$_SESSION['klposcashaccount'] = $MyRow['klposcashaccount'];
		$_SESSION['klpostag'] = $MyRow['klpostag'];

		$_SESSION['cashsalebranch'] = $MyRow['cashsalebranch'];
		$_SESSION['cashsalecustomer'] = $MyRow['cashsalecustomer'];
		$_SESSION['locationname'] = $MyRow['locationname'];
		$_SESSION['taxprovinceid'] = $MyRow['taxprovinceid'];
		$_SESSION['customername'] = $MyRow['name'];
		$_SESSION['salestype'] = $MyRow['salestype'];
		$_SESSION['sales_type'] = $MyRow['sales_type'];
		$_SESSION['currcode'] = $MyRow['currcode'];
		$_SESSION['terms'] = $MyRow['terms'];
		$_SESSION['braddress1'] = $MyRow['braddress1'];
		$_SESSION['specialinstructions'] = $MyRow['specialinstructions'];
		$_SESSION['taxgroupid'] = $MyRow['taxgroupid'];
		$_SESSION['TypeLoc'] = $MyRow['typeloc'];
		
		$_SESSION['PartnerCode'] = $MyRow['partnercode'];
		$_SESSION['PartnerName'] = $MyRow['partnername'];
		$_SESSION['PartnerAddress'] = $MyRow['partneraddress'];
		$_SESSION['PartnerNPWP'] = $MyRow['partnernpwp'];
		$_SESSION['PPN'] = $MyRow['ppn'];
		$_SESSION['AreaSalesCreditCard'] = $MyRow['areasalescreditcard'];
		$_SESSION['AreaSalesCash'] = $MyRow['areasalescash'];
		$_SESSION['AreaSalesCashOthers'] = $MyRow['areasalescashothers'];
		$_SESSION['CashSalesReported'] = $MyRow['cashsalesreported'];
		$_SESSION['HPPCompensation'] = $MyRow['hppcompensation'];
		$_SESSION['AccountHPPCompensation'] = $MyRow['accounthppcompensation'];
		$_SESSION['AccountPOSReceivable'] = $MyRow['accountposreceivable'];
		$_SESSION['AccountBankDanamon'] = $MyRow['accountbankdanamon'];
		$_SESSION['AccountBankBNI'] = $MyRow['accountbankbni'];
		$_SESSION['AccountBankMandiri'] = $MyRow['accountbankmandiri'];
		$_SESSION['AccountBankBCA'] = $MyRow['accountbankbca'];
		$_SESSION['AccountComissionCreditCard'] = $MyRow['accountcomissioncreditcard'];
		$_SESSION['ComissionCCDanamon'] = $MyRow['comissionccdanamon'];
		$_SESSION['ComissionAmexDanamon'] = $MyRow['comissionamexdanamon'];
		$_SESSION['ComissionCCBNI'] = $MyRow['comissionccbni'];
		$_SESSION['ComissionAmexBNI'] = $MyRow['comissionamexbni'];
		$_SESSION['ComissionCCMandiri'] = $MyRow['comissionccmandiri'];
		$_SESSION['ComissionCCBCA'] = $MyRow['comissionccbca'];
		$_SESSION['ComissionAmexBCA'] = $MyRow['comissionamexbca'];
		$_SESSION['PercentConsignmentPTADU'] = $MyRow['percentconsignmentptadu'];
		$_SESSION['AccountConsignmentSalesPTADU'] = $MyRow['accountconsignmentsalesptadu'];
		$_SESSION['AccountConsignmentCOGSPartner'] = $MyRow['accountconsignmentcogspartner'];
		$_SESSION['AccountWeChat'] = $MyRow['accountwechat'];
		$_SESSION['ComissionWeChat'] = $MyRow['comissionwechat'];
		$_SESSION['AccountComissionWeChat'] = $MyRow['accountcomissionwechat'];
		$_SESSION['AccountQRIS'] = $MyRow['accountqris'];
		$_SESSION['ComissionQRIS'] = $MyRow['comissionqris'];
		$_SESSION['AccountComissionQRIS'] = $MyRow['accountcomissionqris'];
		$_SESSION['CounterInvoiceA'] = $MyRow['counterinvoicea'];
		$_SESSION['CounterInvoiceB'] = $MyRow['counterinvoiceb'];
		$_SESSION['CounterInvoiceC'] = $MyRow['counterinvoicec'];
		$_SESSION['SettlementDelayDanamon'] = $MyRow['settlementdelaydanamon'];
		$_SESSION['SettlementDelayBNI'] = $MyRow['settlementdelaybni'];
		$_SESSION['SettlementDelayBCA'] = $MyRow['settlementdelaybca'];
		$_SESSION['SettlementDelayMandiri'] = $MyRow['settlementdelaymandiri'];
		$_SESSION['SettlementDelayQRIS'] = $MyRow['settlementdelayqris'];
		$_SESSION['SettlementDelayWeChat'] = $MyRow['settlementdelaywechat'];
		
	}
}
