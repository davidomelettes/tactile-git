#!/bin/bash
USER=`whoami`
echo 'running as:' $USER
createdb -U $USER tactile_test.$USER -E UTF8 -T template0
psql -U $USER tactile_test.$USER < database.sql > /dev/null
php5 Tester.php tactile_test.$USER $1
false
while [ $? != 0 ]; do
	sleep 2
	dropdb -U $USER tactile_test.$USER
done

