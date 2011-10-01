<?php

/* $Id$*/

     echo '</td>';
     echo '</tr>';
     echo '</table>';

     echo '</td></tr>';
/*Do the borders */

     echo '</table>';
     
     echo '<table width="100%" id="footer">';
     echo '<tr>
				<td width="29%"> </td>
				<td class="footer"><a href="http://www.weberp.org" rel="external"><img src="'. $rootpath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="webERP" title="webERP ' . _('Copyright') . ' &copy; weberp.org - ' . date('Y') . '" /></a></td>
				<td class="footer">'.(strftime('%A')).', '.(date($_SESSION['DefaultDateFormat']).' | '.(strftime(($DefaultClock==12) ? '%I:%M %p' : '%H:%M'))).'</td>
		</tr>';

     echo '<tr>
				<td class="footer"> </td>
				<td class="footer">webERP v' . $_SESSION['VersionNumber'] . ' ' . _('Copyright') . ' &copy; weberp.org - '.date('Y').'</td>
			</tr>';

     if(http_file_exists('http://sflogo.sourceforge.net/sflogo.php')) {
         echo '<tr>
				<td class="footer"></td>
				<td class="footer"><a href="https://sourceforge.net/projects/web-erp"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=70949&type=12" width="120" height="30" border="0" alt="Get webERP web-based ERP Accounting at SourceForge.net. Fast, secure and Free Open Source software downloads" /></a></td>
			</tr>';
     }
     echo '</table>';
     echo '</body>';
     echo '</html>';
?>
