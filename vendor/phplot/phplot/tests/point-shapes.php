<?php

# Testing phplot - Points
require_once 'phplot.php';

# This array is used for both the point shapes and legend:
$shapes = array( 'circle', 'cross', 'diamond', 'dot',
    'halfline', 'line', 'plus', 'rect', 'triangle', 'trianglemid');

# 10 lines, one for each shape:
$data = array(
  array('',   1,   1,   2,   3,   4,   5,   6,   7,   8,   9,  10),
  array('',   2,   2,   3,   4,   5,   6,   7,   8,   9,  10,  11),
  array('',   3,   3,   4,   5,   6,   7,   8,   9,  10,  11,  12),
  array('',   4,   4,   5,   6,   7,   8,   9,  10,  11,  12,  13),
  array('',   5,   5,   6,   7,   8,   9,  10,  11,  12,  13,  14),
  array('',   6,   6,   7,   8,   9,  10,  11,  12,  13,  14,  15),
  array('',   7,   7,   8,   9,  10,  11,  12,  13,  14,  15,  16),
  array('',   8,   8,   9,  10,  11,  12,  13,  14,  15,  16,  17),
  array('',   9,   9,  10,  11,  12,  13,  14,  15,  16,  17,  18),
  array('',  10,  10,  11,  12,  13,  14,  15,  16,  17,  18,  19),
);

$p = new Phplot\Phplot\phplot();

$p->SetTitle('Points plots, 10 lines/10 shapes');
$p->SetDataType('data-data');
$p->SetDataValues($data);

# We don't use the data labels (all set to '') so might as well turn them off:
$p->SetXDataLabelPos('none');

# Need to set area and ticks to get reasonable choices.
#  Increase X range to make room for the legend.
$p->SetPlotAreaWorld(0, 0, 13, 20);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(2);

# Need 10 different colors; defaults are not different:
$p->SetDataColors(array('red', 'green', 'blue', 'yellow', 'cyan',
                        'magenta', 'brown', 'lavender', 'pink', 'orange'));

# Show all 10 shapes:
$p->SetPointShapes($shapes);
# Also show that as the legend:
$p->SetLegend($shapes);
# Make the points bigger so we can see them:
$p->SetPointSizes(10);

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True); # The default

$p->SetPlotType('points');

$p->DrawGraph();
