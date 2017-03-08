SET FOREIGN_KEY_CHECKS=0;

UPDATE pctabs SET tabcode = "CC-BCA-LAIA" WHERE tabcode = "LAIA-CC-BCA";
UPDATE pcashdetails SET tabcode = "CC-BCA-LAIA" WHERE tabcode = "LAIA-CC-BCA";

UPDATE pctabs SET tabcode = "CC-BCA-HARTONO" WHERE tabcode = "HARTONO-CC-BCA";
UPDATE pcashdetails SET tabcode = "CC-BCA-HARTONO" WHERE tabcode = "HARTONO-CC-BCA";

UPDATE pctabs SET tabcode = "CC-BCA-RICARD" WHERE tabcode = "RICARD-CC-BCA";
UPDATE pcashdetails SET tabcode = "CC-BCA-RICARD" WHERE tabcode = "RICARD-CC-BCA";

SET FOREIGN_KEY_CHECKS=1;
