<?php
# PHPlot Example: Line-Point plot showing all the point shapes
require_once __DIR__ . '/../../../src/phplot.php';

# This array is used for both the point shapes and legend:
$shapes = array('bowtie', 'box', 'circle', 'cross', 'delta',
    'diamond', 'dot', 'down', 'halfline', 'home',
    'hourglass', 'line', 'plus', 'rect', 'star',
    'target', 'triangle', 'trianglemid', 'up', 'yield');

# Number of shapes defines the number of lines to draw:
$n_shapes = count($shapes);

# Make offset diagonal lines, one for each shape:
$ppl = 6; # Number of points per line.
$data = array();
for ($i = 0; $i < $ppl; $i++) {
    $subdata = array('', $i);
    $offset = $n_shapes + $ppl - $i - 2;
    for ($j = 0; $j < $n_shapes; $j++) $subdata[] = $offset - $j;
    $data[] = $subdata;
}

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetImageBorderType('plain');

$plot->SetPlotType('linepoints');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle('Linepoints Plot - All Point Shapes');

# Increase X range to make room for the legend.
$plot->SetPlotAreaWorld(0, 0, $ppl, $n_shapes + $ppl - 2);
# Turn off tick labels and ticks - not used for this plot.
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetYTickLabelPos('none');
$plot->SetYTickPos('none');

# Need some different colors;
$plot->SetDataColors(array('orange', 'blue', 'maroon', 'red', 'peru',
    'cyan', 'black', 'gold', 'purple', 'YellowGreen',
    'SkyBlue', 'green', 'SlateBlue', 'navy', 'aquamarine1',
    'violet', 'salmon', 'brown', 'pink', 'DimGrey'));

# Show all shapes:
$plot->SetPointShapes($shapes);

# Make the points bigger so we can see them:
$plot->SetPointSizes(10);

# Make the lines all be solid:
$plot->SetLineStyles('solid');

# Also show that as the legend:
$plot->SetLegend($shapes);

# Draw no grids:
$plot->SetDrawXGrid(False);
$plot->SetDrawYGrid(False);

$plot->DrawGraph();
