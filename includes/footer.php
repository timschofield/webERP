<?php

/* $Id: footer.php 7726 2017-01-13 23:02:10Z daintree $*/

echo '</div>'; // BodyWrapDiv
echo '</div>'; // BodyDiv

echo '<div id="FooterDiv">';
echo '<div id="FooterWrapDiv">';

echo '<div id="FooterLogoDiv">';
	echo '<img src="'. $RootPath . '/' . $_SESSION['LogoFile'] . '" width="120" alt="webERP" title="webERP ' . _('Copyright') . ' &copy; weberp.org - ' . date('Y') . '" />';
echo '</div>';

echo '<div id="FooterTimeDiv">';
	echo DisplayDateTime();
echo '</div>';

echo '<div id="FooterVersionDiv">';
	echo 'webERP ' . _('version') . ' ' . $_SESSION['VersionNumber'] . ' ' . _('Copyright') . ' &copy; 2004 - ' . Date('Y'). ' <a target="_blank" href="http://www.weberp.org/weberp/doc/Manual/ManualContributors.html">weberp.org</a>';
echo '</div>';

echo '</div>'; // FooterWrapDiv
echo '</div>'; // FooterDiv
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

echo '<div>
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '" method="post">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="ScriptName" value="' . $ScriptName . '" />
	<input type="hidden" name="Title" value="' . $Title . '" />
	' . $ShowAdd . $ShowDel . '
        
	
		</form>
	</div>
';
echo '</div>'; // Canvas
echo '</body>
	</html>';
?>
