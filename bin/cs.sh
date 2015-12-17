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
$BASE_DIR/web/_intellij_phpdebug_validator.php,\
$BASE_DIR/bin/doctrine.php,\
$BASE_DIR/web/,\
$BASE_DIR/app/,\
$BASE_DIR/src/RentJeeves/ExternalApiBundle/Services/Yardi/Soap"

$CS_PATH -d memory_limit=1024M --standard=$BASE_DIR/vendor/credit-jeeves/core-bundle/CreditJeeves/CoreBundle/CS -p --ignore=$IGNOR_DIRS $1
