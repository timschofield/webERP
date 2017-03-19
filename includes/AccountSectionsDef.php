<?php
/* $Id: AccountSectionsDef.php 6941 2014-10-26 23:18:08Z daintree $*/
$Sections = array();
$sql = 'SELECT sectionid, sectionname FROM accountsection ORDER by sectionid';
$SectionResult = DB_query($sql);
while( $secrow = DB_fetch_array($SectionResult) ) {
	$Sections[$secrow['sectionid']] = $secrow['sectionname'];
}
DB_free_result($SectionResult); // no longer needed
?>
