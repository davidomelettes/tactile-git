BEGIN;
CREATE VIEW personaddress_overview AS SELECT id, street1 || ', ' || street2 || ', ' || street3 || ', ' || town || ', ' || county || ', ' || postcode AS address, countrycode, person_id, name, main, billing, shipping, payment, technical FROM personaddress;
COMMIT;
