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
cd vendor/CreditJeevesSf1
./bin/build.sh

```

Additional required configuration
http://symfony.com/doc/current/book/installation.html#configuration-and-setup
