<?php
/* KL RICARD Log the script we run so we can optimize CPU time*/
if (isset($Title)) {
	$TitleScriptRunning = $Title;
}else{
	$TitleScriptRunning = "Undefined title";
}

$time = explode(' ', $_SESSION['ScriptStartTime']);
$begintime = $time[1] + $time[0];

$time = microtime();
$time = explode(" ", $time);
$endtime = $time[1] + $time[0];
$runningtime = round(($endtime - $begintime),5);

$AuditSQL = "INSERT INTO auditscripts (executiondate,
					secondsrunning,
					userid,
					scripttitle)
			VALUES('" . Date('Y-m-d H:i:s') . "',
				'" . $runningtime . "',
				'" . trim($_SESSION['UserID']) . "',
				'" . DB_escape_string($TitleScriptRunning) . "')";
$Result = DB_query($AuditSQL);

/* END of logging the script */ 

echo '<div id="mask">
		<div id="dialog"></div>
	</div>';

if (isset($Messages) and count($Messages) > 0) {
	foreach ($Messages as $Message) {
		switch ($Message[1]) {
			case 'error':
				$Class = 'error';
				$Message[2] = $Message[2] ? $Message[2] : _('ERROR') . ' ' . _('Report');
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
			break;
			case 'warn':
			case 'warning':
				$Class = 'warn';
				$Message[2] = $Message[2] ? $Message[2] : _('WARNING') . ' ' . _('Report');
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
			break;
			case 'success':
				$Class = 'success';
				$Message[2] = $Message[2] ? $Message[2] : _('SUCCESS') . ' ' . _('Report');
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
			break;
			case 'info':
			default:
				$Message[2] = $Message[2] ? $Message[2] : _('INFORMATION') . ' ' . _('Message');
				$Class = 'info';
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 2) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
		}
		echo '<div id="MessageContainerFoot">
				<div class="Message ', $Class, ' noPrint">
					<span class="MessageCloseButton">&times;</span>
					<b>', $Message[2], '</b> : ', $Message[0], '
				</div>
			</div>';
	}
}

echo '</section>'; // BodyDiv
echo '<footer class="noPrint">
		<a class="FooterLogo">
			<img src="', $RootPath, '/', $_SESSION['LogoFile'], '" width="120" alt="webERP" title="webERP ' . ' &copy; PT. Angin Dingin Utara - ' . date('Y') . '" />
		</a>
		<div class="FooterVersion">webERP ', _('version'), ' ', $_SESSION['VersionNumber'], '-' , _('PHP'), ' ' , phpversion() ,'</div>
		<div class="FooterTime">', DisplayDateTime(), '</div>
	</footer>'; // FooterDiv
echo '</body>';
echo '</html>';

?>
