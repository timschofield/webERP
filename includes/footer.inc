<?php

/* $Revision: 1.11 $ */

	echo '<BR><BR>';
	echo '</TD>';
	echo '</TR>';
	echo '</TABLE>';

	echo '</TD>';

	echo '<TD BACKGROUND="css/default/images/outline/r.gif" COLSPAN="3" ALIGN="right" VALIGN="top">';
	echo '<IMG SRC="css/default/images/outline/tr.gif" WIDTH="10" HEIGHT="10" ALT=""></TD>';
	echo '</TR>';

	echo '<TR>';
	echo '<TD BACKGROUND="css/default/images/outline/r.gif" WIDTH="10">';
	echo '<IMG SRC="css/default/images/outline/r.gif" WIDTH="10" HEIGHT="10" ALT=""></TD>';
	echo '</TR>';

	echo '<TR>';
	echo '<TD BACKGROUND="css/default/images/outline/bm.gif">';
	echo '<IMG SRC="css/default/images/outline/bl.gif" WIDTH="10" HEIGHT="10" ALT=""></TD>';
	echo '<TD BACKGROUND="css/default/images/outline/bm.gif">';
	echo '<IMG SRC="css/default/images/outline/bm.gif" WIDTH="10" HEIGHT="10" ALT=""></TD>';
	echo '<TD WIDTH="10"><IMG SRC="css/default/images/outline/br.gif" WIDTH="10" HEIGHT="10" ALT=""></TD>';
	echo '</TR>';

	echo '</TABLE>';

	echo '<FONT SIZE=2>' . date('l ' . $_SESSION['DefaultDateFormat'] . ' | g:i A') . '</FONT>';

	echo '<TABLE ALIGN="center" ID="footer">';
	
	echo '<TR>';
	echo '<TD ALIGN="center">';
	echo '<A HREF="http://www.weberp.org" TARGET="_blank">';
	echo '<IMG SRC="' . $rootpath . '/css/' . $theme . '/images/webERP+sm.gif" BORDER="0">';
	echo '</A>';
	echo '<BR>' . _('Version') . ' - ' . $Version;
	echo '</TD>';
	echo '</TR>';

	echo '<TR>';
	echo '<TD ALIGN="center" CLASS="footer">webERP ' . _('Copyright') . ' &copy; Logic Works Ltd - 2004</TD>';
	echo '</TR>';

	echo '</TABLE>';
	echo '</BODY>';
	echo '</HTML>';

?>
