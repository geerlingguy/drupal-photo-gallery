#!/bin/bash
#
# A script to run Drupal VM functional tests.
set -e

printf "\n"${green}"Building local environment via Docker Compose."${neutral}"\n"
docker-compose up -d

printf "\n"${green}"Installing the Drupal Photo Gallery site from configuration."${neutral}"\n"
docker exec d8pix bash -c "\
      drush site-install minimal -y  \
      --site-name='Drupal Photo Gallery' \
      --account-pass=admin \
      --config-dir=../config/sync \
      --uri=local.d8pix.com"

printf "\n"${green}"Checking if the site was installed successfully."${neutral}"\n"
curl --head "http://local.d8pix.com/"
curl "http://local.d8pix.com/" | grep "Enter your Drupal Photo Gallery username."

printf "\n"${green}"Tearing down local environment via Docker Compose."${neutral}"\n"
docker-compose down
