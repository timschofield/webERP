<?php

# Testing PHPlot - Bad data range with SetPlotAreaWorld
# See the script named below for details
$subtitle = 'Xmin==Xmax, Ymin Ymax unset'; // Subtitle for plot
$spaw = array(100, NULL, 100, NULL); // Args to SetPlotAreaWorld
require 'error-SetPlotAreaWorld.php';
