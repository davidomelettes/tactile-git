begin;
alter table organisations add column last_contacted timestamp;
alter table organisations add column last_contacted_by varchar;
alter table organisations add constraint last_contacted_by foreign key (last_contacted_by) references users(username) on update cascade on delete cascade;
alter table people add column last_contacted timestamp;
alter table people add column last_contacted_by varchar;
alter table people add constraint last_contacted_by foreign key (last_contacted_by) references users(username) on update cascade on delete cascade;
CREATE OR REPLACE FUNCTION contact_stamp() RETURNS trigger AS $contact_stamp$
    BEGIN
        IF NEW.usercompanyid IS NULL THEN
            RAISE EXCEPTION 'usercompanyid cannot be null';
        END IF;
IF NEW.owner IS NULL THEN
            RAISE EXCEPTION 'owner cannot be null';
        END IF;

-- If the organisation_id isn't null, update the org
IF NEW.organisation_id IS NOT NULL THEN
        update organisations set last_contacted=NEW.created, last_contacted_by=NEW.owner WHERE usercompanyid=NEW.usercompanyid AND id=NEW.organisation_id AND (NEW.created>=last_contacted OR last_contacted IS NULL);
END IF;
IF NEW.person_id IS NOT NULL THEN
        update people set last_contacted=NEW.created, last_contacted_by=NEW.owner WHERE usercompanyid=NEW.usercompanyid AND id=NEW.person_id AND (NEW.created>=last_contacted OR last_contacted IS NULL);
END IF;

        RETURN NULL;
    END;
$contact_stamp$ LANGUAGE plpgsql;
create trigger contact_stamp after insert  or update on emails for each row execute procedure contact_stamp();

-- Update orgs with last contacted from notes
update organisations set last_contacted=n.created, last_contacted_by=o.owner FROM notes o, (select organisation_id, max(created) as created from notes where organisation_id IS NOT NULL group by organisation_id) n WHERE n.created=o.created AND organisations.id=o.organisation_id ;

-- update orgs with last contacted from emails
update organisations set last_contacted=e.created, last_contacted_by=o.owner FROM emails o, (select organisation_id, max(created) as created from emails where organisation_id IS NOT NULL group by organisation_id) e WHERE e.created=o.created AND organisations.id=o.organisation_id AND e.created > organisations.last_contacted ;

-- Update people with last contacted from notes
update people set last_contacted=n.created, last_contacted_by=p.owner FROM notes p, (select person_id, max(created) as created from notes where person_id IS NOT NULL group by person_id) n WHERE n.created=p.created AND people.id=p.person_id ;

-- update people with last contacted from emails
update people set last_contacted=e.created, last_contacted_by=p.owner FROM emails p, (select person_id, max(created) as created from emails where person_id IS NOT NULL group by person_id) e WHERE e.created=p.created AND people.id=p.person_id AND e.created > people.last_contacted ;

commit;
