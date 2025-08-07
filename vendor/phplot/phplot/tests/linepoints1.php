<?php

# PHPlot Example: Line-Point plot showing point shapes
# If the variable $use_default_shapes is set (from a calling script), show
# the default shapes, otherwise show all shapes.
require_once 'phplot.php';

// Extend PHPlot class to allow access to protected variable(s):
class PHPlot_pv extends Phplot\Phplot\phplot {
    public function GET_point_shapes() { return $this->point_shapes; }
}

# This is a list of selected colors in the PHPlot 'small' map:
$colors = array(
  'orange', 'blue', 'maroon', 'red', 'peru', 'cyan', 'black', 'gold',
  'purple', 'YellowGreen', 'SkyBlue', 'green', 'SlateBlue', 'navy',
  'aquamarine1', 'violet', 'salmon', 'brown', 'pink', 'DimGrey',
);

$plot = new phplot_pv(800, 600);
# Define the $shapes array, used for both the point shapes and legend:
if (isset($use_default_shapes)) {
    # Cheat: grab phplot's internal shapes
    $shapes = $plot->GET_point_shapes();
    $title_suffix = ' (PHPlot Defaults)';
} else {
    $shapes = array(
        'bowtie', 'box', 'circle', 'cross', 'delta', 'diamond', 'dot',
        'down', 'halfline', 'home', 'hourglass', 'line', 'plus', 'rect',
        'star', 'target', 'triangle', 'trianglemid', 'up', 'yield',
    );
    $title_suffix = ' (All Shapes)';
}

# Number of shapes defines the number of lines to draw:
$n_shapes = count($shapes);

# Make offset diagonal lines, one for each shape:
$ppl = 6; # Number of points per line.
$data = array();
for ($x = 0; $x < $ppl; $x++) {
  $row = array('', $x);
  $offset = $n_shapes + $ppl - $x - 2;
  for ($j = 0; $j < $n_shapes; $j++) $row[] = $offset - $j;
  $data[] = $row;
}

$plot->SetImageBorderType('plain');

$plot->SetPlotType('linepoints');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);

# Main plot title:
$plot->SetTitle("Linepoints Plot - Showing $n_shapes Point Shapes\n$title_suffix");

# Increase X range to make room for the legend.
$plot->SetPlotAreaWorld(0, 0, $ppl, $n_shapes + $ppl - 2);
# Turn off tick labels and ticks - not used for this plot.
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetYTickLabelPos('none');
$plot->SetYTickPos('none');

# Need some different colors;
$plot->SetDataColors($colors);

# If not showing default shapes, show all shapes:
if (!isset($use_default_shapes)) $plot->SetPointShapes($shapes);

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
