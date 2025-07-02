CREATE TRIGGER `gltrans_after_update` AFTER UPDATE ON `gltrans` FOR EACH ROW
BEGIN
	IF NEW.account <> OLD.account OR NEW.periodno <> OLD.periodno THEN
		-- Handle account or period changes.
		-- Deduct the old amount from the old account/period.
		UPDATE gltotals
		SET amount = amount - OLD.amount
		WHERE account = OLD.account AND period = OLD.periodno;

		-- Add the new amount to the new account/period.
		INSERT INTO gltotals (account, period, amount)
		VALUES (NEW.account, NEW.periodno, NEW.amount)
		ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
	ELSE
		-- Just update the amount if account and period are the same.
		UPDATE gltotals
		SET amount = amount - OLD.amount + NEW.amount
		WHERE account = NEW.account AND period = NEW.periodno;
	END IF;
END;
