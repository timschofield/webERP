<?php

# PHPlot test: Time values as X ticks labels
require_once 'phplot.php';

# To get a repeatable test with 'random' data:
mt_srand(1);

# Need a base date/time: Can't just use 0 due to UTC/local differences:
$base_time = mktime(0, 0, 0, 1, 1, 2000);
# Twenty minutes:
$interval = 20 * 60;

# Random data at intervals:
$data = array();
$t = $base_time;
for ($i = 1; $i <= 12; $i++) {
  $data[] = array('', $t, mt_rand(0, 100));
  $t += $interval;
}

$p = new Phplot\Phplot\phplot(600,400);
$p->SetTitle('Meaningless Data with Time X Tick Labels');
$p->SetDataType('data-data');
$p->SetDataValues($data);

$p->SetXLabelType('time');
$p->SetXTimeFormat('%H:%M');
$p->SetXTitle('Elapsed Time (hours:minutes)');
# Turn off X data labels, use tick labels only:
$p->SetXDataLabelPos('none');
$p->SetXTickLabelPos('plotdown');
# Even though tick values are given, it makes up its own unless:
$p->SetXTickIncrement($interval);
$p->SetDrawXGrid(True);

# Set the Y min and max, since the data is 0:100
$p->SetPlotAreaWorld(NULL, 0, NULL, 100);
$p->SetYTitle('Meaningless Value');

$p->SetPlotType('lines');
$p->DrawGraph();
