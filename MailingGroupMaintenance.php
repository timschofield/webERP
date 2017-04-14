<?php
include('includes/session.php');
$Title = _('Mailing Group Maintenance');
include('includes/header.php');
$Header = '<p class= "page_title_text"><img src="'. $RootPath.'/css/'.$Theme.'/images/group_add.png" alt="" />' .  $Title . '</p>';
echo $Header;
//show the mail group existed only when user request this page first
if(!isset($_POST['Clean']) and !isset($_GET['Delete']) and !isset($_GET['Edit']) and !isset($_GET['Add']) and !isset($_GET['Remove'])){
	GetMailGroup();
}
//validate the input 
if(isset($_POST['Enter'])){ //user has input a new value
	$InputError = 0;
	if(!empty($_POST['MailGroup']) and mb_strlen(trim($_POST['MailGroup']))<=100 and !ContainsIllegalCharacters($_POST['MailGroup'])){
		$MailGroup = strtolower(trim($_POST['MailGroup']));
	}else{
		$InputError = 1;
		prnMsg(_('The Mail Group should be less than 100 characters and cannot contain illegal characters and cannot be null'),'error');
		exit;
		include('includes/footer.php');
	}
	if($InputError == 0){
		$sql = "INSERT INTO mailgroups (groupname) VALUES ('".$MailGroup."')";
		$ErrMsg = _('Failed to add new mail group');
		$result = DB_query($sql,$ErrMsg);
		GetMailGroup();

	}

}//end of handling new mail group input
//Add the new users to the mail group
if(isset($_GET['Add']) and isset($_GET['UserId'])){
	if(isset($_GET['UserId']) and mb_strlen($_GET['UserId'])<21 and !ContainsIllegalCharacters($_GET['UserId'])){
		$UserId = $_GET['UserId'];
	}else{
		prnMsg(_('The User Id should be set and must be less than 21 and cannot contains illegal characters'),'error');
		include('includes/footer.php');
		exit;
	}
	if(isset($_GET['GroupId']) and is_numeric($_GET['GroupId'])){
		$GroupId = (int)$_GET['GroupId'];
	}else{
		prnMsg(_('The Group Id must be integer'),'error');
		include('includes/footer.php');
		exit;
	}
	if(!empty($_GET['GroupName']) and mb_strlen($_GET['GroupName'])<=100 and !ContainsIllegalCharacters($_GET['GroupName'])){
		$GroupName = trim($_GET['GroupName']);

	}else{
		prnMsg(_('The Group name should be set and must be less than 100 characters and cannot contains illegal characters'),'error');
		include('includes/footer.php');
		exit;
	}
	$sql = "INSERT INTO mailgroupdetails (groupname, userid) VALUES ('".$GroupName."',
									'".$UserId."')";
	$ErrMsg = _('Failed to add users to mail group');
	$result = DB_query($sql,$ErrMsg);
	GetUsers($GroupId, $GroupName);
}

//User try to delete one of the record
if(isset($_GET['Delete'])){
	if(is_numeric($_GET['Id'])){
		$id = (int)$_GET['Id'];
		$sql = "DELETE FROM mailgroups WHERE id = '".$id."'";
		$ErrMsg = _('Failed to delete the mail group which id is '.$id);
		$result = DB_query($sql,$ErrMsg);
		GetMailGroup();
	}else{
		prnMsg(_('The group id must be numeric'),'error');
		include('includes/footer.php');
		exit;
		
	}

}

//User try to Edit the details of the mail groups
if(isset($_GET['Edit'])){
//First Get mailing list from database;
	if(isset($_GET['GroupId']) and is_numeric($_GET['GroupId'])){
		$GroupId = (int) $_GET['GroupId'];
		if(isset($_GET['GroupName']) and mb_strlen($_GET['GroupName'])<=100 and !ContainsIllegalCharacters($_GET['GroupName'])){
			$GroupName = trim($_GET['GroupName']);
		}else{
			prnMsg(_('The Group Name should be less than 100 and cannot contains illegal characters'),'error');
			include('includes/footer.php');
			exit;
		}
		
	}else{
		prnMsg(_('The page must be called with a group id'),'error');
		include('includes/footer.php');
		exit;
	}
	GetUsers($GroupId,$GroupName);
	include('includes/footer.php');	
		

}
//Users remove one user from the group
if(isset($_GET['Remove'])){
	if(!empty($_GET['GroupName']) and mb_strlen($_GET['GroupName'])<=100 and !ContainsIllegalCharacters($_GET['GroupName'])){
		$GroupName = trim($_GET['GroupName']);
	}else{
			prnMsg(_('The Group Name should be less than 100 and cannot contains illegal characters'),'error');
			include('includes/footer.php');
			exit;

	}
	if(isset($_GET['UserId']) and mb_strlen($_GET['UserId'])<21 and !ContainsIllegalCharacters($_GET['UserId'])){
		$UserId = $_GET['UserId'];
	}else{
		prnMsg(_('The User Id should be set and must be less than 21 and cannot contains illegal characters'),'error');
		include('includes/footer.php');
		exit;
	}

	if(isset($_GET['GroupId']) and is_numeric($_GET['GroupId'])){
		$GroupId = (int) $_GET['GroupId'];
		if(isset($_GET['GroupName']) and mb_strlen($_GET['GroupName'])<=100 and !ContainsIllegalCharacters($_GET['GroupName'])){
			$GroupName = trim($_GET['GroupName']);
		}else{
			prnMsg(_('The Group Name should be less than 100 and cannot contains illegal characters'),'error');
			include('includes/footer.php');
			exit;
		}

	}
	$sql = "DELETE FROM mailgroupdetails WHERE userid = '".$UserId."' AND groupname = '".$GroupName."'";
	$ErrMsg = 'Failed to delete the userid '.$UserId.' from group '.$GroupName;
	$result = DB_query($sql,$ErrMsg);
	GetUsers($GroupId,$GroupName);


}
if(!isset($_GET['Edit'])){//display the input form
?>
	<form id="MailGroups" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'); ?>" method="post">
		<input type="hidden" name="FormID" value="<?php echo $_SESSION['FormID']; ?>" />
		<label for="MailGroup"><?php echo _('Mail Group'); ?></label>
			<input type="text" required="required" autofocus="autofocus" name="MailGroup" maxlength="100" size="20" />
			<input type="hidden" name="Clean" value="1" />
			<input type="submit" name="Enter" value="<?php echo _('Submit'); ?>" />
	</form>


<?php 

	include('includes/footer.php');
}
?>








<?php
function GetMailGroup () {
global $db;
//GET the mailing group data if there are any
$sql = "SELECT groupname, id FROM mailgroups ORDER BY groupname";
$ErrMsg = _('Failed to retrieve mail groups information');
$result = DB_query($sql,$ErrMsg);
if(DB_num_rows($result) != 0){
?>
	<table class="selection">
		<tr><th><?php echo _('Mail Group'); ?></th></tr>
<?php
		while($myrow = DB_fetch_array($result)){
?>
			<tr><td><?php echo $myrow['groupname']; ?></td>
	
				<td><?php echo '<a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?GroupId='.$myrow['id'].'&amp;Edit=1&amp;GroupName='.$myrow['groupname'].'" >' .  _('Edit') . '</a>'; ?></td>
				<td><?php echo '<a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?Id='.$myrow['id'].'&amp;Delete=1" onclick="return confirm(\'' ._('Are you sure you wish to delete this group?').'\');">' . _('Delete') . '</a>'; ?></td>
			</tr>

<?php
		}
?>
	</table>
<?php
}
}

function GetUsers ($GroupId,$GroupName) {
	global $db;
	$sql = "SELECT userid FROM mailgroups INNER JOIN mailgroupdetails ON mailgroups.groupname=mailgroupdetails.groupname WHERE mailgroups.id = '".$GroupId."'";
	$ErrMsg = _('Failed to retrieve userid');
	$result = DB_query($sql,$ErrMsg);
	
		$UsersAssigned = array();
	if(DB_num_rows($result) != 0){
		$i = 0; 
		while($myrow = DB_fetch_array($result)){
			$UsersAssigned[$i] = $myrow['userid'];
			$i++;
		}
	}
		
	$sql = "SELECT userid, realname, email FROM www_users ORDER BY realname";
	$ErrMsg = _('Failed to retrieve user information');
	$result = DB_query($sql,$ErrMsg);
	if(DB_num_rows($result) != 0){
	
?>
	<div class="centre"><?php echo _('Current Mail Group').' : '.$GroupName; ?></div>
	<div class="centre"><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'); ?>"><?php echo _('View All Groups'); ?></a></div>

<table class="selection">
	<tr>
		<th colspan="3"><?php echo _('Assigned Users'); ?></th>
		<th colspan="3"><?php echo _('Available Users'); ?></th>
	</tr>
<?php
	$k = 0;
	while($myrow=DB_fetch_array($result)){
		if($k==0){
?>
			<tr class="EvenTableRows">
<?php
			$k = 1;
		}else{
?>
			<tr class="OddTableRows">
<?php
			$k = 0;
		}
	
		if(in_array($myrow['userid'],$UsersAssigned)){
?>
			<td><?php echo $myrow['userid']; ?></td>
			<td><?php echo $myrow['realname']; ?></td>
			<td><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?UserId='.$myrow['userid'].'&amp;GroupName='.$GroupName.'&amp;Remove=1&amp;GroupId='.$GroupId . '" onclick="return confirm(\'' . _('Are you sure you wish to remove this user from this mail group?') . '\');'; ?>"><?php echo _('Remove'); ?></a></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
<?php
		}else{
?>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><?php echo $myrow['userid']; ?></td>
			<td><?php echo $myrow['realname']; ?></td>
			<td><a href="<?php echo htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8').'?UserId='.$myrow['userid'].'&amp;Add=1&amp;GroupName='.$GroupName.'&amp;GroupId='.$GroupId; ?>"><?php echo _('Add'); ?></a></td>
<?php
		}

?>
		</tr>


<?php
	}
?>
</table>
<?php

	}else{
		prnMsg(_('There are no user set up, please set up user first'),'error');
		include('includes/footer.php');
		exit;
	}
}
?>
