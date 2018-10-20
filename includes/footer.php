<?php
echo '<div id="MessageContainerFoot">';

if (isset($Messages) and count($Messages) > 0) {
	if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 0) {
		$LogFile = fopen($_SESSION['LogPath'] . '/weberp.log', 'a');
	}

	foreach ($Messages as $Message) {
		$Prefix = $Message[2];
		switch ($Message[1]) {
			case 'error':
				$Class = 'error';
				$Prefix = $Prefix ? $Prefix : _('ERROR') . ' ' . _('Report');
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
				break;
			case 'warn':
			case 'warning':
				$Class = 'warn';
				$Prefix = $Prefix ? $Prefix : _('WARNING') . ' ' . _('Report');
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
				break;
			case 'success':
				$Class = 'success';
				$Prefix = $Prefix ? $Prefix : _('SUCCESS') . ' ' . _('Report');
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 3) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
				break;
			case 'info':
			default:
				$Prefix = $Prefix ? $Prefix : _('INFORMATION') . ' ' . _('Message');
				$Class = 'info';
				if (isset($_SESSION['LogSeverity']) and $_SESSION['LogSeverity'] > 2) {
					fwrite($LogFile, date('Y-m-d h-m-s') . ',' . $Type . ',' . $_SESSION['UserID'] . ',' . trim($Msg, ',') . "\n");
				}
		}
		echo	'<div class="Message ', $Class, ' noprint">',
					'<span class="MessageCloseButton">&times;</span>',
					'<b>', $Prefix, '</b> : ', $Message[0],
				'</div>';
	}
}
echo '</div>'; // eof MessageContainer div
echo '</div>'; // BodyWrapDiv
echo '</div>'; // BodyDiv

if($_SESSION['ShortcutMenu']==1) { // if the short cut menus are allowed
	$BaseName = basename($_SERVER['PHP_SELF']);
	$ScriptName = $BaseName.'?'.$_SERVER['QUERY_STRING'];
	if ( $BaseName != 'index.php' AND !isset($_SESSION['Favourites'][$ScriptName])) {
		$ShowAdd = '<input type="submit" value="' . _('Add To Menu') . '" name="AddToMenu" />';
	} else {
		$ShowAdd = '';
	}
	if (isset($_SESSION['Favourites'][$ScriptName])) {
		$ShowDel = '<input type="submit" value="' . _('Remove From Menu') . '" name="DelFromMenu" />';
	} else {
		$ShowDel = '';
	}
}  else { //don't show the short cut menu options
	$ShowDel = '';
	$ShowAdd = '';
}

echo	'<div class="centre noprint">',
			'<form action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '" method="post">',
				'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
				'<input name="ScriptName" type="hidden" value="', htmlspecialchars($ScriptName,ENT_QUOTES,'UTF-8'), '" />',
				'<input name="Title" type="hidden" value="', $Title, '" />', $ShowAdd, $ShowDel,
			'</form>',
		'</div>',
		'<div id="FooterDiv">',
			'<div id="FooterWrapDiv">',
				'<div id="FooterLogoDiv">',
					'<img alt="webERP" src="', $RootPath, '/', $_SESSION['LogoFile'], '" title="webERP ', _('Copyright'), ' &copy; weberp.org - ', date('Y'), '" width="120" />',
				'</div>',
				'<div id="FooterTimeDiv">',
					DisplayDateTime(),
				'</div>',
				'<div id="FooterVersionDiv">',
					'webERP ', _('version'), ' ', $_SESSION['VersionNumber'], ' ', _('Copyright'), ' &copy; 2004 - ', Date('Y'), ' <a href="http://www.weberp.org/weberp/doc/Manual/ManualContributors.html" target="_blank">weberp.org</a>',
				'</div>',
			'</div>',//<div id="FooterWrapDiv">',
		'</div><!--END div id="FooterDiv"-->',
	'</div><!--div id="CanvasDiv"-->',
'</body>',
'</html>';

?>
