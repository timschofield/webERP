<?php

# Legend Position Example template
# This generates the pictures and text for SetLegendPosition examples.
require_once 'phplot.php';

if ($argc != 2) {
    fwrite(STDERR, "Usage: php setlegendposition example# > image.png\n");
    exit(1);
}
$x = $argv[1]; // Example number:

// ===== List of Examples =====

$ti[1] = 'Place upper left corner of legend at offset (5,5) from
 upper left corner of image';
$ex[1] = array(0, 0, 'image', 0, 0, 5, 5);

$ti[2] = 'Place bottom left corner of legend at offset (7,-7) from
 bottom left corner of image';
$ex[2] = array(0, 1, 'image', 0, 1, 7, -7);

$ti[3] = 'Place top right corner of legend at top center of plot area';
$ex[3] = array(1, 0, 'plot', 0.5, 0);

$ti[4] = 'Center the legend in the upper half of the plot area';
$ex[4] = array(0.5, 0.5, 'plot', 0.5, 0.25);

$ti[5] = 'Place center of legend at world coordinates (2,60)';
$ex[5] = array(0.5, 0.5, 'world', 2, 60);

$ti[6] = 'Place top right corner of legend at offset (-5,5) from
  world coordinates (4,0)';
$ex[6] = array(1, 0, 'world', 4, 0, -5, 5);

$ti[7] = 'Center the top of the legend below the bottom of the title';
$ex[7] = array(0.5, 0, 'title', 0.5, 1);


// ============================

if (!isset($ex[$x])) {
    fwrite(STDERR, "No such example: $x\n");
    exit(1);
}

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
$p = new Phplot\Phplot\phplot(400, 300);
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetTitle("Legend Position Example #$x");
$p->SetPlotAreaWorld(-4, -70, 4, 80);
$p->SetXTickIncrement(1);
$p->SetYTickIncrement(10);
$p->SetLineStyles('solid');
$p->SetLineWidths(2);
$p->SetImageBorderType('plain');
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);
$p->SetLegend(array('x^3', 'x^2', '-10x'));
$p->SetMarginsPixels(80, 50, 50, 50);
$p->SetPlotType('lines');
call_user_func_array(array($p, 'SetLegendPosition'), $ex[$x]);
$p->DrawGraph();

fwrite(STDERR, "\nCase $x. " . $ti[$x] . "\n");
// Implode array with , separators, but put quotes around strings:
fwrite(STDERR, '$plot->SetLegendPosition(' . implode(', ', array_map(
        create_function('$s', 'return is_string($s) ? "\'$s\'" : $s;'),
        $ex[$x])) . ");\n");
