<?php

if (($KL_SPGSeniorOrSupport OR $KL_SPGJunior)
	AND (!isset($_SESSION['cashsalecustomer']))){
	/*Get the default customer-branch combo from the user's default location record */
	$sql = "SELECT 	locations.cashsalecustomer,
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

	$result = DB_query($sql);
	if (DB_num_rows($result)==0) {
		prnMsg(_('Your SPG user account is not linked to any valid shop. Please contact Kantor IT inmediately.'),'error');
		include('includes/footer.php');
		exit;
	} else {
		$myrow = DB_fetch_array($result); //get the only row returned

		if ($myrow['cashsalecustomer']=='' OR $myrow['cashsalebranch']==''){
			prnMsg(_('To use this script it is first necessary to define a cash sales customer for the location that is your default location.'),'error');
			include('includes/footer.php');
			exit;
		}

		if ($myrow['partnercode']=='NORETAIL'){
			prnMsg(_('To use this script it is first necessary to define a retail partner for the location that is your default location. '),'error');
			include('includes/footer.php');
			exit;
		}

		$_SESSION['ShopAddress1'] = $myrow['deladd1'];
		$_SESSION['ShopAddress2'] = $myrow['deladd2'];
		$_SESSION['ShopAddress3'] = $myrow['deladd3'];
		$_SESSION['ShopAddress4'] = $myrow['deladd4'];
		$_SESSION['ShopAddress5'] = $myrow['deladd5'];
		$_SESSION['klposcashaccount'] = $myrow['klposcashaccount'];
		$_SESSION['klpostag'] = $myrow['klpostag'];

		$_SESSION['cashsalebranch'] = $myrow['cashsalebranch'];
		$_SESSION['cashsalecustomer'] = $myrow['cashsalecustomer'];
		$_SESSION['locationname'] = $myrow['locationname'];
		$_SESSION['taxprovinceid'] = $myrow['taxprovinceid'];
		$_SESSION['customername'] = $myrow['name'];
		$_SESSION['salestype'] = $myrow['salestype'];
		$_SESSION['sales_type'] = $myrow['sales_type'];
		$_SESSION['currcode'] = $myrow['currcode'];
		$_SESSION['terms'] = $myrow['terms'];
		$_SESSION['braddress1'] = $myrow['braddress1'];
		$_SESSION['specialinstructions'] = $myrow['specialinstructions'];
		$_SESSION['taxgroupid'] = $myrow['taxgroupid'];
		$_SESSION['TypeLoc'] = $myrow['typeloc'];
		
		$_SESSION['PartnerCode'] = $myrow['partnercode'];
		$_SESSION['PartnerName'] = $myrow['partnername'];
		$_SESSION['PartnerAddress'] = $myrow['partneraddress'];
		$_SESSION['PartnerNPWP'] = $myrow['partnernpwp'];
		$_SESSION['PPN'] = $myrow['ppn'];
		$_SESSION['AreaSalesCreditCard'] = $myrow['areasalescreditcard'];
		$_SESSION['AreaSalesCash'] = $myrow['areasalescash'];
		$_SESSION['AreaSalesCashOthers'] = $myrow['areasalescashothers'];
		$_SESSION['CashSalesReported'] = $myrow['cashsalesreported'];
		$_SESSION['HPPCompensation'] = $myrow['hppcompensation'];
		$_SESSION['AccountHPPCompensation'] = $myrow['accounthppcompensation'];
		$_SESSION['AccountBankDanamon'] = $myrow['accountbankdanamon'];
		$_SESSION['AccountBankBNI'] = $myrow['accountbankbni'];
		$_SESSION['AccountBankMandiri'] = $myrow['accountbankmandiri'];
		$_SESSION['AccountBankBCA'] = $myrow['accountbankbca'];
		$_SESSION['AccountComissionCreditCard'] = $myrow['accountcomissioncreditcard'];
		$_SESSION['ComissionCCDanamon'] = $myrow['comissionccdanamon'];
		$_SESSION['ComissionAmexDanamon'] = $myrow['comissionamexdanamon'];
		$_SESSION['ComissionCCBNI'] = $myrow['comissionccbni'];
		$_SESSION['ComissionAmexBNI'] = $myrow['comissionamexbni'];
		$_SESSION['ComissionCCMandiri'] = $myrow['comissionccmandiri'];
		$_SESSION['ComissionCCBCA'] = $myrow['comissionccbca'];
		$_SESSION['ComissionAmexBCA'] = $myrow['comissionamexbca'];
		$_SESSION['PercentConsignmentPTADU'] = $myrow['percentconsignmentptadu'];
		$_SESSION['AccountConsignmentSalesPTADU'] = $myrow['accountconsignmentsalesptadu'];
		$_SESSION['AccountConsignmentCOGSPartner'] = $myrow['accountconsignmentcogspartner'];
		$_SESSION['AccountWeChat'] = $myrow['accountwechat'];
		$_SESSION['ComissionWeChat'] = $myrow['comissionwechat'];
		$_SESSION['AccountComissionWeChat'] = $myrow['accountcomissionwechat'];
		$_SESSION['AccountQRIS'] = $myrow['accountqris'];
		$_SESSION['ComissionQRIS'] = $myrow['comissionqris'];
		$_SESSION['AccountComissionQRIS'] = $myrow['accountcomissionqris'];
		$_SESSION['CounterInvoiceA'] = $myrow['counterinvoicea'];
		$_SESSION['CounterInvoiceB'] = $myrow['counterinvoiceb'];
		$_SESSION['CounterInvoiceC'] = $myrow['counterinvoicec'];
		
	}
}

?>