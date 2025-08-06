<?php
# Generate pointshapes that PHPlot uses.
# 01/04/2011
# This makes "N" separate image files named shape-*.png.
# Each one is a "bare" plot - just a single shape.

require_once 'phplot.php';

# Sorted list taken from PHPlot (because there is no way yet to pull it
# from an array).
$shapes = array('bowtie', 'box', 'circle', 'cross', 'delta',
    'diamond', 'dot', 'down', 'halfline', 'home',
    'hourglass', 'line', 'plus', 'rect', 'star',
    'target', 'triangle', 'trianglemid', 'up', 'yield');

# Number of shapes:
$n_shapes = count($shapes);

$data = array(array('', 1, 1));

foreach ($shapes as $shape) {

    $filename = "shape-$shape.png";
    $plot = new Phplot\Phplot\phplot(24, 24, $filename);
    $plot->SetIsInline(True);
    $plot->SetPlotBorderType('none');
    $plot->SetDrawXAxis(False);
    $plot->SetDrawYAxis(False);
    $plot->SetMarginsPixels(2, 2, 2, 2);
    $plot->SetPlotType('points');
    $plot->SetDataType('data-data');
    $plot->SetDataValues($data);
    $plot->SetPlotAreaWorld(0, 0, 2, 2);
    $plot->SetXTickPos('none');
    $plot->SetXTickLabelPos('none');
    $plot->SetYTickPos('none');
    $plot->SetYTickLabelPos('none');
    $plot->SetDataColors(array('black'));
    $plot->SetPointShapes($shape);
    $plot->SetPointSizes(10);
    $plot->SetDrawYGrid(False);
    $plot->DrawGraph();

    echo "$filename\n";
}
