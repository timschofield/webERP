<?php

# PHPlot test: Box plot with data variations
# A calling script can set these variables:
#  $data_colors  = color array for SetDataColors()
#  $line_widths  = line widths array for SetLineWidths()
#  $subtitle = Text below the main title
#  $point_shape and $point_size (single values) for outliers.
require_once 'phplot.php';

if (empty($subtitle)) $subtitle = 'Baseline';

$data = array(
#   Label,              Ymin,  YQ1, Ymid,  YQ3, Ymax, [Youtlier...]
#                       ----  ----  ----  ----  ----  ------------------
# Cases with different outliers 0-3
  array("No\nOutliers",   20,   22,   25,   30,   35),
  array("1\nOutlier",     30,   32,   35,   40,   45,    5),
  array("2\nOutliers",    40,   42,   45,   50,   55,   10,   60),
  array("3\nOutliers",    10,   20,   30,   40,   50,    9,    8,   52),
  array("4\nOutliers",    10,   20,   30,   40,   50,    4,    7,   80, 70),

# Cases with various Yi == Yj
# 1 unique value
  array("1 val\nAAAAA",   50,   50,   50,   50,   50),
# 2 unique values
  array("2 val\nAAAAB",   40,   40,   40,   40,   60),
  array("2 val\nAAABB",   40,   40,   40,   60,   60),
  array("2 val\nAABBB",   40,   40,   60,   60,   60),
  array("2 val\nABBBB",   40,   60,   60,   60,   60),
# 3 unique values
  array("3 val\nAAABC",   50,   50,   50,   60,   70),
  array("3 val\nAABBC",   20,   20,   50,   50,   70),
  array("3 val\nAABCC",   20,   20,   50,   70,   70),
  array("3 val\nABBBC",   20,   50,   50,   50,   70),
  array("3 val\nABBCC",   20,   50,   50,   70,   70),
  array("3 val\nABCCC",   20,   30,   50,   50,   50),
# 4 unique values
  array("4 val\nAABCD",   30,   30,   50,   60,   70),
  array("4 val\nABBCD",   30,   50,   50,   60,   70),
  array("4 val\nABCCD",   30,   40,   50,   50,   70),
  array("4 val\nABCDD",   30,   40,   50,   70,   70),
);

$p = new Phplot\Phplot\phplot(900, 600);
$p->SetTitle("Box Plot with Various Data Cases\n$subtitle");
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotAreaWorld(NULL, 0, NULL, 100);
$p->SetDrawYGrid(False);
$p->SetPlotType('boxes');
$p->SetXTickPos('none');

# Optional style controls:
if (isset($data_colors)) $p->SetDataColors($data_colors);
if (isset($line_widths)) $p->SetLineWidths($line_widths);
if (isset($point_shape)) $p->SetPointShapes($point_shape);
if (isset($point_size)) $p->SetPointSize($point_size);

$p->DrawGraph();
