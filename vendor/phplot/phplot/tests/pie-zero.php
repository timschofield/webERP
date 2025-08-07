<?php
# PHPlot test: Bug 1827263, spoiled chart if close to zero - case 1
require_once 'phplot.php';

# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'suffix' => " shaded",     # Title part 2
  'case' => 1,              # Data test case
  'shading' => NULL,        # Pie shading, 0 for no shading, NULL for default (5)
  'labelpos' => NULL,       # Pie label offset, NULL for none, < .5 for inside
        ), $tp);

$colors = array('red', 'green', 'blue', 'yellow', 'cyan', 'magenta',
    'purple', 'tan',   'orange', 'gray', 'lavender', 'maroon');

# Different data set test cases.
# If the numbers sum to 360, units are degrees.
switch ($tp['case']) {
case 1:
  $descript = 'Case 1, slice 2(green)=0';
  $data = array(
    array('', 50),
    array('', 0),
    array('', 20),
    array('', 50),
    array('', 100),
    array('', 60),
    array('', 50),
    array('', 2),
  );
  break;

case 2:
  $descript = 'Case 2, first(red) and last(cyan)=0';
  $data = array(
    array('', 0),
    array('', 33),
    array('', 66),
    array('', 33),
    array('', 0),
  );
  break;

case 3:
  $descript = 'Case 3, adjacent slices 6(magenta) and 7(purple)=0';
  $data = array(
    array('', 36),
    array('', 36),
    array('', 36),
    array('', 36),
    array('', 36),
    array('',  0),
    array('',  0),
    array('', 36),
    array('', 36),
    array('', 36),
    array('', 36),
    array('', 36),
  );
  break;
}

$plot = new Phplot\Phplot\phplot(800,600);
$plot->SetTitle("Pie Charts with Zero-size Slices\n" . $descript . $tp['suffix']);
$plot->SetPlotType('pie');
$plot->SetDataType('text-data-single');
$plot->SetDataValues($data);
$plot->SetDataColors($colors);
# Make the legend line count match the data array size:
$plot->SetLegend(array_slice($colors, 0, count($data)));
if (isset($tp['shading'])) $plot->SetShading($tp['shading']);
if (isset($tp['labelpos'])) $plot->SetLabelScalePosition($tp['labelpos']);
$plot->DrawGraph();
