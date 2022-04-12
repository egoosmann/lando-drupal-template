#!/bin/bash

# Get the lando logger
. /helpers/log.sh

lando_green "Start base file version check"

cd /tmp

lando_check "Dowload base file from repository"
wget -q https://raw.githubusercontent.com/egoosmann/lando-drupal-template/master/.lando.base.yml

current_version=$(awk '/template_version:/{print $NF;}' /app/.lando.base.yml)
newest_version=$(awk '/template_version:/{print $NF;}' /tmp/.lando.base.yml)

lando_check "Compare base file versions"
if [[ $current_version = $newest_version ]]
then
  lando_check "Your base file is up to date!"
else
  lando_red "Your base file is outdated!"
  read -p "Do you want to update your base file? [y/N] " -r
  if [[ $REPLY =~ ^[Yy]$ ]]
  then
    lando_green "Update your base file"
    mv /tmp/.lando.base.yml /app/.lando.base.yml
    lando_check "Your base file has been updated!"
  else
    lando_yellow "Skip base file update"
  fi
fi

rm -f /tmp/.lando.base.yml
