<?php

/* set all options for DomPDF in one place for reusability and consistency */

use Dompdf\Options;

$DomPDFOptions = new Options();

$DomPDFOptions->set('isHtml5ParserEnabled', true);
$DomPDFOptions->set('isRemoteEnabled', true);

if (isset($SymlinkImageDir) and ($SymlinkImageDir != '')) {
	$DomPDFOptions->setChroot([__DIR__, $SymlinkImageDir]);
} else {
	$DomPDFOptions->setChroot(__DIR__);
}

