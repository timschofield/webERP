<?php

/* set all options for DomPDF in one place for reusability and consistency */

$DomPDFOptions = new \Dompdf\Options();

$DomPDFOptions->set('isHtml5ParserEnabled', true);
$DomPDFOptions->set('isRemoteEnabled', true);

// Ensure PathPrefix is set before using it
if (!isset($PathPrefix)) {
	$PathPrefix = '';
}

if (isset($SymlinkImageDir) and ($SymlinkImageDir != '')) {
	$DomPDFOptions->setChroot([$PathPrefix, $SymlinkImageDir]);
} else {
	$DomPDFOptions->setChroot([$PathPrefix]);
}
