<?php
/* $Revision: 1.2 $ */
$PageSecurity = 3;
include('includes/session.inc');
$title = _('Customer Notes');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Id'])){
	$Id = $_GET['Id'];
} else if (isset($_POST['Id'])){
	$Id = $_POST['Id'];
}
if (isset($_POST['DebtorNo'])){
	$DebtorNo = $_POST['DebtorNo'];
} elseif (isset($_GET['DebtorNo'])){
	$DebtorNo = $_GET['DebtorNo'];
}
echo "<A HREF='" . $rootpath . '/SelectCustomer.php?' . SID .'&DebtorNo='.$DebtorNo."'>" . _('Back to Select Customer') . '</A><BR>';
if ( isset($_POST['submit']) ) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (!is_long((integer)$_POST['priority'])) {
		$InputError = 1;
		prnMsg( _('The contact priority must be an integer.'), 'error');
	} elseif (strlen($_POST['note']) >200) {
		$InputError = 1;
		prnMsg( _("The contact's notes must be two hundred characters or less long"), 'error');
	} elseif( trim($_POST['note']) == '' ) {
		$InputError = 1;
		prnMsg( _("The contact's notes may not be empty"), 'error');
	}
	
	if ($Id AND $InputError !=1) {
	
		$sql = "UPDATE custnotes SET 
				note='" . $_POST['note'] . "',
				date='" . $_POST['date'] . "',
				href='" . $_POST['href'] . "',
				priority='" . $_POST['priority'] . "'
			WHERE debtorno ='".$DebtorNo."' 
			AND noteid=".$Id;
		$msg = _('Customer Notes') . ' ' . $DebtorNo  . ' ' . _('has been updated');
	} elseif ($InputError !=1) {
			
		$sql = "INSERT INTO custnotes (debtorno,href,note,date,priority)
				VALUES (
					'" . $DebtorNo. "',
					'" . $_POST['href'] . "',
					'" . $_POST['note'] . "',
					'" . $_POST['date'] . "',
					'" . $_POST['priority'] . "'
					)";
		$msg = _('The contact notes record has been added');
	}
	
	if ($InputError !=1) {
		$result = DB_query($sql,$db);
				//echo '<br>'.$sql;

		echo '<BR>';
		prnMsg($msg, 'success');
		unset($Id);
		unset($_POST['note']);
		unset($_POST['noteid']);
	}
	} elseif ($_GET['delete']) {
//the link to delete a selected record was clicked instead of the submit button

// PREVENT DELETES IF DEPENDENT RECORDS IN 'SalesOrders'
	
	$sql="DELETE FROM custnotes WHERE noteid=".$Id."
			and debtorno='".$DebtorNo."'";
				$result = DB_query($sql,$db);
						//echo '<br>'.$sql;

				echo '<BR>';
				prnMsg( _('The contact note record has been deleted'), 'success');
				unset($Id);
				unset($_GET['delete']);
	
	}
	
if (!isset($Id)) {
	$SQLname='SELECT * from debtorsmaster where debtorno="'.$DebtorNo.'"';
	$Result = DB_query($SQLname,$db);
	$row = DB_fetch_array($Result);
	echo '<center>' . _('Notes for Customer: <b>') .$row['name'].'</b>';
	
	
	$sql = "SELECT * FROM custnotes where debtorno='".$DebtorNo."' ORDER BY date DESC";
	$result = DB_query($sql,$db);
			//echo '<br>'.$sql;

	echo '<CENTER><table border=1>';
	echo '<tr>
			<th>' . _('Date') . '</th>
			<th>' . _('Note') . '</th>
			<th>' . _('WWW') . '</th>
			<th>' . _('Priority') . '</th>';
		
	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k=1;
		}
		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td><a href="%sId=%s&DebtorNo=%s">'. _('Edit').' </td>
				<td><a href="%sId=%s&DebtorNo=%s&delete=1">'. _('Delete'). '</td></tr>',
				$myrow[4],
				$myrow[3],
				$myrow[2],
				$myrow[5],
				$_SERVER['PHP_SELF'] . "?" . SID, 
				$myrow[0], 
				$myrow[1], 
				$_SERVER['PHP_SELF'] . "?" . SID, 
				$myrow[0],
				$myrow[1]);
			
	}
	//END WHILE LIST LOOP
	echo '</CENTER></table>';
}
if (isset($Id)) {  ?>
	<Center><a href="<?php echo $_SERVER['PHP_SELF'] . '?' . SID .'&DebtorNo='.$DebtorNo;?>"><?=_('Review all notes for this Customer')?></a></Center>
<?php } ?>
<P>

<?php
if (!isset($_GET['delete'])) {

	echo '<form method="post" action="' . $_SERVER['PHP_SELF'] . '?' . SID . '&DebtorNo='.$DebtorNo.'">';
	
	if (isset($Id)) {
		//editing an existing

		$sql = "SELECT * FROM custnotes WHERE noteid=".$Id."
					and debtorno='".$DebtorNo."'";

		$result = DB_query($sql, $db);
				//echo '<br>'.$sql;

		$myrow = DB_fetch_array($result);
		
		$_POST['noteid'] = $myrow['noteid'];
		$_POST['note']	= $myrow['note'];
		$_POST['WWW']  = $myrow['href'];
		$_POST['date']  = $myrow['date'];
		$_POST['priority']  = $myrow['priority'];
		$_POST['debtorno']  = $myrow['debtorno'];
		echo '<input type=hidden name="Id" value='. $Id .'>';
		echo '<input type=hidden name="Con_ID" value=' . $_POST['noteid'] . '>';
		echo '<input type=hidden name="DebtorNo" value=' . $_POST['debtorno'] . '>';
		echo '<center><table><tr><td>'. _('Note ID').':</td><td>' . $_POST['noteid'] . '</td></tr>';
	} else {
		echo '<center><table>';
	}
	?>
	<tr><td><?php echo _('Contact Note');?>:</TD>
	<td><textarea name="note"><?php echo $_POST['note']; ?></textarea></td></tr>
	<tr><td><?php echo _('WWW');?>:</td>
	<td><input type="Text" name="href" value="<?php echo $_POST['href']; ?>" size=35 maxlength=100></td></tr>
	<tr><td><?php echo _('Date');?>:</td>
	<td><input type="Text" name="date" value="<?php echo $_POST['date']; ?>" size=10 maxlength=10></td></tr>
	<tr><td><?php echo _('Priority');?>:</TD>
	<td><input type="Text" name="priority" value="<?php echo $_POST['priority']; ?>" size=1 maxlength=3></td></td>
	</table></center>
	<center><input type="Submit" name="submit" value="<?php echo _('Enter Information');?>"></center>

	</form>
	
	<?php } //end if record deleted no point displaying form to add record 

include('includes/footer.inc');
?>
