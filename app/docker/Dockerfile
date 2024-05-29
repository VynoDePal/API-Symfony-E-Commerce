FROM php:8.3-fpm

WORKDIR /var/www/

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions pdo_pgsql intl

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
    mv composer.phar /usr/local/bin/composer

RUN apt update && apt install -yqq nodejs npm

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y apache2 libapache2-mod-php && \
    # Nettoie les caches après installation
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

COPY . /var/www/

# Configure Apache pour servir votre application Symfony
RUN echo "ServerName localhost" > /etc/apache2/apache2.conf && \
    echo "<VirtualHost *:80>" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    DocumentRoot /var/www/public" >> /etc/apache2/sites-available/000-default.conf && \
    echo "    <Directory /var/www/public>" >> /etc/apache2/sites-available/000-default.conf && \
    echo "        Options Indexes FollowSymLinks MultiViews" >> /etc/apache2/sites-available/000-default.conf && \
    echo "        AllowOverride All" >> /etc/apache2/sites-available/000-default.conf && \
    echo "        Require all granted" >> /etc/apache2/sites-available/000-default.conf && \
    echo "</Directory>" >> /etc/apache2/sites-available/000-default.conf && \
    echo "</VirtualHost>" >> /etc/apache2/sites-available/000-default.conf && \
    a2enmod rewrite && \
    systemctl restart apache2

RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN cd /var/www && \
    composer install && \
    npm install --force

RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www

# Exécutez les migrations de l'application
RUN php bin/console doctrine:migrations:migrate --no-interaction

RUN php bin/console doctrine:migrations:diff --no-interaction

RUN php bin/console doctrine:migrations:migrate --no-interaction

EXPOSE 80

CMD ["apache2-foreground"]