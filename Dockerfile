FROM registry.asap.dsna.fr/bruno.spyckerelle/epeires-test-images:php73

# Install system dependencies for PHP extensions
RUN apt-get update && apt-get install -qqy \
  # gd extension
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  # mcrypt extension
  libmcrypt-dev \
  # SOAP extension
  libxml2-dev \
  # intl extension
  libicu-dev \
  # Composer dependencies
  git \
  unzip

RUN docker-php-ext-install iconv soap intl pdo_mysql \
  && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
  && docker-php-ext-install gd

# Configure Apache
## Set document root
ENV APACHE_DOCUMENT_ROOT /epeires2/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
## Enable mod_rewrite
RUN a2enmod rewrite

# Install Epeires in /epeires2/
RUN mkdir /epeires2 && chown www-data:www-data /epeires2
USER www-data:www-data
WORKDIR /epeires2

COPY --chown=www-data:www-data composer.json composer.lock composer.phar /epeires2/

RUN php composer.phar install --no-dev --prefer-dist
RUN php composer.phar dump-autoload --optimize

COPY --chown=www-data:www-data . /epeires2

# Return to root to start apache2
USER root:root
