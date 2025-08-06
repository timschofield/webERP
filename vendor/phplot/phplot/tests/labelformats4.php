<?php

# Test types of label formatting 4 (time, data:0:prefix+suffix)
require_once 'phplot.php';
$data = array();
$t = mktime(12, 0, 0, 6, 1, 2000);
for ($i = 0; $i < 12; $i++) {
    $data[] = array('', $t, $i * 13.4);
    $t += 60 * 60 * 24;
}
$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("Label Format 4");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetXTickIncrement(60*60*24);
$p->SetYTickIncrement(10);
$p->SetPlotType('lines');
$p->SetXLabelType('time', '%m/%d');
$p->SetXTitle("X: time MM/DD");
$p->SetYLabelType('data', 0, '$', '#');
$p->SetYTitle("Y: data, prec=0, prefix=\$, suffix=#");
$p->DrawGraph();
#fwrite(STDERR, print_r($p, True));
