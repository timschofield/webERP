<?php

# PHPlot test: horizontal bars with data value labels, baseline
# This is a parameterized test. Other scripts can set $tp and then include
# this script. The parameters are shown in the defaults array below:
if (!isset($tp)) $tp = array();
$tp = array_merge(array(
  'title' => 'Horizontal Bar Chart with Data Value Labels',# First or only line
  'suffix' => "\nBaseline Case",       # Title part 2
  'datasign' => 1,              # 1 for all >= 0, -1 for all <= 0, 0 for all
  'yaxis0' => False,            # Move Y axis to 0 if true
  'labelformat' => False,       # Format data labels ?
  'labelangle' => NULL,         # X data label angle
  'ttf' => False,               # Use TTF text for labels
  'shade' => NULL,              # Bar shading, NULL for none or 0 or >0
        ), $tp);
require_once 'phplot.php';
require_once 'config.php';

$data1 = array(
  array('Pos1',  10),
  array('Pos2',   0),
  array('Pos3',  40),
  array('Pos4',  80),
  array('Pos5',  30),
);
$data2 = array(
  array('Neg1', -10),
  array('Neg2', -60),
  array('Neg3',  -5),
  array('Neg4', -30),
  array('Neg6',   0),
);

if ($tp['datasign'] > 0) $data = $data1;
elseif ($tp['datasign'] < 0) $data = $data2;
else $data = array_merge($data1, $data2);

$p = new Phplot\Phplot\phplot(800, 800);

$p->SetTitle($tp['title'] . $tp['suffix']);
$p->SetXTitle('This is the X Axis Title');
$p->SetYTitle('This is the Y Axis Title');
$p->SetDataValues($data);
$p->SetDataType('text-data-yx');
$p->SetPlotType('bars');
$p->SetXDataLabelPos('plotin');

if (isset($tp['shade']))
  $p->SetShading($tp['shade']);

if ($tp['ttf'])
  $p->SetFontTTF('x_label',
     $phplot_test_ttfdir.$phplot_test_ttfonts['sans'], 10);

if ($tp['yaxis0'])
  $p->SetYAxisPosition(0);

if (isset($tp['labelangle']))
  $p->SetXDataLabelAngle($tp['labelangle']);

if ($tp['labelformat'])
  $p->SetXDataLabelType('data', 1, '', '%');

$p->SetYTickPos('none');
$p->DrawGraph();
