<?php

/* MT940 per SCB - Siam Comercial Bank Thailand */

if (substr($LineText,0,4)==':20:') { //Timestamp of report MT940 generation
		$_SESSION['Statement']->ReportCreated = substr($LineText,4); //in format DDDHHMM where DDD is the number of day in year and HHMM is the time
		$TransactionLine = false;
	  }
	  if (substr($LineText,0,4)==':25:'){//The account number in IBAN format
		 $_SESSION['Statement']->AccountNumber = trim(substr($LineText,4));
		 $TransactionLine = false;
	  }
	  if (substr($LineText,0,5)==':28C:'){//The statement number
		 $_SESSION['Statement']->StatementNumber = trim(substr($LineText,5));
		 $TransactionLine = false;
	  }
	  if (substr($LineText,0,6)==':NS:22'){//The account owner name
		 $_SESSION['Statement']->AccountOwner = trim(substr($LineText,6));
		 $TransactionLine = false;
	  }
	  if (substr($LineText,0,6)==':NS:23'){//The account name
		 $_SESSION['Statement']->AccountName = trim(substr($LineText,6));
		 $TransactionLine = false;
	  }
	  if (substr($LineText,0,5)==':60F:'){//The account opening balance
		 $DebitOrCredit = substr($LineText,5,1); //D or C
		 $_SESSION['Statement']->OpeningDate = ConvertSQLDate('20' . substr($LineText,6,2) . '-' . substr($LineText,8,2) . '-' . substr($LineText,10,2));
		 $_SESSION['Statement']->CurrCode = substr($LineText,12,3);
		 if (!array_key_exists($_SESSION['Statement']->CurrCode,$CurrencyName)){
			prnMsg(__('The bank statement currency is a currency not defined in the system. Please see you system administrator'),'warn');
			prnMsg(__('The MT940 bank statement file cannot be imported and processed'),'error');
	        include('includes/footer.php');
	        exit();
			$ReadTheFile ='No';
		}
		 if ($DebitOrCredit =='D'){
			$_SESSION['Statement']->OpeningBalance = doubleval('-' . str_replace(',','.',substr($LineText,15)));
		 } else {
			$_SESSION['Statement']->OpeningBalance = doubleval(str_replace(',','.',substr($LineText,15)));
		 }
		 $TransactionLine = false;
	  }
	  if (substr($LineText,0,4)==':61:'){//It's a transaction line
			$TransactionLine = true;
			$TransDate = ConvertSQLDate('20' . substr($LineText,4,2) . '-' . substr($LineText,6,2) . '-' . substr($LineText,8,2));
			//this format repeats the date from characters 10-14
			$DebitOrCredit = substr($LineText,14,1); //D or C or R
			if ($DebitOrCredit =='R'){ //then it is a 2 character reversal
				if (substr($LineText,14,2)=='RC'){
					$DebitOrCredit ='D';
				} else {
					$DebitOrCredit ='C';
				}
				//Need to find end of value amount - find the , decimal point + 2 characters
				$ValueEnd = strpos($LineText, ',', 12)+2;
				if ($DebitOrCredit =='D'){
					$TransAmount = doubleval('-' . str_replace(',','.',substr($LineText,12,$ValueEnd)));
				} else {
					$TransAmount = doubleval(str_replace(',','.',substr($LineText,12,$ValueEnd)));
				}
			} else { // it will be either D or C
				if (!is_numeric(substr($LineText,15,1)) ){
					//check for a B - no idea why but can be a B after the D or C for some reason in which case the amount starts a character after
					$ValueStart = 16;
				} else {
					$ValueStart = 15;
				}
				//Need to find end of value amount - find the , decimal point + 2 characters
				$ValueEnd = strpos($LineText, ',', $ValueStart)+2;
				if ($DebitOrCredit =='D'){
					$TransAmount = doubleval('-' . str_replace(',','.',substr($LineText,$ValueStart,$ValueEnd)));
				} else {
					$TransAmount = doubleval(str_replace(',','.',substr($LineText,$ValueStart,$ValueEnd)));
				}
			}

			$i++;
			$_SESSION['Trans'][$i] = new BankTrans($TransDate,$TransAmount) ;
			$_SESSION['Trans'][$i]->Description = substr($LineText,$ValueEnd+1);
	  }
	  if (substr($LineText,0,4)==':86:'){
		 if ($TransactionLine) {
			$_SESSION['Trans'][$i]->Description .= ' ' . substr($LineText,4);
		 }
	  }

	 /* if (substr($LineText,0,1)!=':' AND $TransactionLine){
		  //then it is the continuation of an :86: line
		$_SESSION['Trans'][$i]->Description .= $LineText;
	  }*/

	  if (substr($LineText,0,5)==':62F:'){
		 $DebitOrCredit = substr($LineText,5,1); //D or C
		 $_SESSION['Statement']->ClosingDate = ConvertSQLDate('20' . substr($LineText,6,2) . '-' . substr($LineText,8,2) . '-' . substr($LineText,10,2));
		 $CurrCode = substr($LineText,12,3);
		 if ($DebitOrCredit =='D'){
			$_SESSION['Statement']->ClosingBalance = doubleval('-' . str_replace(',','.',substr($LineText,15)));
		 } else {
			$_SESSION['Statement']->ClosingBalance = doubleval(str_replace(',','.',substr($LineText,15)));
		 }
		 $TransactionLine = false;
	  }
