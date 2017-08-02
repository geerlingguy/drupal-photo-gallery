FROM geerlingguy/drupal-vm:latest
MAINTAINER Jeff Geerling

# Install imagemagick.
RUN apt-get install -y imagemagick

WORKDIR /var/www/drupalvm/drupal/web
EXPOSE 22 80 443 3306 8025
