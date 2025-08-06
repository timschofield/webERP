<?php

# Testing phplot - "Stock market" plot, using error bars
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Financial Market Graph (data-data-error), low/close/high',
  'suffix' => "",           # Title part 2
  'EBShape' => NULL,        # ErrorBarShape: tee or line or NULL to omit
  'EBLWidth' => NULL,       # ErrorBarLineWidth: integer or NULL to omit
  'EBColors' => NULL,       # ErrorBarColors: color or arran or NULL to omit
  'EBSize' => NULL,         # ErrorBarSize: integer pixels or NULL to omit
        ), $tp);

require_once 'phplot.php';


# The initial data is rows of (date, low, close, high). This will
# be converted below to the format for data type 'data-data-error'.
# 'close' on each day must be between low and high for next day.
$data = array(
  array('Jan 03', 1, 12.1, 12.1, 14.0),
  array('Jan 04', 2, 11.2, 15.3, 18.3),
  array('Jan 05', 3, 15.3, 19.5, 22.9),
  array('Jan 06', 4, 12.6, 13.2, 15.5),
  array('Jan 07', 5,  9.3, 10.4, 13.2),
  array('Jan 10', 6,  9.6,  9.6, 11.7),
  array('Jan 11', 7,  9.0, 12.2, 13.3),
  array('Jan 12', 8, 12.2, 16.4, 18.2),
  array('Jan 13', 9, 12.6, 12.6, 20.8),
  array('Jan 14',10, 12.6, 21.4, 21.4),
);

# Convert the data array from "low, close, high"
# to "close, max gain, max loss", for data type data-data-error.
$n_rows = count($data);
for ($i = 0; $i < $n_rows; $i++) {
  $low = $data[$i][2];
  $close = $data[$i][3];
  $high = $data[$i][4];
  $data[$i][2] = $close;
  $data[$i][3] = $high - $close;
  $data[$i][4] = $close - $low;
}

# print_r($data);
# exit;

$p = new Phplot\Phplot\phplot(800,600);
$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetDataType('data-data-error');
$p->SetDataValues($data);
#print_r($p);
#exit;

# We don't use the data labels (all set to '') so might as well turn them off:
#$p->SetXDataLabelPos('none');
# Turn off unused ticks and tick labels
$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');

# Need to set area and ticks to get reasonable choices.
$p->SetPlotAreaWorld(0, 0, 11, 25);
$p->SetXTickIncrement(1);

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);  # Is default

# Options for error bars:
if (isset($tp['EBShape'])) $p->SetErrorBarShape($tp['EBShape']);
if (isset($tp['EBLWidth'])) $p->SetErrorBarLineWidth($tp['EBLWidth']);
if (isset($tp['EBColors'])) $p->SetErrorBarColors($tp['EBColors']);
if (isset($tp['EBSize'])) $p->SetErrorBarSize($tp['EBSize']);

$p->SetPlotType('lines');
$p->DrawGraph();
