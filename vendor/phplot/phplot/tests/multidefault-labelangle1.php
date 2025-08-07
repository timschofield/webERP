<?php

# Testing PHPlot - Multi-plot X data label angle issue
# This shows an issue with X data label angle when doing multiple plotss.
# X data label angle defaults to X label angle, but the applied default is
# stored back into the class, where it becomes indistinguisable from a user
# setting. So if X label angle changes later, X data label angle won't.

require_once 'phplot.php';

$data = array(
    array('Jan', 10),
    array('Feb', 20),
    array('Mar', 30),
    array('Apr', 40),
    array('May', 30),
    array('Jun', 20),
);

// Common setup:
$plot = new Phplot\Phplot\phplot(460, 600);
$plot->SetPrintImage(False);
$plot->SetTitle("Multiple Plots, X data label angle\n"
              . "X label angle 90 (top), 0 (bottom)");
$plot->SetPlotType('bars');
$plot->SetDataValues($data);
$plot->SetYTickPos('none');
$plot->SetDataType('text-data-yx');
$plot->SetXDataLabelPos('plotin');

// Plot #1:
$plot->SetPlotAreaPixels(NULL, 60, NULL, 300);
$plot->SetXLabelAngle(90);
$plot->DrawGraph();

// Plot #2:
$plot->SetPlotAreaPixels(NULL, 330, NULL, 570);
$plot->SetXLabelAngle(0);
$plot->DrawGraph();

// Finish:
$plot->PrintImage();
