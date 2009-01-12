<?php

/* $Revision: 1.32 $ */

	echo '<BR><BR>';
	echo '</TD>';
	echo '</TR>';
	echo '</TABLE>';

	echo '</TD>';
/*Do the borders */

	echo '</TABLE>';
	setlocale(LC_ALL,$_SESSION['Language']);
	echo '<p style="text-align:right">'.(strftime('%A')).', '.(date($_SESSION['DefaultDateFormat']).' | '.(strftime(($DefaultClock==12) ? '%I:%M %p' : '%H:%M'))).'</p>';


	echo '<TABLE width="100%" ALIGN="center" ID="footer">';

	echo '<TR>';
	echo '<TD ALIGN="center">';
	/* Find a logo in the companies directory */
	$logo = '../weberp/logo.jpg';
	$companydir = 'companies/' . $_SESSION['DatabaseName'] . '/';
	$incompanydir = scandir($companydir,1);
	if ($incompanydir !== FALSE)
		foreach($incompanydir as $logofilename)
			if (strncasecmp($logofilename,'logo.',5) === 0 AND
				is_readable($companydir . $logofilename) AND
				is_file($companydir . $logofilename)) {
				$logo = $logofilename;
			break;
		}
	echo '<A HREF="http://www.weberp.org" TARGET="_blank"><IMG SRC="'. $rootpath . '/companies/' . $_SESSION['DatabaseName'] . '/' . $logo . '" WIDTH=20% BORDER="0" ALT="" TITLE="webERP ' . _('Copyright') . ' &copy; webrp.org - ' . date('Y') . '"></A>';
 	echo '<BR>' . _('Version') . ' - ' . $Version;

	echo '</TD></TR>';

	echo '<TR><TD ALIGN="center" CLASS="footer">webERP ' . _('Copyright') . ' &copy; weberp.org - '.date('Y').'</TD></TR>';

	if(isset($_SERVER['HTTPS'])){
		if($_SERVER['HTTPS'] == 'on'){
			echo '<TR><TD ALIGN="center"><a href="http://sourceforge.net"><img src="https://sflogo.sourceforge.net/sflogo.php?group_id=70949&amp;type=1" width="88" height="31" border="0" alt="SourceForge.net Logo" /></a></TD></TR>';
		} else {
        	echo '<TR><TD ALIGN="center"><a href="http://sourceforge.net"><img src="http://sflogo.sourceforge.net/sflogo.php?group_id=70949&amp;type=1" width="88" height="31" border="0" alt="SourceForge.net Logo" /></a></TD></TR>';
        }
	}

	echo '</TABLE>';

	echo '</BODY>';
	echo '</HTML>';

?>