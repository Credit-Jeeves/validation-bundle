--
-- RentTrack DB Sanitize Script
--
-- Having real properties and units data is useful and is not PII
-- However it IS a business secret so DO NOT distribute sanitized snapshots
--
-- NOTE: keep in mind, that if it can ever be combined with a real name, email, or SSN, then it would be PII.
--

--
-- General data
--
delete from cj_settings;
delete from session;
delete from ext_log_entries;
delete from rj_merchant_account_migration;
update email_translation set value = 'staging@renttrack.com' where value like '%darryl%';
-- delete from jms_jobs; -- excluded from backup in santitize_db.sh

--
-- User data
--
-- anonymize user records
update cj_user set first_name = mid(md5(first_name),1,10), middle_initial = mid(md5(middle_initial),1,1), last_name = mid(md5(last_name),1,8);
update cj_user set phone = NULL, date_of_birth = NULL, ssn = md5(ssn), invite_code = mid(md5(invite_code),1,10), password = md5(password);

-- email may not be unique, but email_canonical and username canonical must be
update cj_user set email = concat(mid(md5(email),1,16),'@example.com');
update cj_user set username_canonical = concat(mid(md5(username_canonical),1,16),'@example.com');
update cj_user set username = email, email_canonical = username_canonical;
update cj_address set city = mid(md5(city),1,12), zip = mid(md5(zip),1,5);

-- anonymize user leases
update rj_contract_waiting set resident_id = mid(md5(resident_id),1,12),
first_name = mid(md5(first_name),1,12),
last_name = mid(md5(last_name),1,12);
update rj_resident_mapping set resident_id = mid(md5(resident_id),1,12);
update rj_unit_mapping set external_unit_id = mid(md5(external_unit_id),1,12);
update rj_contract_waiting set first_name = mid(md5(first_name),1,10),last_name = mid(md5(last_name),1,8);
update rj_contract_waiting set resident_id = mid(md5(resident_id),1,12);

-- import data
delete from rj_import_error;
delete from rj_import_property;
delete from rj_import_property;

-- user invites
update rj_invite set first_name = mid(md5(first_name),1,10), last_name = mid(md5(last_name),1,8), phone = mid(md5(phone),1,10), email = concat(mid(md5(email),1,16),'@example.com'), unitName = mid(md5(unitName),1,4);

-- sales affiliates
update cj_affiliate set name = mid(md5(name),1,16);
update cj_account_group_affiliate set auth_token = mid(md5(auth_token),1,20), external_key = mid(md5(external_key),1,20), website_url = 'http://www.example.com';

-- remove id verification data
delete from cj_applicant_pidkiq;

-- clear credit info out this completely
delete from atb_simulation;
update cj_applicant_report set raw_data = "";
update cj_applicant_tradelines set tradeline = "";


--
-- Property Manager data
--
update cj_holding set name = mid(md5(name),1,16);
update rj_group set name = mid(md5(name),1,20), mailing_address_name = mid(md5(name),1,20), statement_descriptor = mid(md5(statement_descriptor),1,16), website_url = 'http://www.example.com';
update rj_billing_account set token = concat(left(token, 24),mid(md5(token),1,12)), nickname = mid(md5(nickname),1,10);
update rj_deposit_account set merchant_name = md5(merchant_name), account_number = mid(md5(account_number),1,20);
update rj_group_phone set phone = mid(md5(phone),1,10);
delete from yardi_settings;
delete from resman_settings;
delete from resman_settings;
delete from rj_mri_settings;
delete from rj_profitstars_settings;
delete from rj_amsi_settings;

-- Trusted Landlord
update rj_trusted_landlord set first_name = mid(md5(first_name),1,10), last_name = mid(md5(last_name),1,8), company_name = mid(md5(company_name),1,25);
update rj_check_mailing_address set addressee = mid(md5(addressee),1,10);

--
-- PII Check Changes
--
-- we check for 'darryl' anywhere in the DB, so remove from street names as needed
update rj_property_address set street = mid(md5(street),1,10) where street like "%darryl%";
update rj_property_address set ss_index = mid(md5(ss_index),1,10) where ss_index like "%darryl%";
update rj_group set street_address_1 = mid(md5(street_address_1),1,10) where street_address_1 like "%darryl%";
delete from rj_smarty_streets_cache where id like "%darryl%" or value like "%darryl%";

--
-- Payment data
--
update rj_transaction set merchant_name = md5(merchant_name);
update rj_payment_account set name = mid(md5(name),1,10), token = concat(left(token, 24),mid(md5(token),1,12));
-- expire a looong time from now
update rj_payment_account set cc_expiration = '2025-12-01' where cc_expiration is not NULL;


--
-- OAuth
--
-- useful for debugging API issues
update auth_code set token = concat(left(token, 24),mid(md5(token),1,12));
update access_token set token = concat(left(token, 24),mid(md5(token),1,12));
update refresh_token set token = concat(left(token, 24),mid(md5(token),1,12));
update partner set name = mid(md5(name),1,5), request_name = mid(md5(request_name),1,5);
update client set name = mid(md5(name),1,5), secret = mid(md5(secret),1,16), random_id = mid(md5(random_id),1,16);



