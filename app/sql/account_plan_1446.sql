BEGIN;
UPDATE account_plans SET opportunity_limit=25, contact_limit=1000 WHERE id=5;
COMMIT;
