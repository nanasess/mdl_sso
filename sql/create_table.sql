CREATE TABLE IF NOT EXISTS dtb_oauth2_client (
    oauth2_client_id bigint NOT NULL,
    client_id varchar(128) NOT NULL,
    client_secret varchar(128) NOT NULL,
    app_name varchar(64) NOT NULL,
    authorize_endpoint text NULL,
    token_endpoint text NULL,
    userinfo_endpoint text NULL,
    create_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date timestamp NOT NULL,
    del_flg smallint NOT NULL DEFAULT 0,
    short_name varchar(16) NULL,
    scope text NULL,
    PRIMARY KEY(oauth2_client_id)
);
CREATE TABLE IF NOT EXISTS dtb_oauth2_openid_userinfo (
    customer_id bigint NOT NULL,
    oauth2_client_id bigint NOT NULL,
    sub varchar(900) NOT NULL,
    name text NULL,
    given_name text NULL,
    family_name text NULL,
    middle_name text NULL,
    nickname text NULL,
    preferred_username varchar(255) NULL,
    profile text NULL,
    picture text NULL,
    website text NULL,
    email varchar(255) NULL,
    email_verified smallint NOT NULL DEFAULT 0,
    gender varchar(32) NULL,
    birthdate timestamp NULL,
    zoneinfo varchar(128) NULL,
    locale varchar(32) NULL,
    phone_number varchar(128) NULL,
    phone_number_verified smallint NOT NULL DEFAULT 0,
    updated_at timestamp NOT NULL,
    PRIMARY KEY (customer_id,oauth2_client_id)
);

CREATE TABLE IF NOT EXISTS dtb_oauth2_openid_userinfo_address (
    customer_id bigint NOT NULL,
    oauth2_client_id bigint NOT NULL,
    formatted text NULL,
    street_address text NULL,
    locality text NULL,
    region text NULL,
    postal_code varchar(32) NULL,
    country varchar(32) NULL,
    PRIMARY KEY (customer_id,oauth2_client_id)
);
CREATE TABLE IF NOT EXISTS dtb_oauth2_token (
    oauth2_client_id bigint NOT NULL,
    customer_id bigint NOT NULL,
    access_token text NOT NULL,
    refresh_token text NULL,
    token_type varchar(32) NULL,
    id_token text NULL,
    expires_in int NULL,
    create_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    update_date timestamp NOT NULL,
    scope text NULL,
    PRIMARY KEY (oauth2_client_id,customer_id)
);
CREATE INDEX IX_dtb_oauth2_openid_userinfo_preferred_username ON dtb_oauth2_openid_userinfo (preferred_username);
CREATE UNIQUE INDEX IX_dtb_oauth2_openid_userinfo_sub ON dtb_oauth2_openid_userinfo (sub);

-- DELETE FROM dtb_oauth2_client WHERE oauth2_client_id = 999999;
INSERT INTO dtb_oauth2_client (oauth2_client_id, client_id, client_secret, app_name, authorize_endpoint, token_endpoint, userinfo_endpoint, create_date, update_date, del_flg, short_name, scope) VALUES (999999, 'dummy', 'dummy', 'DUMMY', 'http://localhost:8086/sso/DUMMY/authorize', 'http://localhost:8086/sso/DUMMY/token', 'http://localhost:8086/sso/DUMMY/userinfo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 0, 'DUMMY', 'profile');
