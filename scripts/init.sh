#!/bin/sh

# Créer les dossiers nécessaires
mkdir -p /var/www/html/storage/logs/scheduler
touch /var/www/html/storage/logs/scheduler/advice-scheduled.log

# Définir les permissions
chmod -R 777 /var/www/html/storage/logs

# Configurer et démarrer cron
echo "* * * * * /usr/local/bin/php /var/www/html/artisan advices:send-scheduled >> /var/www/html/storage/logs/scheduler/advice-scheduled.log 2>&1" > /etc/crontabs/root
chmod 0644 /etc/crontabs/root

# Démarrer cron en premier plan
/usr/sbin/crond -f -l 8 &

# Démarrer supervisor
/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
