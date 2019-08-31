/* Replace this file with actual dump of your database */
DELETE FROM dtb_oauth2_client WHERE oauth2_client_id = 999999;
INSERT INTO dtb_oauth2_client (oauth2_client_id, client_id, client_secret, app_name, authorize_endpoint, token_endpoint, userinfo_endpoint, create_date, update_date, del_flg, short_name, scope) VALUES (999999, 'dummy', 'dummy', 'DUMMY', 'http://localhost:8085/sso/DUMMY/authorize', 'http://localhost:8085/sso/DUMMY/token', 'http://localhost:8085/sso/DUMMY/userinfo', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 0, 'DUMMY', 'profile');
