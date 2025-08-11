DELETE FROM www_users WHERE blocked = 1;
DELETE FROM www_users WHERE userid LIKE "999-%" AND userid != "999-AR";
	
UPDATE www_users
SET userid = CONCAT('SPG-', SUBSTRING(userid, 1, 3))
WHERE fullaccess = 17 OR fullaccess = 22;



