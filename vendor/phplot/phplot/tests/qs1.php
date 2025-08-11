<?php

# From: PHPlot Quickstart - 1
//Include the code
require_once 'phplot.php';

//Define the object
$plot = new Phplot\Phplot\phplot();

//Define some data
$example_data = array(
     array('a',3),
     array('b',5),
     array('c',7),
     array('d',8),
     array('e',2),
     array('f',6),
     array('g',7)
);
$plot->SetDataValues($example_data);

//Turn off X axis ticks and labels because they get in the way:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

//Draw it
$plot->DrawGraph();
