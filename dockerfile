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

# Gives read/write permissions
RUN chown -R www-data:staff /srv/sites/kbhff/kbhff_dk


# Install useful tools and install important libaries
RUN apt-get -y update && \
    apt-get -y --no-install-recommends install nano wget \
    dialog \
    libsqlite3-dev \
    libsqlite3-0 && \
    apt-get -y --no-install-recommends install default-mysql-client

# Install PHP extensions and other dependencies
RUN docker-php-ext-install pdo_mysql && \
    docker-php-ext-install pdo_sqlite && \
    docker-php-ext-install mysqli

# Install PHP extensions and other dependencies
# RUN apt-get update && \
#     apt-get install -y libpng-dev && \
#     docker-php-ext-install pdo pdo_mysql gd

# Expose the port Apache listens on
EXPOSE 80
