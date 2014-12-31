BEGIN;

UPDATE tactile_accounts ta SET country = c.code FROM countries c WHERE c.name = ta.country;

CREATE TABLE invoices (
  id bigserial NOT NULL,
  account_id integer NOT NULL REFERENCES tactile_accounts(id),
  invoice_date date NOT NULL,
  created timestamp NOT NULL DEFAULT NOW(),
  vat_rate numeric NOT NULL,
  sent_at timestamp,
  PRIMARY KEY (id)
);

CREATE TABLE invoice_lines (
  id bigserial NOT NULL,
  invoice_id bigint NOT NULL REFERENCES invoices(id),
  payment_record_id bigint NOT NULL REFERENCES payment_records(id),
  product varchar NOT NULL DEFAULT 'Tactile',
  net_amount numeric NOT NULL,
  gross_amount numeric NOT NULL,
  plan_id integer NOT NULL REFERENCES account_plans(id),
  created timestamp NOT NULL DEFAULT NOW(),
  PRIMARY KEY (id)
);


ALTER TABLE payment_records ADD COLUMN invoiced boolean NOT NULL DEFAULT 'f';
UPDATE payment_records SET invoiced = 't';

ALTER TABLE tactile_accounts ADD COLUMN vat_number_new varchar;
UPDATE tactile_accounts SET vat_number_new = vat_number;
ALTER TABLE tactile_accounts DROP COLUMN vat_number;
ALTER TABLE tactile_accounts RENAME vat_number_new TO vat_number;


COMMIT;