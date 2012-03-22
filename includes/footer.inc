<?php

/* $Id$*/

echo '<table width="100%" id="footer">';

echo '<tr>';
echo '<td style="width:33%"></td><td class="footer">';
echo '<img src="'. $rootpath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="webERP" title="webERP ' . _('Copyright') . ' &copy; weberp.org - ' . date('Y') . '" /></td>';
echo '<td class="footer" style="text-align: right;width:33%">'.strftimeToUTF8((strftime('%A'))).', '.(date($_SESSION['DefaultDateFormat']).' | '.(strftimeToUTF8(strftime(($DefaultClock==12) ? '%I:%M %p' : '%H:%M')))).'</td>';

echo '</tr>';
echo '<tr><td colspan="3" class="footer">webERP ' ._('version') .' ' . $_SESSION['VersionNumber'] . '</td></tr>';

//	if(http_file_exists('http://sflogo.sourceforge.net/sflogo.php')) {
//		echo '<tr><td class="footer"><a href="https://sourceforge.net/projects/web-erp"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=391629&amp;type=12" width="120" height="30" border="0" alt="Get webERP web-based ERP Accounting at SourceForge.net. Fast, secure and Free Open Source software downloads" /></a></td></tr>';
//	}

echo '</table>';

echo '</div>';
echo '</body>';
echo '</html>';
 function strftimeToUTF8 ($str) {
 
     $encoding = mb_detect_encoding($str,"GB2312,BIG5,UTF-8");

     switch($encoding){
     		case 'EUC-CN':
			return iconv('GB2312','UTF-8',$str);
			break;
		case 'EUC-TW':
			return iconv('BIG5','UTF-8',$str);
			break;
		default:
			return $str;
     }
     }
?>
