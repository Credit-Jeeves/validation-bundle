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
http://stg.renttrack.com/
http://stg2.creditjeeves.com/
ATB - http://stg2.creditjeeves.com:8080/

DEV
---
http://dev2.creditjeeves.com/
http://dev2.renttrack.com/
http://dev.creditjeeves.com/phpMyAdmin/
http://dev2.renttrack.com:81/ - JENKINS

SELENIUM
---
For selenium server need to mount folder
sudo sshfs -o allow_other -p 22 ec2-user@dev.creditjeeves.com:/var/www/dev2.creditjeeves.com/data/fixtures -o \
 IdentityFile=/home/ec2-user/alex /var/www/dev2.creditjeeves.com/data/fixtures

ec2-user@54.243.130.78 - SELENIUM ssh user alex
./screen.sh - run selenium for RT
./screen2.sh - run selenium for sf1 CJ

PHP Coding Standards Fixer
---
To check your code against PHP Coding Standards, run from the project root directory:
./bin/cs.sh <path_to_file>

The PHP Coding Standards Fixer tool fixes most issues in your code when you want to follow the PHP coding standards as defined in the PSR-1 and PSR-2 documents.

We use Fixer with git pre-commit hook.
For install hook - run:
`php ./bin/pre-commit -i`

For fix without hook:
`php ./vendor/fabpot/php-cs-fixer/php-cs-fixer fix {$fileName} --level=all`

_______________________________
Yardi Soap, generate class from wsdl, example

php ./vendor/besimple/wsdl2php/bin/wsdl2php.php \
    -w"./src/RentJeeves/ExternalApiBundle/Resources/files/ItfResidentTransactions20.asmx.wsdl" \
    -v2 \
    -nRentJeeves\\ExternalApiBundle\\Services\\Yardi\\Soap \
    -cClient \
    -osrc \
    --backup \
    --instance_on_getter \
    --access=protected \
    --empty_parameter_name \
    --wsdl2java_style
_______________________________

TODO
----

Environment:
1. Make sure default time zone is correct value
2. If XDebug is installed - need to do required check for nesting level
3. Add check for encryption keys
    /etc/cj/mk
    /var/local/cj/rk
4. Add check for nginx buffer values (summary page requires)
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;