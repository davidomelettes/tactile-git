begin;
CREATE TABLE system_companies (
    id bigserial NOT NULL,
    company_id bigint NOT NULL,
    enabled boolean DEFAULT true NOT NULL
);
ALTER TABLE ONLY system_companies ADD CONSTRAINT system_companies_company_id_key UNIQUE (company_id);
ALTER TABLE ONLY system_companies ADD CONSTRAINT system_companies_pkey PRIMARY KEY (id);
ALTER TABLE ONLY system_companies ADD CONSTRAINT system_companies_company_id_fkey FOREIGN KEY (company_id) REFERENCES company(id) ON UPDATE CASCADE ON DELETE CASCADE;
commit;

