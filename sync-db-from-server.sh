#!/bin/bash
user=$1
host=$2
address="${user}@${host}"

ssh ${address} <<EOF
  cd ~/${host}
  mkdir ~/.lando
  drush sql-dump > ~/.lando/database-${host}.sql
  exit
EOF

scp -rp ${address}:/home/${user}/.lando/database-${host}.sql /tmp

# Drop the current database.
drush sql-drop -y
# Import the new database.
drush sqlc < /tmp/database-${host}.sql
