CREATE TRIGGER `gltrans_after_delete` AFTER DELETE ON `gltrans` FOR EACH ROW
BEGIN
	UPDATE gltotals
	SET amount = amount - OLD.amount
	WHERE account = OLD.account AND period = OLD.periodno;
END;
