BEGIN;
update account_plans set opportunity_limit =50 where name='SME';
COMMIT;
