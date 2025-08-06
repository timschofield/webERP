<?php

# PHPlot test - empty plot tests (no data = no graph with no error)
# This is a parameterized test. Set plot_type, optionally data_type and
# call this script.
if (empty($plot_type)) $plot_type = 'bars'; // Plot type
if (empty($data_type)) $data_type = 'text-data'; // Data type
if (empty($rows)) $rows = 2;  // Number of rows of data
if (empty($cols)) {      // Number of empty value columns per row
  if ($data_type == 'data-data-error') $cols = 3; // Y, +err, -err
  elseif ($plot_type == 'ohlc' || $plot_type == 'candlesticks'
       || $plot_type == 'candlesticks2') $cols = 4; // O, H, L, C
  else $cols = 1;
}
$need_x = ($data_type == 'data-data' || $data_type == 'data-data-yx'
       || $data_type == 'data-data-error');

# Build a data array based on $rows and $cols with all empty values.
$data = array();
for ($r = 0; $r < $rows; $r++) {
  $row = array(chr(ord('A')+$r)); // Label
  if ($need_x) $row[] = $r + 1;  // Independent variable
  for ($c = 0; $c < $cols; $c++) $row[] = ''; // Dependent variable values
  $data[] = $row;
}

require_once 'phplot.php';
$p = new Phplot\Phplot\phplot();
$p->SetTitle("Empty Plot: $plot_type, $data_type");
$p->SetPlotType($plot_type);
$p->SetDataType($data_type);
$p->SetDataValues($data);
$p->DrawGraph();
