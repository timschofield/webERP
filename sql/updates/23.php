<?php

ChangeColumnDefault('querystring', 'audittrail', 'text', 'NULL', NULL);
ChangeColumnDefault('invtext', 'debtortrans', 'text', 'NULL', NULL);
ChangeColumnDefault('description', 'glbudgetheaders', 'text', 'NULL', NULL);
ChangeColumnDefault('purpose', 'pcashdetails', 'text', 'NULL', NULL);
ChangeColumnDefault('receipt', 'pcashdetails', 'text', 'NULL', NULL);
ChangeColumnDefault('comments', 'purchorders', 'longblob', 'NULL', NULL);
ChangeColumnDefault('params', 'reportfields', 'text', 'NULL', NULL);
ChangeColumnDefault('comments', 'salesorders', 'longblob', 'NULL', NULL);
ChangeColumnDefault('internalcomment', 'salesorders', 'blob', 'NULL', NULL);
ChangeColumnDefault('longdescriptiontranslation', 'stockdescriptiontranslations', 'text', 'NULL', NULL);
ChangeColumnDefault('narrative', 'stockmoves', 'text', 'NULL', NULL);
ChangeColumnDefault('transtext', 'supptrans', 'text', 'NULL', NULL);
ChangeColumnDefault('comments', 'woitems', 'longblob', 'NULL', NULL);
ChangeColumnDefault('closecomments', 'workorders', 'longblob', 'NULL', NULL);
ChangeColumnDefault('remark', 'workorders', 'text', 'NULL', NULL);

UpdateDBNo(basename(__FILE__, '.php'), __('Set default to nullable fields'));
