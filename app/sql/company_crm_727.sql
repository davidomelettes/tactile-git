ALTER TABLE company_crm DROP CONSTRAINT company_crm_status_id_fkey;
ALTER TABLE company_crm ADD FOREIGN KEY (status_id) REFERENCES company_statuses(id) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE company_crm DROP CONSTRAINT company_crm_source_id_fkey;
ALTER TABLE company_crm ADD FOREIGN KEY (source_id) REFERENCES company_sources(id) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE company_crm DROP CONSTRAINT company_crm_classification_id_fkey;
ALTER TABLE company_crm ADD FOREIGN KEY (classification_id) REFERENCES company_classifications(id) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE company_crm DROP CONSTRAINT company_crm_rating_id_fkey;
ALTER TABLE company_crm ADD FOREIGN KEY (rating_id) REFERENCES company_ratings(id) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE company_crm DROP CONSTRAINT company_crm_industry_id_fkey;
ALTER TABLE company_crm ADD FOREIGN KEY (industry_id) REFERENCES company_industries(id) ON UPDATE CASCADE ON DELETE SET NULL;

ALTER TABLE company_crm DROP CONSTRAINT company_crm_type_id_fkey;
ALTER TABLE company_crm ADD FOREIGN KEY (type_id) REFERENCES company_types(id) ON UPDATE CASCADE ON DELETE SET NULL;