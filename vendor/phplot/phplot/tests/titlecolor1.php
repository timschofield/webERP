<?php

# Testing phplot - Title Colors. Case 1 - 3 different colors (main, X, Y)
# Other scripts can set $c1, $c2, and/or $c3 to a color or NULL to set the
# main, X, and/or Y titles, then include this script.
require_once 'phplot.php';
if (empty($c1) && empty($c2) && empty($c3)) {
    $c1 = 'red';
    $c2 = 'blue';
    $c3 = 'green';
}

$subtitle = 'Main: ' . (empty($c1)? 'default' : $c1)
          . ', X: ' . (empty($c2)? 'default' : $c2)
          . ', Y: ' . (empty($c3)? 'default' : $c3);

$data = array(
  array('A', 0),
  array('B', 1),
  array('C', 2),
);

$p = new Phplot\Phplot\phplot(600, 600);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTitle("Title color test\n$subtitle");
$p->SetXTitle("Title color test - X title", 'both');
$p->SetYTitle("Title color test - Y title", 'both');
if (!empty($c1)) $p->SetTitleColor($c1);
if (!empty($c2)) $p->SetXTitleColor($c2);
if (!empty($c3)) $p->SetYTitleColor($c3);
$p->DrawGraph();
