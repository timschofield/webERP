<?php

# PHPlot Test: Pie border color and control, baseline/master
# Note: Requires PHPlot >= 6.0.6 for SetPieBorderColor() and
# SetDrawPieBorders().
require_once 'phplot.php';

/*
   Other scripts set these variables, then include this script:
$border_on = False;  // Pie segment boundaries: True, False, or unset
$border_color = 'white';  // Pie boundary color, or unset
$grid_color = NULL;  // Grid color (default for pie boundary color) or unset
$shading = 0; // Shading; 0 and NULL for no shading, or > 0 for shading.

*/

# Check for PHPlot methods and skip the test if missing:
if (isset($border_color) && !method_exists('Phplot\Phplot\phplot', 'SetPieBorderColor')
 || isset($border_on)    && !method_exists('Phplot\Phplot\phplot', 'SetDrawPieBorders')) {
    echo "Skipping test because required PHPlot methods are missing\n";
    exit(2);
}

$data = array();
for ($i = 1; $i <= 15; $i++) $data[] = array('', 1);

// Build plot title:
if (!isset($border_on)) $b = 'default';
elseif ($border_on) $b = 'on';
else $b = 'off';
if (isset($border_color)) $c = $border_color;
else $c = 'default';
if (isset($grid_color)) $g = $grid_color;
else $g = 'default';
if (isset($shading)) $shade = "Shading: $shading";
else $shade = "Unshaded";
$title = "Test Pie Border Control, $shade\n"
       . "Border: $b, Border color: $c, Grid color: $g";

$plot = new Phplot\Phplot\phplot(800,600);
$plot->SetTitle($title);
$plot->SetImageBorderType('plain');
$plot->SetPlotType('pie');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetShading(isset($shading) ? $shading : 0);
if (isset($border_color)) $plot->SetPieBorderColor($border_color);
if (isset($border_on)) $plot->SetDrawPieBorders($border_on);
if (isset($grid_color)) $plot->SetGridColor($grid_color);
$plot->DrawGraph();
