SELECT periodno,
stkcategory,
SUM(amt-disc),
SUM(cost),
SUM(qty)
FROM salesanalysis
WHERE periodno = 61
GROUP BY periodno, stkcategory

