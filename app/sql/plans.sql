BEGIN;
INSERT INTO account_plans (name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month)
	VALUES ('Free', 2, 10*1024, 2, 250, 0);
INSERT INTO account_plans (name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month)
	VALUES ('Micro', 3, 250*1024, 3, 400, 6);

INSERT INTO account_plans (name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month)
	VALUES ('SME', 7, 750*1024, 20, 7500, 15);

INSERT INTO account_plans (name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month)
	VALUES ('Business', 20, 5*1024*1024, 500, 25000, 35);

INSERT INTO account_plans (name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month)
	VALUES ('Premier', 50, 10*1024*1024, 7500, 100000, 60);

INSERT INTO account_plans (name, user_limit, file_space, opportunity_limit, contact_limit, cost_per_month)
	VALUES ('Enterprise', 100, 50*1024*1024, 100000, 500000, 75);

COMMIT;