<?php

/////////////////////////////////////////////////////////////////////
//  Company Selection
/////////////////////////////////////////////////////////////////////

if (!isset($_GET['Company']) AND !isset($_POST['Company'])){
	include('includes/header.inc');
	prnMsg('Script called without the correct parameters','warn');
	include('includes/footer.inc');
}else{
	if(isset($_GET['Company'])){
		$Company = $_GET['Company'];
	}else{
		$Company = $_POST['Company'];
	}
} 

?>
