# syntax=docker/dockerfile:1

# ---------------------------------------------------------------------------
# Étape 1 — Assets front (Vite)
# Node n'existe que le temps de la compilation : l'image finale ne l'embarque
# pas. C'est aussi ce qui évite d'avoir à installer Node sur le serveur.
# ---------------------------------------------------------------------------
FROM node:22-alpine AS assets

WORKDIR /app

# Les dépendances d'abord : cette couche n'est reconstruite que si
# package-lock.json bouge, pas à chaque modification d'une vue.
COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources/ resources/

RUN npm run build


# ---------------------------------------------------------------------------
# Étape 2 — Dépendances PHP
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

# --no-scripts : les scripts post-install appellent artisan, qui n'existe pas
# encore à ce stade. L'autoloader est régénéré juste après la copie du code.
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction

COPY . .

RUN composer dump-autoload --no-dev --optimize --classmap-authoritative


# ---------------------------------------------------------------------------
# Étape 3 — Image d'exécution : nginx + PHP-FPM
# ---------------------------------------------------------------------------
FROM php:8.3-fpm-alpine AS runtime

# Dépendances de compilation retirées à la fin pour garder l'image légère.
RUN set -eux; \
    apk add --no-cache \
        nginx \
        supervisor \
        su-exec \
        libpng \
        libjpeg-turbo \
        libwebp \
        freetype \
        libzip \
        icu-libs; \
    apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        freetype-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j"$(nproc)" \
        gd \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        intl \
        bcmath \
        opcache \
        pcntl; \
    apk del --no-network .build-deps

WORKDIR /var/www/html

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/zz-app.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint
COPY docker/wait-for-db.php /usr/local/bin/wait-for-db.php
RUN chmod +x /usr/local/bin/entrypoint

# Le code, puis les artefacts des étapes précédentes.
COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /app/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

# storage/ et bootstrap/cache doivent être inscriptibles par PHP-FPM.
# Le contenu de storage/app/public sera monté sur un volume : on crée
# malgré tout l'arborescence pour que le conteneur démarre sans volume.
RUN set -eux; \
    mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod -R ug+rwX storage bootstrap/cache

EXPOSE 80

# Sonde utilisée par Dokploy : la route /up est fournie par Laravel.
HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/up") ? 0 : 1);'

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
