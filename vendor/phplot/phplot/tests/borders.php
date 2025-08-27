<?php

# Plot and image border variations - core and default test
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'plotborder' => NULL,     # Plot border type or NULL to skip
  'pbcolor' => 'red',       # Grid color, used for plot border
  'imageborder' => NULL,    # Image border type or NULL to skip
  'ibcolor' => NULL,        # Image border color
  'ibwidth' => NULL,        # Image border width
        ), $tp);
require_once 'phplot.php';

// $debug = True;  // Uncomment or set externally to show some coord info at end

$data = array(
  array('',  -2,  -2),
  array('',  -1,  -1),
  array('',  0,   0),
  array('',  1,   1),
  array('',  2,   2),
  array('',  3,   4),
  array('',  4,   8),
);
$p = new Phplot\Phplot\phplot();

# Build a title:
$title = 'Plot Border: ';
if (empty($tp['plotborder'])) $title .= '(default)';
elseif (is_array($tp['plotborder']))
    $title .= '[' . implode(',', $tp['plotborder']) . ']';
else $title .= $tp['plotborder'];
$title .= ', Color: ';
if (empty($tp['pbcolor'])) $title .= '(default)';
else $title .= $tp['pbcolor'];
$title .= "\nImage Border: ";
if (empty($tp['imageborder'])) $title .= '(default)';
else $title .= $tp['imageborder'];
$title .= ', Color: ';
if (empty($tp['ibcolor'])) $title .= '(default)';
else $title .= $tp['ibcolor'];
$title .= ', Width: ';
if (empty($tp['ibwidth'])) $title .= '(default)';
else $title .= $tp['ibwidth'];


$p->SetTitle($title);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetDrawXGrid(False);
$p->SetDrawYGrid(False);
if (!empty($tp['plotborder'])) $p->SetPlotBorderType($tp['plotborder']);
if (!empty($tp['pbcolor'])) $p->SetGridColor($tp['pbcolor']);
if (!empty($tp['imageborder'])) $p->SetImageBorderType($tp['imageborder']);
if (!empty($tp['ibcolor'])) $p->SetImageBorderColor($tp['ibcolor']);
if (!empty($tp['ibwidth'])) $p->SetImageBorderWidth($tp['ibwidth']);

# Move Y axis in so affect of plot area borders is more visible:
$p->SetYAxisPosition(0);
# Turn on all side labels, ticks, titles:
$p->SetXTickLabelPos('both');
$p->SetXTickPos('both');
$p->SetYTickLabelPos('both');
$p->SetYTickPos('both');
$p->SetPlotType('lines');
$p->SetXTitle('X Axis Title Here', 'both');
$p->SetYTitle('Y Axis Title Here', 'both');

$p->DrawGraph();
if (isset($debug)) {
    fwrite(STDERR, 'plot_area = ' . implode(', ', $p->plot_area) . "\n");
    fwrite(STDERR, "plot_min_y = $p->plot_min_y, plot_max_y = $p->plot_max_y\n");
    $ymin = $p->ytr($p->plot_min_y);
    $ymax = $p->ytr($p->plot_max_y);
    fwrite(STDERR, "Translated Y max = $ymax, Y min =  $ymin\n");
}
