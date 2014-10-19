<?php
/* $Id: Z_poAdmin.php 6810 2014-08-13 02:55:08Z rchacon $*/
/* Steve Kitchen */
/* Up front menu for language file maintenance */

//$PageSecurity = 15;

include ('includes/session.inc');
$Title = _('UTILITY PAGE') . ' ' . _('that helps maintain language files');// _('Maintain Language Files')
$ViewTopic = 'SpecialUtilities';// Filename in ManualContents.php's TOC.
$BookMark = 'Z_poAdmin';// Anchor's id in the manual's html document.
include('includes/header.inc');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme . 
		'/images/maintenance.png" title="' . 
		_('Maintain Language Files') . '" />' . ' ' . 
		_('Maintain Language Files') . '</p>';

/* Check if we have gettext - we're useless without it... */

if (!function_exists('gettext')){
	prnMsg (_('gettext is not installed on this system') . '. ' . _('You cannot use the language files without it'),'error');
	exit;
}

if (!is_writable('./locale/' . $_SESSION['Language'])) {
	prnMsg(_('You do not have write access to the required files please contact your system administrator'),'error');
} else {
	echo '<p><a href="' . $RootPath . '/Z_poRebuildDefault.php?' . SID . '">' .   _('Rebuild the System Default Language File') . '</a>';
	echo '<p><a href="' . $RootPath . '/Z_poAddLanguage.php?' . SID . '">' . _('Add a New Language to the System')  . '</a>';
	echo '<p><a href="' . $RootPath . '/Z_poEditLangHeader.php?' . SID . '">' .  _('Edit a Language File Header') . '</a>';
	echo '<p><a href="' . $RootPath . '/Z_poEditLangModule.php?' . SID . '">' .  _('Edit a Language File Module') . '</a>';
	echo '<p><a href="' . $RootPath . '/Z_poEditLangRemaining.php?' . SID . '">' .  _('Edit Remaining Strings For This Language') . '</a>';
	echo '<p><a href="' . $RootPath . '/locale/'.$_SESSION['Language'].'/LC_MESSAGES/messages.po' . SID . '">' .  _('Download messages.po file') . '</a>';
	echo '<p><a href="' . $RootPath . '/locale/'.$_SESSION['Language'].'/LC_MESSAGES/messages.mo' . SID . '">' .  _('Download messages.mo file') . '</a>';
}

include('includes/footer.inc');
?>
