#!/bin/bash
#
# A script to run Drupal VM functional tests.
printf "\n"${green}"Building local environment via Docker Compose."${neutral}"\n"
docker-compose up -d

printf "\n"${green}"Installing the Drupal Photo Gallery site from configuration."${neutral}"\n"
docker exec d8pix-local bash -c "drush site-install minimal -y \
      --site-name='Drupal Photo Gallery' \
      --account-pass=admin \
      --config-dir=../config/sync \
      --db-url=mysql://drupal:drupal@localhost/drupal \
      --uri=local.d8pix.com"

printf "\n"${green}"Checking if the site was installed successfully."${neutral}"\n"
curl --head "http://local.d8pix.com/"

printf "\n"${green}"Tearing down local environment via Docker Compose."${neutral}"\n"
docker-compose down
