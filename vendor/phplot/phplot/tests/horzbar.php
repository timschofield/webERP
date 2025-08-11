<?php

# PHPlot test: horizontal bars
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Horizontal Bar Chart',   # First or only line
  'suffix' => "\nBaseline Case",       # Title part 2
  'nrows' => 10,                # Number of bar groups
  'ncols' => 3,                 # Number of bars per group
  'horizontal' => True,         # Horizontal or vertical bars
  'shade' => NULL,              # Bar shading, NULL to skip
  'longlabel' => False,         # Data variation: long data label
  'negative' => 0,              # Make every nth data value negative
  'yaxis0' => False,            # Move Y axis to 0 if true
  'plotarea' => NULL,           # Array[4] for SetPlotAreaWorld, or NULL.
  'ydatalabelpos' => NULL,      # Y data label position (SetYDataLabelPos)
  'ydatalabelangle' => NULL,    # Y data label angle
  'ttf' => False,               # Use all TTF text
        ), $tp);
require_once 'phplot.php';
require_once 'config.php';
// require_once 'debug.php'; // DEBUG

# Generate and return a text-data data array:
function gen_data($rows, $points)
{
    $data = array();
    for ($r = 0; $r < $rows; $r++) {
        $row = array("Row$r");
        for ($c = 0; $c < $points; $c++)
            $row[] = $r + $c + 1;
        $data[] = $row;
    }
    return $data;
}

$data = gen_data($tp['nrows'], $tp['ncols']);

if ($tp['longlabel'])
    $data[min(3, $tp['nrows']-1)][0] = 'Long Label Test';

if ($tp['negative'] > 0) {
    $phase = $tp['negative'];
    for ($r = 0; $r < $tp['nrows']; $r++) {
        for ($c = 1; $c <= $tp['ncols']; $c++) {
            if (--$phase <= 0) {
              $data[$r][$c] *= -1;
              $phase = $tp['negative'];
            }
        }
    }
}

$p = new Phplot\Phplot\phplot(800, 800);
// $p->SetCallback('debug_scale', 'debug_handler'); // DEBUG

$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetXTitle('This is the X Axis Title');
$p->SetYTitle('This is the Y Axis Title');
$p->SetDataValues($data);
if ($tp['horizontal'])
  $p->SetDataType('text-data-yx');
else
  $p->SetDataType('text-data');
// phplot_show_data_array('', $data, $p->data_type); // DEBUG

$p->SetPlotType('bars');
if (isset($tp['shade']))
  $p->SetShading($tp['shade']);

if (isset($tp['plotarea']))
  call_user_func_array(array($p, 'SetPlotAreaWorld'), $tp['plotarea']);

if ($tp['ttf']) {
  $p->SetTTFPath($phplot_test_ttfdir);
  $p->SetDefaultTTFont($phplot_test_ttfonts['sans']);
}

if ($tp['yaxis0'])
  $p->SetYAxisPosition(0);
if (isset($tp['ydatalabelpos']))
  $p->SetYDataLabelPos($tp['ydatalabelpos']);
if (isset($tp['ydatalabelangle']))
  $p->SetYDataLabelAngle($tp['ydatalabelangle']);

$p->SetYTickPos('none');
$p->DrawGraph();
