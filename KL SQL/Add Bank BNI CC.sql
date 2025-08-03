ALTER TABLE `klretailpartners` ADD `accountbankbni` VARCHAR(20) 
CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'GL account for Bank BNI CC transactions' AFTER `accountbankdanamon`;

ALTER TABLE `klretailpartners` ADD `comissionccbni` DECIMAL(5,2) 
NOT NULL COMMENT '% of Credit card comission paid to BNI' AFTER `comissionamexdanamon`, 

ADD `comissionamexbni` DECIMAL(5,2) 
NOT NULL COMMENT '% of Credit card comission paid to American Express by BNI' AFTER `comissionccbni`;
