FROM php:8.3-apache

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN install-php-extensions pdo_pgsql intl

RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
    mv composer.phar /usr/local/bin/composer

RUN apt update && apt install -yqq nodejs npm

WORKDIR /var/www

COPY . /var/www

COPY ./apache.conf /etc/apache2/sites-available/000-default.conf

RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN cd /var/www && \
    composer install && \
    npm install --force

RUN chown -R www-data:www-data /var/www && \
    chmod -R 755 /var/www

RUN chmod +x /var/www/docker.sh

ENTRYPOINT ["/bin/bash", "docker.sh"]

CMD ["apache2-foreground"]

EXPOSE 80