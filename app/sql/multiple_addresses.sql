BEGIN;

-- Create address tables and views
CREATE TABLE organisation_addresses (
	id SERIAL PRIMARY KEY,
	organisation_id INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
	name VARCHAR NOT NULL DEFAULT 'Main',
	main BOOLEAN NOT NULL DEFAULT FALSE,
	street1 VARCHAR,
	street2 VARCHAR,
	street3 VARCHAR,
	town VARCHAR,
	county VARCHAR,
	postcode VARCHAR,
	country_code VARCHAR REFERENCES countries(code) ON UPDATE CASCADE ON DELETE SET NULL
);
CREATE VIEW organisation_addresses_overview AS SELECT a.id, a.organisation_id, a.name, a.main, a.street1, a.street2, a.street3, a.town, a.county, a.postcode, c.name AS country FROM organisation_addresses a LEFT JOIN countries c ON a.country_code = c.code;
CREATE TABLE person_addresses (
	id SERIAL PRIMARY KEY,
	person_id INT NOT NULL REFERENCES people(id) ON UPDATE CASCADE ON DELETE CASCADE,
	name VARCHAR NOT NULL DEFAULT 'Main',
	main BOOLEAN NOT NULL DEFAULT FALSE,
	street1 VARCHAR,
	street2 VARCHAR,
	street3 VARCHAR,
	town VARCHAR,
	county VARCHAR,
	postcode VARCHAR,
	country_code VARCHAR REFERENCES countries(code) ON UPDATE CASCADE ON DELETE SET NULL
);
CREATE VIEW person_addresses_overview AS SELECT a.id, a.person_id, a.name, a.main, a.street1, a.street2, a.street3, a.town, a.county, a.postcode, c.name AS country FROM person_addresses a LEFT JOIN countries c ON a.country_code = c.code;

-- Move data from organisations and people tables into respective address tables
INSERT INTO organisation_addresses (main, organisation_id, street1, street2, street3, town, county, postcode, country_code) SELECT 'true', id, street1, street2, street3, town, county, postcode, country_code FROM organisations WHERE (street1 IS NOT NULL OR street2 IS NOT NULL OR street3 IS NOT NULL OR town IS NOT NULL OR county IS NOT NULL OR postcode IS NOT NULL OR country_code IS NOT NULL);
INSERT INTO person_addresses (main, person_id, street1, street2, street3, town, county, postcode, country_code) SELECT 'true', id, street1, street2, street3, town, county, postcode, country_code FROM people WHERE (street1 IS NOT NULL OR street2 IS NOT NULL OR street3 IS NOT NULL OR town IS NOT NULL OR county IS NOT NULL OR postcode IS NOT NULL OR country_code IS NOT NULL);

-- Tidy up tables by dropping moved columns
ALTER TABLE organisations DROP COLUMN street1;
ALTER TABLE organisations DROP COLUMN street2;
ALTER TABLE organisations DROP COLUMN street3;
ALTER TABLE organisations DROP COLUMN town;
ALTER TABLE organisations DROP COLUMN county;
ALTER TABLE organisations DROP COLUMN postcode;
ALTER TABLE organisations DROP COLUMN country_code;
ALTER TABLE people DROP COLUMN street1;
ALTER TABLE people DROP COLUMN street2;
ALTER TABLE people DROP COLUMN street3;
ALTER TABLE people DROP COLUMN town;
ALTER TABLE people DROP COLUMN county;
ALTER TABLE people DROP COLUMN postcode;
ALTER TABLE people DROP COLUMN country_code;

COMMIT;
