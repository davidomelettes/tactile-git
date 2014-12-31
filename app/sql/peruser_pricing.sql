BEGIN;

ALTER TABLE account_plans ADD COLUMN per_user BOOLEAN NOT NULL DEFAULT FALSE;
INSERT INTO account_plans (name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month, per_user)
VALUES ('Solo', '1', '10485760', '20', '250', '0', 'true');
INSERT INTO account_plans (name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month, per_user)
VALUES ('Premium', '0', '1048576000', '0', '0', '6', 'true');
ALTER TABLE tactile_accounts ADD COLUMN per_user_limit INT NOT NULL DEFAULT '1';
ALTER TABLE payment_records ADD COLUMN description VARCHAR;
ALTER TABLE payment_records ADD COLUMN repeatable BOOLEAN NOT NULL DEFAULT TRUE;

COMMIT;