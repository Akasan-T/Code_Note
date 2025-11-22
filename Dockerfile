# ベースイメージ
FROM php:8.4

# 作業ディレクトリ
WORKDIR /workdir

# Composer インストール
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME="/opt/composer"
ENV PATH="$PATH:/opt/composer/vendor/bin"

# 必要パッケージ
RUN apt-get update && apt-get install -y zip unzip git

# PHP拡張
RUN docker-php-ext-install pdo_mysql

# Laravelディレクトリにコピー（ビルド時のみ）
COPY note-app/ ./note-app/

# Laravelディレクトリに移動
WORKDIR /workdir/note-app

# 依存関係をビルド時にインストール
RUN composer install --no-interaction --optimize-autoloader

# ポート開放
EXPOSE 9000

# サーバ起動
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9000"]