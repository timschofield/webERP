<?php
/* $Revision: 1.3 $ */
/*Code to check that ShiptRef and Contract or JobRef entered are valid entries
This is used by the UpdateLine button when a purchase order line item is updated and
by the EnterLine button when a new purchase order line item is entered
*/


              if (($_POST['ShiptRef']!="" AND $_POST['ShiptRef']!="0") OR !isset($_POST['ShiptRef'])) { /*Dont bother if no shipt ref selected */
              /*Check for existance of Shipment Selected */
              $sql = "SELECT Count(*) FROM Shipments WHERE ShiptRef ='".  $_POST['ShiptRef'] . "' AND Closed =0";
                     $ShiptResult = DB_query($sql,$db,'','',false,false);
                     if (DB_error_no!=0 OR DB_num_rows($ShiptResult)==0){
                             $AllowUpdate = False;
                             echo '<BR><B>' . _('The Update Could Not Be Processed') . '</B><BR>' . _('There was some snag in retrieving the shipment reference entered - see the listing of open shipments to ensure a valid shipment reference is entered');
                     } else {
                            $ShiptRow = DB_fetch_row($ShiptResult);
                            if($ShiptRow[0]!=1){
                                   $AllowUpdate = False;
                                   echo '<BR><B>' . _('The Update Could Not Be Processed') . '</B><BR>' . _('The shipment entered is either closed or not set up in the database. Please refer to the list of open shipments from the link to ensure a valid shipment reference is entered');
                            }
                     }
              }

              if (($_POST['JobRef']!="" AND $_POST['JobRef']!="0") OR !isset($_POST['JobRef'])) {  /*Dont bother with this lot if there was not Contract selected */
              /*Check for existance of Shipment Selected */
              $sql = "SELECT Count(*) FROM Contracts WHERE ContractRef ='".  $_POST['JobRef'] . "'";
                     $JobResult = DB_query($sql,$db);
                     if (DB_error_no!=0 OR DB_num_rows($JobResult)==0){
                             $AllowUpdate = False;
                             echo '<BR><B>' . _('The Update Could Not Be Processed') . '</B><BR>' . _('There was a problem retrieving the contract reference entered - see the listing of contracts to ensure a valid contract reference is entered');
                     } else {
                            $JobRow = DB_fetch_row($JobResult);
                            if($JobRow[0]!=1){
                                   $AllowUpdate = False;
                                   echo '<BR><B>' . _('The Update Could Not Be Processed') . '</B><BR>' . _('The contract reference entered is not set up in the database. Please refer to the list of contracts from the link to ensure a valid contract reference is entered. If you do not wish to reference the cost of this item to a contract then leave the contract reference field blank');
                            }
                     }
              }

?>
