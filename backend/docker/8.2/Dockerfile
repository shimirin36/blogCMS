# PHP 8.2 + FPM (Docker Hub 公式)
FROM php:8.2-fpm

# 必要パッケージをインストール
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer インストール
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Xdebug インストールと設定
RUN pecl install xdebug && docker-php-ext-enable xdebug && \
    echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# 作業ディレクトリ
WORKDIR /var/www/html

# Laravel ソースをコピー
COPY . .

# 依存パッケージをインストール
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# php-fpm 起動
CMD ["php-fpm"]
