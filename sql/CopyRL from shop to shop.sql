UPDATE locstock AS loc1, locstock AS loc2
 	SET loc1.reorderlevel = loc2.reorderlevel
 WHERE  loc1.loccode = "TOKU2"
 	AND loc2.loccode = "TOKOB";
