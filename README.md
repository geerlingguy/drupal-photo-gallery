# Drupal Photo Gallery

[![Build Status](https://travis-ci.org/geerlingguy/drupal-photo-gallery.svg?branch=master)](https://travis-ci.org/geerlingguy/drupal-photo-gallery)

An intelligent photo gallery built with [Drupal 8](https://www.drupal.org/8), Amazon [S3](https://aws.amazon.com/s3/), [Lambda](https://aws.amazon.com/lambda/), and [Rekognition](https://aws.amazon.com/rekognition/).

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
  1. Edit your hosts file and add: `192.168.88.23  local.d8pix.com`
  1. Run the command (if on Mac) `sudo ifconfig lo0 alias 192.168.88.23/24`
  1. Run `docker-compose up -d`
  1. Either:
    - Import the site's production MySQL database (if you're working from a live production site).
    - Run the `drush site-install` command below (to set it up locally without any live content).
  1. If you need to see images from a live site, enable the Stage File Proxy module.

### Running Drush commands

You can either start an interactive session inside the container with:

    docker exec -it d8pix /bin/bash

Or you can run one-off commands:

    docker exec d8pix bash -c "drush --root=/var/www/drupalvm/drupal/web --uri=local.d8pix.com cex -y"

### Install site from existing configuration

Install a fresh instance of the Acquia Photo Gallery site with the following drush command (run it inside the Docker container using the `docker exec -it...` command in 'Running Drush commands'):

    drush site-install minimal -y \
      --site-name="Drupal Photo Gallery" \
      --account-pass=admin \
      --config-dir=../config/sync \
      --db-url=mysql://drupal:drupal@localhost/drupal \
      --uri=local.d8pix.com

Then, log into the site using the credentials:

  - **Email**: `admin`
  - **Password**: `admin`

> Alternatively, you can provide your own `--account-name`, `--account-email`, and `--account-pass`.

## Updating Drupal site configuration

This site uses a full configuration export, and to update the site's configuration, you can run (from within the docroot):

    drush --uri=local.d8pix.com cex -y

This should update the configuration as stored in `config/default`. Commit this new config, then test the configuration by reinstalling the site (to make sure the config works on a fresh install).

## AWS setup

To allow AWS Lambda to call back to your Drupal site (so faces and labels can be integrated with your media entities), you must have Drupal running on a publicly-accessible URL. Therefore before any of the AWS integration for Rekognition can be tested, make sure you're running an installation of this site on a server with a publicly-accessible URL.

After that, make sure you create a Drupal user account with permission to create and update nodes and taxonomy terms, then store that account's credentials for use by the Rekognition AWS Lambda function.

After you have a Drupal site installed, and the API user created, do the following to prepare your local workstation and AWS account for the Rekognition resources:

  1. Install AWS CLI.
  1. Generate a 'programmatic access' AWS access key for an AWS IAM User with admin rights.
  1. Store the access key ID and secret in a location suitable for use with the AWS CLI.
  1. Create an S3 bucket named `drupal-lambda`, and upload a .zip archive containing the file `web/modules/contrib/rekognition_api/lambda/index.js`, after renaming the .zip archive to `drupal-media-rekognition.zip` (so the full S3 path is `s3://drupal-lambda/drupal-media-rekognition.zip`)

Now that you're workstation is ready, and the lambda code is in place, run the following command to deploy the required AWS resources via AWS CloudFormation:

    aws cloudformation deploy \
      --region us-east-1 \
      --profile mm \
      --template-file web/modules/contrib/rekognition_api/lambda/DrupalMediaRekognition.yml \
      --stack-name drupal-media-rekognition \
      --capabilities CAPABILITY_NAMED_IAM \
      --parameter-overrides \
       DrupalUrl='http://www.example.com/' \
       DrupalUsername='rekognition-api-user' \
       DrupalPassword='secure-password-here'

After this operation completes, you should see a message like:

    Successfully created/updated stack - drupal-media-rekognition

If you don't see that message, take a look in the AWS Console in the CloudFormation section, or use the AWS CLI to view detailed logs of what caused any issues.

TODO: Any other setup required?
