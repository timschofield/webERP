<?php

# From: PHPlot Quickstart - 2
//Include the code
require_once 'phplot.php';

//create a PHPlot object with 800x600 pixel image
$plot = new Phplot\Phplot\phplot(800,600);

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

//Set titles
$plot->SetTitle("A Simple Plot\nMade with PHPlot");
$plot->SetXTitle('X Data');
$plot->SetYTitle('Y Data');

//Turn off X axis ticks and labels because they get in the way:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

//Draw it
$plot->DrawGraph();
