#!/bin/bash

# Get the lando logger
. /helpers/log.sh

lando_green "Compare template versions..."

cd /tmp

wget -q https://raw.githubusercontent.com/egoosmann/lando-drupal-template/master/.lando.base.yml

current_version=$(awk '/template_version:/{print $NF;}' /app/.lando.base.yml)
newest_version=$(awk '/template_version:/{print $NF;}' /tmp/.lando.base.yml)

rm -f /tmp/.lando.base.yml

if [[ $current_version = $newest_version ]]
then
  lando_green "Your template is up to date!"
else
  lando_red "Your template is outdated!"
fi
