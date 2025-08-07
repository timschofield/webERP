<?php
# Testing phplot - "Typical" plot with lots of elements, for manual
require_once 'phplot.php';

$data = array(
  array('', -4, -64,  16,  40),
  array('', -3, -27,   9,  30),
  array('', -2,  -8,   4,  20),
  array('', -1,  -1,   1,  10),
  array('',  0,   0,   0,   0),
  array('',  1,   1,   1, -10),
  array('',  2,   8,   4, -20),
  array('',  3,  27,   9, -30),
  array('',  4,  64,  16, -40),
);

# Size of plot is set by PDF 72dpi resolution:
$p = new Phplot\Phplot\phplot(400, 300);

$p->SetDataType('data-data');
$p->SetDataValues($data);

# Titles:
$p->SetTitle('A Plot Containing Some Lines');
$p->SetXTitle('Independent Variable');
$p->SetYTitle('Dependent Variable');

# We don't use the data labels (all set to '') so might as well turn them off:
$p->SetXDataLabelPos('none');

# Need to set area and ticks to get reasonable choices.
$p->SetPlotAreaWorld(-4, -70, 4, 80);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(10);

# Don't use dashes for 3rd line:
$p->SetLineStyles(array('solid', 'dashed', 'solid'));

# Make the lines thicker:
$p->SetLineWidths(2);

# Image border:
$p->SetImageBorderType('raised');
$p->SetImageBorderColor('blue');

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);

# And a legend:
$p->SetLegend(array('x^3', 'x^2', '-10x'));

$p->SetPlotType('lines');
$p->DrawGraph();
