-- Convert prices to use non- SQL mode specific end date we will have a year 10000 problem but its a way off!:
UPDATE prices SET enddate='9999-12-31' WHERE enddate='0000-00-00';
CREATE table favourites (userid varchar(20) NOT NULL DEFAULT '',
	caption varchar(50) NOT NULL DEFAULT '',
	href varchar(200) NOT NULL DEFAULT '#',
	PRIMARY KEY (userid,caption)) Engine=InnoDB DEFAULT CHARSET=utf8;

-- Update version number:
UPDATE config SET confvalue='4.14' WHERE confname='VersionNumber';
