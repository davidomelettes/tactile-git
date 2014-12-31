create table opportunity_contacts (opportunity_id bigint not null references opportunities(id) on update cascade on delete cascade, organisation_id bigint references organisations(id) on update cascade on delete cascade, person_id bigint references people(id) on update cascade on delete cascade, relationship varchar, CHECK ((person_id IS NULL AND organisation_id IS NOT NULL) OR (person_id IS NOT NULL AND organisation_id IS NULL)));