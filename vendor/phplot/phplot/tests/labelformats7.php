<?php

# Test types of label formatting: multi-format printf, case 1
# For PHPlot >= 6.2
# Other scripts can set n_formats=1,2,3 then include this file.
require_once 'phplot.php';
if (empty($n_formats) || $n_formats < 1 || $n_formats > 3) $n_formats = 1;

# Skip this test with older PHPlot versions:
if ($n_formats > 1 && Phplot\Phplot\phplot::version_id < 60200) {
    echo "Skipping test because it requires version >= 6.2.0\n";
    exit(2);
}

$data = array(
   array('', 1, -10.15, -6, -2, 2.11,  6, 10),
   array('', 2,  -9.34, -5, -1, 3.22,  7, 11),
   array('', 3,  -8.56, -4,  0, 4.33,  8, 12),
   array('', 4,  -7.03, -3,  1, 5.44,  9, 13),
   array('', 5,  -6.17, -2,  2, 6.55, 10, 14),
   array('', 6,  -5.80, -1,  3, 7.66, 11, 15),
   array('', 7,  -4.93,  0,  4, 9.88, 12, 16),
);
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetDataType('data-data');
$p->SetPlotType('points');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetPlotAreaWorld(0, NULL, 8, NULL);
$p->SetYDataLabelPos('plotin');
switch ($n_formats) {
case 1:
    $which = 'printf with 1 format: %.1f';
    $p->SetYDataLabelType('printf', '%.1f');
    break;
case 2:
    $which = 'printf with 2 formats: %.1f, (%.1f)';
    $p->SetYDataLabelType('printf', '%.1f', '(%.1f)');
    break;
case 3:
    $which = 'printf with 3 formats: /%.1f/, \%.1f\, ZERO';
    $p->SetYDataLabelType('printf', '/%.1f/', '\%.1f\\', 'ZERO');
    break;
}
$p->SetTitle("Data Value Label Format Test\n$which");
$p->DrawGraph();
