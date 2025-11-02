<?php

/* set all options for DomPDF in one place for reusability and consistency */

use Dompdf\Options;

$options = new Options();

$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

if (isset($SymlinkImageDir) and ($SymlinkImageDir != '')) {
	$options->setChroot([__DIR__, $SymlinkImageDir]);
} else {
	$options->setChroot(__DIR__);
}

