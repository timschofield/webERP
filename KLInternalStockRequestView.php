<?php

/********************************************************************************************************************
* KL RICARD: Display all internal stock requests still not fulfilled without user modification in quantity delivered
*            and without showing tags fields.
********************************************************************************************************************/

include('includes/session.php');

$Title = __('View Unfulfilled Stock Requests');
$ViewTopic = 'Inventory';
$BookMark = 'ViewRequest';
include('includes/header.php');

include('includes/SQL_CommonFunctions.php');

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/inventory.png" title="' . __('Contract') . '" alt="" />' . __('View Unfulfilled Stock Requests') . '</p>';

// Fetch all uncompleted internal stock requests of the department (shop) assigned to SPG

$SQL = "SELECT stockrequest.dispatchid, 
                locations.locationname, 
                departments.description AS departmentname, 
                stockrequestitems.stockid, 
                stockmaster.description AS stockdescription,
                stockrequestitems.quantity, 
                stockrequestitems.qtydelivered,
                stockrequestitems.uom
        FROM stockrequest
        JOIN stockrequestitems
            ON stockrequestitems.dispatchid = stockrequest.dispatchid
        JOIN stockmaster
            ON stockrequestitems.stockid = stockmaster.stockid
        JOIN locations
            ON stockrequest.loccode = locations.loccode
        JOIN departments
            ON stockrequest.departmentid = departments.departmentid
        WHERE stockrequest.departmentid = '" . $_SESSION['AllowedDepartment'] . "'
            AND stockrequestitems.completed = 0
        ORDER BY stockrequest.dispatchid,
            stockrequestitems.stockid";

$Result = DB_query($SQL);

if (DB_num_rows($Result) > 0) {
    echo '<table class="selection">
            <thead>
                <tr>
                    <th class="SortedColumn">' . __('Request ID') . '</th>
                    <th class="SortedColumn">' . __('From') . '</th>
                    <th class="SortedColumn">' . __('To') . '</th>
                    <th class="SortedColumn">' . __('Stock ID') . '</th>
                    <th class="SortedColumn">' . __('Stock Description') . '</th>
                    <th class="SortedColumn">' . __('Qty Requested') . '</th>
                    <th class="SortedColumn">' . __('Qty Pending') . '</th>
                    <th class="SortedColumn">' . __('Units') . '</th>
                </tr>
            </thead>
            <tbody>';

    while ($MyRow = DB_fetch_array($Result)) {
        echo '<tr class="striped_row">
				<td>' . $MyRow['dispatchid'] . '</td>
    			<td>' . $MyRow['locationname'] . '</td>
                <td>' . $MyRow['departmentname'] . '</td>
            	<td>' . $MyRow['stockid'] . '</td>
            	<td>' . $MyRow['stockdescription'] . '</td>
                <td class="number">' . locale_number_format($MyRow['quantity'], 'Variable') . '</td>
                <td class="number">' . locale_number_format($MyRow['quantity'] - $MyRow['qtydelivered'], 'Variable') . '</td>
                <td>' . $MyRow['uom'] . '</td>
              </tr>';
    }

    echo '</tbody></table>';
} else {
    echo '<p>' . __('There are no unfulfilled internal stock requests.') . '</p>';
}

include('includes/footer.php');
