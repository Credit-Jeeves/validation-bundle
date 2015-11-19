#! /bin/sh

set -x

BUILD="dev"
if [ $1 ]; then
    BUILD=$1
fi

DIR="$(cd `dirname $0` ; pwd)"
BUILDS_DIR="${WORKSPACE}/../../jobs/${JOB_NAME}_${EXECUTOR_NUMBER}/builds"
BUILD_DIR="$DIR/../app/logs/build"
PHPUNIT_PATH=`which phpunit`
PHPUNIT_PARAMS=(--debug -v --testsuite "Project Test Suite")

export SYMFONY__DATABASE__NAME="renttrack_jenkins"
export SYMFONY__SERVER__NAME="dev2"
export SYMFONY__SELENIUM__HOST__URL="http://10.164.182.167:4444/wd/hub"
if [ "jenkins" = $BUILD ]; then
    export SYMFONY__DATABASE__NAME="renttrack_jenkins_pr${EXECUTOR_NUMBER}"
    export SYMFONY__SERVER__NAME="pr${EXECUTOR_NUMBER}.test"
    export SYMFONY__SELENIUM__HOST__URL="http://10.164.182.167:444${EXECUTOR_NUMBER}/wd/hub"
fi

rm -rf $BUILD_DIR/*
mkdir $BUILD_DIR/coverage
mkdir $BUILD_DIR/coverage/html


if [ ! -f $DIR/vendor/autoload.php ]; then
     rm -rf vendor/credit-jeeves/credit-jeeves
     php bin/composer.phar install --no-scripts
fi

php bin/environment.php --$BUILD || exit 1

# Code coverage disabled since it triples build time
#if [ "master" = $BUILD ]; then
#    PHPUNIT_PARAMS="$PHPUNIT_PARAMS --coverage-clover=$BUILD_DIR/coverage/clover.xml --coverage-html=$BUILD_DIR/coverage/html"
#fi

echo "##### RUN PHPUNIT ALL TESTS #####"
nice -n 2 php -C -q -d memory_limit=8192M $PHPUNIT_PATH \
  "${PHPUNIT_PARAMS[@]}" \
  --log-junit=$BUILD_DIR/allTests.xml

echo "##### RUN MIGRATION TEST #####"

php bin/console.php --app=rj --env=migration doctrine:database:drop --force
php bin/console.php --app=rj --env=migration doctrine:database:create && \
php bin/console.php --app=rj --env=migration database:restore 'doctrine.dbal.default_connection' data/files/sql/rj.sql
if php bin/console.php doctrine:migrations:migrate --app=rj --env=migration -n ; then
  echo 'OK' > $BUILD_DIR/rj_migration.res
  echo "##### RUN RJ_DB TEST #####"
  php bin/console.php --app=rj --env=migration database:restore 'doctrine.dbal.default_connection' data/files/sql/drop_all_data.sql
  if php bin/console.php --app=rj --env=migration khepin:yamlfixtures:load ; then
    echo 'OK' > $BUILD_DIR/rj_migration_structure.res
  else
    echo 'FAIL' > $BUILD_DIR/rj_migration_structure.res
  fi
  cat $BUILD_DIR/rj_migration_structure.res
else
  echo 'FAIL' > $BUILD_DIR/rj_migration.res
fi
cat $BUILD_DIR/rj_migration.res

echo "##### CHECKS CODING STANDARDS #####"
if ./bin/cs.sh "--report=checkstyle --report-file=$BUILD_DIR/phpcs.xml ./" ; then
  echo 'OK' > $BUILD_DIR/phpcs.res
else
  echo 'FAIL' > $BUILD_DIR/phpcs.res
fi

exit 0
