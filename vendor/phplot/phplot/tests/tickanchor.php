<?php

# PHPlot test: tick anchor points, master script
require_once 'phplot.php';
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'subtitle' => 'Baseline',    # Sub-title
  'start' => -10,              # Data min value
  'stop' => 10,                # Data max value
  'delta' => 3,                # Data step value
  'x_tick_anchor' => NULL,     # Anchor for X ticks or NULL to not set one
  'y_tick_anchor' => NULL,     # Anchor for Y ticks or NULL to not set one
  'x_tick_step' => NULL,       # Increment for X ticks or NULL to not set one
  'y_tick_step' => NULL,       # Increment for Y ticks or NULL to not set one
        ), $tp);

extract($tp); // Import all parameters

# Printing helper
function prif($name, &$var, $before = '')
{
  return "$before $name=" . (isset($var) ? $var : "n/a");
}

# Auto title:
$title = 'X/Y Tick Anchors' . (empty($subtitle)? "\n" : " - $subtitle\n")
 . prif('X tick anchor', $x_tick_anchor)
 . prif('Y tick anchor', $y_tick_anchor, ',')
 . prif('X tick inc.', $x_tick_step, ',')
 . prif('Y tick inc.', $y_tick_step, ',');

$data = array();
for ($x = $start; $x <= $stop; $x += $delta) $data[] = array('', $x, $x);

$plot = new Phplot\Phplot\phplot(800, 600);
$plot->SetTitle($title);
$plot->SetPlotType('lines');
$plot->SetDataType('data-data');
$plot->SetDataValues($data);
$plot->SetDrawXGrid(True);
$plot->SetDrawYGrid(True);

if (isset($x_tick_step)) $plot->SetXTickIncrement($x_tick_step);
if (isset($y_tick_step)) $plot->SetYTickIncrement($y_tick_step);
if (isset($x_tick_anchor)) $plot->SetXTickAnchor($x_tick_anchor);
if (isset($y_tick_anchor)) $plot->SetYTickAnchor($y_tick_anchor);

$plot->DrawGraph();
