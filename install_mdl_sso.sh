#!/bin/sh

SCRIPT_DIR=$(cd $(dirname $0) && pwd)
DBSERVER=${DBSERVER-"127.0.0.1"}
DBNAME=${DBNAME:-"cube213_dev"}
DBUSER=${DBUSER:-"cube213_dev_user"}
DBPASS=${DBPASS:-"password"}
SQL_DIR="${SCRIPT_DIR}/sql"
DBTYPE=$1;

case "${DBTYPE}" in
"pgsql" )
    #-- DB Seting Postgres
    PSQL=psql
    PGUSER=postgres
    DROPDB=dropdb
    CREATEDB=createdb
    DBPORT=5432
    DB=$1;
;;
"mysql" )
    #-- DB Seting MySQL
    MYSQL=mysql
    ROOTUSER=root
    ROOTPASS=$DBPASS
    DBSERVER="127.0.0.1"
    DBPORT=3306
    DB=mysqli;
;;
* ) echo "ERROR:: argument is invaid"
exit
;;
esac

case "${DBTYPE}" in
"pgsql" )
    # PostgreSQL
    echo "create table..."
    ${PSQL} -h ${DBSERVER} -U ${DBUSER} -f ${SQL_DIR}/create_table.sql ${DBNAME}
;;
"mysql" )
    DBPASS=`echo $DBPASS | tr -d " "`
    if [ -n ${DBPASS} ]; then
	PASSOPT="--password=$DBPASS"
	CONFIGPASS=$DBPASS
    fi
    echo "create table..."
    echo "SET SESSION default_storage_engine = InnoDB; SET sql_mode = 'NO_ENGINE_SUBSTITUTION';" |
        cat - ${SQL_DIR}/create_table.sql |
        ${MYSQL} -h ${DBSERVER} -u ${DBUSER} ${PASSOPT} ${DBNAME}
;;
esac

echo "linking plugin..."
cd $SCRIPT_DIR/../../../downloads/plugin/
ln -s ../../vendor/nanasess/mdl_sso/plugins/Sso Sso
ls -al $SCRIPT_DIR/../../../downloads/plugin

echo "Finished Successful!"
