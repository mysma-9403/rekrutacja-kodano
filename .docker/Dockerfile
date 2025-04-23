FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    bash \
    git \
    curl \
    unzip \
    icu-dev \
    libzip-dev \
    oniguruma-dev \
    tzdata \
    mysql-client \
    supervisor \
    rabbitmq-c \
    rabbitmq-c-dev \
    autoconf \
    gcc \
    g++ \
    make \
    musl-dev

RUN docker-php-ext-install \
      intl \
      pdo \
      pdo_mysql \
      zip \
      opcache

RUN pecl install amqp \
 && docker-php-ext-enable amqp

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN curl -sS https://get.symfony.com/cli/installer | bash \
 && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html

COPY .docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY .docker/supervisor/messenger.conf   /etc/supervisor/conf.d/messenger.conf

RUN mkdir -p var && chown -R www-data:www-data var

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
