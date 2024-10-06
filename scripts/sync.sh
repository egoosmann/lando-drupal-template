#!/bin/bash

# Get the lando logger
. /helpers/log.sh

user=$1
host=$2
address="${user}@${host}"

lando_green "Start syncing from server."
lando_green "Connect with ${address}."

# Creates the .lando directory on the remote server if it doesn't exist.
# Creates archives of the files and database.
lando_green "Create archives of the files and database on te remote server."
ssh -o StrictHostKeychecking=no ${address} <<EOF
  cd ~/${host}

  if [ ! -d .lando-sync ]; then
    mkdir .lando-sync
  fi

  ./vendor/bin/drush archive:dump --db --destination=/home/${user}/${host}/.lando-sync/${host}-db.tar.gz --overwrite
  ./vendor/bin/drush archive:dump --files --destination=/home/${user}/${host}/.lando-sync/${host}-files.tar.gz --overwrite
  exit
EOF

# Copy the archives to the local machine.
lando_green "Copy the archives to the local machine."
mkdir .lando-sync
scp -rp ${address}:/home/${user}/${host}/.lando-sync/${host}-db.tar.gz .lando-sync
scp -rp ${address}:/home/${user}/${host}/.lando-sync/${host}-files.tar.gz .lando-sync

# Delete the archives on the remote server.
lando_green "Delete the archives to the remote machine."
ssh -o StrictHostKeychecking=no ${address} <<EOF
  if [ -d /home/${user}/${host}/.lando-sync ]; then
      rm -rf /home/${user}/${host}/.lando-sync
  fi

  exit
EOF

# Extract the archives.
lando_green "Extract the local archives."
tar -xvzf .lando-sync/${host}-db.tar.gz -C .lando-sync
tar -xvzf .lando-sync/${host}-files.tar.gz -C .lando-sync

# Import the database.
if [ -f .lando-sync/database/database.sql ]; then
  lando_green "Database found in archive."
  lando_green "Start importing the database."
  ./vendor/bin/drush sql-drop -y
  ./vendor/bin/drush sqlc < .lando-sync/database/database.sql
fi

# Copy the files.
if [ -d .lando-sync/files ]; then
  lando_green "Files found in archive."
  lando_green "Start moving the files."
  rm -rf web/sites/default/files
  mv .lando-sync/files web/sites/default/.
fi

# Clean up.
lando_green "Delete the local archive files."
rm -rf .lando-sync
