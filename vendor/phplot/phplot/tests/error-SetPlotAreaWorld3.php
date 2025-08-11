<?php

# Testing PHPlot - Bad data range with SetPlotAreaWorld
# See the script named below for details
$subtitle = 'Xmin>Xmax, Ymin>Ymax'; // Subtitle for plot
$spaw = array(200, 20, 200, 20); // Args to SetPlotAreaWorld
require 'error-SetPlotAreaWorld.php';
