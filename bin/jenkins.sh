#! /bin/sh

DIR="$(cd `dirname $0` ; pwd)"
BUILDS_DIR="$WORKSPACE/../../jobs/$JOB_NAME/builds"
BUILD_DIR="$DIR/../app/logs/build"
PHPUNIT_PATH=`which phpunit`

#DB_DUMP="$DIR/../data/sql/dump_20130328.sql"
#DB_NAME=cj2_migration
#DB_USER=credit_jeeves
#DB_PASSWORD=passw0rd

rm -rf $BUILD_DIR/*
#rm -rf vendor/*
mkdir $BUILD_DIR/coverage
mkdir $BUILD_DIR/coverage/html

if [ ! -f $DIR/vendor/autoload.php ]; then
     php bin/composer.phar install
fi

php bin/environment.php --dev || exit 1

echo "##### RUN PHPUNIT ALL TESTS #####"
#nice -n 5
php -C -q -d memory_limit=1024M $PHPUNIT_PATH -v \
  --log-junit=$BUILD_DIR/allTests.xml
#  --coverage-clover=$BUILD_DIR/coverage/clover.xml \
#  --coverage-html=$BUILD_DIR/coverage/html \

#echo "##### RUN QUNIT TESTS #####"
#if nice -n 5 sh bin/js.sh ; then
#  echo 'OK' > $BUILD_DIR/js.res
#else
#  echo 'FAIL' > $BUILD_DIR/js.res
#fi
#cat $BUILD_DIR/js.res

#echo "##### RUN MIGRATION TEST #####"

#mysql -u$DB_USER -p$DB_PASSWORD -D$DB_NAME < "$DIR/../data/sql/drop_all_tables.sql" &&
#mysql -u$DB_USER -p$DB_PASSWORD -D$DB_NAME < $DB_DUMP &&
#if php symfony doctrine:migrate --env=migration ; then
#  echo 'OK' > $BUILD_DIR/migration.res
#  echo "##### RUN DB TEST #####"
#  mysql -u$DB_USER -p$DB_PASSWORD -D$DB_NAME < "$DIR/../data/sql/drop_all_data.sql" &&
#  if php symfony doctrine:data-load --env=migration ; then
#    echo 'OK' > $BUILD_DIR/migration_structure.res
##    mysqldump -u$DB_USER -p$DB_PASSWORD $DB_NAME > $DB_DUMP # TODO find out good procedure for this
#  else
#    echo 'FAIL' > $BUILD_DIR/migration_structure.res
#  fi
#  cat $BUILD_DIR/migration_structure.res
#else
#  echo 'FAIL' > $BUILD_DIR/migration.res
#fi
#cat $BUILD_DIR/migration.res

echo "##### CHECKS CODING STANDARDS #####"
if ./bin/cs.sh "--report=checkstyle --report-file=$BUILD_DIR/phpcs.xml -vvv ./" ; then
  echo 'OK' > $BUILD_DIR/phpcs.res
else
  echo 'FAIL' > $BUILD_DIR/phpcs.res
fi

exit 0
