<?php

# Testing phplot - Default TT font (1a): No default path or font set, usettf
require_once 'phplot.php';

// Extend PHPlot class to allow access to protected variable(s):
class PHPlot_pv extends Phplot\Phplot\phplot {
    public function GET_default_ttfont() { return $this->default_ttfont; }
}

$data = array(
  array('A', 3,  6),
  array('B', 2,  4),
  array('C', 1,  2),
);
$p = new phplot_pv(800, 800);
$p->SetDataType('text-data');
$p->SetDataValues($data);
$p->SetPlotType('bars');
$p->SetTitle('TTF default not set, SetUseTTF(true)');
$p->SetXTitle('X Axis Title');
$p->SetYTitle('Y Axis Title');
$p->SetUseTTF(True);
$p->DrawGraph();
fwrite(STDERR, "OK defaultfont1a: default_ttfont="
               . $p->GET_default_ttfont() . "\n");
