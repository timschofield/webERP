<?php

DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_3');
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_4');
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_5');
DropConstraint('stockitemproperties', 'stockitemproperties_ibfk_6');

DropConstraint('stockrequestitems', 'stockrequestitems_ibfk_3');
DropConstraint('stockrequestitems', 'stockrequestitems_ibfk_4');

UpdateDBNo(basename(__FILE__, '.php'), __('Drop duplicate foreign keys'));
