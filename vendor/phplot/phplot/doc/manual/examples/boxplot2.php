<?php
# PHPlot Example - Box Plot with outliers and line styles
require_once __DIR__ . '/../../../src/phplot.php';

# Data array: each row is (label, X, Ymin, YQ1, Ymid, YQ3, Ymax, [Youtlier...])
$data = array(
    array('', 1,  10, 15, 20, 25, 30),
    array('', 2,  12, 14, 18, 20, 24,  6, 8, 28),
    array('', 3,   5, 11, 19, 28, 35),
    array('', 4,  14, 17, 21, 26, 28,  9, 12, 35, 32),
    array('', 5,  12, 15, 22, 27, 30),
    array('', 6,  15, 18, 20, 22, 26, 12),
    array('', 7,  10, 15, 21, 26, 28, 32),
    array('', 8,  11, 15, 20, 24, 27, 6, 8),
    array('', 9,  10, 15, 19, 22, 26, 4, 34),
);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle('Box Plot with outliers and styles');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetPlotType('boxes');
$plot->SetImageBorderType('plain'); // Improves presentation in the manual

# Use dashed lines for the upper and lower whiskers:
$plot->SetLineStyles('dashed');
# Make the box and belt use a thicker line:
$plot->SetLineWidths(array(3, 3, 1));
# Make the outliers red, and everything else blue:
$plot->SetDataColors(array('blue', 'blue', 'red', 'blue'));
# Draw the outliers using a "star":
$plot->SetPointShapes('star');

$plot->DrawGraph();
