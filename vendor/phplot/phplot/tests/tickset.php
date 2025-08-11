<?php

# Testing phplot - Setting tick increment and/or number of ticks
require_once 'phplot.php';
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
extract(array_merge(array(
  'numxtick' => NULL,         // Number of X tick marks, NULL to not set
  'numytick' => NULL,         // Number of Y tick marks, NULL to not set
  'xtickinc' => NULL,         // X tick increment, NULL to not set
  'ytickinc' => NULL,         // Y tick increment, NULL to not set
  'set_num_ticks_first' => TRUE,   // Set number of ticks before increment
  'xmin' => NULL,             // Min X plot range for SetPlotAreaWorld
  'xmax' => NULL,             // Max X plot range for SetPlotAreaWorld
  'ymin' => NULL,             // Min Y plot range for SetPlotAreaWorld
  'ymax' => NULL,             // Max Y plot range for SetPlotAreaWorld
        ), $tp));
require_once 'phplot.php';

$data = array(
    array('A', 2, 3, 9),
    array('B', 3, 6, 8),
    array('C', 4, 9, 7),
    array('D', 5, 6, 6),
    array('E', 4, 3, 4),
    array('F', 3, 6, 2),
);

function do_numticks(&$subtitle, $p, $numxtick, $numytick)
{
    if (isset($numxtick)) {
        $p->SetNumXTicks($numxtick);
        $subtitle[] = "Set num X ticks=$numxtick";
    }
    if (isset($numytick)) {
        $p->SetNumYTicks($numytick);
        $subtitle[] = "Set num Y ticks=$numytick";
    }
}

function do_tickinc(&$subtitle, $p, $xtickinc, $ytickinc)
{
    if (isset($xtickinc)) {
        $p->SetXTickIncrement($xtickinc);
        $subtitle[] = "Set X tick inc=$xtickinc";
    }
    if (isset($ytickinc)) {
        $p->SetYTickIncrement($ytickinc);
        $subtitle[] = "Set Y tick inc=$ytickinc";
    }
}

function do_setrange(&$subtitle, $p, $xmin, $xmax, $ymin, $ymax)
{
    if (isset($xmin) || isset($xmax) || isset($ymin) || isset($ymax)) {
        $ss = "\nSet Range:";
        if (isset($xmin)) $ss .= " Xmin=$xmin";
        if (isset($xmax)) $ss .= " Xmax=$xmax";
        if (isset($ymin)) $ss .= " Ymin=$ymin";
        if (isset($ymax)) $ss .= " Ymax=$ymax";
        $subtitle[] = $ss;
        $p->SetPlotAreaWorld($xmin, $ymin, $xmax, $ymax);
    }
}

$title = "Tick Parameter Settings\n"; // More below
$subtitle = array();

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');

if ($set_num_ticks_first) do_numticks($subtitle, $p, $numxtick, $numytick);
do_tickinc($subtitle, $p, $xtickinc, $ytickinc);
if (!$set_num_ticks_first) do_numticks($subtitle, $p, $numxtick, $numytick);
do_setrange($subtitle, $p, $xmin, $xmax, $ymin, $ymax);

if (empty($subtitle)) $subtitle = array('Baseline - auto');
$p->SetTitle($title . implode('; ', $subtitle));

$p->DrawGraph();
