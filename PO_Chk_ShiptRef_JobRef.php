<?php
/* $Revision: 1.7 $ */
/*Code to check that ShiptRef and Contract or JobRef entered are valid entries
This is used by the UpdateLine button when a purchase order line item is updated and
by the EnterLine button when a new purchase order line item is entered
*/

              if (($_POST['ShiptRef']!="" AND $_POST['ShiptRef']!=0) or !isset($_POST['ShiptRef'])) { /*Dont bother if no shipt ref selected */

              	/*Check for existance of Shipment Selected */
              $sql = "SELECT COUNT(*) FROM shipments WHERE shiptref ='".  $_POST['ShiptRef'] . "' AND closed =0";
                     $ShiptResult = DB_query($sql,$db,'','',false,false);
                     if (DB_error_no!=0 OR DB_num_rows($ShiptResult)==0){
                             $AllowUpdate = False;
                             prnMsg(_('The update could not be processed') . '<br>' . _('There was some snag in retrieving the shipment reference entered') . ' - ' . _('see the listing of open shipments to ensure a valid shipment reference is entered'),'error');
                     } else {
                            $ShiptRow = DB_fetch_row($ShiptResult);
                            if($ShiptRow[0]!=1){
                                   $AllowUpdate = False;
                                   prnMsg( _('The update could not be processed') . '<br>' . _('The shipment entered is either closed or not set up in the database') . '. ' . _('Please refer to the list of open shipments from the link to ensure a valid shipment reference is entered'),'error');
                            }
                     }
              }
		/*
              if (($_POST['JobRef']!='' AND $_POST['JobRef']!='0') OR !isset($_POST['JobRef'])) {  //Dont bother with this lot if there was not Contract selected 
             
              $sql = "SELECT COUNT(*) FROM contracts WHERE contractref ='".  $_POST['JobRef'] . "'";
                     $JobResult = DB_query($sql,$db);
                     if (DB_error_no!=0 OR DB_num_rows($JobResult)==0){
                             $AllowUpdate = False;
                             prnMsg(_('The update could not be processed') . '<br>' . _('There was a problem retrieving the contract reference entered') . ' - ' . _('see the listing of contracts to ensure a valid contract reference is entered'),'error');
                     } else {
                            $JobRow = DB_fetch_row($JobResult);
                            if($JobRow[0]!=1){
                                   $AllowUpdate = False;
                                   prnMsg( _('The update could not be processed') . '<br>' . _('The contract reference entered is not set up in the database') . '. ' . _('Please refer to the list of contracts from the link to ensure a valid contract reference is entered') . '. ' . _('If you do not wish to reference the cost of this item to a contract then leave the contract reference field blank'),'error');
                            }
                     }
              }
	*/
?>
