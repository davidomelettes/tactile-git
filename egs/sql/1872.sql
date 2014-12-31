BEGIN;


ALTER TABLE resource_templates ADD COLUMN name varchar NOT NULL DEFAULT 'Standard';

DROP VIEW resource_templates_overview;
CREATE VIEW resource_templates_overview AS SELECT rt.id, rt.name, rt.person_id, (p.firstname::text || ' '::text) || p.surname::text AS person, rt.resource_type_id, ry.name AS resource_type, rt.standard_rate, rt.overtime_rate, rt.quantity, rt.cost, rt.usercompanyid
   FROM resource_templates rt
   LEFT JOIN person p ON rt.person_id = p.id
   LEFT JOIN resource_types ry ON rt.resource_type_id = ry.id;
ALTER TABLE resource_templates ADD CONSTRAINT "resource_templates_person_id_usercompany_id_name_unique" UNIQUE (name,person_id,usercompanyid);
COMMIT;