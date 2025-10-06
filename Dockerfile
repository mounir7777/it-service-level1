FROM php:8.2-apache

# Apache vorbereiten
RUN a2enmod rewrite headers

# DocumentRoot auf /var/www/html
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf /etc/apache2/apache2.conf

# Projekt kopieren
COPY . /var/www/html

# Rechte & PHP-Prod-Settings
RUN chown -R www-data:www-data /var/www/html \
 && echo "display_errors=0" > /usr/local/etc/php/conf.d/prod.ini \
 && echo "log_errors=1" >> /usr/local/etc/php/conf.d/prod.ini

EXPOSE 80
