# Étape de build
FROM composer:2 as build

WORKDIR /app

# Copier les fichiers composer
COPY composer.json composer.lock ./

# Installer les dépendances
RUN composer install --ignore-platform-reqs --prefer-dist --no-dev --no-scripts --no-progress --no-interaction

# Copier le reste des fichiers de l'application
COPY . .

# Exécuter les scripts composer
RUN composer dump-autoload --optimize

# Étape de production
FROM php:7.4.33-fpm-alpine

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install pdo pdo_mysql

# Installer les dépendances système nécessaires
RUN apk add --no-cache nginx supervisor

# Configurer Nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Configurer PHP
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini

# Configurer Supervisor
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Créer le répertoire de travail
WORKDIR /var/www/html

# Copier le code de l'application depuis l'étape de build
COPY --from=build /app .

# Créer le répertoire de stockage et ajuster les permissions
RUN mkdir -p storage/framework/{sessions,views,cache} \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Exposer le port 80
EXPOSE 80

# Démarrer les services avec Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
