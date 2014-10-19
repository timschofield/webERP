<?php

/* $Id: footer.inc 6594 2014-03-02 23:15:39Z turbopt $*/

echo '</div>'; // BodyWrapDiv
echo '</div>'; // BodyDiv

echo '<div id="FooterDiv">';
echo '<div id="FooterWrapDiv">';

echo '<div id="FooterLogoDiv">';
	echo '<img src="'. $RootPath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="webERP" title="webERP ' . _('Copyright') . ' &copy; weberp.org - ' . date('Y') . '" />';
echo '</div>';

echo '<div id="FooterTimeDiv">';
	echo DisplayDateTime();
echo '</div>';

echo '<div id="FooterVersionDiv">';
	echo 'webERP ' . _('version') . ' ' . $_SESSION['VersionNumber'] . ' ' . _('Copyright') . ' &copy; 2004 - ' . Date('Y'). ' <a target="_blank" href="http://www.weberp.org/weberp/doc/Manual/ManualContributors.html">weberp.org</a>';
echo '</div>';

echo '</div>'; // FooterWrapDiv
echo '</div>'; // FooterDiv
echo '</div>'; // Canvas

echo '</body>';
echo '</html>';

?>