<?php

# Testing phplot - Using the 'large' color map, bug 2803900.
require_once 'phplot.php';

# Color names, selected from the X11 database using a script:
$colors = array(
    'violet red',
    'light blue',
    'PeachPuff1',
    'azure4',
    'DodgerBlue3',
    'CadetBlue1',
    'light coral',
    'DarkTurquoise',
    'coral',
    'azure3',
    'peru',
    'blue',
    'medium purple',
    'beige',
    'maroon4',
    'DeepPink3',
    'PaleVioletRed2',
    'CadetBlue',
    'PaleGoldenrod',
    'light sea green',
    'brown1',
    'RosyBrown3',
    'dark khaki',
    'PaleVioletRed3',
    'turquoise2',
    'OrangeRed3',
    'LightCyan3',
    'lime green',
    'IndianRed',
    'LightGoldenrod1',
    'sienna2',
    'salmon2',
    'LightYellow1',
    'yellow2',
    'chocolate',
    'ghost white',
    'orchid2',
    'HotPink1',
    'turquoise4',
    'LightPink1',
);
# Sort so the names are in alpha order:
sort($colors);
# Number of colors to use:
$nc = count($colors);

# Make a data array with 1 point, $nc data sets:
$row = array('');
for ($i = 0; $i < $nc; $i++) {
  $row[] = ($i % 5) + 1;
}
$data = array($row);

$p = new Phplot\Phplot\phplot(1024, 768);
$p->SetPlotType('bars');
$p->SetShading(0);
$p->SetDataType('text-data');
$p->SetDataValues($data);

# Select the "large" color map:
$p->SetRGBArray('large');

$p->SetTitle('Selections from the PHPlot Large Color Map');
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');
$p->SetYTickIncrement(1);
$p->SetPlotAreaWorld(0.1, 0, NULL, NULL);

# Use all colors:
$p->SetDataColors($colors);

# Make a legend with all the color names:
$p->SetLegend($colors);

$p->DrawGraph();
