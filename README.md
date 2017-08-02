# Drupal Photo Gallery

TODO: Description of this site.

## Resources

  - [Rekognition API](https://www.drupal.org/project/rekognition_api) module.
  - [Drupal Photo Gallery](https://github.com/geerlingguy/drupal-photo-gallery) project.
  - Blog posts with background:
    - [Building an Open Source Photo Gallery with Face and Object Recognition (Part 1)](https://dev.acquia.com/blog/building-an-open-source-photo-gallery-with-face-and-object-recognition-part-1/17/07/2017/18466)
    - Part 2 (coming soon!)

## Local setup

This site uses the [Drupal VM Docker base image](https://hub.docker.com/r/geerlingguy/drupal-vm/) to build a local development environment. To use the Docker setup:

  1. Ensure you have Docker and Composer installed.
  1. Run `composer install` in this directory.
  1. Edit your hosts file and add: `192.168.88.23  local.drupal-photo-gallery.com`
  1. Run the command (if on Mac) `sudo ifconfig lo0 alias 192.168.88.23/24`
  1. Run `docker-compose up -d`
  1. Either:
    - Import the site's production MySQL database (if you're working from a live production site).
    - Run the `drush site-install` command below (to set it up locally without any live content).
  1. If you need to see images from a live site, enable the Stage File Proxy module.

### Running Drush commands

You can either start an interactive session inside the container with:

    docker exec -it drupal-photo-gallery-local /bin/bash

Or you can run one-off commands:

    docker exec drupal-photo-gallery-local bash -c "drush --root=/var/www/drupalvm/drupal/web --uri=local.drupal-photo-gallery.com cex -y"

### Install site from existing configuration

Install a fresh instance of the Acquia Photo Gallery site with the following drush command:

    drush site-install minimal -y \
      --site-name="Drupal Photo Gallery" \
      --account-pass=admin \
      --config-dir=../config/sync \
      --db-url=mysql://drupal:drupal@localhost/drupal \
      --uri=local.drupal-photo-gallery.com

Then, log into the site using the credentials:

  - **Email**: `admin`
  - **Password**: `admin`

> Alternatively, you can provide your own `--account-name`, `--account-email`, and `--account-pass`.

## Updating configuration

This site uses a full configuration export, and to update the site's configuration, you can run:

    drush --root=/var/www/drupalvm/drupal/web --uri=local.drupal-photo-gallery.com cex -y

This should update the configuration as stored in `config/default`. Commit this new config, then test the configuration by reinstalling the site (to make sure the config works on a fresh install).
