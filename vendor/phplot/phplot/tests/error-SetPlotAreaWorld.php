<?php
# Testing PHPlot - Bad data range with SetPlotAreaWorld - baseline/master
# Other scripts set $spaw and $subtitle and then include this script.
require 'phplot.php';
if (empty($spaw)) $spaw = array(20, 20, 200, 200); // Args to SetPlotAreaWorld
if (empty($subtitle)) $subtitle = 'Baseline(no error)'; // Subtitle for plot
$plot = new Phplot\Phplot\phplot();
$plot->SetTitle("SetPlotAreaWorld(" . implode(', ', $spaw) . ")\n$subtitle");
$plot->SetDataType('data-data');
$plot->SetDataValues(array(array('',  25, 25), array('', 195, 195)));
$plot->SetPlotType('lines');
call_user_func_array(array($plot, 'SetPlotAreaWorld'), $spaw);
$plot->DrawGraph();
