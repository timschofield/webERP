<?php

/* GIFTS format Bank of New Zealand */
$LineText = mb_substr($LineText,0,-1);
$LineArray = explode(',',$LineText);
if ($LineArray[0] == 5) { //Opening balance
	$_SESSION['Statement']->AccountNumber = trim($LineArray[2],'"');
	$_SESSION['Statement']->OpeningBalance = doubleval($LineArray[3]);
	$_SESSION['Statement']->OpeningDate = trim($LineArray[10],'"');
	$_SESSION['Statement']->AccountName = trim($LineArray[9],'"');
}
if ($LineArray[0] == 6) { //Closing balance
	$_SESSION['Statement']->AccountNumber = trim($LineArray[2],'"');
	$_SESSION['Statement']->ClosingBalance = doubleval($LineArray[3]);
	$_SESSION['Statement']->ClosingDate = trim($LineArray[10],'"');
}
if ($LineArray[0] == 3) {//A Transaction Line
	$i++;
	$_SESSION['Trans'][$i] = new BankTrans(trim($LineArray[10],'"'),doubleval(-$LineArray[3])) ;
	$_SESSION['Trans'][$i]->Description = trim($LineArray[12],'"') . '-' . trim($LineArray[6],'"') . ' ' . trim($LineArray[7],'"') . ' ' . trim($LineArray[8],'"') . ' ' . trim($LineArray[9],'"') . ' ' . trim($LineArray[11],'"') . ' ' . trim($LineArray[14],'"');
}
