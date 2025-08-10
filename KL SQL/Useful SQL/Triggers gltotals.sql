DELIMITER //

CREATE TRIGGER gltrans_after_insert AFTER INSERT ON gltrans FOR EACH ROW
BEGIN
    INSERT INTO gltotals (account, period, amount)
    VALUES (NEW.account, NEW.periodno, NEW.amount)
    ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
END //

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
END //

CREATE TRIGGER `gltrans_after_delete` AFTER DELETE ON `gltrans` FOR EACH ROW
BEGIN
    UPDATE gltotals
    SET amount = amount - OLD.amount
    WHERE account = OLD.account AND period = OLD.periodno;
END //

DELIMITER ;



CREATE TRIGGER `gltrans_after_delete` AFTER DELETE ON `gltrans`
 FOR EACH ROW BEGIN
    UPDATE gltotals
    SET amount = amount - OLD.amount
    WHERE account = OLD.account AND period = OLD.periodno;
END

CREATE TRIGGER `gltrans_after_insert` AFTER INSERT ON `gltrans`
 FOR EACH ROW BEGIN
    INSERT INTO gltotals (account, period, amount)
    VALUES (NEW.account, NEW.periodno, NEW.amount)
    ON DUPLICATE KEY UPDATE amount = amount + NEW.amount;
END

CREATE TRIGGER `gltrans_after_update` AFTER UPDATE ON `gltrans`
 FOR EACH ROW BEGIN
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
END

CREATE TRIGGER `klstockmarketplaces_creation_timestamp` BEFORE INSERT ON `klstockmarketplaces`
 FOR EACH ROW SET NEW.date_created = NOW()
 
 
 111204030
 
 111311100
 
 111800000
 111800100
 111900000

211021400
211021500

DELETE FROM `gltrans` WHERE `account` = "111800000AD";
DELETE FROM `chartdetails` WHERE `accountcode` = "111800000AD";
DELETE FROM `chartmaster` WHERE `accountcode` = "111800000AD";
DELETE FROM `glaccountusers` WHERE `accountcode` = "111800000AD";

DELETE FROM `gltrans` WHERE `account` = "111800100AD";
DELETE FROM `chartdetails` WHERE `accountcode` = "111800100AD";
DELETE FROM `chartmaster` WHERE `accountcode` = "111800100AD";
DELETE FROM `glaccountusers` WHERE `accountcode` = "111800000AD";