<?php

$PageSecurity= 15;
require('includes/session.inc');
$title = _('Export WebERP items');
include('includes/header.inc');
require_once('includes/archive.php');


function is_selected($x){
	global $_POST;
	
	if($_POST['exported'] == $x){
		print(_('selected') . ' ');
	}

}

function is_disabled(){
	global $_POST;
	
	if($_POST['migrate'] == 'yes'){
		//print(" disabled=true ");
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

function exported_val(){
	global $_POST;
	
	if(!isset($_POST['exported'])) {
		print (" value=" . _('choice not made') . " ");
	} else {
		print (" value='".$_POST['exported']."' ");
	}
	
	if($_POST['exported']=='Weberp_system'){
		$_POST['exported_val']='Weberp_system';
	}
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

include('includes/CSVclasses.php');


echo '<BR>
	<FORM ACTION="' .  $PHP_SELF . '" method="POST" enctype="application/x-www-form-urlencoded" >';

echo '<TABLE ALIGN="CENTER" WIDTH="450" ALIGN="CENTER">
		<TR><TD ALIGN=LEFT VALIGN=TOP><B>' . _('Export Whole System') . ':</b></TD>
		<TD><small>' . _('If you are performing a system backup, please use the') . ' "' . _('Export whole System') . '" ' . _('feature.') . '</SMALL><BR />';
?>
		<INPUT TYPE=RADIO onClick="javascript:document.getElementById('exported_val').value='Weberp_system';document.getElementById('exported').disabled='true';document.getElementById('combo_box').style.display='none';document.getElementById('combo_box').style.visibility='hidden';document.getElementById('combo_box2').style.display='none';document.getElementById('combo_box2').style.visibility='hidden';" name='migrate' id='migrate' value='yes' <?php is_checked('yes'); ?> /> Yes
		&nbsp; <input type=Radio onClick="javascript:document.getElementById('exported').value='choice not made';document.getElementById('exported').disabled='';document.getElementById('combo_box').style.display='block';document.getElementById('combo_box').style.visibility='visible';document.getElementById('combo_box2').style.display='block';document.getElementById('combo_box2').style.visibility='visible';document.getElementById('migrate').value='no'	;" name='migrate' id='migrate' value='no' <?php is_checked('no'); ?>   /> No

	 </td>
</tr>
<tr>
         <td align=left width=30% ><div name='combo_box2' id='combo_box2' ><b><?php echo _('Export Item'); ?>:</b></div></td>
         <td align=left>
         <div name='combo_box' id='combo_box' >
		 <input type=hidden name="exported_val" id="exported_val" <?php exported_val(); ?>/>
        <select name="exported" id="exported" onChange="javascript:document.getElementById('exported_val').value=document.getElementById('exported').value;" <?php is_disabled(); ?>>
        <!--
        onChange="javascript:if(document.getElementById('migrate').value=='yes')document.getElementById('exported').value='all';"
        -->
        <option value="choice not made" <?php is_selected(_('choice not made')); ?>><?php echo _('Please Select'); ?></option>
        <!-- <option value="Weberp_system" <?php is_selected("Weberp_system"); ?>><?php echo _('My WebERP+ System'); ?></option> -->
		<option value="Banking" <?php is_selected(_('Banking')); ?>><?php echo _('Banking'); ?></option>
		<option value="Customers" <?php is_selected(_('Customers')); ?>><?php echo _('Customers'); ?></option>
        <option value="EDI" <?php is_selected(_('EDI')); ?>><?php echo _('EDI'); ?></option>
		<option value="GL" <?php is_selected(_('GL')); ?>><?php echo _('General Ledger'); ?></option>
        <option value="Manufacturing" <?php is_selected(_('Manufacturing')); ?>><?php echo _('Manufacturing'); ?></option>
		<option value="Purchasing" <?php is_selected(_('Purchasing')); ?>><?php echo _('Purchasing'); ?></option>
		<option value="Sales" <?php is_selected(_('Sales')); ?>><?php echo _('Sales'); ?></option>
		<option value="Shipping" <?php is_selected(_('Shipping')); ?>><?php echo _('Shipping'); ?></option>
		<option value="Stocks" <?php is_selected(_('Stocks')); ?>><?php echo _('Stocks'); ?></option>
		<OPTION VALUE="Suppliers" <?php is_selected(_('Suppliers')); ?>><?php echo _('Suppliers'); ?></option>
		<OPTION VALUE="Tax" <?php is_selected(_('Tax')); ?>><?php echo _('Tax Details'); ?></OPTION>
		<OPTION VALUE="Users" <?php is_selected(_('Users')); ?>><?php echo _('Users'); ?></OPTION>	
        </SELECT></DIV></TD>
<TR>
         <TD COLSPAN=2 ALIGN=CENTER>
	 <INPUT TYPE="SUBMIT" value="Export" name="submit" onClick="javascript:if(document.getElementById('migrate').value=='no' && document.getElementById('exported_val').value=='Weberp_system'){document.getElementById('exported_val').value=document.getElementById('exported').value='choice not made';}"/>
	 </TD>
</TR>
</TABLE>
</FORM>
<?php check_migrate(); ?>
<BR><BR>
<?php

if(isset($_POST['exported_val'])){
	// ok go on
		
	if($_POST['exported_val']!=_('choice not made') || $_POST['migrate']==_('yes')){
				
		if($_POST['migrate']==_('yes')){
		 $test = new CSVExport('Weberp_system',$db);
		 $_POST['exported_val'] = 'Weberp_system';
		}else {
			$test = new CSVExport($_POST['exported_val'],$db);
		}
		// guarantee tmp folder is there
		if(!is_dir($LogosFolder.$_SESSION['SelectedCompany']."/exports/tmp")) {
			mkdir($LogosFolder.$_SESSION['SelectedCompany']."/exports/tmp");
		}
		
		if($test->SetExportsDir($LogosFolder.$_SESSION['SelectedCompany']."/exports")){
		        //print "<hr>";
			if($test->Export()){
			        print("<center><b>".$_POST['exported_val']."</b> item has been exported successfully.</center><br />");
				$browser = new FileBrowser($test->ExportsDir);
				$browser->SetBrowsedFolder($WWWLogos.$_SESSION['SelectedCompany']."/exports");
				$browser->SetIcon('bdoc.gif');
				$browser->SetFileType('zip');
				$browser->Browse();
			}
		}
	}
	else{
		print"<center><font color=red >Please select an item to export.</font></center><br  />";
		$filer = new FileBrowser($LogosFolder.$_SESSION['SelectedCompany']."/exports");
		$filer->SetBrowsedFolder($WWWLogos.$_SESSION['SelectedCompany']."/exports");
		$filer->SetFileType("zip");
		$filer->SetIcon("bdoc.gif");
		$filer->Browse();
		
	}
	

}else{
	print("<script type='text/javascript'>");
	print("document.getElementById('migrate').value='yes';document.getElementById('exported_val').value='Weberp_system';document.getElementById('exported').disabled='true';document.getElementById('combo_box').style.display='none';document.getElementById('combo_box').style.visibility='hidden';document.getElementById('combo_box2').style.display='none';document.getElementById('combo_box2').style.visibility='hidden';");
	print("</script>");
        $filer = new FileBrowser($LogosFolder.$_SESSION['SelectedCompany']."/exports");
	$filer->SetBrowsedFolder($WWWLogos.$_SESSION['SelectedCompany']."/exports");
	$filer->SetFileType('zip');
	$filer->SetIcon('bdoc.gif');
	$filer->Browse();

}

echo '<BR><BR><BR>
	<CENTER>' . _('Please note that the exported files are automatically <font color=red >deleted</font> from the system after 24 hours.') . '<BR></CENTER>';
include('includes/footer.inc');
?>

