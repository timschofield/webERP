<?php

# Testing phplot: callback

# Array of known callbacks, from PHPlot source:
$cblist = array(
    'draw_setup',
    'draw_image_background',
    'draw_plotarea_background',
    'draw_titles',
    'draw_axes',
    'draw_graph',
    'draw_border',
    'draw_legend',
);
# For marking callbacks that get called:
foreach ($cblist as $cbname) $wasnt_called[$cbname] = True;

require_once 'phplot.php';

# Test callback function. $arg is the 'reason' value.
function cb($img, $arg)
{
  global $wasnt_called;
  static $x = 20;
  static $y = 20;
  static $color_index = -1; // Color not yet allocated.

  # Cannot output this in normal test mode or it thinks we failed.
  # fwrite(STDERR, "callback: $arg\n");
  # Make a color index. Use gray (146,146,146) for compatibility with
  # older versions of this test which blindly used color index 1.
  if ($color_index == -1)
      $color_index = imagecolorresolve($img, 146, 146, 146);

  imagestring($img, 3, $x, $y, "CB: $arg", $color_index);
  $y += 20;
  # Mark it has being called:
  unset($wasnt_called[$arg]);
}

function fatal($message)
{
  fwrite(STDERR, "ERROR: $message\n");
  exit(1);
}

$data = array(
  array('A', -3,  6),
  array('B', -2,  4),
  array('C', -1,  2),
  array('D',  0,  0),
  array('E',  1, -2),
  array('F',  2, -4),
  array('G',  3, -6),
);
$p = new Phplot\Phplot\phplot(400,300);
foreach ($cblist as $cbname) {
  if (!$p->SetCallback($cbname, 'cb', $cbname))
    fatal("Invalid callback: $cbname");
}

# Test valid/invalid callbacks:
if ($p->SetCallback('no-such-callback', 'cb'))
  fatal("Did not return false for invalid SetCallback reason");
if ($p->GetCallback('no-such-callback', 'cb'))
  fatal("Did not return false for invalid GetCallback reason");
if ($p->RemoveCallback('no-such-callback', 'cb'))
  fatal("Did not return false for invalid RemoveCallback reason");
foreach ($cblist as $cbname) {
  $v = $p->GetCallback($cbname);
  if ($v != 'cb')
    fatal("GetCallback did not return function for: $cbname");
}

# Continue with plot...
$p->SetTitle('Callback Test');
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXDataLabelPos('none');
$p->SetLegend('Legend');
$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(1.0);
$p->SetPlotType('lines');
$p->DrawGraph();

# Check that all the callbacks were called:
if ($wasnt_called) {
  foreach ($wasnt_called as $cbname => $unused)
    fwrite(STDERR, " - $cbname wasn't called!\n");
  fatal("One or more callbacks never got called.");
}
