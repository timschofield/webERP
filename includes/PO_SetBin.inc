<?php
	if(isset($_POST['LineNo']) && isset($_POST['StockID']) && (isset($_POST['BinID'])) && ($_SESSION['PO']->Managed == 1)) {
		if(($LnItm->LineNo == $_POST['LineNo']) && ($LnItm->StockID == $_POST['StockID'])) {
			$LnItm->BinID = $_POST['BinID'];
			$_SESSION['PO']->LineItems[$LnItm->LineNo]->BinID = $_POST['BinID'];
		}
	}
?>