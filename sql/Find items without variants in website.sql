SELECT oc_product.product_id,
	oc_product.model,
	oc_product_description.name
FROM oc_product, oc_product_description
WHERE oc_product.product_id = oc_product_description.product_id
	AND oc_product_description.language_id = 1
	AND oc_product.status = 1
	AND oc_product.model LIKE "%-%"
	AND NOT EXISTS (SELECT * 
					FROM oc_ga_product_variant
					WHERE oc_ga_product_variant.product_id = oc_product.product_id)