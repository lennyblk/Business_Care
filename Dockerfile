# Étape 1: Build de l'application avec Composer
FROM composer:2 as build

WORKDIR /app

# Copier les fichiers composer
COPY composer.json composer.lock ./

# Installer les dépendances
RUN composer install --ignore-platform-reqs --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

# Copier le reste des fichiers de l'application
COPY . .

# Optimiser l'autoloader
RUN composer dump-autoload --optimize

# Étape 2: Image finale avec PHP, Nginx et Supervisor
FROM php:7.4-fpm-alpine

# Installer les extensions PHP nécessaires et les outils
RUN docker-php-ext-install pdo pdo_mysql
RUN apk add --no-cache nginx supervisor dcron

# Setup supervisor directories
RUN mkdir -p /var/run/supervisor \
    && mkdir -p /var/log/supervisor \
    && chmod -R 777 /var/run/supervisor

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Créer le répertoire de travail
WORKDIR /var/www/html

# Va chercher les fichiers qui se trouvent dans le répertoire /app de l'étape nommée build qui a installé les dépendances
COPY --from=build /app .

# Crée les répertoires pour les sessions, les vues, le cache et les logs
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && mkdir -p storage/logs \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /var/run \
    && mkdir -p /var/www/html/storage/logs/scheduler \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

# Installation des outils nécessaires en une seule commande
RUN apk add --no-cache nginx supervisor dcron

# Configuration des répertoires et permissions en une seule étape
RUN mkdir -p /var/run/supervisor \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /var/www/html/storage/logs/scheduler \
    && touch /var/www/html/storage/logs/scheduler/advice-scheduled.log \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 777 /var/run/supervisor \
    && touch /var/log/supervisor/cron.log \
    && touch /var/log/supervisor/cron.err

# Configuration de l'environnement cron
RUN chmod +x /var/www/html/artisan \
    && echo '#!/bin/sh\n/usr/local/bin/php /var/www/html/artisan "$@"' > /usr/local/bin/artisan \
    && chmod +x /usr/local/bin/artisan

# Configuration du crontab
COPY crontab /etc/crontabs/root
RUN chmod 0644 /etc/crontabs/root \
    && touch /var/log/crond.log

# Exposer le port 80
EXPOSE 80

# Démarrer Supervisor en lui indiquant où trouver son fichier de configuration
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
