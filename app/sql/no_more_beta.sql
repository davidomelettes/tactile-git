BEGIN;
UPDATE tactile_accounts set current_plan_id =4 where current_plan_id=3;
UPDATE tactile_accounts set current_plan_id=7 where site_address='senokian';
update tactile_accounts set account_expires='2020-12-15' where site_address='senokian';
COMMIT;