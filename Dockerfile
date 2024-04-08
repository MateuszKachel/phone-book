FROM php:8.2-apache

# Install SQLite
RUN apt-get update && \
    apt-get install -y sqlite3 libsqlite3-dev && \
    docker-php-ext-install pdo_sqlite && \
    a2enmod rewrite

# Copy application source
COPY . /var/www/html

# Change ownership of /var/www/html to www-data
RUN chown -R www-data:www-data /var/www/html

# Change document root for Apache
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80
