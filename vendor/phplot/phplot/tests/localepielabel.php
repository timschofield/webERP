<?php

# PHPlot test: Error (notice level) formatting pie label in PT locale
# Bug #3488064 "Pie label failures in locales with comma decimal"
require_once 'phplot.php';

# Note: This changes the locale to one with comma for decimal point
# separator, but this only works on Linux, not on Windows. To duplicate
# this on Windows requires changing the Regional settings in Control
# Panel.
putenv("LC_ALL=pt_BR");

# Note: This is the same data as ex-pie1 in the manual (without labels)
$data = array(
  array('', 7849),
  array('', 299),
  array('', 5447),
  array('', 944),
  array('', 541),
  array('', 3215),
  array('', 791),
  array('', 19454),
  array('', 311),
  array('', 9458),
  array('', 9710),
);


$p = new Phplot\Phplot\phplot(800, 600);
$p->SetTitle("Pie Chart with Labels\n"
         .   "Using a locale where numbers look like: 12.345,67");
$p->SetDataType('text-data-single');
$p->SetDataValues($data);
$p->SetPlotType('pie');
$p->DrawGraph();
