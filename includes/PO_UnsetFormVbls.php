<?php
/* $Id: PO_UnsetFormVbls.php 5766 2012-12-19 06:45:03Z daintree $*/
/*PO_UnsetFormVariable on the purchase order line items */
                    unset($_POST['StockID']);
                    unset($_POST['Qty']);
                    unset($_POST['Price']);
                    unset($_POST['ItemDescription']);
                    unset($_POST['GLCode']);
                    unset($_POST['GLAccountName']);
                    unset($_POST['ReqDelDate']);
                    unset($_POST['ShiptRef']);
                    unset($_POST['Jobref']);
?>