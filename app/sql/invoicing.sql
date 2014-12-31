create index company_contact_methods_type on organisation_contact_methods (type);
create index contact_method_order_type on contact_method_order (type);
begin;
alter table organisations add column xero_id varchar;
alter table payment_records add column xero_invoice_id varchar;
commit;
