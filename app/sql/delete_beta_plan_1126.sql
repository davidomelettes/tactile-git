BEGIN;
DELETE FROM account_plans WHERE id = 3 AND name = 'Beta';
COMMIT;
