<?php
# PHPlot test: Box plot, missing values
require_once 'phplot.php';

# Data rows are (label, X, Ymin, YQ1, Ymid, YQ3, Ymax, [Youtlier1...])
# Specified behavior for missing (non-numeric, NULL, or empty string) values:
#   If YQ1 or YQ3 are missing => skip row
#   If Ymid is missing => Don't draw median belt
#   If Ymin or Ymax are missing => Don't draw the corresponding whisker/T
#   If any outliers are missing, just skip them.

$data = array(
#   Label,               X    Ymin YQ1  Ymid YQ3  Ymax [Youtlier...]
#                        ---- ---- ---- ---- ---- ---- -------------------
  array("Baseline",         1,   5,  10,  15,  20,  25),
  array("Missing\nYmin",    2,  '',  10,  15,  20,  25),
  array("Missing\nYQ1",     3,   5,  '',  15,  20,  25),
  array("Missing\nYmid",    4,   5,  10,  '',  20,  25),
  array("Missing\nYQ3",     5,   5,  10,  15,  '',  25),
  array("Missing\nYmax",    6,   5,  10,  15,  20,  ''),
  array("Missing\n3 Y's",   7,  '',  10,  '',  20,  ''),
  array("All NULL",         8,NULL,NULL,NULL,NULL,NULL),
  array("Baseline",         9,   5,  10,  15,  20,  25),
  array("4 outliers",      10,   5,  10,  15,  20,  25, 2, 3, 28, 29),
  array("-1 missing",      11,   5,  10,  15,  20,  25, 2, '', 28, 29),
  array("-2 missing",      12,   5,  10,  15,  20,  25, '', 3, 28, ''),
  array("Ymin==\nYQ1",     13,   7,   7,  15,  20,  25),
  array("YQ3==\nYmax",     14,   5,  10,  15,  22,  22),
);

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle('Box Plot with Missing Values');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetDrawYGrid(False);
$p->SetPlotType('boxes');
$p->SetXTickIncrement(1);
$p->DrawGraph();
