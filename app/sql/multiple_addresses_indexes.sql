BEGIN;

create index organisation_addresses_organisation_id_main on organisation_addresses(organisation_id, main);
create index person_addresses_person_id_main on person_addresses(person_id, main);

COMMIT;
