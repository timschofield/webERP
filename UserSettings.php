<?php

/* $Id$*/

include('includes/session_views.inc');
$Title = _('User Settings');
include('includes/header_views.inc');

echo '<p class="page_title_text"><img src="'.$MainView->getStyleLink() .'/images/user.png" title="' .
	_('User Settings') . '" alt="" />' . ' ' . _('User Settings') . '</p>';

$PDFLanguages = array(_('Latin Western Languages - Times'),
					_('Eastern European Russian Japanese Korean Hebrew Arabic Thai'),
					_('Chinese'),
					_('Free Serif'));


if (isset($_POST['Modify'])) {
	// no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if ($_POST['DisplayRecordsMax'] <= 0){
		$InputError = 1;
		prnMsg(_('The Maximum Number of Records on Display entered must not be negative') . '. ' . _('0 will default to system setting'),'error');
	}

	//!!!for the demo only - enable this check so password is not changed
	if ($AllowDemoMode AND $_POST['Password'] != ''){
		$InputError = 1;
		prnMsg(_('Cannot change password in the demo or others would be locked out!'),'warn');
	}

 	$UpdatePassword = 'N';

	if ($_POST['PasswordCheck'] != ''){
		if (mb_strlen($_POST['Password'])<5){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		} elseif (mb_strstr($_POST['Password'],$_SESSION['UserID'])!= False){
			$InputError = 1;
			prnMsg(_('The password cannot contain the user id'),'error');
		}
		if ($_POST['Password'] != $_POST['PasswordCheck']){
			$InputError = 1;
			prnMsg(_('The password and password confirmation fields entered do not match'),'error');
		}else{
			$UpdatePassword = 'Y';
		}
	}


	if ($InputError != 1) {
		// no errors
		if ($UpdatePassword != 'Y'){
			$sql = "UPDATE www_users
					SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
						theme='" . $_POST['Theme'] . "',
						language='" . $_POST['Language'] . "',
						email='". $_POST['email'] ."',
						pdflanguage='" . $_POST['PDFLanguage'] . "'
					WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg =  _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

			prnMsg( _('The user settings have been updated') . '. ' . _('Be sure to remember your password for the next time you login'),'success');
		} else {
			$sql = "UPDATE www_users
				SET displayrecordsmax='" . $_POST['DisplayRecordsMax'] . "',
					theme='" . $_POST['Theme'] . "',
					language='" . $_POST['Language'] . "',
					email='". $_POST['email'] ."',
					pdflanguage='" . $_POST['PDFLanguage'] . "',
					password='" . CryptPass($_POST['Password']) . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg =  _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

			prnMsg(_('The user settings have been updated'),'success');
		}
	  // update the session variables to reflect user changes on-the-fly
		$_SESSION['DisplayRecordsMax'] = $_POST['DisplayRecordsMax'];
		$_SESSION['Theme'] = trim($_POST['Theme']); /*already set by session.inc but for completeness */
        $_SESSION['Style'] = trim($_POST['Style']);
		$Theme = $_SESSION['Style'];
		$_SESSION['Language'] = trim($_POST['Language']);
		$_SESSION['PDFLanguage'] = $_POST['PDFLanguage'];
		include ('includes/LanguageSetup.php'); // After last changes in LanguageSetup.php, is it required to update?
	}
}


$UserForm = $MainView->createForm();
$UserForm->setAction(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));
$UserForm->addHiddenControl('RealName',$_SESSION['UsersRealName']);
//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null,$dimensions = null)
$UserForm->addControl(1,-1,'static',_('User ID') . ':',array('text' => $_SESSION['UserID']),null,1);
$UserForm->addControl(2,-1,'static',_('User Name') . ':',array('text' => $_SESSION['UsersRealName']),null,1);

if (!isset($_POST['DisplayRecordsMax']) OR $_POST['DisplayRecordsMax']=='') {

  $_POST['DisplayRecordsMax'] = $_SESSION['DefaultDisplayRecordsMax'];

}
//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null,$dimensions = null)
$UserForm->addControl(3,1,'text',_('Maximum Number of Records to Display') . ':',array( 'value' => $_POST['DisplayRecordsMax'],
                                                                                        'name' => 'DisplayRecordsMax',
                                                                                        'size' => 3,
                                                                                        'maxlength' => 3,
                                                                                        'required' => true),'integer');
                                                                                        
$UserForm->addControl(4,2,'select', _('Language') . ':',array('name' => 'Language'));
if (!isset($_POST['Language'])){
	$_POST['Language']=$_SESSION['Language'];
}
//addControlOption($key,$text,$value,$isSelected = null,$id = null)
foreach ($LanguagesArray as $LanguageEntry => $LanguageName){
                                                                                //is this language selected?
    $UserForm->addControlOption(4,$LanguageName['LanguageName'],$LanguageEntry,((isset($_POST['Language']) AND $_POST['Language'] == $LanguageEntry) || (!isset($_POST['Language']) AND $LanguageEntry == $DefaultLanguage)));
}
//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null,$dimensions = null)
$UserForm->addControl(5,3,'select',_('Theme') . ':',array('name' => 'Theme', 'childselect' => 'StylesID'));
$UserForm->addControl(6,4,'select',_('Styles') . ':',array('name' => 'Style', 'id' => 'StylesID', 'hasparent' => true));
$allThemes = $MainView->getTemplates(true);
$styleOptions = array();
$i = 0;
foreach ($allThemes as $aTheme) {
    //addControlOption($key,$text,$value,$isSelected = null,$parentID = null,$id = null)
    $UserForm->addControlOption(5,$aTheme['themename'],$aTheme['themefolder'],($MainView->getTheme() == 'themes/' . $aTheme['themefolder']),null,$i);
    if (is_array($aTheme['styles'])) {
        foreach($aTheme['styles'] as $key => $style) {
            $UserForm->addControlOption(6,$style,$style,($MainView->getTheme() == 'themes/' . $aTheme['themefolder'] && $MainView->getStyle() == $style),$i);
        }
    }
    $i++;
}

if (!isset($_POST['PasswordCheck'])) {
	$_POST['PasswordCheck']='';
}
if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}

$controlsettings['name'] = 'Password';
$controlsettings['value'] = $_POST['Password'];
$controlsettings['pattern'] = '(?!^'.$_SESSION['UserID'].'$).{5,}';
$controlsettings['title'] = _('Must be more than 5 characters and cannot be as same as userid');
$controlsettings['placeholder'] = _('More than 5 characters');
$controlsettings['size'] ='20';

//addControl($key,$tabindex,$type,$caption = null,$settings = null,$htmlclass = null,$row = null,$order = null,$dimensions = null)
$UserForm->addControl(7,5,'password',_('New Password') . ':',$controlsettings);
$controlsettings['name'] = 'PasswordCheck';
$controlsettings['value'] = $_POST['PasswordCheck'];
$UserForm->addControl(8,6,'password',_('Confirm Password') . ':',$controlsettings);
$UserForm->addControl(9,-1,'content',null,array( 'align' => 'center',
                                                 'text' => '<i>' . _('If you leave the password boxes empty your password will not change') . '</i>'
                                                ));
$sql = "SELECT email from www_users WHERE userid = '" . $_SESSION['UserID'] . "'";
$result = DB_query($sql,$db);
$myrow = DB_fetch_array($result);
if(!isset($_POST['email'])){
	$_POST['email'] = $myrow['email'];
}
                                                
$UserForm->addControl(10,7,'email',   _('Email') . ':',array( 'name' => 'email',
                                                            'size' => 40,
                                                            'value' => $_POST['email']));
                                                            
if (!isset($_POST['PDFLanguage'])){
	$_POST['PDFLanguage']=$_SESSION['PDFLanguage'];
}

$UserForm->addControl(11,8,'select',_('PDF Language Support') . ':',array('name' => 'PDFLanguage'));

foreach($PDFLanguages as $i => $Lang){
    //addControlOption($key,$text,$value,$isSelected = null,$id = null)
    $UserForm->addControlOption(11,$Lang,$i,($_POST['PDFLanguage']==$i));
}

$UserForm->addControl(12,9,'submit',_('Modify'),array('name' => 'Modify',
                                                      'value' => _('Modify')));
$UserForm->display();
include('includes/footer_views.inc');
?>
