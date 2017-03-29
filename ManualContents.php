<?php
/* $Id: ManualContents.php 5450 2009-12-24 15:28:49Z icedlava $ */
/* Shows the local manual content if available, else shows the manual content in en-GB. */
/* This program is under the GNU General Public License, last version. */
/* This creative work is under the CC BY-NC-SA, later version. */

/*
This table of contents allows the choice to display one section or select multiple sections to format for print.
Selecting multiple sections is for printing.
The outline of the Table of Contents is contained in the 'ManualOutline.php' file that can be easily translated.
The individual topics in the manual are in straight html files that are called along with the header and foot from here.
Each function in KwaMoja can initialise a $ViewTopic and $Bookmark variable, prior to including the header.inc file.
This will display the specified topic and bookmark if it exists when the user clicks on the Manual link in the KwaMoja main menu.
In this way the help can be easily broken into sections for online context-sensitive help.
Comments beginning with Help Begin and Help End denote the beginning and end of a section that goes into the online help.
What section is named after Help Begin: and there can be multiple sections separated with a comma.
*/

// BEGIN: Procedure division ---------------------------------------------------
$Title = _('webERP Manual');
// Set the language to show the manual:
session_start();
$Language = $_SESSION['Language'];
if(isset($_GET['Language'])) {// Set an other language for manual.
	$Language = $_GET['Language'];
}
// Set the Cascading Style Sheet for the manual:
$ManualStyle = 'locale/' . $Language . '/Manual/style/manual.css';
if(!file_exists($ManualStyle)) {// If locale ccs not exist, use doc/Manual/style/manual.css. Each language can have its own css.
	$ManualStyle = 'doc/Manual/style/manual.css';
}
// Set the the outline of the webERP manual:
$ManualOutline = 'locale/' . $Language . '/Manual/ManualOutline.php';
if(!file_exists($ManualOutline)) {// If locale outline not exist, use doc/Manual/ManualOutline.php. Each language can have its own outline.
	$ManualOutline = 'doc/Manual/ManualOutline.php';
}


// Begin old code ==============================================================
ob_start();
$PathPrefix = '../../';

// Output the header part:
$ManualHeader = 'locale/' . $Language . '/Manual/ManualHeader.html';
if(file_exists($ManualHeader)) {// Use locale ManualHeader.html if exists. Each language can have its own page header.
	include($ManualHeader);
} else {// Default page header:
	echo '<!DOCTYPE html>
	<html>
	<head>
	  <title>', $Title, '</title>
	  <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	  <link rel="stylesheet" type="text/css" href="', $ManualStyle, '" />
	</head>
	<body lang="', str_replace('_', '-', substr($Language, 0, 5)), '">
		<div id="pagetitle">', $Title, '</div>
		<div class="right">
			<a id="top">&#160;</a><a class="minitext" href="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '">☜ ', _('Table of Contents'), '</a><br />
			<a class="minitext" href="#bottom">⬇ ', _('Go to Bottom'), '</a>
		</div>';
}

include($ManualOutline);
$_GET['Bookmark'] = isset($_GET['Bookmark']) ? $_GET['Bookmark'] : '';
$_GET['ViewTopic'] = isset($_GET['ViewTopic']) ? $_GET['ViewTopic'] : '';

//all sections of manual listed here

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint">';

if(((!isset($_POST['Submit'])) and (empty($_GET['ViewTopic']))) || ((isset($_POST['Submit'])) and (isset($_POST['SelectTableOfContents'])))) {
	// if not submittws then coming into manual to look at TOC
	// if SelectTableOfContents set then user wants it displayed
	if(!isset($_POST['Submit'])) {
		echo '<p>Click on a link to view a page, or<br />
			 Check boxes and click on Display Checked to view selected in one page
			 <input type="submit" name="Submit" value="Display Checked" />
			</p>';
	}
	echo "<ul>\n<li style=\"list-style-type:none;\">\n<h1>";
	if(!isset($_POST['Submit'])) {
		echo ' <input type="checkbox" name="SelectTableOfContents">';
	}
	echo _('Table of Contents'), "</h1></li>\n";
	$j = 0;
	foreach($TOC_Array['TableOfContents'] as $Title => $SubLinks) {
		$Name = 'Select' . $Title;
		echo "<ul>\n";
		if(!isset($_POST['Submit'])) {
			echo '<li class="toc" style="list-style-type:none;"><input type="checkbox" name="' . $Name . '">' . "\n";
			echo '<section style="margin-bottom:5px;">
					<div class="roundedOne">
						<input type="checkbox" value="None" id="roundedOne'.$j.'" name="' . $Name . '" />
						<label for="roundedOne'.$j.'"></label>';

			echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?ViewTopic=' . $Title . '" style="padding-left:1%";>' . $SubLinks[0] . '</a></li>' . "\n";

			echo '</div>
				</section>';
		} else {
			echo ' <li class="toc"><a href="#' . $Title . '">' . $SubLinks[0] . '</a></li>' . "\n";
		}
		if(count($SubLinks) > 1) {
			echo '<ul>' . "\n";
			foreach($SubLinks as $k => $SubName) {
				if($k == 0)
					continue;
				echo '<li>' . $SubName . '</li>' . "\n";
			}
			echo '</ul>' . "\n";
		}
		echo '</ul>' . "\n";
		++$j;
	}
	echo '</ul>' . "\n";
}
echo '</form>' . "\n";

if(!isset($_GET['ViewTopic'])) {
	$_GET['ViewTopic'] = '';
}

foreach($TOC_Array['TableOfContents'] as $Name => $FullName) {
	$PostName = 'Select' . $Name;
	if(($_GET['ViewTopic'] == $Name) or (isset($_POST[$PostName]))) {
		if($Name == 'APIFunctions') {
			$Name .= '.php';
		} else {
			$Name .= '.html';
		}
		$ManualPage = 'locale/' . $Language . '/Manual/Manual' . $Name;
		if(!file_exists($ManualPage)) {// If locale topic page not exist, use topic page in doc/Manual.
			$ManualPage = 'doc/Manual' . $Name;
		}
		echo '<div id="manualpage">';
		include($ManualPage);
		echo '</div>';
	}
}

// Output the footer part:
$ManualFooter = 'locale/' . $Language . '/Manual/ManualFooter.html';
if(file_exists($ManualFooter)) {// Use locale ManualHeader.html if exists. Each language can have its own page footer.
	include($ManualFooter);
} else {// Default page footer:
	echo '<div class="right">
			<a id="bottom">&#160;</a><a class="minitext" href="#top">⬆ ', _('Go to Top'), '</a>
		</div>
	</body>
	</html>';
}

ob_end_flush();
// End old code ================================================================


// END: Procedure division -----------------------------------------------------
?>