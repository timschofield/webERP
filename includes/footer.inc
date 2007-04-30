<?php

/* $Revision: 1.25 $ */

	echo '<BR><BR>';
	echo '</TD>';
	echo '</TR>';
	echo '</TABLE>';

	echo '</TD>';
/*Do the borders */
	echo '<TD BGCOLOR="#555555" COLSPAN="3"></TD></TR>';
	echo '<TR BGCOLOR="#555555"><TD  COLSPAN="3"></TD></TR>';
	echo '<TR BGCOLOR="#555555"><TD COLSPAN="5"><FONT SIZE="1">&nbsp;</FONT></TD></TR>';
	
	echo '</TABLE>';

	if ($DefaultClock==12) {
		$hour=date('g:i a');
	} else {
		$hour=date('H:i');
	}

      $weekDay=strtolower(date('l'));
      if ($weekDay!=_($weekDay)) {
        $weekDay=_($weekday);
      } else {
        $weekDay=strftime('%A');
      }
      if ($DefaultClock==12) {
        $ampm='_'.date('a');
        if ($ampm!=_($ampm)) {
          $ampm=_($ampm);
        } else { 
          $ampm=strftime('%p');
        }
      } else {
        $ampm='';
      }
	echo '<p style="text-align:right">'.$weekDay.', '.(date($_SESSION['DefaultDateFormat'].' | '.(($DefaultClock==12) ? 'g:i ' : 'H:i')).$ampm).'</p>';

	echo '<TABLE ALIGN="center" ID="footer">';
	
	echo '<TR>';
	echo '<TD ALIGN="center">';
	echo '<A HREF="http://www.weberp.org" TARGET="_blank"><IMG SRC="'. $rootpath . '/css/webERPsm.gif" BORDER="0" ALT="" TITLE="webERP ' . _('Copyright') . ' &copy; Logic Works Ltd - ' . date('Y') . '"></A>';
	echo '<BR>' . _('Version') . ' - ' . $Version;
	echo '</TD></TR>';

	echo '<TR><TD ALIGN="center" CLASS="footer">webERP ' . _('Copyright') . ' &copy; Logic Works Ltd - '.date('Y').'</TD></TR>';
	
//	echo '<TR><TD ALIGN="center"><a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=70949&amp;type=1" width="88" height="31" border="0" alt="SourceForge.net Logo" /></a></TD></TR>';

	echo '</TABLE>';
	
	echo '</BODY>';
	echo '</HTML>';

?>