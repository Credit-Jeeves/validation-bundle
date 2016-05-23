RentTrack
=========
Welcome to the RentTrack core project!

This project contains the RentTrack webapps, api, job queue and background tasks.

Installation
============
Assuming you have the prerequisite server environment (see below). Download and install the application using:

    git clone REPO_URL FOLDER
    cd FOLDER
    php bin/composer.phar install --no-scripts
    php bin/environment.php --prod

Possible params for environment.php --help or in the file /data/environment/profiles.ini

Prerequisites
-------------
We use Chef code to configure our server infrastructure.  In the production environment, this configuration is deployed to a cluster of servers.  In staging and development environments, the same configuration is deployed to a "collapsed" server.  A "collapsed" server is a server that runs all RentTrack services on a single machine.  See the [Collapsed Playbook](https://credit.atlassian.net/wiki/display/RT/Collapsed+Playbook#CollapsedPlaybook-LocalCollapsedServer(UsingVagrant)) for how to setup and deploy code your own collapsed server on a local virtual machine.

The general prerequisites are

* LNMP-stack setup
    * PHP 5.6
    * MySQL 5.1
    * and Nginx
* Selenium server (for running function tests)

The Chef source code lives at https://github.com/Credit-Jeeves/devops-collapsed



Contributing
============
Before making a pull request please read our [Developer Guidelines]() and [Branching Policy](https://credit.atlassian.net/wiki/display/RT/Branching+Management+Policy).
New engineers should read the [New Engineer On Boarding](https://credit.atlassian.net/wiki/display/RT/New+Engineer+On-boarding) wiki page.

Running Tests
-------------
We currently run both unit and functional tests together via phpunit. To run:

    php -C -q -d memory_limit=8192M /usr/bin/phpunit --debug -v --testsuite "Project Test Suite"

Yeah, it really takes 8GB!!


PHP Coding Standards Fixer
--------------------------
To check your code against PHP Coding Standards, run from the project root directory:

    ./bin/cs.sh <path_to_file>

The PHP Coding Standards Fixer tool fixes most issues in your code when you want to follow the PHP coding standards as defined in the PSR-1 and PSR-2 documents.

We use Fixer with git pre-commit hook.
For install hook - run:

    php ./bin/pre-commit -i

For fix without hook:

    php ./vendor/fabpot/php-cs-fixer/php-cs-fixer fix {$fileName} --level=all

JavaScript and CSS Development
------------------------------

Auto update cached css & js as you edit:

    php app/console assetic:dump --app=cj --watch
    php app/console assetic:dump --app=rj --watch


Managing Dependencies
---------------------
When updating composer dependency please only edit composer.json -- never directly edit composer.lock.
Once composer.json is updated, remove composer.lock and rebuild depedency graph:

    rm composer.lock
    php -d memory_limit=-1 bin/composer.phar install  -vv

NOTE: you will need a dev system with at lease 4GB of RAM to do this.


Auto-generating Yardi Soap Classes
----------------------------------
Example:

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


TODO
====

Environment:
1. Make sure default time zone is correct value
2. If XDebug is installed - need to do required check for nesting level
3. Add check for encryption keys
    /etc/cj/mk
    /var/local/cj/rk

