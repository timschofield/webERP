UPDATE kurakura_kl_erp.stockdescriptiontranslations SET date_updated = NOW();
DELETE FROM kl_online_shop.oc_product_description WHERE `language_id` = 2;
