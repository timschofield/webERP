<?php
# Testing phplot - Line-Points, extensions to suppress either
# the line or the points on each plot.

require_once 'phplot.php';

$data = array(
  array('',   1,   1,   2,   3),
  array('',   2,   2,   3,   4),
  array('',   3,   3,   4,   5),
  array('',   4,   4,   5,   6),
  array('',   5,   5,   6,   7),
  array('',   6,   6,   7,   8),
  array('',   7,   7,   8,   9),
  array('',   8,   8,   9,  10),
  array('',   9,   9,  10,  11),
  array('',  10,  10,  11,  12),
);

$p = new Phplot\Phplot\phplot();
$p->SetTitle('PlotType: linepoints with suppression');
$p->SetDataType('data-data');
$p->SetDataValues($data);

# We don't use the data labels (all set to '') so might as well turn them off:
$p->SetXDataLabelPos('none');

# Need to set area and ticks to get reasonable choices.
#  Increase X range to make room for the legend.
$p->SetPlotAreaWorld(0, 0, 13, 20);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(2);

$p->SetDataColors(array('red', 'green', 'blue'));

$p->SetPointShapes(array( 'circle', 'none', 'diamond'));
$p->SetLineStyles(array('solid', 'solid', 'none'));
$p->SetLegend(array('circle+line', 'line only', 'diamond only'));

# Make the points bigger so we can see them:
$p->SetPointSizes(10);

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True); # The default

$p->SetPlotType('linepoints');

$p->DrawGraph();
