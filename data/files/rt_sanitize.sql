-- RentTrack Sanitization Script - relevant to release 2.8

update cj_user set first_name = mid(md5(first_name),1,10), middle_initial = mid(md5(middle_initial),1,1), last_name = mid(md5(last_name),1,8);
update cj_user set phone = NULL, date_of_birth = NULL, ssn = md5(ssn), invite_code = mid(md5(invite_code),1,10), password = md5(password);
update cj_user set email = concat(mid(md5(email),1,16),'@example.com');
update cj_user set username = email, username_canonical = email, email_canonical = email;

update cj_account_group set name = mid(md5(name),1,20);

update cj_address set city = mid(md5(city),1,12), zip = mid(md5(zip),1,5);

update cj_affiliate set name = mid(md5(name),1,16);

delete from cj_applicant_pidkiq;
update cj_applicant_report set raw_data = md5(raw_data);

update cj_holding set name = mid(md5(name),1,16);

update cj_settings set precise_id_user_pwd = NULL, precise_id_eai = NULL, credit_profile_user_pwd = NULL, credit_profile_eai = NULL;

update jms_jobs set stackTrace = NULL;

update rj_billing_account set token = concat(left(token, 24),mid(md5(token),1,12)), nickname = mid(md5(nickname),1,10);

update rj_checkout_heartland set merchant_name = md5(merchant_name);
update rj_deposit_account set merchant_name = md5(merchant_name);

update rj_group_phone set phone = mid(md5(phone),1,10);

update rj_invite set first_name = mid(md5(first_name),1,10), last_name = mid(md5(last_name),1,8), phone = mid(md5(phone),1,10), email = concat(mid(md5(email),1,16),'@example.com'), unitName = mid(md5(unitName),1,4);

update rj_payment_account set name = mid(md5(name),1,10), token = concat(left(token, 24),mid(md5(token),1,12));
update rj_payment_account set cc_expiration = '2017-12-01' where cc_expiration is not NULL;

update rj_property set city = mid(md5(city),1,12), zip = mid(md5(zip),1,5), street = mid(md5(street),1,12), number = mid(md5(number),1,6), jb = '34.44943', kb = '-119.709369', google_reference = NULL;

update rj_unit set name = mid(md5(name),1,4);

-- RentTrack Sanitization Script - relevant to release 3.4
update rj_contract_waiting set resident_id = mid(md5(resident_id),1,12),
first_name = mid(md5(first_name),1,12),
last_name = mid(md5(last_name),1,12);
update rj_resident_mapping set resident_id = mid(md5(resident_id),1,12);
update rj_unit_mapping set external_unit_id = mid(md5(external_unit_id),1,12);
update rj_deposit_account set account_number = FLOOR(RAND() * 500000);
delete from refresh_token;
delete from access_token;
delete from auth_code;
delete from client;

-- RentTrack Sanitization Script - relevant to next release after 3.4
-- delete from yardi_settings;
