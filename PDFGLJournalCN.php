<?php

require(__DIR__ . '/includes/session.php');

if (isset($_POST['JournalNo'])) {
	$JournalNo=$_POST['JournalNo'];
	$TypeID=$_POST['Type'];
} else if (isset($_GET['JournalNo'])) {
	$JournalNo=$_GET['JournalNo'];
	$TypeID=$_GET['Type'];
} else {
	$JournalNo='';
	$TypeID='';
}
if ($JournalNo=='Preview') {
	$FormDesign = simplexml_load_file(sys_get_temp_dir().'/Journalc.xml');
} else {
	$FormDesign = simplexml_load_file($PathPrefix.'companies/'.$_SESSION['DatabaseName'].'/FormDesigns/Journalc.xml');
}

// Set the paper size/orintation
$PaperSize = $FormDesign->PaperSize;
$PageNumber=1;
$LineHeight=$FormDesign->LineHeight;
include('includes/PDFStarter.php');
$pdf->addInfo('Title', __('中国(甲式10)会计凭证') );
$pdf->addInfo('Author','webERP ' . 'CQZ二次修改');
$pdf->addInfo('Subject',__('会计凭证——中国式会计凭证--登录ERP打印或下载此凭证的用户：').$_SESSION['UsersRealName']);
$pdf->SetProtection(array('modify','copy','annot-forms'), '');

if ($JournalNo=='Preview') {
	$LineCount = 2; // UldisN
} else {
	$SQL="SELECT gltrans.type,
				gltrans.typeno,
				gltrans.trandate,
				gltrans.account,
				systypes.typename,
				chartmaster.accountname,
				gltrans.narrative,
				gltrans.amount,
				gltrans.tag,
				tags.tagdescription,
				gltrans.jobref
			FROM gltrans
			INNER JOIN chartmaster
				ON gltrans.account=chartmaster.accountcode
			INNER JOIN systypes
				ON gltrans.type=systypes.typeid
			LEFT JOIN tags
				ON gltrans.tag=tags.tagref
			WHERE gltrans.type='".$TypeID."'
				AND gltrans.typeno='" . $JournalNo . "'";
	$Result = DB_query($SQL);
	$LineCount = DB_num_rows($Result); // UldisN
	$MyRow = DB_fetch_array($Result);
	$JournalDate=$MyRow['trandate'];
	DB_data_seek($Result, 0);
	$Typemame=$MyRow['typename'];
	include('includes/PDFGLJournalHeaderCN.php');
}
$Counter=1;
$YPos=$FormDesign->Data->y;
while ($Counter<=$LineCount) {
	if ($JournalNo=='Preview') {
		$AccountCode=str_pad('',10,'x');
		$Date='1/1/1900';
		$Description=str_pad('',30,'x');
		$Narrative=str_pad('',30,'x');
		$Amount='XXXX.XX';
		$Tag=str_pad('',25,'x');
		$JobRef=str_pad('',25,'x');
	} else {
		$MyRow=DB_fetch_array($Result);
		if ($MyRow['tag']==0) {
			$MyRow['tagdescription']='None';
		}
		$AccountCode = $MyRow['account'];
		$Description = $MyRow['accountname'];
		$Date = $MyRow['trandate'];
		$Narrative = $MyRow['narrative'];
		$Amount = $MyRow['amount'];
		$Tag = $MyRow['tag'].' - '.$MyRow['tagdescription'];
		$JobRef = $MyRow['jobref'];
	}

	if ( $MyRow['amount'] > 0) {
			$DebitAmount = locale_number_format($MyRow['amount'],$_SESSION['CompanyRecord']['decimalplaces']);
			$DebitTotal += $MyRow['amount'];
			$CreditAmount = ' ';
	} else {
			$CreditAmount = locale_number_format(-$MyRow['amount'],$_SESSION['CompanyRecord']['decimalplaces']);
			$CreditTotal += $MyRow['amount'];
			$DebitAmount = ' ';
	}
	$pdf->SetTextColor(0,0,0);
	if((mb_strlen($Narrative,'GB2312')+ substr_count($Narrative," "))>40){
	$pdf->addTextWrap($FormDesign->Data->Column1->x+3,$Page_Height-$YPos-5,$FormDesign->Data->Column1->Length,$FormDesign->Data->Column1->FontSize, $Narrative);
	$pdf->addTextWrap($FormDesign->Data->Column2->x+3,$Page_Height-$YPos+3,$FormDesign->Data->Column2->Length,$FormDesign->Data->Column2->FontSize, $AccountCode);
	$pdf->addTextWrap($FormDesign->Data->Column3->x+3,$Page_Height-$YPos+3,$FormDesign->Data->Column3->Length,$FormDesign->Data->Column3->FontSize, $Description);
	}else{
	$pdf->addTextWrap($FormDesign->Data->Column1->x+3,$Page_Height-$YPos,$FormDesign->Data->Column1->Length,$FormDesign->Data->Column1->FontSize, $Narrative);
	$pdf->addTextWrap($FormDesign->Data->Column2->x+3,$Page_Height-$YPos,$FormDesign->Data->Column2->Length,$FormDesign->Data->Column2->FontSize, $AccountCode);
	$pdf->addTextWrap($FormDesign->Data->Column3->x+3,$Page_Height-$YPos,$FormDesign->Data->Column3->Length,$FormDesign->Data->Column3->FontSize, $Description);
	}
	$pdf->SetFont('helvetica', '', 10);
	$pdf->addTextWrap($FormDesign->Data->Column4->x+3,$Page_Height-$YPos,$FormDesign->Data->Column4->Length,$FormDesign->Data->Column4->FontSize,$DebitAmount , 'right');

	$pdf->addTextWrap($FormDesign->Data->Column5->x+3,$Page_Height-$YPos,$FormDesign->Data->Column5->Length,$FormDesign->Data->Column5->FontSize, $CreditAmount, 'right');


	$YPos += $LineHeight;
	$Counter++;

	$DebitTotal1=locale_number_format($DebitTotal,$_SESSION['CompanyRecord']['decimalplaces'],  'right');
	$CreditTotal1=locale_number_format(-$CreditTotal,$_SESSION['CompanyRecord']['decimalplaces'],  'right');

	$pdf->SetFont('javiergb', '', 10);

	if ($YPos >= $FormDesign->LineAboveFooter->starty){
		/* We reached the end of the page so finsih off the page and start a newy */
		$PageNumber++;
		$YPos=$FormDesign->Data->y;
		include('includes/PDFGLJournalHeaderCN.php');
	}
}
$pdf->setlineStyle(array('width'=>0.8));
$pdf->SetLineStyle(array('color'=>array(0,0,0)));
$pdf->Line($XPos=540, $Page_Height-$YPos+15, $FormDesign->Column33->endx,$Page_Height - $FormDesign->Column33->endy);

//end if need a new page headed up


//$pdf->addJpegFromFile('hjje.jpg',$FormDesign->Headings->Column7->x+3+20,$Page_Height - 282,110,28);
$pdf->SetTextColor(0,0,255);
$pdf->addText($FormDesign->Headings->Column7->x+3,$Page_Height-$FormDesign->Headings->Column7->y,$FormDesign->Headings->Column7->FontSize, __('合 计 金 额'));//$FormDesign->Headings->Column7->name
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('helvetica', '', 10);
$pdf->addTextWrap($FormDesign->Headings->Column8->x+3,$Page_Height - $FormDesign->Headings->Column8->y, $FormDesign->Headings->Column8->Length,$FormDesign->Headings->Column8->FontSize, $DebitTotal1, 'right');
$pdf->addTextWrap($FormDesign->Headings->Column9->x+3,$Page_Height - $FormDesign->Headings->Column9->y, $FormDesign->Headings->Column9->Length,$FormDesign->Headings->Column9->FontSize, $CreditTotal1, 'right');
$pdf->SetFont('javiergb', '', 10);

if ($LineCount == 0) {   //UldisN
	$Title = __('GRN Error');
	include('includes/header.php');
	prnMsg(__('There were no GRN to print'),'warn');
	echo '<br /><a href="'.$RootPath.'/index.php">'. __('Back to the menu').'</a>';
	include('includes/footer.php');
	exit();
} else {
    $pdf->OutputD($_SESSION['DatabaseName'] . '_GRN_' . date('Y-m-d').'.pdf');//UldisN
    $pdf->__destruct(); //UldisN
}
