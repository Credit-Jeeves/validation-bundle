# Doctrine Migration File Generated on 2014-03-18 21:03:32
# Migrating from 20140313130206 to 20140313130208

# Version 20140313130207
UPDATE cj_operation
                SET `order_id` = 1
                WHERE `id` = 1
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 1 AND `cj_order_id` = 1;
UPDATE cj_operation
                SET `order_id` = 2
                WHERE `id` = 2
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 2 AND `cj_order_id` = 2;
UPDATE cj_operation
                SET `order_id` = 3
                WHERE `id` = 3
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 3 AND `cj_order_id` = 3;
UPDATE cj_operation
                SET `order_id` = 5
                WHERE `id` = 4
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 4 AND `cj_order_id` = 5;
UPDATE cj_operation
                SET `order_id` = 6
                WHERE `id` = 5
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 5 AND `cj_order_id` = 6;
UPDATE cj_operation
                SET `order_id` = 7
                WHERE `id` = 6
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 6 AND `cj_order_id` = 7;
UPDATE cj_operation
                SET `order_id` = 8
                WHERE `id` = 7
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 7 AND `cj_order_id` = 8;
UPDATE cj_operation
                SET `order_id` = 9
                WHERE `id` = 8
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 8 AND `cj_order_id` = 9;
UPDATE cj_operation
                SET `order_id` = 10
                WHERE `id` = 9
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 9 AND `cj_order_id` = 10;
UPDATE cj_operation
                SET `order_id` = 14
                WHERE `id` = 10
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 10 AND `cj_order_id` = 14;
UPDATE cj_operation
                SET `order_id` = 17
                WHERE `id` = 12
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 12 AND `cj_order_id` = 17;
INSERT cj_operation
                SET
                `order_id` = 33,
                `amount` = 1500.00,
                `type` = 'rent',
                `created_at` = 2013-10-24 06:21:28
                ,`contract_id` = 4;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 12 AND `cj_order_id` = 33;
INSERT cj_operation
                SET
                `order_id` = 38,
                `amount` = 1500.00,
                `type` = 'rent',
                `created_at` = 2013-10-24 06:21:28
                ,`contract_id` = 4;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 12 AND `cj_order_id` = 38;
INSERT cj_operation
                SET
                `order_id` = 62,
                `amount` = 1500.00,
                `type` = 'rent',
                `created_at` = 2013-10-24 06:21:28
                ,`contract_id` = 4;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 12 AND `cj_order_id` = 62;
INSERT cj_operation
                SET
                `order_id` = 77,
                `amount` = 1500.00,
                `type` = 'rent',
                `created_at` = 2013-10-24 06:21:28
                ,`contract_id` = 4;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 12 AND `cj_order_id` = 77;
INSERT cj_operation
                SET
                `order_id` = 81,
                `amount` = 1500.00,
                `type` = 'rent',
                `created_at` = 2013-10-24 06:21:28
                ,`contract_id` = 4;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 12 AND `cj_order_id` = 81;
UPDATE cj_operation
                SET `order_id` = 18
                WHERE `id` = 13
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 13 AND `cj_order_id` = 18;
INSERT cj_operation
                SET
                `order_id` = 21,
                `amount` = 1000.00,
                `type` = 'rent',
                `created_at` = 2013-10-24 08:44:39
                ,`contract_id` = 1;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 13 AND `cj_order_id` = 21;
INSERT cj_operation
                SET
                `order_id` = 40,
                `amount` = 1000.00,
                `type` = 'rent',
                `created_at` = 2013-10-24 08:44:39
                ,`contract_id` = 1;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 13 AND `cj_order_id` = 40;
INSERT cj_operation
                SET
                `order_id` = 51,
                `amount` = 1000.00,
                `type` = 'rent',
                `created_at` = 2013-10-24 08:44:39
                ,`contract_id` = 1;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 13 AND `cj_order_id` = 51;
INSERT cj_operation
                SET
                `order_id` = 59,
                `amount` = 1000.00,
                `type` = 'rent',
                `created_at` = 2013-10-24 08:44:39
                ,`contract_id` = 1;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 13 AND `cj_order_id` = 59;
INSERT cj_operation
                SET
                `order_id` = 83,
                `amount` = 1000.00,
                `type` = 'rent',
                `created_at` = 2013-10-24 08:44:39
                ,`contract_id` = 1;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 13 AND `cj_order_id` = 83;
UPDATE cj_operation
                SET `order_id` = 20
                WHERE `id` = 15
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 15 AND `cj_order_id` = 20;
UPDATE cj_operation
                SET `order_id` = 22
                WHERE `id` = 16
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 16 AND `cj_order_id` = 22;
INSERT cj_operation
                SET
                `order_id` = 39,
                `amount` = 2500.00,
                `type` = 'rent',
                `created_at` = 2013-10-25 13:53:10
                ,`contract_id` = 7;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 16 AND `cj_order_id` = 39;
UPDATE cj_operation
                SET `order_id` = 24
                WHERE `id` = 17
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 17 AND `cj_order_id` = 24;
INSERT cj_operation
                SET
                `order_id` = 50,
                `amount` = 550.00,
                `type` = 'rent',
                `created_at` = 2013-10-30 18:02:49
                ,`contract_id` = 11;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 17 AND `cj_order_id` = 50;
UPDATE cj_operation
                SET `order_id` = 25
                WHERE `id` = 18
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 18 AND `cj_order_id` = 25;
INSERT cj_operation
                SET
                `order_id` = 78,
                `amount` = 650.00,
                `type` = 'rent',
                `created_at` = 2013-10-30 18:02:53
                ,`contract_id` = 12;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 18 AND `cj_order_id` = 78;
UPDATE cj_operation
                SET `order_id` = 26
                WHERE `id` = 19
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 19 AND `cj_order_id` = 26;
INSERT cj_operation
                SET
                `order_id` = 79,
                `amount` = 680.00,
                `type` = 'rent',
                `created_at` = 2013-10-30 22:35:22
                ,`contract_id` = 15;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 19 AND `cj_order_id` = 79;
UPDATE cj_operation
                SET `order_id` = 27
                WHERE `id` = 20
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 20 AND `cj_order_id` = 27;
UPDATE cj_operation
                SET `order_id` = 28
                WHERE `id` = 21
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 21 AND `cj_order_id` = 28;
UPDATE cj_operation
                SET `order_id` = 29
                WHERE `id` = 22
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 22 AND `cj_order_id` = 29;
UPDATE cj_operation
                SET `order_id` = 30
                WHERE `id` = 23
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 23 AND `cj_order_id` = 30;
INSERT cj_operation
                SET
                `order_id` = 80,
                `amount` = 530.00,
                `type` = 'rent',
                `created_at` = 2013-10-01 04:00:00
                ,`contract_id` = 16;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 23 AND `cj_order_id` = 80;
UPDATE cj_operation
                SET `order_id` = 31
                WHERE `id` = 24
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 24 AND `cj_order_id` = 31;
INSERT cj_operation
                SET
                `order_id` = 53,
                `amount` = 1400.00,
                `type` = 'rent',
                `created_at` = 2013-11-21 18:37:11
                ,`contract_id` = 27;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 24 AND `cj_order_id` = 53;
INSERT cj_operation
                SET
                `order_id` = 71,
                `amount` = 1400.00,
                `type` = 'rent',
                `created_at` = 2013-11-21 18:37:11
                ,`contract_id` = 27;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 24 AND `cj_order_id` = 71;
UPDATE cj_operation
                SET `order_id` = 32
                WHERE `id` = 25
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 25 AND `cj_order_id` = 32;
UPDATE cj_operation
                SET `order_id` = 34
                WHERE `id` = 26
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 26 AND `cj_order_id` = 34;
UPDATE cj_operation
                SET `order_id` = 35
                WHERE `id` = 27
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 27 AND `cj_order_id` = 35;
UPDATE cj_operation
                SET `order_id` = 36
                WHERE `id` = 28
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 28 AND `cj_order_id` = 36;
UPDATE cj_operation
                SET `order_id` = 37
                WHERE `id` = 29
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 29 AND `cj_order_id` = 37;
INSERT cj_operation
                SET
                `order_id` = 46,
                `amount` = 500.10,
                `type` = 'rent',
                `created_at` = 2013-12-15 13:50:30
                ,`contract_id` = 42;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 29 AND `cj_order_id` = 46;
INSERT cj_operation
                SET
                `order_id` = 64,
                `amount` = 500.10,
                `type` = 'rent',
                `created_at` = 2013-12-15 13:50:30
                ,`contract_id` = 42;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 29 AND `cj_order_id` = 64;
UPDATE cj_operation
                SET `order_id` = 41
                WHERE `id` = 30
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 30 AND `cj_order_id` = 41;
UPDATE cj_operation
                SET `order_id` = 42
                WHERE `id` = 31
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 31 AND `cj_order_id` = 42;
UPDATE cj_operation
                SET `order_id` = 43
                WHERE `id` = 32
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 32 AND `cj_order_id` = 43;
UPDATE cj_operation
                SET `order_id` = 44
                WHERE `id` = 33
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 33 AND `cj_order_id` = 44;
UPDATE cj_operation
                SET `order_id` = 45
                WHERE `id` = 34
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 34 AND `cj_order_id` = 45;
INSERT cj_operation
                SET
                `order_id` = 82,
                `amount` = 418.00,
                `type` = 'rent',
                `created_at` = 2013-12-19 00:44:32
                ,`contract_id` = 45;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 34 AND `cj_order_id` = 82;
UPDATE cj_operation
                SET `order_id` = 47
                WHERE `id` = 35
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 35 AND `cj_order_id` = 47;
UPDATE cj_operation
                SET `order_id` = 48
                WHERE `id` = 36
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 36 AND `cj_order_id` = 48;
UPDATE cj_operation
                SET `order_id` = 49
                WHERE `id` = 37
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 37 AND `cj_order_id` = 49;
INSERT cj_operation
                SET
                `order_id` = 74,
                `amount` = 3456.00,
                `type` = 'rent',
                `created_at` = 2014-01-22 18:09:49
                ,`contract_id` = 49;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 37 AND `cj_order_id` = 74;
UPDATE cj_operation
                SET `order_id` = 52
                WHERE `id` = 38
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 38 AND `cj_order_id` = 52;
UPDATE cj_operation
                SET `order_id` = 54
                WHERE `id` = 39
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 39 AND `cj_order_id` = 54;
UPDATE cj_operation
                SET `order_id` = 55
                WHERE `id` = 40
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 40 AND `cj_order_id` = 55;
UPDATE cj_operation
                SET `order_id` = 56
                WHERE `id` = 41
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 41 AND `cj_order_id` = 56;
UPDATE cj_operation
                SET `order_id` = 57
                WHERE `id` = 42
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 42 AND `cj_order_id` = 57;
UPDATE cj_operation
                SET `order_id` = 58
                WHERE `id` = 43
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 43 AND `cj_order_id` = 58;
UPDATE cj_operation
                SET `order_id` = 60
                WHERE `id` = 44
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 44 AND `cj_order_id` = 60;
UPDATE cj_operation
                SET `order_id` = 61
                WHERE `id` = 45
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 45 AND `cj_order_id` = 61;
INSERT cj_operation
                SET
                `order_id` = 68,
                `amount` = 9876.00,
                `type` = 'rent',
                `created_at` = 2014-02-18 15:56:55
                ,`contract_id` = 57;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 45 AND `cj_order_id` = 68;
UPDATE cj_operation
                SET `order_id` = 65
                WHERE `id` = 47
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 47 AND `cj_order_id` = 65;
UPDATE cj_operation
                SET `order_id` = 66
                WHERE `id` = 48
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 48 AND `cj_order_id` = 66;
UPDATE cj_operation
                SET `order_id` = 67
                WHERE `id` = 49
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 49 AND `cj_order_id` = 67;
UPDATE cj_operation
                SET `order_id` = 69
                WHERE `id` = 50
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 50 AND `cj_order_id` = 69;
UPDATE cj_operation
                SET `order_id` = 70
                WHERE `id` = 51
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 51 AND `cj_order_id` = 70;
UPDATE cj_operation
                SET `order_id` = 75
                WHERE `id` = 54
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 54 AND `cj_order_id` = 75;
UPDATE cj_operation
                SET `order_id` = 76
                WHERE `id` = 55
                ;
DELETE `cj_order_operation`
                WHERE `cj_operation_id` = 55 AND `cj_order_id` = 76;

# Version 20140313130208
ALTER TABLE cj_operation
                CHANGE order_id order_id BIGINT NOT NULL;
DROP TABLE cj_order_operation;
ALTER TABLE cj_operation
                DROP INDEX UNIQ_21F5D92D2576E0FD,
                ADD INDEX IDX_21F5D92D2576E0FD (contract_id);
ALTER TABLE cj_operation
                ADD order_id BIGINT DEFAULT NULL;
ALTER TABLE cj_operation
                ADD CONSTRAINT FK_21F5D92D8D9F6D38
                FOREIGN KEY (order_id)
                REFERENCES cj_order (id);
CREATE INDEX IDX_21F5D92D8D9F6D38 ON cj_operation (order_id);
