<?php

# Dual text types: GD and TTF, default TTF, GD X and Y titles
# This requires PHPlot > 5.0.5
require_once 'phplot.php';

# TTF Font info is in this configuration file:
require 'config.php';

$data = array(array('', 0, 0, 0), array('', 10, 5, 10));

$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("Dual Text Types:\nDefault TTF, TTF title");
$p->SetDataType('data-data');
$p->SetDataValues($data);
$p->SetXTitle('Title of X Data, GD Font 5');
$p->SetYTitle('Title of Y Data, GD Font 3');

$p->SetTTFPath($phplot_test_ttfdir);
$p->SetDefaultTTFont($phplot_test_ttfonts['serifitalic']);
$p->SetFont('title', $phplot_test_ttfonts['serifbold'], 36);
$p->SetFontGD('x_title', 5);
$p->SetFontGD('y_title', 3);

$p->SetXDataLabelPos('none');
$p->SetLegend(array("Legend Line 1", "Legend Line 2"));

$p->SetXTickIncrement(1.0);
$p->SetYTickIncrement(1.0);

# Draw both grids:
$p->SetDrawXGrid(True);
$p->SetDrawYGrid(True);

$p->SetPlotType('lines');
$p->DrawGraph();
