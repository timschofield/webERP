<?php

if (($KL_SPGSeniorOrSupport or $KL_SPGJunior)
	and (!isset($_SESSION['cashsalecustomer']))){
	/*Get the default customer-branch combo from the user's default location record */
	$SQL = "SELECT 
				l.cashsalecustomer,
				l.cashsalebranch,
				l.locationname,
				l.deladd1,
				l.deladd2,
				l.deladd3,
				l.deladd4,
				l.deladd5,
				l.klposcashaccount,
				l.klpostag,
				l.taxprovinceid,
				l.typeloc,
				dm.name,
				dm.salestype,
				dm.currcode,
				dm.customerpoline,
				krp.partnercode,
				krp.partnername,
				krp.partneraddress,
				krp.partnernpwp,
				krp.ppn,
				krp.areasalescreditcard,
				krp.areasalescash,
				krp.areasalescashothers,
				krp.cashsalesreported,
				krp.counterinvoicea,
				krp.counterinvoiceb,
				krp.counterinvoicec,
				krp.hppcompensation,
				krp.accountposreceivable,
				krp.accounthppcompensation,
				krp.percentconsignmentptadu,
				krp.accountconsignmentsalesptadu,
				krp.accountconsignmentcogspartner,
				krp.accountcomissioncreditcard,
				krp.accountbankdanamon,
				krp.comissionccdanamon,
				krp.comissionamexdanamon,
				krp.settlementdelaydanamon,
				krp.accountbankbni,
				krp.comissionccbni,
				krp.comissionamexbni,
				krp.settlementdelaybni,
				krp.accountbankmandiri,
				krp.comissionccmandiri,
				krp.comissionamexmandiri,
				krp.settlementdelaymandiri,
				krp.accountbankbca,
				krp.comissionccbca,
				krp.comissionamexbca,
				krp.settlementdelaybca,
				krp.accountbankbri,
				krp.comissionccbri,
				krp.comissionamexbri,
				krp.settlementdelaybri,
				krp.accountcomissionwechat,
				krp.accountwechat,
				krp.comissionwechat,
				krp.settlementdelaywechat,
				krp.accountcomissionqris,
				krp.accountqrismandiri,
				krp.comissionqrismandiri,
				krp.settlementdelayqrismandiri,
				krp.accountqrisbri,
				krp.comissionqrisbri,
				krp.settlementdelayqrisbri,
				cb.brname,
				cb.braddress1,
				cb.specialinstructions,
				cb.salesman,
				cb.taxgroupid,
				hr.dissallowinvoices,
				st.sales_type,
				pt.terms
			FROM locations l
			INNER JOIN debtorsmaster dm
				ON dm.debtorno = l.cashsalecustomer
			INNER JOIN klretailpartners krp
				ON krp.partnercode = l.partnercode
			INNER JOIN custbranch cb
				ON cb.debtorno = l.cashsalecustomer 
				AND cb.branchcode = l.cashsalebranch
			INNER JOIN holdreasons hr
				ON hr.reasoncode = dm.holdreason
			INNER JOIN salestypes st
				ON st.typeabbrev = dm.salestype
			INNER JOIN paymentterms pt
				ON pt.termsindicator = dm.paymentterms
			WHERE l.loccode = '" . $_SESSION['UserStockLocation'] . "'";

	$Result = DB_query($SQL);
	if (DB_num_rows($Result)==0) {
		prnMsg(__('Your SPG user account is not linked to any valid shop. Please contact Kantor IT inmediately.'),'error');
		include('includes/footer.php');
		exit();
	} else {
		$MyRow = DB_fetch_array($Result); //get the only row returned

		if ($MyRow['cashsalecustomer']=='' OR $MyRow['cashsalebranch']==''){
			prnMsg(__('To use this script it is first necessary to define a cash sales customer for the location that is your default location.'),'error');
			include('includes/footer.php');
			exit();
		}

		if ($MyRow['partnercode']=='NORETAIL'){
			prnMsg(__('To use this script it is first necessary to define a retail partner for the location that is your default location. '),'error');
			include('includes/footer.php');
			exit();
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
		$_SESSION['CounterInvoiceA'] = $MyRow['counterinvoicea'];
		$_SESSION['CounterInvoiceB'] = $MyRow['counterinvoiceb'];
		$_SESSION['CounterInvoiceC'] = $MyRow['counterinvoicec'];
		$_SESSION['CashSalesReported'] = $MyRow['cashsalesreported'];
		$_SESSION['HPPCompensation'] = $MyRow['hppcompensation'];
		$_SESSION['AccountHPPCompensation'] = $MyRow['accounthppcompensation'];
		$_SESSION['PercentConsignmentPTADU'] = $MyRow['percentconsignmentptadu'];
		$_SESSION['AccountConsignmentSalesPTADU'] = $MyRow['accountconsignmentsalesptadu'];
		$_SESSION['AccountConsignmentCOGSPartner'] = $MyRow['accountconsignmentcogspartner'];

		$_SESSION['AccountPOSReceivable'] = $MyRow['accountposreceivable'];

		$_SESSION['AccountComissionCreditCard'] = $MyRow['accountcomissioncreditcard'];
		$_SESSION['AccountBankDanamon'] = $MyRow['accountbankdanamon'];
		$_SESSION['ComissionCCDanamon'] = $MyRow['comissionccdanamon'];
		$_SESSION['ComissionAmexDanamon'] = $MyRow['comissionamexdanamon'];
		$_SESSION['SettlementDelayDanamon'] = $MyRow['settlementdelaydanamon'];
		$_SESSION['AccountBankBNI'] = $MyRow['accountbankbni'];
		$_SESSION['ComissionCCBNI'] = $MyRow['comissionccbni'];
		$_SESSION['ComissionAmexBNI'] = $MyRow['comissionamexbni'];
		$_SESSION['SettlementDelayBNI'] = $MyRow['settlementdelaybni'];
		$_SESSION['AccountBankMandiri'] = $MyRow['accountbankmandiri'];
		$_SESSION['ComissionCCMandiri'] = $MyRow['comissionccmandiri'];
		$_SESSION['ComissionAmexMandiri'] = $MyRow['comissionamexmandiri'];
		$_SESSION['SettlementDelayMandiri'] = $MyRow['settlementdelaymandiri'];
		$_SESSION['AccountBankBCA'] = $MyRow['accountbankbca'];
		$_SESSION['ComissionCCBCA'] = $MyRow['comissionccbca'];
		$_SESSION['ComissionAmexBCA'] = $MyRow['comissionamexbca'];
		$_SESSION['SettlementDelayBCA'] = $MyRow['settlementdelaybca'];
		$_SESSION['AccountBankBRI'] = $MyRow['accountbankbri'];
		$_SESSION['ComissionCCBRI'] = $MyRow['comissionccbri'];
		$_SESSION['ComissionAmexBRI'] = $MyRow['comissionamexbri'];
		$_SESSION['SettlementDelayBRI'] = $MyRow['settlementdelaybri'];
		$_SESSION['AccountComissionWeChat'] = $MyRow['accountcomissionwechat'];
		$_SESSION['AccountWeChat'] = $MyRow['accountwechat'];
		$_SESSION['ComissionWeChat'] = $MyRow['comissionwechat'];
		$_SESSION['SettlementDelayWeChat'] = $MyRow['settlementdelaywechat'];
		$_SESSION['AccountComissionQRIS'] = $MyRow['accountcomissionqris'];
		$_SESSION['AccountQRISMandiri'] = $MyRow['accountqrismandiri'];
		$_SESSION['ComissionQRISMandiri'] = $MyRow['comissionqrismandiri'];
		$_SESSION['SettlementDelayQRISMandiri'] = $MyRow['settlementdelayqrismandiri'];
		$_SESSION['AccountQRISBRI'] = $MyRow['accountqrisbri'];
		$_SESSION['ComissionQRISBRI'] = $MyRow['comissionqrisbri'];
		$_SESSION['SettlementDelayQRISBRI'] = $MyRow['settlementdelayqrisbri'];
	}
}
