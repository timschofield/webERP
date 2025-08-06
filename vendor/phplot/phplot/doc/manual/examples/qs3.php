<?php
//Include the code
require_once __DIR__ . '/../../../src/phplot.php';

//Define the object
$plot = new Phplot\Phplot\phplot(800,600);

//Set titles
$plot->SetTitle("A 3-Line Plot\nMade with PHPlot");
$plot->SetXTitle('X Data');
$plot->SetYTitle('Y Data');


//Define some data
$example_data = array(
     array('a',3,4,2),
     array('b',5,'',1),  // here we have a missing data point, that's ok
     array('c',7,2,6),
     array('d',8,1,4),
     array('e',2,4,6),
     array('f',6,4,5),
     array('g',7,2,3)
);
$plot->SetDataValues($example_data);

//Turn off X axis ticks and labels because they get in the way:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');

//Draw it
$plot->DrawGraph();
