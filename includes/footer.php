<?php

/* $Id: footer.php 7726 2017-01-13 23:02:10Z daintree $*/

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

echo '</div>'; // BodyWrapDiv
echo '</div>'; // BodyDiv

echo '<div id="FooterDiv">';
echo '<div id="FooterWrapDiv">';

echo '<div id="FooterLogoDiv">';
	echo '<img src="'. $RootPath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="webERP" title="webERP ' . _('Copyright') . ' &copy; PT. Angin Dingin Utara - ' . date('Y') . '" />';
echo '</div>';

echo '<div id="FooterTimeDiv">';
	echo DisplayDateTime();
echo '</div>';

echo '<div id="FooterVersionDiv">';
	echo 'webERP ' . _('version') . ' ' . $_SESSION['VersionNumber'];
echo '</div>';

echo '</div>'; // FooterWrapDiv
echo '</div>'; // FooterDiv
echo '</div>'; // Canvas

echo '</body>
	</html>';
	


?>
