Credit-Jeeves Symfony 2.2
========================

http://symfony.com/doc/2.2/contributing/code/standards.html - Default coding standard for this project

YAML - is default configs format

Annotation - is default configuration where it is possible

Installation
------------

```
git clone ... FOLDER
cd FOLDER
php bin/composer.phar install
php bin/environment.php --prod
```

Possible params for environment.php --help or in the file /data/environment/profiles.ini


Manual Installation
------------

```
git clone ... FOLDER
cd FOLDER
php bin/composer.phar install # --dev # requires to run tests
cp app/config/parameters_prod.yml app/config/parameters.yml # or parameters_dev.yml
#cp data/files/web/* web/ # it need only for dev environment
cd vendor/credit-jeeves/credit-jeeves
./bin/build.sh

```

Additional required configuration
http://symfony.com/doc/current/book/installation.html#configuration-and-setup

RentJeeves
==========

Installation
------------

To be done.

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
