<?php

ChangeColumnSize('password', 'emailsettings', 'VARCHAR(100)', ' NOT NULL ', '', '100');

UpdateDBNo(basename(__FILE__, '.php'), _('Increase the potential size of the SMPT server password to 100 chars'));
