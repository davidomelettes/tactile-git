BEGIN;

CREATE TABLE user_logins (
	login_time TIMESTAMP NOT NULL DEFAULT now(),
	entered_username varchar NOT NULL,
	site_address varchar NOT NULL,
	was_successful BOOLEAN NOT NULL DEFAULT TRUE
);

COMMIT;
