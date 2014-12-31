begin;
create view store_vouchersoverview as select v.*, c.username as buyer, 
r.username as redeemer from store_vouchers v left join customers c on (c.id = buyer_id) left join customers r on (r.id = v.redeemed_by);
commit;
