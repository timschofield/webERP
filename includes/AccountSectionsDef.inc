<?php
$Sections = array();
$sql = 'SELECT sectionid, sectionname FROM accountsection ORDER by sectionid';
$SectionResult = DB_query($sql,$db);
while( $secrow = DB_fetch_array($SectionResult) ) {
	$Sections[$secrow['sectionid']] = $secrow['sectionname'];
}
DB_free_result($SectionResult); // no longer needed
?>