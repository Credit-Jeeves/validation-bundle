-- RentTrack Sanitization Script - relevant to release 2.8

update cj_user set first_name = mid(md5(first_name),1,10), middle_initial = mid(md5(middle_initial),1,1), last_name = mid(md5(last_name),1,8);
update cj_user set phone = NULL, date_of_birth = NULL, ssn = md5(ssn), invite_code = mid(md5(invite_code),1,10), password = md5(password);
-- email may not be unique, but email_canonical and username canonical must be
update cj_user set email = concat(mid(md5(email),1,16),'@example.com');
update cj_user set username_canonical = concat(mid(md5(username_canonical),1,16),'@example.com');
update cj_user set username = email, email_canonical = username_canonical;

update cj_account_group set name = mid(md5(name),1,20);

update cj_address set city = mid(md5(city),1,12), zip = mid(md5(zip),1,5);

update cj_affiliate set name = mid(md5(name),1,16);

delete from cj_applicant_pidkiq;

-- clear credit info out this completely
update cj_applicant_report set raw_data = "";
update cj_applicant_tradelines set tradeline = "";

update cj_holding set name = mid(md5(name),1,16);

-- delete settings completely
delete from cj_settings;

update jms_jobs set stackTrace = NULL;

update rj_billing_account set token = concat(left(token, 24),mid(md5(token),1,12)), nickname = mid(md5(nickname),1,10);

update rj_checkout_heartland set merchant_name = md5(merchant_name);
update rj_deposit_account set merchant_name = md5(merchant_name);

update rj_group_phone set phone = mid(md5(phone),1,10);

update rj_invite set first_name = mid(md5(first_name),1,10), last_name = mid(md5(last_name),1,8), phone = mid(md5(phone),1,10), email = concat(mid(md5(email),1,16),'@example.com'), unitName = mid(md5(unitName),1,4);

update rj_payment_account set name = mid(md5(name),1,10), token = concat(left(token, 24),mid(md5(token),1,12));
-- expire a looong time from now
update rj_payment_account set cc_expiration = '2025-12-01' where cc_expiration is not NULL;


-- having real properties and units are useful and is not PII
-- however it IS a business secret so DO NOT distribute sanitized snapshots
-- NOTE: keep in mind, that if it can ever be combined with a real name, email, or SSN, then it would be PII.

-- update rj_property set city = mid(md5(city),1,12), zip = mid(md5(zip),1,5), street = mid(md5(street),1,12), number = mid(md5(number),1,6), jb = '34.44943', kb = '-119.709369', google_reference = NULL;

-- update rj_unit set name = mid(md5(name),1,4);

-- RentTrack Sanitization Script - relevant to release 3.4
update rj_contract_waiting set resident_id = mid(md5(resident_id),1,12),
first_name = mid(md5(first_name),1,12),
last_name = mid(md5(last_name),1,12);
update rj_resident_mapping set resident_id = mid(md5(resident_id),1,12);
update rj_unit_mapping set external_unit_id = mid(md5(external_unit_id),1,12);
-- update rj_deposit_account set account_number = FLOOR(RAND() * 500000);

-- RentTrack Sanitization Script - relevant to next release after 3.4
delete from yardi_settings;

-- RentTrack Sanitization Script - relevant to 4.2
update cj_user set city = mid(md5(city),1,12), zip = mid(md5(zip),1,5);

delete from atb_simulation;
delete from cj_checkout_authorize_net_aim;
update cj_account_group_affiliate set auth_token = mid(md5(auth_token),1,20), external_key = mid(md5(external_key),1,20), website_url = 'http://www.example.com';

update rj_contract_waiting set first_name = mid(md5(first_name),1,10),last_name = mid(md5(last_name),1,8);
update rj_contract_waiting set resident_id = mid(md5(resident_id),1);

update rj_resident_mapping set resident_id = mid(md5(resident_id),1);

update cj_account_group set statement_descriptor = mid(md5(statement_descriptor),1,16), website_url = 'http://www.example.com';

update ext_log_entries set username = mid(md5(username),1,20);

-- useful for debugging API issues
update auth_code set token = concat(left(token, 24),mid(md5(token),1,12));
update access_token set token = concat(left(token, 24),mid(md5(token),1,12));
update refresh_token set token = concat(left(token, 24),mid(md5(token),1,12));
update partner set name = mid(md5(name),1,5), request_name = mid(md5(request_name),1,5);
update client set name = mid(md5(name),1,5), secret = mid(md5(secret),1,16), random_id = mid(md5(random_id),1,16);

-- RentTrack Sanitization Script - relevant to 4.2.1
update rj_group_account_mapping set account_number = FLOOR(RAND() * 500000);
delete from resman_settings;
delete from session;
