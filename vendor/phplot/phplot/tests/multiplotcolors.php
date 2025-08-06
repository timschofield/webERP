<?php

# Testing phplot - Multiplot vs data colors (bug), baseline
require_once 'phplot.php';

# These parameters can be set in a calling script:
if (!isset($plot_type)) $plot_type = 'linepoints'; // E.g. 'bars'
if (!isset($do_callback)) $do_callback = FALSE;  // Use data_color callback?

# 2 data sets
$data2 = array(
  array('',  0, 1),
  array('',  2, 3),
  array('',  3, 4),
  array('',  4, 5),
);

# 3 data sets:
$data3 = array(
  array('',  0, 2, 3),
  array('',  4, 5, 6),
  array('',  5, 6, 7),
  array('',  6, 7, 8),
);

# A data_color callback which is the same as the standard
# no-callback behavior, but presence of a callback changes
# how colors are allocated.
function sdc($img, $unused, $row, $col)
{
    return $col;
}

$title = "Multiplot vs data colors, type $plot_type";
$p = new Phplot\Phplot\phplot(800, 800);
if ($do_callback) {
    $p->SetCallback('data_color', 'sdc');
    $title .= ", with data_color callback";
}

$p->SetPrintImage(False);
$p->SetTitle($title);
$p->SetPlotType($plot_type);
$p->SetLineStyles('solid');
$p->SetDataType('text-data');
$p->SetPlotAreaWorld(0, 0, 4, 10);

$p->SetPlotAreaPixels(60, 60, 339, 339);
$p->SetDataValues($data3);
$p->DrawGraph();

$p->SetPlotAreaPixels(460, 60, 739, 339);
$p->SetDataValues($data2);
$p->DrawGraph();

$p->SetPlotAreaPixels(60, 460, 339, 739);
$p->SetDataValues($data3);
$p->DrawGraph();

$p->SetPlotAreaPixels(460, 460, 739, 739);
$p->SetDataValues($data2);
$p->DrawGraph();

$p->PrintImage();
