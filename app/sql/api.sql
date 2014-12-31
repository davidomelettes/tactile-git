BEGIN;

ALTER TABLE users ADD COLUMN api_token VARCHAR UNIQUE;

ALTER TABLE tactile_accounts ADD COLUMN tactile_api_enabled BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE company ADD COLUMN status_id INT REFERENCES company_statuses(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE company ADD COLUMN source_id INT REFERENCES company_sources(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE company ADD COLUMN classification_id INT REFERENCES company_classifications(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE company ADD COLUMN rating_id INT REFERENCES company_ratings(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE company ADD COLUMN industry_id INT REFERENCES company_industries(id) ON UPDATE CASCADE ON DELETE SET NULL;
ALTER TABLE company ADD COLUMN type_id INT REFERENCES company_types(id) ON UPDATE CASCADE ON DELETE SET NULL;

UPDATE company SET
status_id = company_crm.status_id,
source_id = company_crm.source_id,
classification_id = company_crm.classification_id,
rating_id = company_crm.rating_id,
industry_id = company_crm.industry_id,
type_id = company_crm.type_id
FROM company_crm WHERE company_crm.company_id = company.id;
DROP TABLE company_crm;

ALTER TABLE company ADD COLUMN street1 VARCHAR;
ALTER TABLE company ADD COLUMN street2 VARCHAR;
ALTER TABLE company ADD COLUMN street3 VARCHAR;
ALTER TABLE company ADD COLUMN town VARCHAR;
ALTER TABLE company ADD COLUMN county VARCHAR;
ALTER TABLE company ADD COLUMN postcode VARCHAR;
ALTER TABLE company ADD COLUMN country_code VARCHAR REFERENCES countries(code) ON UPDATE CASCADE ON DELETE SET NULL;

UPDATE company SET
street1 = ca.street1,
street2 = ca.street2,
street3 = ca.street3,
town = ca.town,
county = ca.county,
postcode = ca.postcode,
country_code = ca.countrycode
FROM companyaddress ca WHERE main AND company.id = ca.company_id;
DROP TABLE companyaddress CASCADE;

ALTER TABLE person ADD COLUMN street1 VARCHAR;
ALTER TABLE person ADD COLUMN street2 VARCHAR;
ALTER TABLE person ADD COLUMN street3 VARCHAR;
ALTER TABLE person ADD COLUMN town VARCHAR;
ALTER TABLE person ADD COLUMN county VARCHAR;
ALTER TABLE person ADD COLUMN postcode VARCHAR;
ALTER TABLE person ADD COLUMN country_code VARCHAR REFERENCES countries(code) ON UPDATE CASCADE ON DELETE SET NULL;

UPDATE person SET
street1 = ca.street1,
street2 = ca.street2,
street3 = ca.street3,
town = ca.town,
county = ca.county,
postcode = ca.postcode,
country_code = ca.countrycode
FROM personaddress ca WHERE main AND person.id = ca.person_id;
DROP TABLE personaddress CASCADE;

COMMIT;
