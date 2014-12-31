BEGIN;
CREATE OR REPLACE VIEW webpagesoverview AS 
 SELECT w.id, w.name, w.keywords, w.description, w.visible, w.page_element, w.parent_id, w.website_id, w.webpage_category_id, w."owner", w.alteredby, w.created, w.access_controlled, w.publishon, w.withdrawon, w.menuorder, c.name AS webpage_category
   FROM webpages w
   LEFT JOIN webpage_categories c ON c.id = w.webpage_category_id;
COMMIT;
