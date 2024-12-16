ARG PHP_VERSION="${PHP_VERSION:-8.2.18}"
ARG ALPINE_VERSION="${ALPINE_VERSION:-3.19}"

# docker.io/library/php:8.0.6-alpine3.12
# php:8.0.6-fpm-alpine3.13
FROM php:${PHP_VERSION}-fpm-alpine${ALPINE_VERSION} as php-base

# https://getcomposer.org/doc/03-cli.md#composer-allow-superuser
ENV COMPOSER_ALLOW_SUPERUSER 1

#COPY --from=composer /usr/bin/composer /usr/bin/composer
#COPY php.ini /usr/local/etc/php/php.ini
#COPY php-fpm.conf /usr/local/etc/php-fpm.d/php-fpm.conf
COPY healthcheck /usr/local/bin/healthcheck

WORKDIR /app

RUN apk update && apk add curl && \
  curl -sS https://getcomposer.org/installer | php \
  && chmod +x composer.phar && mv composer.phar /usr/local/bin/composer

RUN set -xe \
    && apk add --no-cache --virtual .persistent-deps \
        fcgi \
        git \
        icu-libs \
        zlib \
        postgresql-client

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        zlib-dev \
        postgresql-dev \
        mysql-client \
        sqlite-dev \
        libzip-dev

RUN docker-php-ext-install \
        intl \
        pgsql \
        mysqli \
        pdo \
        pdo_pgsql \
        pdo_mysql \
        pdo_sqlite \
        zip

RUN pecl install \
        apcu \
		mongodb \
		redis
RUN docker-php-ext-enable \
        apcu \
        opcache \
        mongodb \
        redis
RUN set -xe apk del .build-deps \
RUN set -xe composer clear-cache \
RUN set -xe chmod +x /usr/local/bin/healthcheck

RUN docker-php-ext-enable mysqli
RUN docker-php-ext-enable pgsql

# Install node.js
RUN apk add nodejs
RUN apk add npm
RUN npm install -g gulp-cli

RUN apk add bash
RUN apk add nano

HEALTHCHECK CMD /usr/local/bin/healthcheck

FROM php-base AS debug

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS
RUN apk add --update linux-headers
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

RUN apk add \
        libzip-dev \
        zip \
  && docker-php-ext-install zip
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.idekey=PHPSTORM" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

#COPY xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

CMD ["php-fpm"]
EXPOSE 9000

FROM php-base AS prod

CMD ["php-fpm"]
EXPOSE 9000