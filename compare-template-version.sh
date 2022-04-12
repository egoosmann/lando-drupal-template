#!/bin/bash

# Get the lando logger
. /helpers/log.sh

lando_green "Compare .lando.base.yml versions..."

cd /tmp

wget -q https://raw.githubusercontent.com/egoosmann/lando-drupal-template/master/.lando.base.yml

current_version=$(awk '/template_version:/{print $NF;}' /app/.lando.base.yml)
newest_version=$(awk '/template_version:/{print $NF;}' /tmp/.lando.base.yml)

rm -f /tmp/.lando.base.yml

if [[ $current_version = $newest_version ]]
then
  lando_green "Your .lando.base.yml is up to date!"
else
  lando_red "Your .lando.base.yml is outdated!"
  read -p "Do you want to update your .lando.base.yml file? [Y/n] " -n 1 -r
  echo # (optional) move to a new line
  if [[ $REPLY =~ ^[Yy]$ ]]
  then
    lando_green "Replace file."
  fi
fi
