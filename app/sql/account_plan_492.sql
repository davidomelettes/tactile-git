BEGIN;
UPDATE account_plans SET opportunity_limit =10, contact_limit=1000 WHERE name='Micro';
COMMIT;
