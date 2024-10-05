#!/bin/bash
user=$1
host=$2
address="${user}@${host}"

echo "Start syncing from server."
echo "Connect with ${address}."

# Create the .lando directory on the remote server if it doesn't exist.
# Create the archives of the files and database.
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
mkdir .lando-sync
scp -rp ${address}:/home/${user}/${host}/.lando-sync/${host}-db.tar.gz .lando-sync
scp -rp ${address}:/home/${user}/${host}/.lando-sync/${host}-files.tar.gz .lando-sync

# Delete the archives on the remote server.
ssh -o StrictHostKeychecking=no ${address} <<EOF
  if [ -d /home/${user}/${host}/.lando-sync ]; then
      rm -rf /home/${user}/${host}/.lando-sync
  fi

  exit
EOF

# Extract the archives.
tar -xvzf .lando-sync/${host}-db.tar.gz -C .lando-sync
tar -xvzf .lando-sync/${host}-files.tar.gz -C .lando-sync

# Import the database
if [ -f .lando-sync/database/database.sql ]; then
  ./vendor/bin/drush sql-drop -y
  ./vendor/bin/drush sqlc < .lando-sync/database/database.sql
fi

# Copy the files.
if [ -d .lando-sync/files ]; then
  rm -rf web/sites/default/files
  mv .lando-sync/files web/sites/default/.
fi

# Clean up.
rm -rf .lando-sync
