UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "EMAIL-AUD";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "EMAIL-AUD";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "EMAIL-AUD";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "EMAIL-AUD";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "EMAIL-AUD";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "EMAIL-AUD";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "EMAIL-AUD";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "EMAIL-AUD";
DELETE FROM custbranch WHERE debtorno = "EMAIL-AUD";
DELETE FROM custbranch WHERE branchcode = "EMAIL-AUD";
DELETE FROM debtorsmaster WHERE debtorno = "EMAIL-AUD";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "EMAIL-IDR";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "EMAIL-IDR";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "EMAIL-IDR";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "EMAIL-IDR";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "EMAIL-IDR";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "EMAIL-IDR";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "EMAIL-IDR";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "EMAIL-IDR";
DELETE FROM custbranch WHERE debtorno = "EMAIL-IDR";
DELETE FROM custbranch WHERE branchcode = "EMAIL-IDR";
DELETE FROM debtorsmaster WHERE debtorno = "EMAIL-IDR";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "IIM01";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "IIM01";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "IIM01";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "IIM01";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "IIM01";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "IIM01";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "IIM01";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "IIM01";
DELETE FROM custbranch WHERE debtorno = "IIM01";
DELETE FROM custbranch WHERE branchcode = "IIM01";
DELETE FROM debtorsmaster WHERE debtorno = "IIM01";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "RETAILWEB";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "RETAILWEB";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "RETAILWEB";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "RETAILWEB";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "RETAILWEB";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "RETAILWEB";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "RETAILWEB";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "RETAILWEB";
DELETE FROM custbranch WHERE debtorno = "RETAILWEB";
DELETE FROM custbranch WHERE branchcode = "RETAILWEB";
DELETE FROM debtorsmaster WHERE debtorno = "RETAILWEB";

UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "UNKNOWN";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "UNKNOWN";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "UNKNOWN";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "UNKNOWN";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "UNKNOWN";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "UNKNOWN";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "UNKNOWN";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "UNKNOWN";
DELETE FROM custbranch WHERE debtorno = "UNKNOWN";
DELETE FROM custbranch WHERE branchcode = "UNKNOWN";
DELETE FROM debtorsmaster WHERE debtorno = "UNKNOWN";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno LIKE "WEB00%";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno LIKE "WEB00%";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno LIKE "WEB00%";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode LIKE "WEB00%";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust LIKE "WEB00%";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch LIKE "WEB00%";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno LIKE "WEB00%";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode LIKE "WEB00%";
DELETE FROM custbranch WHERE debtorno LIKE "WEB00%";
DELETE FROM custbranch WHERE branchcode LIKE "WEB00%";
DELETE FROM debtorsmaster WHERE debtorno LIKE "WEB00%";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "PENNYANDRE";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "PENNYANDRE";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "PENNYANDRE";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "PENNYANDRE";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "PENNYANDRE";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "PENNYANDRE";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "PENNYANDRE";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "PENNYANDRE";
DELETE FROM custbranch WHERE debtorno = "PENNYANDRE";
DELETE FROM custbranch WHERE branchcode = "PENNYANDRE";
DELETE FROM debtorsmaster WHERE debtorno = "PENNYANDRE";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "ANTIPODES";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "ANTIPODES";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "ANTIPODES";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "ANTIPODES";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "ANTIPODES";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "ANTIPODES";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "ANTIPODES";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "ANTIPODES";
DELETE FROM custbranch WHERE debtorno = "ANTIPODES";
DELETE FROM custbranch WHERE branchcode = "ANTIPODES";
DELETE FROM debtorsmaster WHERE debtorno = "ANTIPODES";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "WEBSLARK";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "WEBSLARK";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "WEBSLARK";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "WEBSLARK";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "WEBSLARK";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "WEBSLARK";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "WEBSLARK";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "WEBSLARK";
DELETE FROM custbranch WHERE debtorno = "WEBSLARK";
DELETE FROM custbranch WHERE branchcode = "WEBSLARK";
DELETE FROM debtorsmaster WHERE debtorno = "WEBSLARK";



UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "PATTISON";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "PATTISON";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "PATTISON";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "PATTISON";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "PATTISON";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "PATTISON";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "PATTISON";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "PATTISON";
DELETE FROM custbranch WHERE debtorno = "PATTISON";
DELETE FROM custbranch WHERE branchcode = "PATTISON";
DELETE FROM debtorsmaster WHERE debtorno = "PATTISON";

UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "NMIMPORT";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "NMIMPORT";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "NMIMPORT";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "NMIMPORT";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "NMIMPORT";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "NMIMPORT";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "NMIMPORT";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "NMIMPORT";
DELETE FROM custbranch WHERE debtorno = "NMIMPORT";
DELETE FROM custbranch WHERE branchcode = "NMIMPORT";
DELETE FROM debtorsmaster WHERE debtorno = "NMIMPORT";

UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "BYRATRIN";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "BYRATRIN";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "BYRATRIN";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "BYRATRIN";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "BYRATRIN";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "BYRATRIN";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "BYRATRIN";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "BYRATRIN";
DELETE FROM custbranch WHERE debtorno = "BYRATRIN";
DELETE FROM custbranch WHERE branchcode = "BYRATRIN";
DELETE FROM debtorsmaster WHERE debtorno = "BYRATRIN";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "BENEDID";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "BENEDID";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "BENEDID";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "BENEDID";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "BENEDID";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "BENEDID";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "BENEDID";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "BENEDID";
DELETE FROM custbranch WHERE debtorno = "BENEDID";
DELETE FROM custbranch WHERE branchcode = "BENEDID";
DELETE FROM debtorsmaster WHERE debtorno = "BENEDID";

UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "WEBSHOP";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "WEBSHOP";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "WEBSHOP";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "WEBSHOP";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "WEBSHOP";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "WEBSHOP";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "WEBSHOP";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "WEBSHOP";
DELETE FROM custbranch WHERE debtorno = "WEBSHOP";
DELETE FROM custbranch WHERE branchcode = "WEBSHOP";
DELETE FROM debtorsmaster WHERE debtorno = "WEBSHOP";

UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "VANGRAY";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "VANGRAY";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "VANGRAY";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "VANGRAY";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "VANGRAY";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "VANGRAY";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "VANGRAY";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "VANGRAY";
DELETE FROM custbranch WHERE debtorno = "VANGRAY";
DELETE FROM custbranch WHERE branchcode = "VANGRAY";
DELETE FROM debtorsmaster WHERE debtorno = "VANGRAY";

UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "OREILLY";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "OREILLY";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "OREILLY";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "OREILLY";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "OREILLY";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "OREILLY";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "OREILLY";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "OREILLY";
DELETE FROM custbranch WHERE debtorno = "OREILLY";
DELETE FROM custbranch WHERE branchcode = "OREILLY";
DELETE FROM debtorsmaster WHERE debtorno = "OREILLY";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "SHA";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "SHA";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "SHA";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "SHA";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "SHA";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "SHA";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "SHA";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "SHA";
DELETE FROM custbranch WHERE debtorno = "SHA";
DELETE FROM custbranch WHERE branchcode = "SHA";
DELETE FROM debtorsmaster WHERE debtorno = "SHA";

UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "NOVEL";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "NOVEL";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "NOVEL";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "NOVEL";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "NOVEL";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "NOVEL";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "NOVEL";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "NOVEL";
DELETE FROM custbranch WHERE debtorno = "NOVEL";
DELETE FROM custbranch WHERE branchcode = "NOVEL";
DELETE FROM debtorsmaster WHERE debtorno = "NOVEL";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "CALLAN";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "CALLAN";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "CALLAN";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "CALLAN";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "CALLAN";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "CALLAN";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "CALLAN";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "CALLAN";
DELETE FROM custbranch WHERE debtorno = "CALLAN";
DELETE FROM custbranch WHERE branchcode = "CALLAN";
DELETE FROM debtorsmaster WHERE debtorno = "CALLAN";


UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "XAVIER";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "XAVIER";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "XAVIER";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "XAVIER";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "XAVIER";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "XAVIER";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "XAVIER";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "XAVIER";
DELETE FROM custbranch WHERE debtorno = "XAVIER";
DELETE FROM custbranch WHERE branchcode = "XAVIER";
DELETE FROM debtorsmaster WHERE debtorno = "XAVIER";

UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "OMAKASE";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "OMAKASE";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "OMAKASE";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "OMAKASE";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "OMAKASE";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "OMAKASE";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "OMAKASE";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "OMAKASE";
DELETE FROM custbranch WHERE debtorno = "OMAKASE";
DELETE FROM custbranch WHERE branchcode = "OMAKASE";
DELETE FROM debtorsmaster WHERE debtorno = "OMAKASE";

UPDATE custcontacts SET debtorno = "OBSOLETE" WHERE debtorno = "OLDWEB0001";
UPDATE custnotes SET debtorno = "OBSOLETE" WHERE debtorno = "OLDWEB0001";
UPDATE debtortrans SET debtorno = "OBSOLETE" WHERE debtorno = "OLDWEB0001";
UPDATE debtortrans SET branchcode = "OBSOLETE" WHERE branchcode = "OLDWEB0001";
UPDATE salesanalysis SET cust = "OBSOLETE" WHERE cust = "OLDWEB0001";
UPDATE salesanalysis SET custbranch = "OBSOLETE" WHERE custbranch = "OLDWEB0001";
UPDATE salesorders SET debtorno = "OBSOLETE" WHERE debtorno = "OLDWEB0001";
UPDATE salesorders SET branchcode = "OBSOLETE" WHERE branchcode = "OLDWEB0001";
DELETE FROM custbranch WHERE debtorno = "OLDWEB0001";
DELETE FROM custbranch WHERE branchcode = "OLDWEB0001";
DELETE FROM debtorsmaster WHERE debtorno = "OLDWEB0001";
