<?php
echo '<div id="mask">
		<div id="dialog"></div>
	</div>';

if (isset($Messages) and count($Messages) > 0) {
	$LogFile = false;

	if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 0) { // add these 3 lines
		$LogFile = fopen($_SESSION['LogPath'] . '/weberp - ' . $_SESSION['DatabaseName'] . '.log', 'a');
	}

	echo '<div id="MessageContainerFoot">';

	foreach ($Messages as $Message) {
		switch ($Message[1]) {
			case 'error':
				$Class = 'error';
				$Message[2] = $Message[2] ? $Message[2] : __('ERROR') . ' ' . __('Report');
				if (!empty($LogFile) && isset($_SESSION['LogSeverity']) && $_SESSION['LogSeverity'] > 0) {
					fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Message[2] . ',' . $_SESSION['UserID'] . ',' . strip_tags(trim($Message[0], ',')) . "\n");
				}
			    break;

			case 'warn':
			case 'warning':
				$Class = 'warn';
				$Message[2] = $Message[2] ? $Message[2] : __('WARNING') . ' ' . __('Report');
				if (!empty($LogFile) && isset($_SESSION['LogSeverity']) && $_SESSION['LogSeverity'] > 1) {
					fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Message[2] . ',' . $_SESSION['UserID'] . ',' . strip_tags(trim($Message[0], ',')) . "\n");
				}
				break;

            case 'info':
                $Class = 'info';
                $Message[2] = $Message[2] ? $Message[2] : __('INFORMATION') . ' ' . __('Message');
				if (!empty($LogFile) && isset($_SESSION['LogSeverity']) && $_SESSION['LogSeverity'] > 2) {
					fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Message[2] . ',' . $_SESSION['UserID'] . ',' . strip_tags(trim($Message[0], ',')) . "\n");
				}
				break;

            case 'success':
			default:
                $Class = 'success';
                $Message[2] = $Message[2] ? $Message[2] : __('SUCCESS') . ' ' . __('Report');
				if (!empty($LogFile) && isset($_SESSION['LogSeverity']) && $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d H:i:s') . ',' . $Message[2] . ',' . $_SESSION['UserID'] . ',' . strip_tags(trim($Message[0], ',')) . "\n");
				}
		}

		echo '<div class="Message ', $Class, ' noPrint">
					<span class="MessageCloseButton">&times;</span>
					<b>', $Message[2], '</b> : ', $Message[0], '
			</div>';
	}

	if (!empty($LogFile)) {
		fclose($LogFile);
	}
	echo '</div>';
}

echo '</section>'; // BodyDiv
echo '<footer class="noPrint">
		<a class="FooterLogo" href="https://www.weberp.org" target="_blank">
			<div class="logo logo-left">web</div><div class="logo logo-right"><i>ERP</i></div>
		</a>
		<div class="FooterVersion">webERP ', __('version'), ' ', $_SESSION['VersionNumber'], '+', $_SESSION['DBVersion'], '</div>
		<div class="FooterTime">', DisplayDateTime(), '</div>
	  </footer>'; // FooterDiv
echo '</body>';
echo '</html>';
