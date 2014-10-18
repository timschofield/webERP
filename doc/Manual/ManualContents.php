<?php
/* $Id: ManualContents.php 5450 2009-12-24 15:28:49Z icedlava $ */
/* This table of contents allows the choice to display one section or select multiple sections to format for print.
     Selecting multiple sections is for printing

     The outline of the Table of Contents is contained in the 'ManualOutline.php' file that can be easily translated.

     The individual topics in the manual are in straight html files that are called along with the header and foot from here.

     Each function in webERP can initialise a $ViewTopic and $Bookmark variable, prior to including the header.inc file.
     This will display the specified topic and bookmark if it exists when the user clicks on the Manual link in the webERP main menu.
     In this way the help can be easily broken into sections for online context-sensitive help.
	 Comments beginning with Help Begin and Help End denote the beginning and end of a section that goes into the online help.
	 What section is named after Help Begin: and there can be multiple sections separated with a comma.
*/

ob_start();
$PathPrefix='../../';

//include($PathPrefix.'includes/session.inc');
include('ManualHeader.html');
include('ManualOutline.php');
$_GET['Bookmark'] = isset($_GET['Bookmark'])?$_GET['Bookmark']:'';
$_GET['ViewTopic'] = isset($_GET['ViewTopic'])?$_GET['ViewTopic']:'';

//all sections of manual listed here

echo'  <form action="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'" method="post">';
//echo '  <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (((!isset($_POST['Submit'])) AND (empty($_GET['ViewTopic']))) ||
     ((isset($_POST['Submit'])) AND(isset($_POST['SelectTableOfContents'])))) {
	// if not submittws then coming into manual to look at TOC
	// if SelectTableOfContents set then user wants it displayed
	if (!isset($_POST['Submit'])) {
		echo '<p>Click on a link to view a page, or<br />
			 Check boxes and click on Display Checked to view selected in one page
			 <input type="submit" name="Submit" value="Display Checked" />
			</p>';
	}
	echo "<ul>\n<li style=\"list-style-type:none;\">\n<h1>";
	if (!isset($_POST['Submit'])) {
	   echo ' <input type="checkbox" name="SelectTableOfContents">';
	}
	echo "Table of Contents</h1></li>\n";
	foreach ($TOC_Array['TableOfContents'] as $Title => $SubLinks) {

	   $Name = 'Select' . $Title;
	   echo "<ul>\n";
	   if (!isset($_POST['Submit'])) {
		 echo '<li class="toc" style="list-style-type:none;"><input type="checkbox" name="'.$Name.'">'."\n";
		 echo '<a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?ViewTopic='.$Title.'">'.$SubLinks[0].'</a></li>'."\n";
	   } else {
		 echo' <li class="toc"><a href="#'.$Title.'">'.$SubLinks[0].'</a></li>'."\n";
	   }
	   if (count($SubLinks)>1) {
		  echo'<ul>'."\n";
		  foreach ($SubLinks as $k=>$SubName) {
			if ($k == 0) continue;
			echo '<li>'.$SubName.'</li>'."\n";
		  }
			echo '</ul>'."\n";
		}
		echo '</ul>'."\n";
	}
	echo '</ul>'."\n";
}
echo '</form>'."\n";

if (!isset($_GET['ViewTopic'])){
	$_GET['ViewTopic'] = '';
}

foreach ($TOC_Array['TableOfContents'] as $Name=>$FullName){
	$PostName = 'Select' . $Name;
	if (($_GET['ViewTopic'] == $Name)  OR (isset($_POST[$PostName]))){

		if ($Name=='APIFunctions') {
			$ManualPage = 'Manual' . $Name . '.php';
		} else {
			$ManualPage = 'Manual' . $Name . '.html';
		}

		if (file_exists($ManualPage)) {
		  include($ManualPage);
		}
	}
}

include('ManualFooter.html');
ob_end_flush();