<?php

/* $Revision: 1.12 $ */

	echo '<BR><BR>';
	echo '</TD>';
	echo '</TR>';
	echo '</TABLE>';

	echo '</TD>';
/*Do the borders */
	echo '<TD BGCOLOR="#555555" COLSPAN="3"></TD></TR>';
	echo '<TR><TD BGCOLOR="#555555" WIDTH="10"></TD></TR>';
	echo '<TR HEIGHT="10" BGCOLOR="#555555"><TD COLSPAN=3></TD></TR>';
	
	echo '</TABLE>';

	echo '<FONT SIZE=2>' . date('l ' . $_SESSION['DefaultDateFormat'] . ' | g:i A') . '</FONT>';

	echo '<TABLE ALIGN="center" ID="footer">';
	
	echo '<TR>';
	echo '<TD ALIGN="center">';
	echo '<A HREF="http://www.weberp.org" TARGET="_blank">';
	echo '<IMG SRC="' . $rootpath . '/css/' . $theme . '/images/webERP+sm.gif" BORDER="0" ALT="" TITLE="webERP ' . _('Copyright') . ' &copy; Logic Works Ltd - 2005">';
	echo '</A>';
	echo '<BR>' . _('Version') . ' - ' . $Version;
	echo '</TD>';
	echo '</TR>';

	echo '<TR>';
	echo '<TD ALIGN="center" CLASS="footer">webERP ' . _('Copyright') . ' &copy; Logic Works Ltd - 2005</TD>';
	echo '</TR>';

	echo '</TABLE>';
	echo '</BODY>';
	echo '</HTML>';

?>
