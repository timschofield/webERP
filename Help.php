<?php
/* $Revision: 1.1 $ */

if (isset($_GET['Title'])){
	$HelpPageTitle = $_GET['Title'];
}elseif(isset($_POST['HelpPageTitle'])){
	$HelpPageTitle = $_POST['HelpPageTitle'];
}

$title = "Help On " . $HelpPageTitle;

$PageSecurity = 1;

include("includes/session.inc");
include("includes/header.inc");

if (isset($_GET['Page'])){
	$Page = $_GET['Page'];
} elseif (isset($_POST['Page'])){
	$Page = $_POST['Page'];
}

if (isset($_GET['Title'])){
	$HelpPageTitle = $_GET['Title'];
} elseif (isset($_POST['HelpPageTitle'])){
	$HelpPageTitle = $_POST['HelpPageTitle'];
}


if ($_POST['submit']) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (strlen($_POST['Narrative']) < 3) {
		$InputError = 1;
		echo "<BR>The narrative must be typo its less than two characters long! <BR>It was ignored.";
	}

	if ($InputError !=1 AND isset($_POST['HelpID'])) {

		$sql = "UPDATE Help SET Narrative = '" . $_POST['Narrative'] . "' WHERE HelpID =" . $_POST['HelpID'];
		$msg = "The help record has been updated.";
	} elseif ($InputError !=1) {

	/*Must be submitting new entries in the help narrative addition form */

		$sql = "INSERT INTO Help (PageID, Narrative) VALUES (" . $_POST['PageID'] . ", '" . $_POST['Narrative'] . "')";
		$msg = "The new help narrative has been added";
	}
	//run the SQL from either of the above possibilites
	$result = DB_query($sql,$db);
	echo "<BR>$msg";

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button


	$sql="DELETE FROM Help WHERE ID=" . $_GET['HelpID'];

	$ErrMsg = "The help narrative could not be deleted because";
	$DbgMsg = "<BR>The following SQL was used:";

	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

	echo "<BR>The selected help narrative has been deleted <p>";
	
}

if (!isset($Page)){ /*the help page was called without specifying the page */
	echo "<BR>The help page must be called from a link on the page on which help is required.";
}

/*Display the help */

/*First off retrieve the overview of the pages function from the Scripts table */


$sql = "SELECT PageID, PageDescription FROM Scripts WHERE FileName='" . $Page ."'";
$result = DB_query($sql,$db);

echo "<CENTER><table border=1>\n";
echo "<tr><td class='tableheader'>Overview of " . $HelpPageTitle ."</td></tr>\n";

$myrow = DB_fetch_row($result);

echo "<TR><TD>" . $myrow[1] . "</TD></TR>";

echo "<TR><TD><HR></TD></TR>";

$PageID = $myrow[0];

/*Now get the help records recorded for PageID */

$sql = "SELECT Narrative, ID FROM Help WHERE PageID=" . $PageID . " ORDER BY ID";
$result = DB_query($sql,$db);

while ($myrow = DB_fetch_row($result)) {


	printf("<tr><td>%s</td><td><a href='%sHelpID=%s&Page=%s&Title=%s'>Edit</td><td><a href='%sHelpID=%s&delete=yes&Page=%s&Title=%s'>DELETE</td></tr>", $myrow[0], $_SERVER['PHP_SELF'] . "?" . SID, $myrow[1], $Page, $HelpPageTitle, $_SERVER['PHP_SELF'] . "?" . SID, $myrow[1], $Page, $HelpPageTitle);

//END WHILE LIST LOOP
}

echo "</table></CENTER>";

echo "<FORM METHOD='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";

if (isset($_GET['HelpID']) AND ! isset($_GET['delete'])) {
	//editing an existing sales type

	$sql = "SELECT Narrative FROM Help WHERE HelpID=" . $_GET['HelpID'];

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_row($result);

	echo "<INPUT TYPE=HIDDEN NAME='HelpID' VALUE=" . $HelpID . ">";

	$_POST['Narrative'] = $myrow[0];

}

echo "<INPUT TYPE=HIDDEN NAME='PageID' VALUE=" . $PageID . ">";
echo "<INPUT TYPE=HIDDEN NAME='Page' VALUE='" . $Page . "'>";
echo "<INPUT TYPE=HIDDEN NAME='HelpPageTitle' VALUE='" . $HelpPageTitle . "'>";

echo "<CENTER><TABLE><TR><TD>Narrative:</TD></TR>";

echo "<TR><TD><textarea name='Narrative' cols=100% rows=3>" . $_POST['Narrative'] . "</textarea></TD></TR>";

echo "</TABLE>";

echo "<CENTER><input type='Submit' name='submit' value='Enter Information'>";

echo "</FORM>";


include("includes/footer.inc");
?>
