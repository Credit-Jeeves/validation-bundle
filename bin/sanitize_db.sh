#!/bin/bash -e
#
# rt_sanitize.sh
#
# Take a sanitized snapshot of the renttrack DB that contains no PII
#
# By Cary Penniman
#
export SNAP_DATE=$(date +"%Y%m%d")
export DIR="$( dirname "${BASH_SOURCE[0]}" )"

#
# Environmental Settings
#
export DB_USER="root"
export DB_NAME="renttrack"
#export DB_NAME="rj_stg"
export SANITIZE_SCRIPT_SQL="${DIR}/../data/files/rt_sanitize.sql"

#
# You should not need to change anything below this line
#
export BACKUP_FILE="${SNAP_DATE}_${DB_NAME}_backup.sql"
export CLEAN_DUMP_FILE="./${SNAP_DATE}_sanitized_${DB_NAME}_backup.sql"
export CLEAN_DB_NAME="${DB_NAME}_sanitized"

function delete_snaphot {
    echo "Removing snapshots from server"
    rm $BACKUP_FILE $CLEAN_DUMP_FILE
}

echo "Please enter the database password for ${DB_USER}."
read -s PASSWORD

echo "Current list of databases..."
mysql -u $DB_USER -p$PASSWORD --execute="show databases;" # just a test

# Create a copy of the production database, within the production environment:
#
echo "Dump ${DB_NAME} to ${BACKUP_FILE}..."
mysqldump --lock-all-tables -u $DB_USER -p$PASSWORD $DB_NAME > $BACKUP_FILE

echo "Create ${CLEAN_DB_NAME} DB where sanitization will run..."
set +e
mysql -u $DB_USER -p$PASSWORD --execute="drop database ${CLEAN_DB_NAME};"
set -e
mysql -u $DB_USER -p$PASSWORD --execute="create database ${CLEAN_DB_NAME};"

echo "Import dump from ${BACKUP_FILE} to ${CLEAN_DB_NAME}..."
mysql -u $DB_USER -p$PASSWORD $CLEAN_DB_NAME < $BACKUP_FILE

echo "Run sanitize script..."
mysql -u $DB_USER -p$PASSWORD $CLEAN_DB_NAME < $SANITIZE_SCRIPT_SQL

echo "Dump santized DB to ${CLEAN_DUMP_FILE}..."
mysqldump --lock-all-tables -u $DB_USER -p$PASSWORD $CLEAN_DB_NAME > $CLEAN_DUMP_FILE

echo -n "Performing sanity check for proper sanitization..."

if grep -iq darryl $CLEAN_DUMP_FILE; then
    echo "  ERROR: Found PII in snapshot!!"
    delete_snaphot
    exit 1
else
    echo "ok."
fi

echo -n "Archiving snapshot..."
tar -cvzf $CLEAN_DUMP_FILE.tar.gz $CLEAN_DUMP_FILE

# Deliver the dump to the development team.
#
# TODO : upload to private S3 bucket and send email

delete_snaphot




