Credit-Jeeves & Rent-Track
============================

http://symfony.com/doc/2.2/contributing/code/standards.html - Default coding standard for this project

YAML - is default configs format

Annotation - is default configuration where it is possible

DEV resources
------------

http://dev.creditjeeves.com/phpMyAdmin


Installation
------------

```
git clone REPO_URL FOLDER
cd FOLDER
php bin/composer.phar install --no-scripts
php bin/environment.php --prod
```

Possible params for environment.php --help or in the file /data/environment/profiles.ini

Manual Installation
------------

```
git clone ... FOLDER
cd FOLDER
cp app/config/parameters_prod.yml app/config/parameters.yml # or parameters_dev.yml
php bin/composer.phar install # --dev # requires to run tests
#cp data/files/web/* web/ # it need only for dev environment
cd vendor/credit-jeeves/credit-jeeves
./bin/build.sh

```

Additional required configuration
http://symfony.com/doc/current/book/installation.html#configuration-and-setup

Manual Installation for dev env
-------------------------------

```
php bin/console.php doctrine:database:drop --force
php bin/console.php doctrine:database:create
php bin/console.php doctrine:schema:create --app=rj
php bin/console.php khepin:yamlfixtures:load --app=rj --env=dev


php bin/console.php doctrine:database:drop --force --env=test
php bin/console.php doctrine:database:create --env=test
php bin/console.php doctrine:schema:create --app=rj --env=test
php bin/console.php khepin:yamlfixtures:load --app=rj --env=test
```

or

```
php bin/environment.php --profile=data/environment/dev/builder/db.ini
```


Manual migration for prod env
-------------------------------

```
php bin/console.php doctrine:migrations:migrate
```

JavaScript and CSS
------------------

Auto update cached css & js
```
php app/console assetic:dump --app=cj --watch
php app/console assetic:dump --app=rj --watch
```

Tests
-----

Selenium server (RC) version 2.33.0 does not work with Firefox 23.x


STG
---
Last DB 20130924_credit_jeeves_2_before_RT.sql
