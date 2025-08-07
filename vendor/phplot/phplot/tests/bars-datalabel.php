<?php
# Testing phplot - Bars, with new (post-5.0rc2) datalabel feature
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Bar chart with data labels',
  'suffix' => " 4x1",       # Title part 2
  'ngroups' => 4,           # Number of bar groups
  'nbars' => 1,             # Number of bars per group
  'fontsize' => NULL,       # Label font size or NULL to omit
  'shading' => NULL,        # SetShading: 0 or pixels or NULL to omit
  'yprec' => NULL,          # Y formatting precision (digits), NULL to omit
  'data' => NULL,           # Set if necessary to override the data array
        ), $tp);
require_once 'phplot.php';

if (isset($tp['data'])) {
  $data = $tp['data'];
} else {
  # Generate a data array with the requested number of bars and groups.
  # Want some increase, decrease; positive, negative, zero.
  $data = array();
  $dtheta = M_PI / 6.0;
  $r = 12.0;
  $cdt = cos($dtheta);
  $sdt = sin($dtheta);
  $x = $r;
  $y = 0.0;
  for ($grp = 1; $grp <= $tp['ngroups']; $grp++) {
    $row = array("=$grp=");
    for ($bar = 1; $bar <= $tp['nbars']; $bar++) {
      $row[] = floor($x + 0.5);
      $tx = $x * $cdt - $y * $sdt;
      $y  = $x * $sdt + $y * $cdt;
      $x = $tx;
    }
    $data[] = $row;
  }
}

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetDataType('text-data');
$p->SetDataValues($data);

# Options:
#  Note: <=5.0rc3 used x_label font. This was corrected.
if (isset($tp['fontsize'])) $p->SetFont('y_label', $tp['fontsize']);
if (isset($tp['shading'])) $p->SetShading($tp['shading']);
# Note: This didn't work <= 5.0rc3
if (isset($tp['yprec'])) $p->SetPrecisionY($tp['yprec']);

$p->SetXTickLabelPos('none');
$p->SetXTickPos('none');
$p->SetYDataLabelPos('plotin');
$p->SetPlotType('bars');
$p->DrawGraph();
