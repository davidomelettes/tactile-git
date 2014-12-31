BEGIN;

-- Move numeric values into their own column
ALTER TABLE custom_field_map ADD COLUMN value_numeric NUMERIC;
UPDATE custom_field_map cfm SET value_numeric = CASE
    WHEN value = '' THEN NULL
    ELSE value::float
    END
    FROM custom_fields cf
    WHERE cf.id = cfm.field_id AND cf.type = 'n' AND cfm.value IS NOT NULL;
DROP VIEW custom_field_map_overview;
CREATE VIEW custom_field_map_overview AS
SELECT m.id, m.field_id, m.organisation_id, m.person_id, m.opportunity_id, m.activity_id, m.hash, m.value, m.value_numeric, m.enabled, m.option, f.name, f.type, o.value AS option_name
    FROM custom_field_map m
    LEFT JOIN custom_fields f ON m.field_id = f.id
    LEFT JOIN custom_field_options o ON m.option = o.id;

-- Table for storing search queries
CREATE TABLE advanced_searches (
    id SERIAL PRIMARY KEY,
    owner VARCHAR NOT NULL REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE,
    usercompanyid INT NOT NULL REFERENCES organisations(id) ON UPDATE CASCADE ON DELETE CASCADE,
    name VARCHAR NOT NULL,
    record_type VARCHAR NOT NULL,
    query TEXT NOT NULL
);

-- Column for beta access
ALTER TABLE users ADD COLUMN beta BOOLEAN NOT NULL DEFAULT FALSE;

COMMIT;
