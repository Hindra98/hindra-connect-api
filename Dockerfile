# Dockerfile pour un projet PHP avec Slim Framework
# Image PHP officielle avec Apache
FROM php:8.2-apache

# Active mod_rewrite pour Slim
RUN a2enmod rewrite

# Installe les outils nécessaires
RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Installe Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Copie le projet dans le conteneur
COPY . /var/www/html

# Positionne le dossier de travail
WORKDIR /var/www/html

# Installe les dépendances PHP (production)
RUN composer install --no-dev --optimize-autoloader

# Copie la config Apache personnalisée
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Droits sur les fichiers
RUN chown -R www-data:www-data /var/www/html

# Expose le port HTTP
EXPOSE 80
