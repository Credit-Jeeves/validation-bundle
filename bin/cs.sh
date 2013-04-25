#! /bin/sh

BASE_DIR="$(cd `dirname $0` ; pwd)"

BASE_DIR=$(echo $BASE_DIR | replace '/bin' '')

CS_PATH=`which phpcs`

if [ -z "$CS_PATH" ]; then
  echo "Please install PHP_CodeSniffer"
  exit 1
fi

IGNOR_DIRS="$BASE_DIR/vendor/,$BASE_DIR/data/,\
$BASE_DIR/web/uploads/,$BASE_DIR/web/js/vendor/,\
$BASE_DIR/src/*/*/Resources/,$BASE_DIR/web/config_dev.php,\
$BASE_DIR/src/CreditJeeves/TestBundle/Experian/,\
$BASE_DIR/src/CreditJeeves/ExperianBundle/Pidkiq,\
$BASE_DIR/src/CreditJeeves/ExperianBundle/NetConnect,\
$BASE_DIR/src/CreditJeeves/CoreBundle/sfConfig.php,\
$BASE_DIR/src/CreditJeeves/CoreBundle/CS/,$BASE_DIR/src/CreditJeeves/CoreBundle/Arf/,\
$BASE_DIR/app/"

$CS_PATH -d memory_limit=1024M --standard=$BASE_DIR/src/CreditJeeves/CoreBundle/CS -p --ignore=$IGNOR_DIRS $1
