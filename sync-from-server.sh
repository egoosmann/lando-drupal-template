#!/bin/bash
user=$1
host=$2
address="${user}@${host}"

ssh ${address} <<EOF
  cd ~/${host}
  mkdir ~/.lando
  drush sql-dump > ~/.lando/database-${host}.sql
  drush archive:dump --destination=/home/${user}/.lando/archive-${host}.tar --no-core
  exit
EOF

scp -rp ${address}:/home/${user}/.lando/database-${host}.sql /tmp
scp -rp ${address}:/home/${user}/.lando/archive-${host}.tar /tmp

mkdir /tmp/archive-${host}
tar xvzf /tmp/archive-${host}.tar -C /tmp/archive-${host}

# Drop the current database.
drush sql-drop -y
# Import the new database.
drush sqlc < /tmp/database-${host}.sql

# Remove previous files backup first
rm -rf /app/web/sites/default/files.lando.bak
# Create new backup of the files
mv /app/web/sites/default/files /app/web/sites/default/files.lando.bak
# Copy the new files from the archive to the files folder.
cp -r /tmp/archive-${host}/web/sites/${host}/files /app/web/sites/default/.
