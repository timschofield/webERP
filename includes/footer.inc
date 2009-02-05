<?php

/* $Revision: 1.37 $ */

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
	echo '<td align="center">';
 	echo '<a href="http://www.weberp.org" rel="external"><img src="'. $rootpath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="webERP" title="webERP ' . _('Copyright') . ' &copy; webrp.org - ' . date('Y') . '" /></a>';
 	echo '<br />' . _('Version') . ' - ' . $Version;

	echo '</td></tr>';

	echo '<tr><td align="center" class="footer">webERP ' . _('Copyright') . ' &copy; weberp.org - '.date('Y').'</td></tr>';

	if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on'){
		echo '<tr><td align="center"><a href="http://sourceforge.net"><img src="https://sflogo.sourceforge.net/sflogo.php?group_id=70949&amp;type=1" width="88" height="31" alt="SourceForge.net Logo" /></a></td></tr>';
	} else {
       	echo '<tr><td align="center"><a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=70949&amp;type=1" width="88" height="31"  alt="SourceForge.net Logo" /></a></td></tr>';
	}

	echo '</table>';

	echo '</body>';
	echo '</html>';

?>