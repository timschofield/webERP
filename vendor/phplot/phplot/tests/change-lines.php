<?php

# From PHPlot Help Forum, 2005-04-10: Lines which change color and from
# solid to dash. This produced a bug report which has been fixed.
require_once 'phplot.php';

$graph = new Phplot\Phplot\phplot(800,600);
$graph->setNumXTicks(1);
$graph->setPrecisionY(0);
$graph->SetDataColors(array("blue","red","green","red","blue","green"));
$graph->SetLineStyles(array('solid','solid','solid','dashed','dashed','dashed'))
;
$graph->SetTitle('Change Line Color and Style (Solid/Dashed)');

$example_data = array(
     array('Val 1',90,85,63,"","",""),
     array('Val 2',93,86,64,"","",""),
     array('Val 3',93,87,64,"","",""),
     array('Val 4',86,78,54,"","",""),
     array('Val 5',88,80,53,"","",""),
     array('Val 6',89,81,50,89,81,50),
     array('Val 7',"","","",90,82,50),
     array('Val 8',"","","",90,82,50),
     array('Val 9',"","","",92,83,49)
);

$graph->SetDataValues($example_data);
$graph->DrawGraph();
