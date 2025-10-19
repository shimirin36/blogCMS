# PHP 8.2 + CLI ベース
FROM php:8.2-cli

# 必要パッケージをインストール
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Composer インストール
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Xdebug インストールと設定
RUN pecl install xdebug && docker-php-ext-enable xdebug && \
    echo "zend_extension=xdebug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.discover_client_host=false" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# 作業ディレクトリ
WORKDIR /var/www/html

# Laravel ソースをコピー
COPY . .

# 依存パッケージをインストール
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# 8000ポートを開放
EXPOSE 8000

# Laravel開発サーバー起動（Xdebug有効）
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
