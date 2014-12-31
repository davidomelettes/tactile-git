BEGIN;
CREATE TABLE user_company_access (
username varchar not null references users(username) on update cascade,
company_id bigint not null references company(id) on update cascade on delete cascade,
unique(username,company_id)
);
COMMIT;
