UPDATE locstock AS loc1, locstock AS loc2
	SET loc1.reorderlevel = loc2.reorderlevel
WHERE  loc1.loccode = "TOKOB"
	AND loc2.loccode = "TOKUB"
	AND loc1.stockid = loc2.stockid;
	