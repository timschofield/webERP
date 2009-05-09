<?php

/* $Revision: 1.39 $ */

	echo '</td>';
	echo '</tr>';
	echo '</table>';

	echo '</td></tr>';
/*Do the borders */

	echo '</table>';
	setlocale(LC_ALL,$_SESSION['Language']);
	echo '<p style="text-align:right">'.(strftime('%A')).', '.(date($_SESSION['DefaultDateFormat']).' | '.(strftime(($DefaultClock==12) ? '%I:%M %p' : '%H:%M'))).'</p>';


	echo '<table width="100%" id="footer">';

	echo '<tr>';
	echo '<td class="footer">';
 	echo '<a href="http://www.weberp.org" rel="external"><img src="'. $rootpath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="webERP" title="webERP ' . _('Copyright') . ' &copy; webrp.org - ' . date('Y') . '" /></a>';
 	echo '<br />' . _('Version') . ' - ' . $Version;

	echo '</td></tr>';

	echo '<tr><td class="footer">webERP ' . _('Copyright') . ' &copy; weberp.org - '.date('Y').'</td></tr>';

	if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on'){
		echo '<tr><td class="footer"><a href="https://sourceforge.net/projects/web-erp"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=70949&type=12" width="120" height="30" border="0" alt="Get webERP web-based ERP Accounting at SourceForge.net. Fast, secure and Free Open Source software downloads" /></a></td></tr>';
	} else {
       	echo '<tr><td class="footer"><a href="http://sourceforge.net/projects/web-erp"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=70949&type=12" width="120" height="30" border="0" alt="Get webERP web-based ERP Accounting at SourceForge.net. Fast, secure and Free Open Source software downloads" /></a></td></tr>';
	}

	echo '</table>';

	echo '</body>';
	echo '</html>';

?>