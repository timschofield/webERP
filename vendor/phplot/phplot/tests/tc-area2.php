<?php

# PHPlot test: Truecolor area plot with partial transparency.
# This shows how area plot works, by making the fill areas
# overlap with partially transparent colors.
require_once 'phplot.php';
require_once 'phplot_truecolor.php';

$alpha = 90; // Transparency value: GD alpha (0-127, 0 means opaque).

// Helper for making a label
function gx($color, $u, $v)
{
    return $color . " " . min($u,$v) . "-" . max($u,$v);
}

// Data array builder helper
function mkdata(&$data, $y0, $y1, $y2)
{
    $label = gx('Rd', $y0, $y1) . "\n"
           . gx('Gn', $y1, $y2) . "\n"
           . gx('Bu', $y2, 0);

    $data[] = array('',     $y0, $y1, $y2);
    $data[] = array($label, $y0, $y1, $y2);
    $data[] = array('',     $y0, $y1, $y2);
}

// This data array includes all permutations of the order of the Y values per X.
// The labels show which range each color covers.
$data = array();
mkdata($data,  30, 20, 10);
mkdata($data,  30, 10, 20);
mkdata($data,  20, 30, 10);
mkdata($data,  20, 10, 30);
mkdata($data,  10, 30, 20);
mkdata($data,  10, 20, 30);
// Append a copy of the last points, so that it will have
// the same width. This is a quirk of squaredarea.
$data[] = array('', 10, 20, 30);

$p = new Phplot\Phplot\phplot_truecolor(800, 800);
$p->SetTitle("Area Plot with Overlapping Data\n"
            . "and Partially Transparent Colors (alpha=$alpha)\n"
            . "You should see 7 different color shades");
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('area');
$p->SetDataColors(array('red', 'green', 'blue'), NULL, $alpha);
$p->SetXTickPos('none');
$p->DrawGraph();
