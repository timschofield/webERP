<?php

# Testing phplot - "SAR Data" (whatever that is), from a posting on Help
# "What I would like to see is ALL the data in the graph (all ticks), but only
# X-axis labels for every four hours,ie. 04:00, 08:00, 12:00, 16:00, 20:00
# and 00:00."

require_once 'phplot.php';

# Random data for 24 hours at 10 minute intervals, with dummy 0,0 entry:
$data[0] = array('00:00', 0, NULL);
for ($i = 1; $i < 48 * 6; $i++) {
  $data[] = array(sprintf("%02d:%02d", $i / 6, ($i % 6) * 10), $i, 10.0 + 10.0*sin($i * M_PI/50.0));
}

# Wipe all labels except every 4 hours:
$n = count($data);
for ($i = 0; $i < $n; $i++) if ($i % 24 != 0) $data[$i][0] = '';

$p = new Phplot\Phplot\phplot(1024, 768);
$p->SetTitle('Test: intermittent X labels');
$p->SetDataType('data-data');
$p->SetDataValues($data);

# Use data labels along with tick marks. Needs SetPlotAreaWord(0,0) to get them
# to line up.
$p->SetXTickLabelPos('none');
$p->SetXTickIncrement(6);
$p->SetYTickIncrement(5);
#$p->SetDrawXGrid(true);
$p->SetPlotAreaWorld(0, 0);

$p->SetPlotType('points');
$p->DrawGraph();
