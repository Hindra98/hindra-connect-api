# Dockerfile
FROM php:8.2-apache

# Activer mod_rewrite pour Slim
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers de l'application dans le conteneur
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Définir les permissions
RUN chown -R www-data:www-data /var/www/html

# Copier la configuration Apache personnalisée
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Exposer le port utilisé par Apache
EXPOSE 80

# Lancer Apache en mode foreground
CMD ["apache2-foreground"]
