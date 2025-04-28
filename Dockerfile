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
RUN apk add --no-cache nginx supervisor

# Configurer Nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configurer PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Configurer Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Créer le répertoire de travail
WORKDIR /var/www/html

# Copier le code depuis l'étape de build
COPY --from=build /app .

# Créer les répertoires nécessaires et ajuster les permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Exposer le port 80
EXPOSE 80

# Démarrer Supervisor en lui indiquant où trouver son fichier de configuration
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
