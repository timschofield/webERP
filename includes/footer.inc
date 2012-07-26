<?php

/* $Id$*/

echo '</div>'; // BodyWrapDiv ===HJ===
echo '</div>'; // BodyDiv ===HJ===

echo '<div id="FooterDiv">'; //===HJ===
echo '<div id="FooterWrapDiv">'; //===HJ===

echo '<div id="FooterLogoDiv">'; //===HJ===
	echo '<img src="'. $rootpath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="webERP" title="webERP ' . _('Copyright') . ' &copy; weberp.org - ' . date('Y') . '" /></td>';
echo '</div>';

echo '<div id="FooterVersionDiv">'; //===HJ===
	echo 'webERP ' ._('version') .' ' . $_SESSION['VersionNumber'];
echo '</div>';

echo '<div id="FooterTimeDiv">'; //===HJ===
	echo DisplayDateTime();
echo '</div>';

//	if(http_file_exists('http://sflogo.sourceforge.net/sflogo.php')) {
//		echo '<tr><td class="footer"><a href="https://sourceforge.net/projects/web-erp"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=391629&amp;type=12" width="120" height="30" border="0" alt="Get webERP web-based ERP Accounting at SourceForge.net. Fast, secure and Free Open Source software downloads" /></a></td></tr>';
//	}

echo '</div>'; // FooterWrapDiv ===HJ===
echo '</div>'; // FooterDiv ===HJ===

echo '</body>';
echo '</html>';

?>
