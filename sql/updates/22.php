<?php

ChangeColumnName('last_poll', 'sessions', 'timestamp', 'NOT NULL', 'CURRENT_TIMESTAMP', 'logintime', '');
AddColumn('userid', 'sessions', 'varchar(20)', '', '', 'logintime');
AddColumn('script', 'sessions', 'varchar(100)', 'NOT NULL', '', 'userid');
AddColumn('scripttime', 'sessions', 'timestamp', 'NULL','', 'script');

DropTable('login_data');

UpdateDBNo(basename(__FILE__, '.php'), __('Improved session control'));
