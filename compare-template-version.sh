#!/bin/bash

# Get the lando logger
. /helpers/log.sh

lando_green "Compare template versions..."

current_version=$(awk '/template_version:/{print $NF;}' /app/.lando.base.yml)
newest_template=$(wget -qO- https://raw.githubusercontent.com/egoosmann/lando-drupal-template/master/.lando.base.yml)
newest_version=$(wget -qO- $newest_template)

if [[ $current_version = $newest_version ]]
then
  lando_green "Your template is up to date!"
else
  lando_red "Your template is outdated!"
fi
