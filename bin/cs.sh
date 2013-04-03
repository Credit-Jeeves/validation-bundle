#! /bin/sh

BASE_DIR="$(cd `dirname $0` ; pwd)"

BASE_DIR=$(echo $BASE_DIR | replace '/bin' '')

CS_PATH=`which phpcs`

if [ -z "$CS_PATH" ]; then
  echo "Please install PHP_CodeSniffer"
  exit 1
fi

IGNOR_DIRS="$BASE_DIR/vendor/,$BASE_DIR/app/cache/,$BASE_DIR/data/,$BASE_DIR/app/log/,\
$BASE_DIR/web/uploads/,$BASE_DIR/web/js/vendor/"

$CS_PATH -d memory_limit=1024M --standard=$BASE_DIR/lib/cjCS -p --ignore=$IGNOR_DIRS $1
