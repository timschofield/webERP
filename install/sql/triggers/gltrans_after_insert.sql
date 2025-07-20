CREATE TRIGGER gltrans_after_insert AFTER INSERT ON gltrans FOR EACH ROW
BEGIN
	INSERT INTO gltotals (account, period, amount)
	VALUES (NEW.account, NEW.periodno, NEW.amount)
	ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
END;
