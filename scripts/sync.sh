#!/bin/bash
user=$1
host=$2
address="${user}@${host}"

echo "Start syncing from server."
echo "Connect with ${address}."

# Because a tar file can have issues with long file names, we have to use zip. The commands below can bu used to archive and unpack with tar.
# ./vendor/bin/drush archive:dump --files --destination=/home/${user}/${host}/.lando-sync/${host}-files.tar.gz --overwrite
# tar -xzf .lando-sync/${host}-files.tar.gz -C .lando-sync

# Creates the .lando directory on the remote server if it doesn't exist.
# Creates archives of the files and database.
echo "Creating archives of the files and database on te remote server."
ssh -q -o loglevel=ERROR ${address} bash <<EOF
  cd ~/${host}

  if [ ! -d .lando-sync ]; then
    mkdir .lando-sync
  fi

  ./vendor/bin/drush archive:dump --db --destination=/home/${user}/${host}/.lando-sync/${host}-db.tar.gz --overwrite
  zip -q -r /home/${user}/${host}/.lando-sync/${host}-files.zip /home/${user}/${host}/web/sites/default/files
  exit
EOF

# Copy the archives to the local machine.
echo "Copy the archives to the local machine."
mkdir .lando-sync
scp -rp ${address}:/home/${user}/${host}/.lando-sync/${host}-db.tar.gz .lando-sync
scp -rp ${address}:/home/${user}/${host}/.lando-sync/${host}-files.zip .lando-sync

# Delete the archives on the remote server.
echo "Delete the archives to the remote machine."
ssh -q -o StrictHostKeychecking=no ${address} bash <<EOF
  if [ -d /home/${user}/${host}/.lando-sync ]; then
      rm -rf /home/${user}/${host}/.lando-sync
  fi

  exit
EOF

# Extract the archives.
echo "Extract the local archives."
tar -xzf .lando-sync/${host}-db.tar.gz -C .lando-sync
unzip -q .lando-sync/${host}-files.zip -d .lando-sync/files

# Import the database.
if [ -f .lando-sync/database/database.sql ]; then
  echo "Database found in archive."
  echo "Start importing the database."
  ./vendor/bin/drush sql-drop -y --quiet
  ./vendor/bin/drush sqlc < .lando-sync/database/database.sql
fi

# Copy the files.
if [ -d .lando-sync/files ]; then
  echo "Files found in archive."
  echo "Start moving the files."
  rm -rf web/sites/default/files
  mv .lando-sync/files web/sites/default/.
fi

# Clean up.
echo "Delete the local archive files."
rm -rf .lando-sync
