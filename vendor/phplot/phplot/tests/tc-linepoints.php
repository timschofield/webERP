<?php

# PHPlot test: Truecolor linepoints, looking at point shapes
require_once 'phplot.php';
require_once 'phplot_truecolor.php';

# Array of all point shapes as of PHPlot-5.1.0:
$point_shapes = array(
  'halfline', 'line', 'plus', 'cross', 'rect', 'circle', 'dot', 'diamond',
  'triangle', 'trianglemid', 'delta', 'yield', 'star', 'hourglass', 'bowtie',
  'target', 'box', 'home', 'up', 'down'
);
$n = count($point_shapes);

$data = array();
for ($x = 0; $x < 4; $x++) {
    $row = array('', $x);
    for ($i = 0; $i < $n; $i++) {
        $row[] = (($i + 1) / 3) * $x;
    }
    $data[] = $row;
}

$p = new Phplot\Phplot\phplot_truecolor(1000, 800);
$p->SetTitle("Truecolor linespoint plot with all point shapes");
$p->SetDataType('data-data');
$p->SetDataValues($data);
# Reload data colors and apply alpha to all:
$p->SetDataColors(NULL, NULL, 60);
$p->SetLineWidths(3);
$p->SetLineStyles('solid');
$p->SetPointSizes(16);
$p->SetPointShapes($point_shapes);
$p->SetPlotType('linepoints');
$p->DrawGraph();
