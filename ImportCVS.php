<?php

require("globalvar.php");
    if (isset($SessionSavePath)){
	session_save_path($SessionSavePath);
    }
session_start();
$PageSecurity= 7;
$title = "Import WebERP Items";
require("config.php");
require('includes/session.inc');
require("includes/header.inc");
require_once("includes/archive.php");


function is_selected($x){
	global $_POST;
	
	if($_POST['imported'] == $x) print("selected ");

}

function is_disabled(){
	global $_POST;
	
	if($_POST['migrate'] == 'yes'){
		print("disabled=true 	
		");	
	}
}

function is_checked($x){
	global $_POST;
	if($_POST['migrate'] == $x){
		print(" checked='true' ");
	}elseif(!isset($_POST['migrate']) && $x=='yes'){
		print(" checked='true' ");	
	}

}

function is_overwrite($x){
	global $_POST;
	if($_POST['Overwrite'] == $x){
		print(" checked='true' ");
	}

}

function imported_val(){
	global $_POST;
	
	if(!isset($_POST['imported'])) print (" value='choice not made' ");
	else print (" value='".$_POST['imported']."' ");
}

function check_migrate(){
	global $_POST;
	print("<script type='text/javascript'>");
	if($_POST['migrate']=='yes'){
		
		print("document.getElementById('combo_box').style.display='none';document.getElementById('combo_box').style.visibility='hidden';document.getElementById('combo_box2').style.display='none';document.getElementById('combo_box2').style.visibility='hidden';");
		
	}elseif($_POST['migrate']=='no'){
		print("document.getElementById('combo_box').style.display='block';document.getElementById('combo_box').style.visibility='visible';document.getElementById('combo_box2').style.display='block';document.getElementById('combo_box2').style.visibility='visible';");
	}
	
	print("</script>");
}
// now start coding\
// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
// Dummy Part till we are already in system
// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

//$_SESSION['SelectedCompany']="/home/testCompany";

// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
include("includes/CSVclasses.php");
?>
<br />


<form action="<?php print $PHP_SELF ?>" method="POST" enctype="multipart/form-data" >


<table align="CENTER" width="450" align="center">

		<tr><td align=left valign=top><b>Import Whole System:</b>
		</td><td>
		<small>If you are restoring your system from backup, please use the "Import whole System" feature.</small><br />
		<input type=Radio onClick="javascript:document.getElementById('imported_val').value='Weberp_system';document.getElementById('imported').disabled='true';document.getElementById('combo_box').style.display='none';document.getElementById('combo_box').style.visibility='hidden';document.getElementById('combo_box2').style.display='none';document.getElementById('combo_box2').style.visibility='hidden';" name='migrate' <?php is_checked('yes'); ?> value='yes'/> Yes
		&nbsp; <input type=Radio onClick="javascript:document.getElementById('imported').disabled='';document.getElementById('combo_box').style.display='block';document.getElementById('combo_box').style.visibility='visible';document.getElementById('combo_box2').style.display='block';document.getElementById('combo_box2').style.visibility='visible';" name='migrate' <?php is_checked('no'); ?> value='no'/> No
	
	 </td>
</tr>
<tr>
         <td align=left width=30%><div name='combo_box2' id='combo_box2'><b>Import Item:</b></div></td>
         <td align=left>
		 <div id='combo_box' name='combo_box'>
		 <input type=hidden name="imported_val" id="imported_val" <?php imported_val(); ?>/>
        <select name="imported" id="imported" <?php is_disabled(); ?> onChange="javascript:document.getElementById('imported_val').value=document.getElementById('imported').value;">
        <option value="choice not made" <?php is_selected("choice not made"); ?>>Please Select</option>
        <!-- <option value="Weberp_system" >My WebERP+ System</option> -->
		<option value="Banking" <?php is_selected("Banking"); ?>>Banking</option>
		<option value="Customers" <?php is_selected("Customers"); ?>>Customers</option>
		<option value="EDI" <?php is_selected("EDI"); ?>>EDI</option>
        <option value="GL" <?php is_selected("GL"); ?>>General Ledger</option>
        <option value="Manufacturing" <?php is_selected("Manufacturing"); ?>>Manufacturing</option>
		<option value="Purchasing" <?php is_selected("Purchasing"); ?>>Purchasing</option>
		<option value="Sales" <?php is_selected("Sales"); ?>>Sales</option>
		<option value="Shipping" <?php is_selected("Shipping"); ?>>Shipping</option>
		<option value="Stocks" <?php is_selected("Stocks"); ?>>Stocks</option>
		<option value="Suppliers" <?php is_selected("Suppliers"); ?>>Suppliers</option>
		<option value="Tax" <?php is_selected("Tax"); ?>>Tax Details</option>
		<option value="Users" <?php is_selected("Users"); ?>>Users</option>		
		
        </select>
		</div>
		</td></tr>
	<tr>
         <td align=left><b>Zip File:</b></td>
         <td align=left>
 <input type="hidden" name="MAX_FILE_SIZE" value="5242880" />
 <input name="userfile" type="file" />


	 </td>
</tr>
	

<tr>
		<td align=left valign=top><b>Overwrite Exisiting Entries:</b>
		</td>
		<td>
		<input type=radio name=Overwrite id=Overwrite value='Y' <?php is_overwrite('Y'); ?> /> Yes &nbsp; 
		<input type=radio name=Overwrite id=Overwrite value='' <?php is_overwrite(''); ?> /> No
		</td>
		</tr>
<tr>
         <td colspan=2 align=center>
	 <input type="submit" value="Import" name="submit" />
	 </td>
</tr>
</table>
</form>
<br /><br />	
<?php

check_migrate();

$uploaddir = $CompaniesFolder.$_SESSION['SelectedCompany'].'/exports/uploads/';
@mkdir($uploaddir);
@mkdir($uploaddir."tmp/");
$tmpfilename = "Tmp.zip";
//$tmpfilename = $_FILES['userfile']['name'];
$uploadfile = $uploaddir . basename(ucfirst($tmpfilename));
// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

//print $_FILES['userfile']['tmp_name']."<br>";
//print $_FILES['userfile']['name']."<br>";
//print_r($_FILES);
// ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

if(isset($_POST['imported_val']) ){
	// ok go on

	if(move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)){
		@chmod($uploadfile,0777);
		if($_POST['imported_val']!="choice not made" || $_POST['migrate']=='yes'){
		        
			if($_POST['migrate']=='yes')$_POST['imported_val']='Weberp_system';
			$test = new CSVImport($uploaddir,$_POST['imported_val'],$db);
			$test->EnableOverwrite($_POST['Overwrite']);
		        //print "<hr>";
			if($test->IsTar($tmpfilename)){
			        if($test->Import(ucfirst($tmpfilename))){
						print("<table width=300 align=center cellspacing=0 ><tr><td align=center bgcolor=white ><b>".$_POST['imported_val']."</b> item imported successfully.</td></tr></table><br />");
		//				print("<script type='text/javascript'>alert('".$_POST['imported_val']." item imported successfully.');</script>");
						$test->PrintReports();
					}
			}else{
				print("<center><font color=red >Please upload a valid Zip file (*.zip ).</font></center><br />");
			}
		
		}else{
			print"<center><font color=red >Please select an item to import.</font></center><br />";
		}
	}
	else{

		if($_FILES['userfile']['error'] == 2) print"<center><font color=red >Maximum Zip file size is 5 MB.</font></center><br />";
		elseif($_POST['imported_val']=='choice not made')print"<center><font color=red >Please select an item to import.</font></center><br />";	
		else print"<center><font color=red >Please specify a Zip file to upload.</font></center><br />";
	}

}else{
	print("<script type='text/javascript' >");
	print("document.getElementById('imported_val').value='Weberp_system';document.getElementById('imported').disabled='true';document.getElementById('combo_box').style.display='none';document.getElementById('combo_box').style.visibility='hidden';document.getElementById('combo_box2').style.display='none';document.getElementById('combo_box2').style.visibility='hidden';");
	print("</script>");

}

	
include('includes/footer.inc');
?>

