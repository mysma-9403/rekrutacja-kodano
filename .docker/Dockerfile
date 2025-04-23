FROM php:8.3-fpm-alpine

# 1) zależności systemowe
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

# 2) rozszerzenia PHP
RUN docker-php-ext-install \
      intl \
      pdo \
      pdo_mysql \
      zip \
      opcache

# 3) PECL: amqp
RUN pecl install amqp \
 && docker-php-ext-enable amqp

# 4) Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5) Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
 && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# 6) Ujednolicamy katalog roboczy
WORKDIR /var/www/html

# 7) Konfiguracja supervisor’a
COPY .docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY .docker/supervisor/messenger.conf   /etc/supervisor/conf.d/messenger.conf

# 8) Uprawnienia na cache/logi
RUN mkdir -p var && chown -R www-data:www-data var

# 9) Domyślna komenda
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
