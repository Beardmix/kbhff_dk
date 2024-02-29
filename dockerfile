# Use an official PHP runtime as a parent image
FROM php:7.4-apache

# Set the working directory in the container
WORKDIR /srv/sites/kbhff/kbhff_dk/theme/www

# Copy your PHP application code into the container
COPY . /srv/sites/kbhff/kbhff_dk/
# COPY /www /var/www

# Copy the apache configuration
COPY ./apache/httpd-vhosts.conf /etc/apache2/sites-available/000-default.conf

RUN mkdir -p /srv/sites/apache/logs/

# Install PHP extensions and other dependencies
# RUN apt-get update && \
#     apt-get install -y libpng-dev && \
#     docker-php-ext-install pdo pdo_mysql gd

# Expose the port Apache listens on
EXPOSE 80
