#!/bin/sh

./bin/console doctrine:database:drop --force --env=dev
./bin/console doctrine:database:create --env=dev
./bin/console doctrine:schema:create --env=dev --app=rj
./bin/console khepin:yamlfixtures:load --env=dev

./bin/console doctrine:database:drop --force --env=test
./bin/console doctrine:database:create --env=test
./bin/console doctrine:schema:create --env=test --app=rj
./bin/console khepin:yamlfixtures:load --env=test