<?php

# Dual text types: GD and TTF, default GD, with TTF Title and Y Title
# This requires PHPlot > 5.0.5
require_once 'phplot.php';

# TTF Font info is in this configuration file:
require 'config.php';

$data = array(array('', 0, 0, 0), array('', 10, 5, 10));

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("Dual Text Types:\nDefault GD, TTF title and Y title");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXTitle('Title of X Data, GD Font 5');
$p->SetYTitle('Title of Y Data, TTF Italic Bold');

$p->SetTTFPath($phplot_test_ttfdir);
# Must not call SetDefaultTTFont or it makes the default TTF.
$p->SetFontTTF('title', $phplot_test_ttfonts['serif'], 24);
$p->SetFont('x_title', 5);
$p->SetFontTTF('y_title', $phplot_test_ttfonts['serifbolditalic'], 14);

$p->SetXDataLabelPos('none');
$p->SetLegend(array("Legend Line 1", "Legend Line 2"));

$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(1.0);

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);

$p->SetPlotType('lines');
$p->DrawGraph();
