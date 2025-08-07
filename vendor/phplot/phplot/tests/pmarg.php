<?php

# Test partial margin specification with SetMarginPixels or SetPlotAreaPixels
# Requires PHPlot > 5.0.6
# Mostly this is copied from the unit test u.pmarg.php which tests many
# more cases but without producing pictures.

# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Partial Margin Specification Test',  # First or only line
  'suffix' => '',           # Title part 2
  'doSetMarginsPixels' => False,   # Call SetMarginsPixels?
  'MarginsPixels' => array(NULL,NULL,NULL,NULL),  # Args for SetMarginsPixels
  'doSetPlotAreaPixels' => False,   # Call SetSetPlotAreaPixels?
  'PlotAreaPixels' => array(NULL,NULL,NULL,NULL),  # Args for SetPlotAreaPixels
        ), $tp);
require_once 'phplot.php';

$data = array(
  array('Jan', 100, 200, 300),
  array('Feb', 120, 190, 240),
  array('Mar', 130, 180, 290),
  array('Apr', 140, 170, 260),
  array('May', 120, 160, 200),
  array('Jun', 130, 150, 220),
);
define('PLOT_WIDTH', 1024);
define('PLOT_HEIGHT', 768);

$p = new Phplot\Phplot\phplot(1024, 768);
$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetXTickLabelPos('none');
$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(10.0);
$p->SetYTickPos('both');
$p->SetYTickLabelPos('both');
$p->SetDrawXGrid(False);
$p->SetDrawYGrid(True);
$p->SetXTitle("Two Line\nX Axis Title");
$p->SetYTitle("Three Line\nY Axis\nTitle");
$p->SetPlotAreaWorld(NULL, 0, NULL, NULL);
if ($tp['doSetMarginsPixels'])
  $p->SetMarginsPixels($tp['MarginsPixels'][0],
                       $tp['MarginsPixels'][1],
                       $tp['MarginsPixels'][2],
                       $tp['MarginsPixels'][3]);
if ($tp['doSetPlotAreaPixels'])
  $p->SetPlotAreaPixels($tp['PlotAreaPixels'][0],
                        $tp['PlotAreaPixels'][1],
                        $tp['PlotAreaPixels'][2],
                        $tp['PlotAreaPixels'][3]);
$p->DrawGraph();
