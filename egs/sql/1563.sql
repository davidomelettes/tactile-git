begin;
drop view productoverview;
create view productoverview as
SELECT store_products.id, store_products.productcode, store_products.manufacturercode, store_products.name, 
store_products.shortdescription, store_products.description, store_products.searchkeywords, 
store_products.metadescription, store_products.metakeywords, store_products.thumbnail, store_products.image, 
store_products.price, store_products.normalprice, store_products.oneoffprice, store_products.costprice, 
store_products.minquantity, store_products.maxquantity, store_products.weight, store_products.freeshipping, 
store_products.supplier_id, store_products.stockcontrolenable, store_products.stocklevel, 
store_products.warninglevel, store_products.actiononzero, store_products.section_id, store_products.newproduct, 
store_products.topproduct, store_products.specialoffer, store_products.visible, store_products.forcehide, 
store_products."template", store_products."owner", store_products.alteredby, store_products.created, 
store_products.lastupdated, store_products.usercompanyid, store_sections.title AS section, store_suppliers.name AS 
supplier
   FROM store_products
   LEFT JOIN store_sections ON store_products.section_id = store_sections.id
   LEFT JOIN store_suppliers ON store_products.supplier_id = store_suppliers.id;
commit
