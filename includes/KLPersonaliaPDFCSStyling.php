<?php

/////////////////////////////////////////////////////////////////////
//  Centralized CSS Styling for Salary Slip PDFs
/////////////////////////////////////////////////////////////////////

$HTML .= '<style>
    /* Base styles */
    body {
        font-family: helvetica, sans-serif;
        font-size: 10px;
        line-height: 1.2;
        margin: 15mm 15mm 15mm 15mm;
    }
    
    /* Typography */
    .bold-text, .font-bold {
        font-weight: bold;
    }
    .font-small {
        font-size: 8px;
    }
    .font-normal {
        font-size: 10px;
    }
    .font-medium {
        font-size: 11px;
    }
    .font-large, .font-header {
        font-size: 12px;
    }
    
    /* Layout utilities - FIX: page-break definition */
    .page-break {
        page-break-before: always;
        /* Removed page-break-after causing blank pages */
        height: 0;
        margin: 0;
        padding: 0;
    }
    .margin-top-2 {
        margin-top: 2px;
    }
    .margin-top-5, .mt-5 {
        margin-top: 5px;
    }
    .margin-top-10, .mt-10 {
        margin-top: 10px;
    }
    .margin-top-15, .mt-15 {
        margin-top: 15px;
    }
    .mt-40 {
        margin-top: 40px;
    }
    .mt-60 {
        margin-top: 60px;
    }
    .margin-bottom-2 {
        margin-bottom: 2px;
    }
    .margin-bottom-5, .mb-5 {
        margin-bottom: 5px;
    }
    
    /* Text alignment */
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
    }
    .text-center {
        text-align: center;
    }
    
    /* Tables - FIX: Added default border for tables */
    table {
        width: 100%;
        border-collapse: collapse;
    }
    td, th {
        padding: 3px;
        border: 1px solid #000; /* Default border for all table cells */
    }
    .table-header th {
        font-size: 11px;
        font-weight: bold;
    }
    .salary-table th, .salary-table td {
        padding: 3px;
        border: 1px solid #000; /* Explicit border for salary table */
    }
    .table-no-border td, .table-no-border th, .no-border {
        border: none !important; /* Use !important to override default border */
    }
    table.bordered td, table.bordered th {
        border: 1px solid black;
    }
    table.salary-details {
        font-size: 9px;
    }
    table.salary-details td {
        padding: 1px 2px;
        border: 1px solid #000; /* Ensure borders on salary details */
    }
    
    /* Column widths */
    .full-width {
        width: 100%;
    }
    .col-1, .col-2, .col-3 {
        width: 32%;
    }
    .col-4, .col-5 {
        width: 18%;
    }
    .col-label {
        width: 110px;
        text-align: right;
        padding-right: 5px;
    }
    .col-value {
        width: 100px;
        text-align: right;
    }
    .col-deduction {
        width: 100px;
        text-align: right;
    }
    .col-notes {
        width: 10%;
        text-align: center;
    }
    
    /* Component styles */
    .signature-space {
        margin-top: 60px;
        font-size: 10px;
    }
    .header-big, .company-header {
        font-size: 12px;
        font-weight: bold;
    }
    .header-small {
        font-size: 8px;
    }
    .employee-table {
        width: 100%;
        line-height: 1.1;
        text-align: right;
    }
    .employee-table td {
        padding: 1px;
        text-align: right;
        border: none !important; /* Employee table should not have borders */
    }
    .slip-title {
        font-size: 11px;
        font-weight: bold;
        margin: 2px 0;
    }
    .employee-info {
        font-size: 10px;
        line-height: 1.1;
        margin: 2px 0;
    }
    .footer {
        font-size: 9px;
        line-height: 1.1;
        margin-top: 2px;
    }
    
    /* Additional utilities */
    .line-height-compact {
        line-height: 1.1;
    }
    .line-height-normal {
        line-height: 1.3;
    }
    .padding-sm {
        padding: 1px 2px;
    }
    .padding-md {
        padding: 3px;
    }
    .border-bottom {
        border-bottom: 1px solid black;
    }
    .row {
        display: flex;
        margin-bottom: 5px;
    }
</style>';
?>
